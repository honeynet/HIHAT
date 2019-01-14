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

    define( 'MAP_SECURE', 'active' );
    
    $textOfMappingButton = '-> map';  // text of mapping button, defined in details.php, required for identification of click
    
    // set options for banner     
    $menu_selection = 4;
    $no_logo = true;
       
    // selects source-db for mapping: 1 = ip2location, 2 = hostip.info 
    $mappingMode = 1;
    
    // search-results
    $counterIpsNotFound = 0;
    $counterIpsSuccess  = 0;
    $counterIpsAlreadyCached = 0;
    $counterIpsPrivate  = 0;
    
    // becomes true if unkonwn-string is entered, etc.
    $nothingFound = false;        
    
    // set maximum time limit for execution of this script
    set_time_limit( 180 );
    
    // include stuff for IP-Mapping
    include_once "IPmapper.php";
    include_once "searchEngine.php";
    
    // tooltip for search
    $search4TimeDescription = searchEngine::getSearchDescription();
    
    // include stuff for searchEngine
    include_once "attacklist.php";

    include_once "filters.php";
    
    session_start();
    include "inc/config.php";
    
    // Verbindung zu MySQL Aufbauen
    @mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) OR die(mysql_error());
    //mysql_select_db("ip2location") OR die(mysql_error());

    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
    echo "<html>\n";
    echo "    <head>\n";
    echo "        <title>HIHAT - High Interaction Honeypot Analysis Tool</title>\n";
    echo "        <link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n";
    echo '<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
          <link rel="icon" href="images/favicon.ico" type="image/x-icon">';    
    echo "        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\" />\n";    
    
    echo "    </head>\n";
    echo "    <body>\n";
    echo " <noscript><b>JavaScript must be enabled in order for you to use the MAP.<br></b>However, it seems JavaScript is either disabled or not supported by your browser. To view the MAP, enable JavaScript by changing your browser options, and then try again. </noscript>";
       
    include "banner.php";          
    
       
     
    // SESSION and POSTVAR-MANAGEMENT
    //-------------------------------
    // erase old session if button was pushed
    if ( isset( $_POST['hiddenReset'] )){
        unset( $_SESSION['ip']);         
    };

     
    // check if mapping-data is already stored in session
    if ( isset( $_SESSION['ip'] )) {
        $mapper = $_SESSION['ip'];
        if (! $mapper instanceof IPmapper )
            $mapper = new IPmapper( 0 );    
    } else {
        $mapper = new IPmapper( $mappingMode );             
        $_SESSION['ip'] = $mapper;
    }
    
    // keep ID during lookup-operations
    $idStorage = 1;         
    if ( isset( $_POST['hiddenId'] )){
        $idStorage = abs((int)$_POST['hiddenId']);
    };    
    
    // perform lookup for IPs in database  : MAIN PART
    if ( isset( $_POST['queryValues'] )){
        mysql_select_db(MYSQL_DATABASE) OR die(mysql_error());
        $searchEngine = new searchEngine( $_POST['queryValues'], "on" );
        $searchMode = $searchEngine->getMode();   
        
        // look up attacks to display on google-map ,  store attacks and ips for mapping
        // OLD VERSION: search was performed twice?! - remove this
        //$searchEngine->queryAttacksForResults();
                     
    
        // add IP: IP not in id-database, just look up the lat-long location
        if ( $searchMode == 1 && $searchEngine->addQuery() == "WHERE ID IN (-1)" ) { 
                if ( !IPmapper::is_private_ip( $searchEngine->getSearchString() )) {     // if not private IP: add location
                    mysql_select_db(MYSQL_IPLOOKUP_DB) OR die(mysql_error());
                    $mapper->setMode( $mappingMode );
                    $entryTempId = -1 * abs($mapper->getMinimumKey() - 1);    // calculate new ID that's not yet used
                    $retvalue = $mapper->addIP( $searchEngine->getSearchString(), $entryTempId , array("--"), array("---") );
                                                    
                    switch ($retvalue) {
                        case 0 : $counterIpsNotFound++; break;
                        case 1 : $counterIpsSuccess++; break;
                        case 2 : $counterIpsAlreadyCached++; break;
                        case 3 : $counterIpsPrivate++; break;
                    }
                } else { 
                      $counterIpsPrivate++; 
                }
        } 
        else      // add results of serach for IP(ip found in db), Attack, ID, ID-Range, Selected-IDs or all-attacks (no date-range supported)
              if ( $searchMode >= 1 && $searchMode <= 7 && $searchMode != 6 ) {
                $sql = "SELECT ID, attackerIP, Creation, Module FROM main_logs "
                         .$searchEngine->addQuery()."                
                          ORDER BY
                                ID DESC";  //echo $sql."<br> ";                                                                          
                                                  
                $result_serv = mysql_query($sql) OR die(mysql_error());        
                $ipArray  = array(); // array holding all IPs to look up
                $ipAndAttacksArray = array();
                
                // look up attacks to display on google-map ,  store attacks and ips for mapping
                // look up attacks for ids + ips
                    //if ( $searchMode == 1 | $searchMode == 2 | $searchMode == 7  )  // OLD VERSION: search was performed twice?! - remove this
                $searchEngine->queryAttacksForResults();
                    
                if ( mysql_num_rows( $result_serv )) {         
                      while($row = mysql_fetch_assoc( $result_serv )) {   // look up IPs for the required ids
                          $tempIP = isset( $row['attackerIP'] ) ? $row['attackerIP'] : ""; 
                          $tempID = isset( $row['ID'] )         ? abs((int)$row['ID'])         : "";
                          $ipArray[ $tempID ] = $tempIP;                                                                                                                                                                        
                      }                                    
                           
                      // set back from ip2location-db to lookup-server db 
                      mysql_select_db(MYSQL_IPLOOKUP_DB) OR die(mysql_error());
                      $mapper->setMode( $mappingMode );
                                  
                      foreach( $ipArray as $key => $valIP ) { 
                          // don't try to map private IPs
                          if ( !($searchMode == 1 && IPmapper::is_private_ip($valIP)) ) { 
                              $retvalue = $mapper->addIP( $valIP, $key, $searchEngine->getAttacksPerID( $key ),
                                                                        $searchEngine->getAttacksPerIP($valIP));   // query database for these IPs and add to mapper
                               
                              switch ($retvalue) {
                                  case 0 : $counterIpsNotFound++; break;
                                  case 1 : $counterIpsSuccess++; break;
                                  case 2 : $counterIpsAlreadyCached++; break;
                                  case 3 : $counterIpsPrivate++; break;
                              }                              
                          } else 
                              $counterIpsPrivate++;
                     } 
                } else
                      $nothingFound = true; //echo "no data found";                     
            } else    ;//echo "WRONG MODE ";
                                    
    } else           // if transferred from details: look up IP 
      if ( isset( $_POST['hiddenId'] ) && isset( $_POST['hiddenIP']) && 
           isset( $_POST['submitId'])  && isset( $_POST['hiddenDetections'] )) {  
          if ( strcmp( $_POST['submitId'], $textOfMappingButton ) == 0 && IPmapper::is_ip($_POST['hiddenIP'] )) {
               mysql_select_db(MYSQL_IPLOOKUP_DB) OR die(mysql_error());         
               $mapper->setMode( $mappingMode );
               $detectionStr = htmlentities( $_POST['hiddenDetections'] );    // make sure detection-string is CLEAN 
               $mapper->addIP( $_POST['hiddenIP'], (int)$_POST['hiddenId'], array( $detectionStr ), array((int)$_POST['hiddenId']) );
          }                  
    }
                
    // keep all data for printing on map
    $ipData = $mapper->getAllIpAndDataArray();    
       //echo "DA:"; print_r( $ipData);echo "<br>";
    
  

  
    // start table                 
    echo "<table> <tr> <td>";       
                       
    // javascript for google-maps : print MAIN-MAP
    include "printMap.php";         
    // print actual map
    echo '  <div id="map" class="map_element" style="width: 850px; height: 500px"></div>';      
                       
    echo '</td><td valign="top">';      
                        
            // Mappping-Navigation: print search-form 
    echo '<br>Search:<form name="mapIDs" action="mapping.php" method="post" class="formular">
              <input type="text" name="queryValues" value="enter id(s), ip or attack"               
                                onclick="this.form.queryValues.value = \'\' " onmouseover="return escape( \''.$search4TimeDescription.'\' )" />
              <input type="hidden" name="hiddenId" value="'.$idStorage.'">
              <input type="submit" name="submitMap" value="add Query to Database" />                                                           
              </form><br>
          ';  
    // print reset button          
    echo '<br><form name="mapIDs" action="mapping.php" method="post" class="formular">
              <input type="hidden" name="hiddenReset" value="reset">
              <input type="hidden" name="hiddenId" value="'.$idStorage.'">
              <input type="submit" name="submitReset" value="reset mapdata-cache" 
                           onmouseover="return escape( \'Removes all lavels from map and clears cache\' )"/>                                                           
              </form><br>
    ';      
     // summarize search-results  
     if ( isset( $_POST['queryValues'] )) {              
         echo "<u>Searchresults:</u><br>";
         if ($nothingFound)
            echo "no results<br><br>";
         else 
            echo "Found:".$counterIpsSuccess.
             "<br>Found in Cache:".$counterIpsAlreadyCached."<br>IPs not found:".$counterIpsNotFound."<br>Private IPs:".$counterIpsPrivate."<br><br>";
    }    
    // print link-back
    echo "\n <br><a href=\"detailFramework.php?id=".$idStorage."\"><img src=\"images/backtodetails.png\" border=\"0\" alt=\"back to details\" /></a> \n ";       
            
    // end table
    echo "</td></tr></table>";
            
    //echo '<a href="http://cert.uni-stuttgart.de/stats/dns-replication.php?query='.$remote_address.'&submit=Query" onmouseover="return escape( \'bla\' )" target="_blank" >reverse-dns information</a>';
    echo '<script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script>';
            
    echo '  </body> </html> ';
      
  // store current data in Session
  $_SESSION['ip'] = $mapper;
  
?>      
