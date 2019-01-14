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

     if ( !defined( 'OV_SECURE' ))
        die('You cannot call this file directly!');
              
    function isProxy( $leftSide, $rightSide ) {
      if ( !(strcasecmp( $leftSide, "" ) == 0 || strcasecmp( $rightSide, "" ) == 0 ))  
        if (( strcasecmp( $leftSide, "HTTP_CLIENT_IP" ) == 0 ) ||
            ( strcasecmp( $leftSide, "HTTP_X_FORWARDED_FOR" ) == 0 ) || 
            ( strcasecmp( $leftSide, "HTTP_VIA" ) == 0 ))               
            return true;
        else
            return false; 
    }
    
      // apply url-deocding function to strings in referrer before printing      
     if (defined('URL_DECODE') && URL_DECODE == true )       // must be set in constant.php
          $decode_urls = true;
     else 
          $decode_urls = false;
           //$STARTZEIT = (float)time();
     // default mode for overview-listing
     $defaultMode = 2;
     
           
     // break referrer after x characters
     $wwrapReferrer = 125;     
     $wwrapOverview = 130;
     
    // identifies browser out of user-agent string
    include "browserCheck.php"; 
  
    // include filtering-functions   
    include "filters.php";   
    
    // include functions for table output
    include "printTable.php";
    
    // include list of attacks
    include "attacklist.php";    
    // object with attack-arrays
    $attacks = new attacklist();
    // becomes true as soon as at least one attack per table was found
    $detected = false;    
    // string containing names of detected attacks, for printing out 
    $detectionStr = attacklist::getNoDetectionStr(); 
    // show images for detected attacks , instead of strings
    $showImages = true; 
     // string containing the summarizing table
    $tableString = "";    
    
    // show Empty variables
    $showEmpty = true;
        
    // Character to put into output-string between left and right side of variables
    $varSeparationSign = " = ";      

     // init table-color-vars
    $old_remote_addr = "";
    $colorValue = false;

    // INCLUDE MODULES    
    include "moduleHandler.php";
    $moduleHandler = new moduleHandler();    
    $addonModule = NULL;
    $addonModuleName = "";

    // activate search-function
    include "searchEngine.php";            

    // SESSION-MANAGEMENT
    //-------------------
    // erase old session
    if ( isset( $_GET['delete'] )){
        unset( $_SESSION['sIdArray'] );
        unset( $_SESSION['sMode'] );
        unset( $_SESSION['sStart'] );
        unset( $_SESSION['sEnd'] );
        unset( $_SESSION['sString'] );        
        //unset( $_SESSION['ip'] );            
    } ;         
        
    // look for single search-string 
    if ( isset( $_POST['submitSearch'] )  &  isset( $_POST['attackStr']))  {          
        $searchEngine = new searchEngine( $_POST['attackStr'], "on" );
 
        unset( $_SESSION['sIdArray'] );
        unset( $_SESSION['sMode'] );
        unset( $_SESSION['sStart'] );
        unset( $_SESSION['sEnd'] );
        unset( $_SESSION['sString'] );
        $_SESSION['sIdArray'] =  $searchEngine->getSearchIdArray();
        $_SESSION['sMode']    =  $searchEngine->getMode();
        $_SESSION['sStart']   =  $searchEngine->getSearchStart();
        $_SESSION['sEnd']     =  $searchEngine->getSearchEnd();
        $_SESSION['sString']  =  $searchEngine->getSearchString();
         
     }   // look for date-range, 2 search-strings   (mode 6 only)
    else if ( isset( $_POST['submitRanges'] )  &  isset( $_POST['range1']) & isset( $_POST['range2']) ) {
        $searchEngine = new searchEngine( $_POST, "on" ); 
        
        unset( $_SESSION['sIdArray'] );
        unset( $_SESSION['sMode'] );
        unset( $_SESSION['sStart'] );
        unset( $_SESSION['sEnd'] );
        unset( $_SESSION['sString'] );
        $_SESSION['sIdArray'] =  $searchEngine->getSearchIdArray();
        $_SESSION['sMode']    =  $searchEngine->getMode();
        $_SESSION['sStart']   =  $searchEngine->getSearchStart();
        $_SESSION['sEnd']     =  $searchEngine->getSearchEnd();   
        $_SESSION['sString']  =  $searchEngine->getSearchString();    
    } else  // else display all
        $searchEngine = new searchEngine( "", "off" );    
    
    // if sMode is set -> data from previous search is stored in $_SESSION 
    if ( isset( $_SESSION['sMode'] )){
         $sIdArray = isset($_SESSION['sIdArray']) ? $_SESSION['sIdArray'] : "" ; 
         $sMode    = isset($_SESSION['sMode'])    ? $_SESSION['sMode'] : "" ;
         $sStart   = isset($_SESSION['sStart'])   ? $_SESSION['sStart'] : "" ;
         $sEnd     = isset($_SESSION['sEnd'])     ? $_SESSION['sEnd'] : "" ;
         $sString  = isset($_SESSION['sString'])  ? $_SESSION['sString'] : "";
         
         $searchEngine->setSearchIdArray( $sIdArray );
         $searchEngine->setMode( $sMode );
         $searchEngine->setSearchStart( $sStart );
         $searchEngine->setSearchEnd( $sEnd );
         $searchEngine->setSearchString( $sString );
   }         
      
   // set string of listingMode for filter-function
   $allowedLModes = array( "all", "none", "white", "black"); // Overview-Listing-Modes: 0 = all, 1 = none, 2 = whitelisting, 3 = blacklisting , compare filters.php
   $overviewModeStr = $allowedLModes[ $listingMode ];         // $listingMode was set by config

    // set maximum time limit for execution of this script
    set_time_limit( 180 );
                 
    // create list of tables to search through
    $table_array = array();  
    // server_vars MUST be first -> configure $moduleName  + as identification for tarrayID=0
    array_push( $table_array, "Value_Server", "Value_Get", "Value_Post", "Value_Cookie" );        
    
    $sql = "SELECT ID, attackerIP, attackerBrowser, Source, Value_Server, Value_Get, 
                       Value_Post, Value_Cookie, Module, Creation FROM 
                        main_logs   
                   ".$searchEngine->addQuery()."                
                  ORDER BY
                        ID DESC
                  LIMIT ".$startEntry.",".$showRange;         
     $result_serv = mysql_query($sql) OR die(mysql_error());
     
    //echo      $searchEngine->addQuery();     
    
    // go through server_vars
   if ( mysql_num_rows( $result_serv )) {         
      while($row = mysql_fetch_assoc( $result_serv )) {            
          $id = abs((int)$row['ID']);
          $filteredVars = false;  // if extra-filtered vars for overview-display have been found, this var becomes true
          
          // init variables for attack-detection  ( as new table starts )
          $detected = false;     
          $detectionStr = attacklist::getNoDetectionStr();   
          
          // initialize variables for storage of $_SERVER-data
          $referrer = "no referrer";
          $remote_address = "no remote ip";
          $http_user_agent = "";
          $source = "unknown";
          $creationTime = "time unknown"; 
          // init module-vars
          $addonModuleName = "";                   
          
          // init string holding each of the tables
          $tableString = "";       
            

          // for each table create output-string and finally print it out
          foreach ( $table_array AS $tarrayId => $tarrayVal ) {

                    $outputString[ $tarrayId ] = "";                            
                                                                                                                  
                    //$row['Value'] = str_replace( '&lt;DIR&gt;', " <br>", $row['Value'] ); // remove string &lt;DIR&gt; for phpshell
                    $valueArray = explode( ";semcl", $row[ $tarrayVal] );   // split into variable pairs x=bla                            
                     
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
                                //if (isProxy( $leftSide, $rightSide ))
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
                                $tmpAttack = attackChecking( $leftSide, $rightSide, $attacks, $showImages );                                                
                                if ( $tmpAttack !== -1) {                                                        
                                     if ( $detected === false )
                                          $detectionStr = "";          
                                      $detected = true;     
                                      if ( (strpos( $detectionStr, $tmpAttack) === false ) ) {                                                                                                                          
                                            $detectionStr .= $tmpAttack." ";    
                                                                                                                                                                                              
                                      }                                                                                                                        
                                }                                       
                            }
                          
                            // load module handler for this module, else Handler returns NULL                                                          
                            $addonModule = $moduleHandler->getModule( $addonModuleName );                                                                                                                                                     
                                                                                                                           
                            if ( $tarrayId != 0 & filterVars( $oneLine, $overviewModeStr, $addonModule )) {
                              if ( isset($oneLine[1] )) 
                                if ( $leftSide != "" && ( $rightSide != "" | $showEmpty == true )) {
                                    $outputString[$tarrayId] .=  "\t<span class=\"variable_leftpart\">".
                                          wordwrap( $leftSide, $wwrapOverview, "<br>\n" , true ) ." = ".
                                          "</span><span class=\"variable_rightpart\">".
                                          wordwrap( $rightSide, $wwrapOverview, "<br>\n" , true )."</span>";                                                                                                        
                                           
                                    $outputString[$tarrayId] .= "<br>\n"; // one line for each variable
                                    $filteredVars = true;
                              }
                            } // end: filterVars                        
                        }                                                                                                                                                                                                  
                                               
                        $outputString[ $tarrayId ] .= ""; //<br>\n"; // one line for each variable
                    } // end: foreach row
                                                              
                    // DELETE print out data for this table and id
                    //$outputString[ $tarrayId ] .= "</div>\n ";   // div $tarrayVal 
               
                  //echo nl2br($row['Value']); // \n in <br /> umwandeln
                                                     
               
          }//foreach table


          
          // set link to details
          //$tableString .= "<a href=\"detailFramework.php?id=".$id."\">";             

          // determine table color: switch if 'new-attack' (meaning other ip or time)
          if ( !(strcmp( $old_remote_addr, $remote_address ) == 0  )) { 
              $colorValue = ($colorValue xor true); 
          }
          $old_remote_addr = $remote_address; // store current remote address

          // get string for first two rows of main table, incl. evaluation 
          $tableString .= getTable( $id, $source, $remote_address, $http_user_agent, $creationTime, $referrer, 
                                    $decode_urls, $wwrapReferrer, $detectionStr, $colorValue, false, HIGHLIGHT_ATTACKS, HIGHLIGHT_REFERRER );    
           
          
          if ($filteredVars) {        
              $tableString .=  '<tr>              
                                    <td class="blind"></td>                              
                                    <td colspan="5" class="ref_'.($colorValue ? "a" : "b").'">';
               // print out data for each array
              foreach ( $table_array AS $tarrayId => $tarrayVal ) {    
                  if ( isset( $outputString[ $tarrayId ] )) 
                              $tableString .= $outputString[ $tarrayId ];
              }
              $tableString .= '</td></tr>';
          }
          
          // end table from 'getTable'
          $tableString .= '</table>'; 
          
    
          // check if BOT, only display if showBots=true
          if ( (!($showBots == false && isWebSpider( $http_user_agent))) || $detectionStr != attacklist::getNoDetectionStr())        
              // shortView: check if only interesting values should be displayed 
              if (!( $shortView && (!$filteredVars) && $detectionStr == attacklist::getNoDetectionStr()))
                     // print out table
                      echo $tableString;
                           
      
        } // end: while
    } else 
          echo "No data found  "; // end: if                        
    
?>
