<?php
/* This file is part of HIHAT v1.1
   ================================
   Copyright (c) 2007 HIHAT-Project                   
  
  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.
  
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
  
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 
*/


    /* performs mapping between an IP-address and the geographical data like
                                                    longitude, latidude, etc.                                              
       requires: www.hostip.info as service provider                  */
    class IPmapper { 
        // url to the api of the service provider
        private $hostip_url1 = "http://api.hostip.info/get_html.php?ip=";
        private $hostip_url2 = "&position=true";
        
        /* supported lookup-modes and Datasources: 0 = off, 1 = ip2location(SQL), 2 = www.hostip.info (Web)  */
        protected $mode = 0;
        
        /* holds MAIN DATA per id:  countryLONG, countrySHORT', ipCITY, ipREGION, 
                                    ipLONGITUDE, ipLATITUDE, IP , attackArray                      */
        protected $allIPandDataArray = array();
        
        // true if connection to service-provider was successful
        protected $init_ok = FALSE;
    
        /* calls the $hostip_url with the given $ip -address and stores the result in the
                      main variables of this object
           supported lookup-modes and Datasources: 0 = off, 1 = ip2location(SQL), 2 = www.hostip.info (Web) 
           if error occurs $init_ok is called                                           */           
        function __construct( $mode = 0 ) {   
            if ( $mode == 0 | $mode == 1 | $mode == 2 ) 
                $this->mode = $mode;             
        }
        
        /* returns true if given ip is a valid ip-address of any network (local or non-local) */
        public static function is_ip($ip) {          
            if (ereg("^[0-9]+(.[0-9]+){3}$",$ip)) {         
                $validip = TRUE;
                foreach(explode(".", $ip) as $nextblock) {
                  if( $nextblock<0 || $nextblock>255 )
                   {           
                    $validip = FALSE;
                   }
                 }
            }else 
                $validip = FALSE;         
            return $validip;
        }
        /* returns true if given ip is in reserved private, private-automatic range or localhost     */
        public static function is_private_ip( $ip ) {
            if (ereg("^((127)|(172\.16)|(192)|(10\.)|(169\.254))", $ip ))
                return IPmapper::is_ip( $ip );
            else
                return false;
        }
        
        // returns latitude value of this object
        public function getLat() {
            return $this->latitude;
        }
        
        // returns longitude value of this object
        public function getLong() {
            return $this->longitude;
        }           
        
        // returns city value of this object
        public function getCity() {
            return $this->city;
        }   
        
        // returns country value of this object
        public function getCountry() {
            return $this->country;
        }   
        
        // returns the current mode
        public function getMode() {
            return $this->mode;
        }
        
        // sets the current mode
        public function setMode( $newMode ) {
            $newMode = abs((int)$newMode);
            if ( $newMode >= 0 & $newMode <= 2 )
                $this->mode = $newMode;
        }
        
        /* transforms ip to longbigint, required for ip2location  */
        private function LongIP2int ($IPaddr){
            if ($IPaddr == "" || !$this->is_ip($IPaddr)) {
                return 0;
            } else {
                $ips = split ("\.", "$IPaddr");
                return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256);
            }
        }
        /* transforms longbigint to ip, required for ip2location    */
        private function BigInt2ip( $bigint ){
            return ((int)( $bigint / 16777216 ) % 256).".".((int)( $bigint / 65536 ) % 256).".".
                    ((int)( $bigint / 256 ) % 256).".".((int)(( $bigint ) % 256)+256);            
        }
        
        /* performs lookup in ip2location-sql-database and fetches Longitude, Latitude, Country, Region + City as available
           @param: $ip - source ip to look up
                   $id - entry ID of this ip
           @effect: fills main array $this->allIPandDataArray
           @return: 1 if longitude + latitude found successfully, 0 else                                       */                                  
        private function lookupIP2LOC( $ip, $id, $attackArray, $attackIDArray ){
            $returnval = 0;
                   
                $sql = "SELECT 
                          ipFROM, ipTO, countrySHORT, countryLONG, ipREGION, ipCITY, ipLONGITUDE, ipLATITUDE
                        FROM ip2location
                        WHERE
                          (ipFROM <= ".$this->LongIP2int($ip).") AND (ipTO >= ".$this->LongIP2int($ip).")                   
                "; 
                   
                $result = mysql_query($sql) OR die(mysql_error());
                if(mysql_num_rows($result)) {
                    while($row = mysql_fetch_assoc($result)) {
               
                        $countryLONG  = isset( $row['countryLONG'] )  ? $row['countryLONG']  : "";
                        $countrySHORT = isset( $row['countrySHORT'] ) ? $row['countrySHORT'] : ""; 
                        $ipCITY       = isset( $row['ipCITY'] )       ? $row['ipCITY']       : "";
                        $ipREGION     = isset( $row['ipREGION'] )     ? $row['ipREGION']     : "";                        
                        $ipLONGITUDE  = isset( $row['ipLONGITUDE'] )  ? $row['ipLONGITUDE']  : "";
                        $ipLATITUDE   = isset( $row['ipLATITUDE'] )   ? $row['ipLATITUDE']   : "";
                                         
                        $dataPerIpArray = array( 'countryLONG' => $countryLONG , 
                                                'countrySHORT' => $countrySHORT, 
                                                'ipCITY' => $ipCITY, 
                                                'ipREGION' => $ipREGION,                                                 
                                                'ipLONGITUDE' => $ipLONGITUDE, 
                                                'ipLATITUDE' => $ipLATITUDE,
                                                'IP'        => $ip,
                                                'attacks' => $attackArray,
                                                'ids'     => $attackIDArray );
                                                 
                              
                        if ( !($ipLONGITUDE == "" || $ipLATITUDE == "" || 
                              ($ipLONGITUDE == 0 && $ipLATITUDE == 0 && $countryLONG == "-" ))) {
                            $returnval = 1;                                                       // lookup successful    
                            $this->allIPandDataArray[ (int)$id ] = $dataPerIpArray;     
                        }
                    }                
                }
                return $returnval;
        }
        
       /* performs lookup at http://www.hostip.info and fetches Longitude, Latitude, Country + City as available
       @param: $ip - source ip to look up
               $id - entry ID of this ip
       @effect: fills main array $this->allIPandDataArray
       @return: 1 if longitude + latitude found successfully, 0 else                                       */
        private function lookupHOSTIPINFO( $ip, $id, $attackArray, $attackIDArray ) {
                  $returnval = 0;                  
                  $dataFile = fopen( $this->hostip_url1 . $ip . $this->hostip_url2, "r" ) ;      
                  if ( $dataFile ) {            
                       while (!feof($dataFile)) {
                           $buffer = fgets($dataFile, 1024);                  
                       
                           if ( substr( $buffer, 0, 8) == "Latitude" )
                              $lat = substr( $buffer, 10, 9 );
                           else if (  substr( $buffer, 0, 9) == "Longitude" )
                              $long = substr( $buffer, 11, 9 );
                           else if (  substr( $buffer, 0, 7) == "Country" )
                              $country = substr( $buffer, 9, strlen($buffer) - 9 );
                           else if (  substr( $buffer, 0, 4) == "City" )
                              $city = substr( $buffer, 6, strlen($buffer) - 6 );                                                                                                               
                       }
                       $dataPerIpArray = array( 'countryLONG' => isset($country) ? $country : "",
                                                   'countrySHORT'=> "", 
                                                   'ipCITY'      => isset($city) ? $city : "", 
                                                   'ipREGION'    => "",                                                 
                                                   'ipLONGITUDE' => isset($long) ? $long : "", 
                                                   'ipLATITUDE'  => isset($lat) ? $lat : "",
                                                   'IP'          => $ip,
                                                   'attacks' => $attackArray,
                                                   'ids'     => $attackIDArray );
                                                                   
                       $this->allIPandDataArray[ (int)$id ] = $dataPerIpArray;       
                       fclose($dataFile);
                       if ( $lat != "" && $long != "" )
                            $returnval = 1; // lookup successful
                  };
                  return $returnval;
        }
        
        /* returns 1 if lookup successful, 2 if lookup already in db, 
                   3 if private ip ->  ip not added,
                   0 else                                                         */ 
        public function addIP( $ip, $id, $attackArray, $attackIDArray ){   
            $lookupNeeded = true;
            $id = (int)$id;         
            if ( $this->is_ip( $ip ) && !$this->is_private_ip( $ip )) {                           
                  foreach ( $this->allIPandDataArray as $key => $val ) {   // check if IP already in Array....
                      if ( is_array($val)){
                          $tempIP = isset( $val['IP'] ) ? $val['IP'] : "no ip";
                          if ( !(strpos( $ip, $tempIP ) === false )) {     // if found:add copy for new id to last finding
                                $this->allIPandDataArray[ $id ] = $this->allIPandDataArray[ $key ];                                
                                $lookupNeeded = false;                 
                          }
                      } else
                          echo "array problem in IPmapper";
                  }
            
                  if ( $lookupNeeded ) {                                         // ....else lookup new IP                     
                      if ( $this->mode == 1 )                       
                          return $this->lookupIP2LOC( $ip, $id, $attackArray, $attackIDArray );
                      else if ( $this->mode == 2 )
                          return $this->lookupHOSTIPINFO( $ip, $id, $attackArray, $attackIDArray );     
                  } else
                      return 2;       // entry already in database 
            } else if ( $this->is_private_ip( $ip ))
                    return 3;
              else
                  ;//echo "NO VALID IP".$ip." ";    
            return 0;        
        }
        
        // returns main array with already-fetched-data
        public function getAllIpAndDataArray() {
            return $this->allIPandDataArray;
        }
        // returns minimum key 
        public function getMinimumKey() {
            if ( count($this->allIPandDataArray) > 0 )
                return min( array_keys( $this->allIPandDataArray ));
            else
                return 0;
        }
        
        
    } 

?>
