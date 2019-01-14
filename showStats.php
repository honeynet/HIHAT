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

    $menu_selection = 5;

    session_start();
    
    // default mode for overview-listing
    // Overview-Listing-Modes: 0 = all, 1 = none, 2 = whitelisting, 3 = blacklisting
    $defaultMode = 2;
    
    // shortView: only entries with attacks or filtered variables are displayed
    $shortViewDefault = false;
    
    // registers value as new key in array and increases its counter by one
    // @param: value is optional
    function register( &$array, $key, $value = -1 ) {    
        if ( $value == -1 )             
            if ( isset($array[ $key ]))
                $array[ $key ] ++;
            else
                $array[ $key ] = 1;
        else
                $array[ $key ] = $value;    // value is set
          return $array;
    }    
    
    function isProxy( $leftSide, $rightSide ) {
      if ( !(strcasecmp( $leftSide, "" ) == 0 || strcasecmp( $rightSide, "" ) == 0 ))  
        if (( strcasecmp( $leftSide, "HTTP_CLIENT_IP" ) == 0 ) ||
            ( strcasecmp( $leftSide, "HTTP_X_FORWARDED_FOR" ) == 0 ) || 
            ( strcasecmp( $leftSide, "HTTP_VIA" ) == 0 ))               
            return true;
        else
            return false; 
    }
    
    function endsWith( $str, $sub ) {
       return ( substr( $str, strlen( $str ) - strlen( $sub ) ) === $sub );
    }
    
    // read config files
    include "inc/config.php"; 
    // connect to logging-database
    include_once "connect.php";
           
    error_reporting(E_ALL);
    
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

    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
    echo "<html>\n";
    echo "    <head>\n";
    echo "        <title>HIHAT - High Interaction Honeypot Analysis Tool</title>\n";
    echo "        <link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n";
    echo '<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
          <link rel="icon" href="images/favicon.ico" type="image/x-icon">';        
    echo "        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\" />\n";
    
    echo '<style type="text/css">
            a:visited {text-decoration: underline; color:#000000;}
            a:focus {text-decoration: underline; color:#000000;}
            a:link {text-decoration: underline; color:#000000;}
            a:active {text-decoration: underline; color:#000000;}
            a:hover {text-decoration: underline; color:#000000;} 
      </style>';
      
        include "javascripts.php"; 
    
    echo "    </head>\n";
    echo "    <body>\n";
  
    include "banner.php";               
    
          // apply url-deocding function to strings before printing
     $decode_urls = false;     
     
           //$STARTZEIT = (float)time();
     // default mode for overview-listing
     $defaultMode = 2;
     
           
     // break referrer after x characters
     $wwrapReferrer = 120;     
     $wwrapOverview = 120;
     
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
    
    // hits per attack attempt, per IP    
    $counterOfHitsperIP = 0;
    // array keeping data about moste active ip: 1: number of hits for this single attack   2: latest ID belonging to attack 3: IP of entry
    $maxActiveVal = 10;         
    for ( $j = 0; $j < $maxActiveVal; $j++ ) 
        $mostActiveSingleAttack[$j] = array( 0, 0, "no ip", "unknown module" );  // most hits in a row

    // get array of knwon engines    
    $knownSearchEngines = getKnownSearchEngines(); 
        
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
    $searchDescription = searchEngine::getSearchDescription(); 
    $search4TimeDescription = "Click icons to select time- and date range. <br> Alternatively enter timestamp according to following format: \'16-01-2012 12:51:42\'";
   
    
    //  ------------------------------------------------------  
        
    echo "<br><p class=\"headline\">Statistics:</p>";      
    
    $totalHits = 0;   // number of total access IDs
    $totalSpiders = 0;  // number of bots in these IDs
    $spiderTypes = NULL;   // array key: string with spider name, value: counter of its hits
    $browserTypes = NULL;  // array key: string with user agent name, value: counter of its hits  . Includes web spiders as well
    $ipTypes = NULL;       // array key: string with ip-address, value: counter of its hits
    $totalAttacks = array();   // array with IDs that contained an attack
    $attackTypes = NULL;    // array key: string with attack name, value: counter of its hits
    
    $referrerTypes = NULL;    // array key: string with HTTP_REFERER, value: counter of its hits, only set for known search engines
    $referrerEngines = NULL;  // array key: string with name of search engine used in referrer, value: counter of hits
    
    $noUserAgent = 0;       // counter of entries where HTTP_USER_AGENT is empty / obscured
    $hitsPerModule = NULL;    // hits per extensible module
    $attacksPerModule = NULL; // attacks per module
    $targetFileTypes = NULL;  // array key: string with names of target files, value: counter of its hits
    
    $attackVarsLeft = NULL;   // array key: string with names of variables with attacks, value: counter of its hits
    $attackVarsRight = NULL;   // array key: string with used attack patterns, value: counter of its hits
    $sourcesByVulnerableVars = NULL; // array key: string with left side=vulnerable variable names, value: name of source file 
    
    $totalToolsDownloaded = 0; // total number of malicious tools downloaded
    $toolFiletypes = NULL;    // array key: string with mime-filetypes of downloaded tools, value: counter of its hits
    $totalDownloadedsize = 0; // int of totally downloaded bytes
    
    $knownEngineInReferrer = 0; // number of knwon search engines, if referrer is set        
    $unknownReferrer = 0;       // number of unknwon search engines, if referrer is set
    $proxyCounter = 0;          // number of proxies detected
     
    // for grahpical statistics:
    $months4grahps = NULL;      // number of hits per month
    $GetPostCookieCounter[ 0 ] = 0;  // array: 0=number of uses of the GET array, 1= same for POST, 2= same for COOKIE
    $GetPostCookieCounter[ 1 ] = 0;
    $GetPostCookieCounter[ 2 ] = 0;
     
    // set string of listingMode for filter-function
    $allowedLModes = array( "all", "none", "white", "black"); // Overview-Listing-Modes: 0 = all, 1 = none, 2 = whitelisting, 3 = blacklisting , compare filters.php
    $overviewModeStr = $allowedLModes[ $listingMode ];         // $listingMode was set by config

    // set maximum time limit for execution of this script
    set_time_limit( 180 );
                 
    // create list of tables to search through
    $table_array = array();  
    // server_vars MUST be first -> configure $moduleName  + as identification for tarrayID=0
    array_push( $table_array, "Value_Server", "Value_Get", "Value_Post", "Value_Cookie" );        

    // set time RANGE for query, if required
    $limitRangeStr = "";     
    if ( isset( $_POST['submitRanges'] )  &  isset( $_POST['range1']) & isset( $_POST['range2']) ) {
         $limitRangeStr = "WHERE Creation >= '".searchEngine::string2dateEuropean( $_POST['range1'] )."' AND 
                                 Creation <= '".searchEngine::string2dateEuropean( $_POST['range2']   )."' ";
    }
    
  
    // --------------- PRINT TIME SEARCH FIELDS
    echo "<b>Select date- and time range:<b>";
    echo '<form name="rangeStart" action="showStats.php" method="post" class="formular">
               
            <input type="Text" name="range1" value="" onmouseover="return escape( \''.$search4TimeDescription.'\' )" />
    		        <a href="javascript:cal7.popup();"><img src="img/cal.gif" width="16" height="16" border="0" alt="Click to enter date"></a>    		        
            <input type="Text" name="range2" value="" onmouseover="return escape( \''.$search4TimeDescription.'\' )" />
    		        <a href="javascript:cal8.popup();"><img src="img/cal.gif" width="16" height="16" border="0" alt="Click to enter date"></a>
          
              <input type="submit" name="submitRanges" value="Search Dates" onmouseover="return escape( \''.$search4TimeDescription.'\' )"/>                        
      </form>  <br>

      <script language="JavaScript">
  			<!-- // create calendar object(s) just after form tag closed
  				 // specify form element as the only parameter (document.forms[\'formname\'].elements[\'inputname\']);
  				 // note: you can have as many calendar objects as you need for your application
  				var cal7 = new calendar1(document.forms[\'rangeStart\'].elements[\'range1\']);
  				cal7.year_scroll = true;
  				cal7.time_comp = true;
          
          var cal8 = new calendar1(document.forms[\'rangeStart\'].elements[\'range2\']);
  				cal8.year_scroll = true;
  				cal8.time_comp = true;					
  			//-->
			</script> ';

      echo '<script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script>';   
    // --------------- PRINT TIME SEARCH FIELDS



        
    $sql = "SELECT ID, attackerIP, attackerBrowser, Source, Value_Server, Value_Get, 
                       Value_Post, Value_Cookie, Module, Creation FROM 
                        main_logs 
                  ".$limitRangeStr."                                      
                  ORDER BY
                        ID ASC";         
     $result_serv = mysql_query($sql) OR die(mysql_error());
     
    //echo      $searchEngine->addQuery();     
    
    // go through server_vars
   if ( $totalHits = mysql_num_rows( $result_serv ) ) {         
      while($row = mysql_fetch_assoc( $result_serv )) {            
          $id = abs((int)$row['ID']);
          $filteredVars = false;  // if extra-filtered vars for overview-display have been found, this var becomes true
          
          // init variables for attack-detection  ( as new table starts )
          $detected = false;     
          $detectionStr = attacklist::getNoDetectionStr();   
          
          // initialize variables for storage of $_SERVER-data
          $referrer = "no referrer";
          $remote_address = "no remote ip";
          $http_user_agent = "no user agent";
          $source = "unknown";
          $creationTime = "time unknown"; 
          // init module-vars
          $addonModuleName = "no module defined";                   
          
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
                                if ( !strcmp( $row['Module'], "") == 0 )
                                    $addonModuleName = $row['Module'];                                
                                
                                if ( isset( $row['attackerIP'] ) && $row['attackerIP'] != "" )
                                    $remote_address = $row['attackerIP'];               
                                // next 2 lines just for compatibility reasons:
                                if ( $remote_address == "no remote ip" && $leftSide == "REMOTE_ADDR" )                                                            
                                    $remote_address = $rightSide; //."OLD";                     
                                
                                if ( $leftSide == "HTTP_REFERER")                                             
                                   $referrer =  $rightSide ;                                                                        
                                
                                // check if proxy AND UPDATE STATISTICS
                                if (isProxy( $leftSide, $rightSide ))
                                    $proxyCounter++;            
                                                     
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
                                                
                                             // add statistics:  
                                             array_push( $totalAttacks, $id );   // add this to list of total attacks                                                                                              
                                             register( $attackVarsRight, $rightSide );
                                             register( $attackVarsLeft, $leftSide );  
                                             register( $sourcesByVulnerableVars, $leftSide, $source );                                               
                                                // register attack, split multiple attacks in one string into single elements
                                             if (!(stripos( $tmpAttack, " " ) === false)) {
                                                $singleAttacks = explode( " ", $tmpAttack);
                                                foreach( $singleAttacks AS $key => $value )
                                                    register( $attackTypes, $value );                                                                                                
                                                //print_r( $tmpAttack);
                                             } else   // if no multiple attacks:                                              
                                                register( $attackTypes, $tmpAttack );                                                                                                                                                                                                                                                   
                                      }                                                                                                                        
                                }                                       
                            }
                                                       
                        } // end: if $arrVal                                                                                                                                                                                                  
                                               
                        $outputString[ $tarrayId ] .= ""; //<br>\n"; // one line for each variable
                    } // end: foreach row
                                                              
                    // print out data for this table and id                     
               
                  //echo nl2br($row['Value']); // \n in <br /> umwandeln
                                                     
               
          }//foreach table
          
          
          // ------- store values for graphical stats , AMERICAN TIME FORMAT NEEDED in Creation Value -------
          
          $current_month = (int)substr( $creationTime, 5, 2 );
          if ( $current_month >= 1 && $current_month <= 12 )
              register ( $months4grahps,  $current_month );
          
          // ------- store values for graphical stats -------      
          
          // count $_GET, $_POST, ... usage
          if ( isset( $row['Value_Get'] ) && strcmp( $row['Value_Get'], "" ) != 0 )
              $GetPostCookieCounter[ 0 ] ++;
          if ( isset( $row['Value_Post'] ) && strcmp( $row['Value_Post'], "" ) != 0 )
              $GetPostCookieCounter[ 1 ] ++;
          if ( isset( $row['Value_Cookie'] ) && strcmp( $row['Value_Cookie'], "" ) != 0 )
              $GetPostCookieCounter[ 2 ] ++;
              
          // check referrer, only counted in stats if referrer is set at all
          if ( !strcmp( $referrer, "no referrer") == 0 ) {
              $knownDetected = false;
              foreach ($knownSearchEngines as $engine) {
            		  if (stristr ($referrer, $engine)) {
                			$knownDetected = true;         
                      register( $referrerEngines, $engine ); // register search engine detected in referrer       			
            		  }
            	}
              if ($knownDetected) {
                  $knownEngineInReferrer++;   // increase counter for known referrer - search engines
                  register( $referrerTypes, $referrer ); // register referrer                  
              } else
                  $unknownReferrer++;             
          }

           
            // determine most active attack attempt: NEEDS sql-select in ASC                  
          if ( !(strcmp( $old_remote_addr, $remote_address ) == 0  )) { 
               $i = 0;
               $inserted = false;        
               while( $i < $maxActiveVal && !$inserted ) { 
                  if ($counterOfHitsperIP > $mostActiveSingleAttack[$i][0] ) {  // new maximum
                        for ( $j = $maxActiveVal; $j > $i; $j-- ) {
                            $mostActiveSingleAttack[$j] = $mostActiveSingleAttack[ $j - 1 ];   // safe old vals 
                        
                        }                          
                       $mostActiveSingleAttack[$i] = array( $counterOfHitsperIP, $id - 1, $old_remote_addr, $addonModuleName ); // max. was in last turn
                       $inserted = true;
                  }
                  $i++;
               }                             
              $counterOfHitsperIP = 1;    // AFTERWARDS: set back counter
          } else {    // store current maximum
              if (!isWebSpider( $http_user_agent))    // exclude bots from most active attacks
                    $counterOfHitsperIP++;
          }
          $old_remote_addr = $remote_address; // store current remote address                                                   
            
            
            
                  
          // register module
          register( $hitsPerModule, $addonModuleName );
          // register attacks per module
          if ( !strcmp( $detectionStr, attacklist::getNoDetectionStr()) == 0 )
              register( $attacksPerModule, $addonModuleName );
          
          // register target files
          register( $targetFileTypes, $source );
                          
          // check if web spider
          if (isWebSpider( $http_user_agent)) {
              $totalSpiders++;
              // register spider
              register( $spiderTypes, $http_user_agent );            
          } else 
              if ( !strcasecmp( "no user agent", $http_user_agent ) == 0)   // register user agent
                  register( $browserTypes, $http_user_agent );
              else
                  $noUserAgent++;
              
          // register ip-address
          register( $ipTypes, $remote_address );
                            
    }}  // end: while

    // ---- last check, if previous ip was most active:
    //if ( !(strcmp( $old_remote_addr, $remote_address ) == 0  )) 
    { 
               $i = 0;
               $inserted = false;        
               while( $i < $maxActiveVal && !$inserted ) { 
                  if ($counterOfHitsperIP > $mostActiveSingleAttack[$i][0] ) {  // new maximum
                        for ( $j = $maxActiveVal; $j > $i; $j-- ) {
                            $mostActiveSingleAttack[$j] = $mostActiveSingleAttack[ $j - 1 ];   // safe old vals 
                        
                        }                          
                       $mostActiveSingleAttack[$i] = array( $counterOfHitsperIP, $id , $remote_address, $addonModuleName ); // max. was in last turn
                       $inserted = true;
                  }
                  $i++;
               }                                        
              $counterOfHitsperIP = 1;    // AFTERWARDS: set back counter
      }
     // ---- last check, if previous ip was most active:     
    
    
    
    // ------------------------------ CHECK DOWNLOAD Table
    
    $sql = "SELECT filesize, filetype, Creation FROM ".BINARY_TABLE."  ".
            $limitRangeStr.
          " ORDER BY id_files ASC";   
    $result = mysql_query($sql );
    
    if ( $totalToolsDownloaded = mysql_num_rows( $result )) {                    
        $rows = mysql_num_rows($result);        
        
        for ($i = 0; $i < $rows; $i++) {        
            $data = mysql_fetch_object($result);   
            
            // register mime-type
            register( $toolFiletypes, $data->filetype);
            
            // add filesize
            $totalDownloadedsize += (int)$data->filesize;                                         
        }
   }
   // ------------------------------ CHECKED DOWNLOAD Table           
    
    // wordwrap
    $wwrapReferrer = 133;
    
    // accuracy for rounding
    $accuracy = 2;
    // set grahpic values
    $leftMed = "300"; // width left side, medium    
    $useragent_width = 850;   // width of useragent + webspider , left side
    $startTbl = '<table border="0"><tr><td class="left_stat">';
    
    echo '<br><p class="headline">Traffic:</p>';    
    echo '<table border="0"><tr><td style="width:'.$leftMed.'px;" class="left_stat">';
    
    // PRINT out statistics
    echo 'Total Hits: </td><td class="right_stat">'.$totalHits.'</td><td class="percent_stat">[100%]</td></tr></table><br>';    

   // ----- calculate empty referrer fields
   $valueRef = ($totalHits-($knownEngineInReferrer+$unknownReferrer));
   
   // avoid division by zero
   if ($totalHits <= 0 ) {
      $valueRef = 0;
      $totalHits = 1;
   }
   if ($totalSpiders <= 0 )
      $totalHits = 1;
   if ($totalAttacks <= 0 )
      $totalHits = 1;
    // -----------------
    

    
    
    echo '<table border="0"><tr><td style="width:'.$leftMed.'px;" class="left_stat">'; 
    echo "Total Web Spiders: </td><td class=\"right_stat\">".$totalSpiders." </td><td class=\"percent_stat\">[".round($totalSpiders/$totalHits*100, $accuracy)."%]  </td></tr></table><br><br>";  
            
    echo $startTbl;   
    echo 'Datatransfer by http-get: </td><td class="right_stat">'.$GetPostCookieCounter[0].' </td><td class="percent_stat"> ['.round($GetPostCookieCounter[0]/$totalHits*100, $accuracy).'%] </td></tr>
          <tr><td style="width:'.$leftMed.'px;" class="left_stat">';
    echo "Datatransfer by http-post: </td><td class=\"right_stat\">".$GetPostCookieCounter[1]." </td><td class=\"percent_stat\"> [".round($GetPostCookieCounter[1]/$totalHits*100, $accuracy)."%] </td></tr>  
          <tr><td class=\"left_stat\">";
    echo "Datatransfer by http-cookie: </td><td class=\"right_stat\">".$GetPostCookieCounter[2]." </td><td class=\"percent_stat\"> [".round($GetPostCookieCounter[2]/$totalHits*100, $accuracy)."%] </td></tr></table>\n";    

                 
    echo '<br>'.$startTbl;
    echo "Referrer was set: </td><td class=\"right_stat\">".($knownEngineInReferrer+$unknownReferrer)." </td><td class=\"percent_stat\"> [".round(($knownEngineInReferrer+$unknownReferrer)/$totalHits*100, $accuracy)."%] </td></tr>\n
          <tr><td style=\"width:".$leftMed."px;\" class=\"left_stat\">";       
    echo "Referrer was obstructed: </td><td class=\"right_stat\">".$valueRef."  </td><td class=\"percent_stat\"> [".round($valueRef/$totalHits*100, $accuracy)."%] </td></tr>\n <tr><td class=\"left_stat\">";
    echo "Proxy detected: </td><td class=\"right_stat\">".$proxyCounter."  </td><td class=\"percent_stat\"> [".round($proxyCounter/$totalHits*100, $accuracy)."%] </td></tr>\n<tr><td class=\"left_stat\">";                     
    echo "Number of distinct source IPs: </td><td class=\"right_stat\">".count( $ipTypes )."</td></tr></table>";
      
    
    echo '<br>'.$startTbl;
    echo "Hidden user agent: </td><td class=\"right_stat\">".$noUserAgent."  </td><td class=\"percent_stat\"> [".round($noUserAgent/$totalHits*100, $accuracy)."%] </td></tr>\n
          <tr><td style=\"width:".$leftMed."px;\" class=\"left_stat\">";
    echo "Known search engine in referrer: </td><td class=\"right_stat\">".$knownEngineInReferrer."  </td><td class=\"percent_stat\"> [".round($knownEngineInReferrer/$totalHits*100, $accuracy)."%] </td></tr>\n<tr><td class=\"left_stat\">";    
    echo "Unknwon search engine in referrer: </td><td class=\"right_stat\">".$unknownReferrer."  </td><td class=\"percent_stat\"> [".round($unknownReferrer/$totalHits*100, $accuracy)."%] </td></tr></table>\n";   
  
    $maxReferrer = 15;  // most popular referrer    
    echo "\n<br><br><b>Most popular http-referrer:</b><br>";
    if ( count($referrerTypes) > 0 ) {
        echo '<table border="0"><tr><td style="width:'.$useragent_width.'px;" class="left_stat">';
        arsort($referrerTypes);
        $foreachCounter = 0;
        foreach($referrerTypes AS $key => $value ) {
            $foreachCounter++;
            if ($foreachCounter <= $maxReferrer )
                echo wordwrap( htmlentities(urldecode($key)), $wwrapReferrer, "<br>\n", true)." </td><td class=\"right_stat\">".$value."</td><td class=\"percent_stat\">[".round($value/$knownEngineInReferrer*100, $accuracy)."%]  </td>";
            if ($foreachCounter < $maxReferrer && count($referrerTypes) > $foreachCounter )
                echo "</tr>\n<tr><td style=\"width:".$leftMed."px;\" class=\"left_stat\">";          
        }
    } 
    echo "</tr></table>\n";             
    
    
    $maxReferrerEngines = 25;  /* nr. most popular search engines, if value=lenth of array "getKnownSearchEngines" in
                                  browserCheck.php ALL engines are displayed                                      */   
    echo "\n<br><br><b>Search engines detected in http-referrer:</b><br>";
    if ( count($referrerEngines) > 0 ) {
        echo $startTbl;
        arsort($referrerEngines);
        $foreachCounter = 0;
        foreach($referrerEngines AS $key => $value ) {
            $foreachCounter++;
            if (endswith($key, "."))            // cut last char if name similar to "google."
                $key = substr($key, 0, strlen( $key) - 1 );
            if ($foreachCounter <= $maxReferrerEngines )
                echo htmlentities($key)." </td><td class=\"right_stat\">".$value." Hits </td><td class=\"percent_stat\">[".round($value/$knownEngineInReferrer*100, $accuracy)."%]  </td>";
            if ($foreachCounter < $maxReferrerEngines && count($referrerEngines) > $foreachCounter )
                echo "</tr>\n<tr><td style=\"width:".$leftMed."px;\" class=\"left_stat\">";          
        }
    } 
    echo "</tr></table>\n"; 
  
    $files2show = 15;  // the three most popular targets
    echo "\n<br><br><b>Most often accessed files:</b><br>";
    if ( count($targetFileTypes) > 0 ) {
        echo $startTbl;
        arsort($targetFileTypes);
        $foreachCounter = 0;
        foreach($targetFileTypes AS $key => $value ) {
            $foreachCounter++;
            if ($foreachCounter <= $files2show )
                echo $key." </td><td class=\"right_stat\">".$value." Hits </td><td class=\"percent_stat\">[".round($value/$totalHits*100, $accuracy)."%]  </td>";
            if ($foreachCounter < $files2show && count($targetFileTypes) > $foreachCounter )
                echo "</tr>\n<tr><td style=\"width:".$leftMed."px;\" class=\"left_stat\">";          
        }        
    } 
    echo "</tr></table>\n"; 
           
      
    $upToLoudest = 15;  // the three most loudest ips    
    echo '<p class="headline">Loudest IPs:</p>';    
    if ( count($ipTypes) > 0 ) {
        echo $startTbl;
        arsort($ipTypes);
        $foreachCounter = 0;
        foreach($ipTypes AS $key => $value ) {
            $foreachCounter++;
            if ($foreachCounter <= $upToLoudest )
                echo htmlentities($key)." </td><td class=\"right_stat\">".$value." Hits </td><td class=\"percent_stat\">[".round($value/$totalHits*100, $accuracy)."%]  </td>";
            if ($foreachCounter < $files2show && count($ipTypes) > $foreachCounter )
                echo "</tr>\n<tr><td style=\"width:".$leftMed."px;\" class=\"left_stat\">";          
        }
    } 
    echo "</tr></table>\n"; 
    
    
    echo '<p class="headline">Hits per module:</p>';
    if ( count($hitsPerModule) > 0 ) {
        echo $startTbl;
        arsort($hitsPerModule);  
        $foreachCounter = 0;  
        foreach($hitsPerModule AS $key => $value ) {
            $foreachCounter++;
            echo $key." </td><td class=\"right_stat\">".$value." </td><td class=\"percent_stat\"> [".round($value/$totalHits*100, $accuracy)."%]  </td>";
            if ( count($hitsPerModule) > $foreachCounter )
                echo "</tr>\n<tr><td style=\"width:".$leftMed."px;\" class=\"left_stat\">";          
        }            
    } 
    echo "</tr></table>\n";       
    
    
    echo '<p class="headline">User Agents: <font size="-1">(excluding Web Spiders)</font></p>';
    
    echo '<table border="0"><tr><td style="width:'.$leftMed.'px;" class="left_stat">';
    echo "Number of distinct user agents: </td><td class=\"right_stat\">".count($browserTypes)."</td></tr></table><br>";
    
    if ( count($browserTypes) > 0 ) {
        echo '<table border="0"><tr><td style="width:'.$useragent_width.'px;" class="left_stat">';
        arsort($browserTypes);
        $foreachCounter = 0;  
        foreach($browserTypes AS $key => $value ) {
            $foreachCounter++;
            echo htmlentities($key)." </td><td class=\"right_stat\">".$value." Hits </td><td class=\"percent_stat\">[".round($value/$totalHits*100, $accuracy)."%]  </td>";
            if ( count($browserTypes) > $foreachCounter )
                echo "</tr>\n<tr><td style=\"width:".$leftMed."px;\" class=\"left_stat\">";          
        }
    }
    echo "</tr></table>\n";       
             
    
    echo '<br><p class="headline">Web Spiders:</p>';
    echo '<table border="0"><tr><td style="width:'.$leftMed.'px;" class="left_stat">';
    echo "Total Web Spiders: </td><td class=\"right_stat\">".$totalSpiders." </td><td class=\"percent_stat\">[".round($totalSpiders/$totalHits*100, $accuracy)."%]  </td></tr>\n<tr><td class=\"left_stat\">";      
    echo "Number of distinct web spiders: </td><td class=\"right_stat\">".count($spiderTypes)." </td></tr></table>\n<br>";
      
    if ( count($spiderTypes) > 0 ) {
        echo '<table border="0"><tr><th class="left_stat">Spider Types</th><th colspan="2"></th></tr>
                                <tr><td style="width:'.$useragent_width.'px;" class="left_stat">';
        arsort($spiderTypes);    
        $foreachCounter = 0;  
        foreach($spiderTypes AS $key => $value ) {
            $foreachCounter++;
            echo htmlentities($key)." </td><td class=\"right_stat\">".$value." Hits </td><td class=\"percent_stat\">[".round($value/$totalSpiders*100, $accuracy)."%]  </td>\n";
            if ( count($spiderTypes) > $foreachCounter )
                echo "</tr>\n<tr><td class=\"left_stat\">";          
        }
    }    
    echo "</tr></table>\n";      
    
    
    echo '<p class="headline">Attacks:</p>';
        
    $totalAttacks = array_unique( $totalAttacks );
    echo '<table border="0"><tr><td style="width:'.$leftMed.'px;" class="left_stat">';
    echo "Total number of detected attacks: </td><td class=\"right_stat\">".count(  $totalAttacks )." </td><td class=\"percent_stat\">[".round(count(  $totalAttacks )/$totalHits*100, $accuracy)."%]  </td></tr>\n<tr><td class=\"left_stat\">";
    echo "Number of distinct attack types:  </td><td class=\"right_stat\">".count($attackTypes)."</td></tr></table>\n";  
    
    
    echo "\n<br><b>Attacks per module:</b> <br>";
    if ( count($attacksPerModule) > 0 ) {
        echo $startTbl; 
        arsort($attacksPerModule);   
        $foreachCounter = 0;   
        foreach($attacksPerModule AS $key => $value ) {
            $foreachCounter++;
            echo $key." </td><td class=\"right_stat\">".$value." Hits </td><td class=\"percent_stat\">[".round($value/count( $totalAttacks )*100, $accuracy)."%]  </td>\n";
            if ( count($attacksPerModule) > $foreachCounter )
                echo "</tr>\n<tr><td class=\"left_stat\">";      
        }
    }
    echo "</tr></table>\n"; 
    
            
    echo "\n<br><b>Attack Types:</b> <br>";
    if ( count($attackTypes) > 0 ) {
        echo $startTbl;
        arsort($attackTypes);   
        $foreachCounter = 0;    
        foreach($attackTypes AS $key => $value ){
            $foreachCounter++;
            echo $key." </td><td class=\"right_stat\">".$value." Hits </td><td class=\"percent_stat\">[".round($value/count( $totalAttacks )*100, $accuracy)."%]  </td>\n";
            if ( count($attackTypes) > $foreachCounter )
                echo "</tr>\n<tr><td class=\"left_stat\">";
        }                            
    }    
    echo "</tr></table>\n";     
    
   
    $attackVarsRight2show = 20;  // the most popular patterns
    echo "\n<br><b>Most often used attack patterns:</b><br>";    
    if ( count($attackVarsRight) > 0 ) {
        echo '<table border="0"><tr><td style="width:850px;" class="left_stat">';  // TEMP: LOCAL GRAPHIC VALUE 850
        arsort($attackVarsRight);
        $foreachCounter = 0;
        foreach($attackVarsRight AS $key => $value ) {
            $foreachCounter++;
            if ($foreachCounter <= $attackVarsRight2show )
                echo htmlentities($key)." </td><td class=\"right_stat\">".$value." Hits </td><td class=\"percent_stat\">[".round($value/count( $totalAttacks )*100, $accuracy)."%]  </td>\n";
            if ($foreachCounter < $attackVarsRight2show && count($attackVarsRight) > $foreachCounter )
                echo "</tr>\n<tr><td class=\"left_stat\">";         
        }                
    }         
    echo "</tr></table>\n";   
    
    $attackVarsLeft2show = 15;   // the mostly abused vars, variables containing the previous attack patterns   
    echo "\n<br><b>Sources of these attack patterns:</b><br>";         
    if ( count($attackVarsLeft) > 0 ) {
        echo '<table border="0"><tr><th class="left_source_at_pattern">';
        echo 'Name of file</th><th class="left_stat">Name of variable</th><th colspan="2" class="right_stat"></th</tr><tr><td class="left_stat">';
        arsort($attackVarsLeft);
        $foreachCounter = 0;
        foreach($attackVarsLeft AS $key => $value ) {
            $foreachCounter++;
            if ($foreachCounter <= $attackVarsLeft2show )
                echo $sourcesByVulnerableVars[$key]." </td><td class=\"right_stat\"> ".htmlentities($key)." </td><td class=\"right_stat\">".$value." Hits </td><td class=\"percent_stat\">[".round($value/count( $totalAttacks )*100, $accuracy)."%]  </td>\n";
            if ($foreachCounter < $attackVarsLeft2show && count($attackVarsLeft) > $foreachCounter )
                echo "</tr>\n<tr><td class=\"left_stat\">";     
        }       
    }    
    echo "</tr></table>\n";              
  
    
    
    echo "\n<br><b>Most active attacks or requests</b> (excluding bots)";
    echo $startTbl;
    if ( count($mostActiveSingleAttack) > 0 ) {
          for( $i = 0; $i < $maxActiveVal; $i++ ) {
              echo "IP: ".htmlentities($mostActiveSingleAttack[$i][2])."</td><td class=\"mostactive_stat_hits\"> [".$mostActiveSingleAttack[$i][0].
                   " Hits in a row] </td> <td class=\"mostactive_stat_lastid\">Last ID of attack: <a href='detailFramework.php?id=".(int)$mostActiveSingleAttack[$i][1]."'>"
                              .(int)$mostActiveSingleAttack[$i][1]."</a> </td><td class=\"left_stat\"> in Module \"".$mostActiveSingleAttack[$i][3]."\" </td>\n";
              if ( ($i+1) < $maxActiveVal && (($i+1) < count($mostActiveSingleAttack)))
                  echo "</tr><tr><td class=\"left_stat\">";                              
              }                    
    } 
    echo "</tr></table>\n<br>";        
    
    
    echo '<p class="headline">Captured Downloads:</p>';        
    
    
    echo '<table border="0"><tr><td style="width:'.$leftMed.'px;" class="left_stat">';    
    echo "Total number of captured tools: </td><td class=\"right_stat\">".$totalToolsDownloaded."</td></tr>";    
    if ($totalToolsDownloaded > 0 ) {
        echo "<tr><td class=\"left_stat\">Average size of a captured tool:  </td><td class=\"right_stat\">".round($totalDownloadedsize / $totalToolsDownloaded / 1024.0, 2 )."kb </td></tr>";
        echo "<tr><td class=\"left_stat\">Total size of all captured tools:  </td><td class=\"right_stat\">".round($totalDownloadedsize / 1024.0, 2 )."kb </td></tr>";
    }
    echo "</table>";
           
    echo "\n<br><b>Filetypes:</b> <br>";    
    if ( count($toolFiletypes) > 0 ) {
        echo '<table border="0"><tr><td style="width:'.$leftMed.'px;" class="left_stat">';
        arsort($toolFiletypes);    
        $foreachCounter = 0;
        foreach($toolFiletypes AS $key => $value ) {
            $foreachCounter++;
            echo $key." </td><td class=\"right_stat\">".$value."  </td><td class=\"percent_stat\">[".round($value/$totalToolsDownloaded*100, $accuracy)."%]  </td>\n";        
            if ( count($toolFiletypes) > $foreachCounter )
                echo "</tr>\n<tr><td class=\"left_stat\">";
        }
    }
    echo "</tr></table>\n";    

    
     /* GRAPHICAL STATISTICS with JpGraph 
        Currently disabled, Licence required for non-commercial use  */

    /*       
    echo '<br><p class="headline">Graphical Statistics:</p>';   
    
    echo '<table><tr><td colspan="2">';
    
    echo '<b>Traffic:</b>';     

    echo '</td></tr><tr><td>';
    
    //$months4grahps = array( 10, 9, 10, 9);
     
    if ( $months4grahps != NULL ) {       
        // print total Hits - Graphic        
        $imageString = "<img src=\"graphicTotals.php?";          
        foreach( $months4grahps as $key => $value )
            $imageString .= "".$key."=".$value."&";
        
        $imageString = substr( $imageString, 0, strlen($imageString)-1 );
        $imageString .=  "\" />";  
        echo $imageString;
    }
    echo '</td><td>';
    if ( $hitsPerModule != NULL ) {       
          // print Pie graphic
          $imageString = "<img src=\"graphicPie.php?";          
          foreach( $hitsPerModule as $key => $value )
              $imageString .= "".$key."=".$value."&";
          
          $imageString .= "title=Hits per Module&xsize=480&ysize=200";                   
          $imageString .=  "\" />";  
          echo $imageString;
    }
    
    echo "</td></tr>\n<tr><td>";
    
    echo "<b>Attacks:</b></td></tr>\n<tr><td>";
    

        
    if ( $attackTypes != NULL ) {       
          // print Pie graphic
          $imageString = "\n <img src=\"graphicPie.php?";          
          foreach( $attackTypes as $key => $value )
              $imageString .= "".$key."=".$value."&";
                    
          //$imageString = substr( $imageString, 0, strlen($imageString)-1 );
          $imageString .= "xsize=500";        
          $imageString .=  "\" />";  
          echo $imageString;
    }
    
    echo '</td><td>';
    
    if ( $attacksPerModule != NULL ) {       
          // print Pie graphic
          $imageString = "\n <img src=\"graphicPie.php?";          
          foreach( $attacksPerModule as $key => $value )
              $imageString .= "".$key."=".$value."&";
          
          $imageString .= "title=Attacks per Module&xsize=480&ysize=200";                   
          $imageString .=  "\" />";  
          echo $imageString;
    }
    
    echo '</td></tr></table>';
    
    */ 
    
    
    echo '<br><br></body></html>';        
?>
