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
      
      /* Search Engine for display of selected entries
         entries are display edaccording to current search-options, supports search for 
         different IPs, detected attack-strings,..   */
      class SearchEngine { 
          /* search pattern to look for   */
          protected $searchString;
          /* ip to search for   */
          protected $searchIp;
          /* id to search for   */
          protected $searchId;          
          /* start  for range-search, either by ID or DATE */
          protected $searchStart = 1;
          /* end for range-search, either by ID or DATE */
          protected $searchEnd = 1;
          /* array for selected-ids-search    */
          protected $searchIdArray = NULL;          
          
          /* supported search modes: 1 = IP                         (e.g. 127.0.0.1)
                                     2 = ATTACK                     (e.g. SQL)
                                     3 = ID                         (e.g. 55)
                                     4 = ID-Range                   (e.g. 55-130)
                                     5 = Comma Seperated IDs        (e.g. 55, 4 , 19 , 129)
                                     6 = Date-Range                 (e.g. 09-11-2006 01:18:00 to 09-12-2006 01:18:00)
                                         This depends on: - date format of Tigra-Javascript-Calendar
                                                              * European or American date-format-version of this Calendar
                                                          - mysql timestamp format     
                                     7 = ALL ATTACKS                ( e.g. "attacks", "ALL-attacks" )                                                         
                                     0 = OFF-show-all*/
          protected $mode = 0;
          
          /* store list of IDs, that have been created by the IP(arraykey)  */                    
          protected $attackCachePerIP = array();
          /* store list of Attacks, that refer to the ID(arraykey)  */
          protected $attackCachePerID = array();
          
          /* description for tooltips */
          private static $searchDescription = "The following search options are supported: <br> 1. Search for single ID ( e.g. \'315\' ) <br> 2. Search for ID Range  ( e.g. \'315-1500\' ) <br> 3. Search for ID Set  ( e.g. \'315, 400, 401, 1500\' ) <br> 4. Search for attack  ( e.g. \'SQL\' ) <br> 5. Search for all attacks detected ( e.g. \'ALL ATTACKS\' or \'ATTACKS\' ) <br> 6. Search for IP address ( e.g. \'207.46.232.182\' ) "; 
          
          /* constructor sets search-Strings/vars/array,        
             parameters: $active      :search deactivated if $active = "off"        */                    
          function __construct( $searchStr, $active = "on" ) {                 
                $this->searchString = "";                                            
                $this->mode = 0;
                
                if ( $active != "off")   
                    if ( is_array( $searchStr )) {    // look for DATE-Range (check first because Array handed over)                        
                        $this->mode = 6;                        
                          if ( isset( $searchStr['range1']) & isset( $searchStr['range2'])) {
                                // input-validation has to been done before usage!                                                     
                              $this->searchStart = $searchStr['range1'];                           
                              $this->searchEnd   = $searchStr['range2'];
                          } else { // range variables not set
                              $this->searchStart = 0;                              
                              $this->searchEnd   = 0;
                          } 
                         $this->searchString = "";                         
                    } else if ( $this->is_ip( $searchStr ) ) {                          // look for IP
                        $this->mode = 1;
                        $this->searchString = $searchStr;  
                        $this->searchIdArray = array();  
                        
                        $this->perform( );   // perform search, takes long time                                                    
                    } else if ( ereg( "^[0-9]+$", $searchStr )) {                       // look for single ID
                        $this->mode = 3;
                        $this->searchString = (int)$searchStr;                       
                    } else if ( ereg( "^[0-9]+-[0-9]+$", $searchStr )) {                // look for ID-range
                        $this->mode = 4;
                        $this->searchStart = (int)substr( $searchStr, 0, strpos( $searchStr, "-") );
                        $this->searchEnd   = (int)substr( $searchStr, strpos( $searchStr, "-") + 1 );                                                                
                    } else if ( ereg( "^[0-9]+((\ )*\,(\ )*[0-9]+)+$", $searchStr )) { // look for seperated IDs
                        $this->mode = 5;
                        $this->searchIdArray = explode ( "," , $searchStr );                                      
                        // make sure only integers are handed over to db
                        foreach ( $this->searchIdArray AS $key => $value )
                            $this->searchIdArray[ $key ] = (int)$value;                                                                                                                                     
                    } else if ( strcasecmp( $searchStr, "attacks" ) == 0 |               // look for ALL ATTACKS
                                strcasecmp( $searchStr, "all attacks" ) == 0 | 
                                strcasecmp( $searchStr, "all-attacks" ) == 0) {                 
                          $this->mode = 7;
                          $this->searchString = "";     
                          
                          $this->searchIdArray = array();
                          $this->perform( );  // perform search, takes long time                             
                                       
                    } else  {                                                              // look for ATTACK
                        $this->mode = 2;                                                           
                        $this->searchString = $searchStr;
                        
                        $this->searchIdArray = array();
                        $this->perform( );  // perform search, takes long time         
                        //echo print_r( $this->searchResults );                 
                    }                                                                      
                                                  
          }
          // performs search and fills cache-arrays
          //requires:  "sqlFunctions.php" and "attacklist.php" to be included
          private function perform( $addToQuery = "" ) {            
                  // object with attack-arrays
                  $attacks = new attacklist();
                  // becomes true as soon as at least one attack per table was found
                  $detected = false;    
                  // string containing names of detected attacks, for printing out 
                  $detectionStr = attacklist::getNoDetectionStr();                                              
               
                  // set maximum time limit for execution of this script
                  set_time_limit( 300 );
                               
                  // create list of tables to search through
                  $table_array = array();  
                  // server_vars MUST be first -> configure $moduleName  + as identification for tarrayID=0
                  array_push( $table_array, "Value_Server", "Value_Get", "Value_Post", "Value_Cookie" );        
                  
                  $sql = "SELECT ID, attackerIP, attackerBrowser, Source, Value_Server, Value_Get, 
                                     Value_Post, Value_Cookie, Module, Creation FROM 
                                      main_logs                                             
                                ORDER BY
                                      ID DESC";            
                   $result_serv = mysql_query($sql) OR die(mysql_error());
         
                  // go through server_vars
                 if ( mysql_num_rows( $result_serv )) {         
                    while($row = mysql_fetch_assoc( $result_serv )) {            
                        $id = abs((int)$row['ID']);
                          
                        // init variables for attack-detection  ( as new table starts )
                        $detected = false;     
                        $detectionStr = attacklist::getNoDetectionStr();   
                        
                        // initialize variables for storage of $_SERVER-data
                        $referrer = "no referrer";
                        $remote_address = "no remote ip";
                        $http_user_agent = "";
                        $source = "unknown";
                        $creationTime = "time unknown";                         
                                                
                        $tableString = "";

              
                        // for each table create output-string and finally print it out
                        foreach ( $table_array AS $tarrayId => $tarrayVal ) {
                            $outputString[ $tarrayId ] = "";                                                      
                                                                                    
                                   $valueArray = explode( ";semcl", $row[$tarrayVal] );   // split into variable pairs x=bla                            
                                   
                                  // for each row: split it into variable pairs and print them out   
                                  foreach ( $valueArray AS $arrKey => $arrVal) { 
                                      if ( $arrVal != "" ) {
                                          $oneLine = explode( "=", $arrVal , 2);    // split into variable and value   
                                          
                                          // filter $_SERVER array
                                          $leftSide =  isset($oneLine[0]) ? $oneLine[0] : "";
                                          $rightSide = isset($oneLine[1]) ? $oneLine[1] : "";                                                        
                                          if ( $tarrayId == 0 ) {                                                                                                                             
                                              $source = $row['Source'];   
                                              $creationTime = $row['Creation'];
                                              $addonModuleName = $row['Module'];    
                                              
                                              // check for proxy-server
                                             // if (isProxy( $leftSide, $rightSide ))
                                              //    $detectionStr = "PROXY";                                      
                                              
                                              if ( isset( $row['attackerIP'] ) && $row['attackerIP'] != "" )
                                                  $remote_address = $row['attackerIP'];               
                                              // next 2 lines just for compatibility reasons:
                                              if ( $remote_address == "no remote ip" && $leftSide == "REMOTE_ADDR" )                                                            
                                                  $remote_address = $rightSide; //."OLD";                     
                                              
                                              if ( $leftSide == "HTTP_REFERER")                                             
                                                 $referrer =  $rightSide ;                                                                        
                                                                   
                                              if ( isset( $row['attackerBrowser'] ) && $row['attackerBrowser'] != "" )
                                                  $http_user_agent = $row['attackerBrowser'];
                                              // next 2 lines just for compatibility reasons:                                   
                                              if ( $leftSide == "HTTP_USER_AGENT")         
                                                  $http_user_agent = $rightSide;                                                                                                        
                                          } else {                                                                          
                                              $tmpAttack = attackChecking( $leftSide, $rightSide, $attacks, false );                                                
                                              if ( $tmpAttack !== -1) {   // attack found                                                      
                                                   if ( $detected === false )
                                                        $detectionStr = "";          
                                                    $detected = true;     
                                                    if ( (strpos( $detectionStr, $tmpAttack) === false ) ) {   // add each new-found attack only once                                                                                                                    
                                                          $detectionStr .= $tmpAttack." ";    
                                                          
                                                          // ADDED this for mapping: store attacks per ID in Array
                                                          if ( !isset($this->attackCachePerIP[ $id ]))   
                                                                 $this->attackCachePerID[ $id ] = array();              
                                                          array_push( $this->attackCachePerID[ $id ], $tmpAttack );
                                                        
                                                          // ADDED this for search 
                                                          if ( $this->mode == 7 )   // all attacks
                                                              array_push( $this->searchIdArray, $id );
                                                          else                      // specific attack
                                                            if ( $this-> mode == 2 & ((strcasecmp( $tmpAttack, $this->searchString ) == 0 ))  ) {
                                                                  array_push( $this->searchIdArray, $id );                                                                                              
                                                                  }                                                                                                                                                                                                                                                                                                                                                  
                                                    }                                                                                                                        
                                              }                                             
                                          }                                                                                                                     
                                      }                     
                                  } // end: foreach row  
                                  // ADDED this for search                                  
                                  if ( $this->mode == 1 && $this->is_ip( $remote_address ))
                                      if ( (strcasecmp( $remote_address, $this->searchString ) == 0 ))
                                          array_push( $this->searchIdArray, (int)$id );                                                                                                                
                        }//foreach table     
                         
                        // -----ADDED this for mapping: store IDs per IP in Array
                                               if ( !isset($this->attackCachePerIP[ $remote_address ]))  
                                                      $this->attackCachePerIP[ $remote_address ] = array();
                                               array_push( $this->attackCachePerIP[ $remote_address ], $id );
                        // -----                                                               
                      } // end: while
                      
                      // remove duplicate ids from ip-array , saves memory
                      $this->attackCachePerIP[ $remote_address ] = array_unique( $this->attackCachePerIP[ $remote_address ] );
                  } // end if                  
           } 
           
           
          /* fills the $this->$attackCachePerID -Array for current searchresults  
             return void                                          */          
          public function queryAttacksForResults() {
                $this->perform( $this->addQuery());
          }
                  
          /* converts string from Tigra-Javascript-Calendar EUROPEAN-Version 
             returns: mysql-datetimestamp   */
          public static function string2dateEuropean( $str ) {
               return ( (int)substr( $str, 6, 10 )."-". (int)substr( $str, 3, 5 )."-". 
                        (int)substr( $str, 0, 2 )." ". 
                        (int)substr( $str, 11, 13 ).":".(int)substr( $str, 14, 16 ).":".(int)substr( $str,17, 19));
          }      
               
          /* adds 'WHERE'-part of sql-query, dependent on the current mode 
             returns: for mode 3, 4, 5 and 6 -> string with 'where'-clause looking for ID or Creation column       
                                        else -> empty String                                                     */
          public function addQuery() {              
              if ( $this->mode == 3 )   // look for ID
                  return "WHERE ID = ".(int)$this->searchString;
                  
              else if ( $this->mode == 4 )   // look for ID-Range
                  return "WHERE ID >= ".(int)$this->searchStart." AND 
                                ID <= ".(int)$this->searchEnd;

              // look for DATE-Range                                
              else if ( $this->mode == 6 ) { // // input-validation of range-varws has to been done before usage
                  return "WHERE Creation >= '".$this->string2dateEuropean( $this->searchStart )."' AND 
                                Creation <= '".$this->string2dateEuropean( $this->searchEnd   )."' ";
              // look for IP, Attack, SELECTED IDs, ALL-Attacks                  
              } else if ( $this->mode == 1 | $this->mode == 2 | $this->mode == 5 | $this->mode == 7 ) {
                  $returnStr = "WHERE ID IN (";
                  if ( is_array( $this->searchIdArray ) & $this->searchIdArray != NULL & (count($this->searchIdArray) > 0 ))
                      foreach ( $this->searchIdArray as $key => $value )
                          $returnStr .= ($key == 0) ? (int)$value : (",".(int)$value); 
                  else
                      $returnStr .= "-1";   // array is empty -> nothing is found -> don't display any ID                                                             
                  return $returnStr.")";                  
              } else
                    return "";             // invalid mode, display all IDs
          }
        
                
          // returns the current search-mode
          public function getMode() {
              return (isset( $this->mode ) ? $this->mode : -1 );
          }          
          // sets the current search-mode                          
          public function setMode( $newMode ) {
              if ( $newMode >= 0 & $newMode <= 7 )
                  $this->mode = (int)$newMode;
          }    
          
          // returns the current array with search-results (IDs to show)
          public function getSearchIdArray() {
              return $this->searchIdArray;
          }                 
          // sets the current array with search-results (IDs to show)
          public function setSearchIdArray( $newArray ) {
                if ( is_array($newArray) & count( $newArray ) > 0 ) {
                    $this->searchIdArray = array();
                    foreach ( $newArray as $key => $val )
                        array_push( $this->searchIdArray, abs( (int)$val ) );
                }                                  
          }
          
          // returns the current index where to start the search , can be int or timestamp-string
          public function getSearchStart() {
              return $this->searchStart;
          }
           /*  sets the current index where to start the search , can be int or timestamp-string
               NO VALIDITY CHECK done here in order to be able to receive arbitrary data from session!!!!    */
          public function setSearchStart( $newStart ) {
              if ( $newStart > 0 )
                  $this->searchStart = $newStart;
          }
          
          // returns the current index where to end the search , can be int or timestamp-string
          public function getSearchEnd() {
              return $this->searchEnd;
          }
          /*  sets the current index where to end the search , can be int or timestamp-string
              NO VALIDITY CHECK done here in order to be able to receive arbitrary data from session!!!!    */
          public function setSearchEnd( $newEnd ) {
              if ( $newEnd > 0 )
                  $this->searchEnd = $newEnd;
          }
          
          // returns the current search string
          public function getSearchString(){
              return $this->searchString;
          }
          /* sets the current serach string, only used for single-IDs: only Int                   */
          public function setSearchString( $newStr ) {
              $this->searchString = (int)$newStr;
          }             
          
          public function getAttacksPerID( $id ){
                return $this->attackCachePerID[ (int)$id ];
          }
          public function getAttacksPerIP( $ip ){
                if ( IPmapper::is_ip( $ip))
                    return isset( $this->attackCachePerIP[$ip] ) ? array_unique( $this->attackCachePerIP[$ip ] ) : false; 
                else
                    return false;
          }
          
        /* returns true if given ip is a valid ip-address of any network (local or non-local) */
        public static function is_ip($ip) {
            if ( is_array( $ip ))
                return false;          
            if (ereg("^[0-9]+(\.[0-9]+){3}$",$ip)) {         
                $validip = true; 
                foreach(explode(".", $ip) as $nextblock) {
                  if( $nextblock<0 || $nextblock>255 )
                   {           
                    $validip = false;
                   }
                 }
            }else 
                $validip = false;         
            return $validip;
        }
        
        /* returns tooltip description   */
        public static function getSearchDescription() {
            return self::$searchDescription;
        }
                         
              
      }

?>
