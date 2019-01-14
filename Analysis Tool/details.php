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

    
    /* supported lookup-modes and Datasources: 0 = off, 1 = ip2location(SQL), 2 = www.hostip.info (Web)  */
    $mappingMode = 1;
    
    /* max number of file-tables to display */
    $maxFileDisplay = 5;
    
    if ( !defined( 'DET_SECURE' ))
        die('You cannot call this file directly!');
    
        //include "automaticDownload.php";
         
    // string containing the summarizing table
    $tableString = "";    
    
    // Sign to put into output-string between left and right side of variables
    $varSeparationSign = " = ";
    
      // apply url-deocding function to strings in referrer before printing      
     if (defined('URL_DECODE') && URL_DECODE == true )       // must be set in constant.php
          $decode_urls = true;
     else 
          $decode_urls = false;
     
    // break referrer after x characters
    $wwrapReferrer = 140;
    
    // initialize variables for storage of $_SERVER-data
    $referrer = "no referrer";
    $remote_address = "no remote ip";
    $http_user_agent = "";
    $source = "unknown";
    $creationTime = "time unknown";    
    
    // true if SQL query returned empty results
    $noResults = false;   
    
    // identifies browser out of user-agent string
    include_once "browserCheck.php"; 
    
    // include functions for table output
    include "printTable.php";

    // include filtering-functions   
    include "filters.php";   

   // include list of attacks
    include "attacklist.php";    
    // object with attack-arrays
    $attacks = new attacklist();
    // becomes true as soon as at least one attack per table was found
    $detected = false;    
    // string containing names of detected attacks, for printing out 
    $detectionStr = "no attack found";   
    // show images for detected attacks , instead of strings
    $showImages = true;
    
    // create list of tables to search through, UPDATE: NOW LIST OF DATA FIELDS IN SERVER-TABLE
    $table_array = array();  
    // server_vars MUST be last ->  + as identification for tarrayID=3
    array_push( $table_array, "Value_Get", "Value_Cookie", "Value_Post", "Value_Server" );       
   
  
  
    // ensure clean id-variable
    if ( isset( $_GET['id'] ) ) {     
        $id = max( 1, abs( (int) $_GET['id'] ));
    } else
        $id = 1;

    // default, or if no search-data exists, browse through all IDs
    $nextHighest = $id + 1;
    $nextLowest  = $id - 1;

    include "searchEngine.php";            
    $searchEngine = new searchEngine( "", "off" );      
   
   // SESSION-MANAGEMENT
   // If search-data exists, paging only through search-results
   if ( isset( $_SESSION['sMode'] )){   
          // read data from session
         $sIdArray = isset($_SESSION['sIdArray']) ? $_SESSION['sIdArray'] : "" ; 
         $sMode    = isset($_SESSION['sMode'])    ? $_SESSION['sMode'] : "" ;
         $sStart   = isset($_SESSION['sStart'])   ? $_SESSION['sStart'] : "" ;
         $sEnd     = isset($_SESSION['sEnd'])     ? $_SESSION['sEnd'] : "" ;
                  
         // set data from session to current search-module (search module ? "a" : "b"varifies data)
         $searchEngine->setSearchIdArray( $sIdArray );
         $searchEngine->setMode( $sMode );
         $searchEngine->setSearchStart( $sStart );
         $searchEngine->setSearchEnd( $sEnd );

        // if Session active: determine next + previous Index for paging (blättern)
        $sql = "SELECT Max(ID) as ID FROM main_logs              
                ".$searchEngine->addQuery()." AND ID < ".$id;        //echo $sql."<br> ";       
        $result = mysql_query($sql) OR die(mysql_error());               
        $nextLowest = mysql_fetch_row($result);
        $nextLowest = (int)$nextLowest[0];
        if ( $nextLowest == 0 )
             $nextLowest = $id;
    
        $sql = "SELECT Min(ID) as ID FROM main_logs              
               ".$searchEngine->addQuery()." AND ID > ".$id;        //echo $sql."<br> "; 
        $result = mysql_query($sql) OR die(mysql_error());               
        $nextHighest = mysql_fetch_row($result);
        $nextHighest = (int)$nextHighest[0];
        if ( $nextHighest == 0 )
             $nextHighest = $id;      
    }
         
       // SESSION-MANAGEMENT
    //-------------------
    // erase old session
    //if ( isset( $_GET['delete'] )){
      //  unset( $_SESSION['ip'] );     
    //} ;     
     
   if ( isset( $_SESSION['ip'] )) {
        //print_r($_SESSION['ip']);         
        $mapper = $_SESSION['ip'];
        if (! $mapper instanceof IPmapper )
            $mapper = new IPmapper( 0 );    
   } else { 
        $mapper = new IPmapper( $mappingMode );             
        $_SESSION['ip'] = $mapper;
    }
                            
    //echo $sql."<br> "; //count(mysql_num_row
    
    
     // get results from DB for this ID
    $sql = "SELECT ID, Source, attackerIP, attackerBrowser, Value_Server, Value_Get, 
                   Value_Post, Value_Cookie, Module, Creation FROM 
                        main_logs
           WHERE ID = ".$id."                                                
           ORDER BY
                 ID DESC";           
     $result = mysql_query($sql) OR die(mysql_error());
  
    if ( $nextLowest != $id && $id != 1 )         
        echo '<a href="detailFramework.php?id='.$nextLowest.'"><img src="images/id_previous.png" border="0" alt="previous ID" /></a>';
    else
        echo '<img src="images/id_previous-inactive.png" border="0" alt="previous ID" />';   // no search result
        
    if ( $nextHighest != $id )         
        echo '<a href="detailFramework.php?id='.$nextHighest.'"><img src="images/id_next.png" border="0" alt="next ID" /></a>';
    else
        echo '<img src="images/id_next-inactive.png" border="0" alt="next ID" />';     // no search result
        
        
    if (isset( $_SESSION['sMode']) && $_SESSION['sMode'] != 0 )
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"overview.php\"><img src=\"images/backtosearchresults.png\" border=\"0\" alt=\"back to search results\" /></a>";              
    
   // becomes true as soon as at least one attack per table was found
    $detected = false;    
    // string containing names of detected attacks, for printing out 
    $detectionStr = "no attack found";   
    
    
     // check for downloaded-files         -------------------------------------------
    $fileCounter = 0;
    $sql = "SELECT source_url, filename, filesize, filetype FROM ".BINARY_TABLE." WHERE id_files=".$id;                                                                                                                                                                 
    $readResult = mysql_query($sql);
    if ( $readResult )
         while($row = mysql_fetch_assoc($readResult)) {              
            $filename = $row['filename'];
            $filesize = $row['filesize'];
            $file_url = $row['source_url'];
            $filetype = $row['filetype'];
            $fileCounter++; 
                          
                
            // print out file-table
            if ( $fileCounter <= $maxFileDisplay ) {     
               // inner file-table                       
              $fileTable = "\t<table border=\"1\" cellspacing=\"0\" cellpadding=\"1\"  width=\"100%\" bgcolor=\"#E4E4E4\">   ";
              
              $fileTable .= "<tr> <td>ID</td> <td>\n\t";                            
              $fileTable .= $id;
              $fileTable .= " </td> </tr>";
              
              $fileTable .= "<tr> <td>Filename</td> <td>\n\t";              
              $fileTable .= $filename;
              $fileTable .= " </td> </tr>"; 
                    
              
              $fileTable .= "<tr> <td>Filetype</td> <td>\n\t";              
              $fileTable .= $filetype;
              $fileTable .= " </td> </tr>";
              
              $fileTable .= "<tr> <td>Source-URL</td> <td>\n\t";              
              $fileTable .= $file_url;
              $fileTable .= "</td> </tr>";  
              
              $fileTable .= "<tr> <td>Filesize</td> <td>\n\t";              
              $fileTable .= $filesize;
              $fileTable .= " bytes </td> </tr> </table>";
               
              // put outer table in inner table 
              $outerFileTable = "\t<table align=\"center\"  border=\"1\" cellspacing=\"1\" cellpadding=\"0\" width=\"100px\" bgcolor=\"#E4E4E4\">   ";
              $outerFileTable .= "<tr><td><nobr>Captured File: <a href='downloadTools.php?id=$id'>Download</a></nobr></td></tr>";
              $outerFileTable .= "<tr><td>".$fileTable."</td></tr>\n</table>";                                     
           } else
              ;//echo "Error! Too many files selected for display!";
    } // -------------------------------------------
    
       
    // go through server_vars
   if ( mysql_num_rows( $result )) {         
      while($row = mysql_fetch_assoc( $result )) {     
             //print_r($row[$tarrayVal]);
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
          // init module-vars
          $addonModuleName = "";                   
          
          // init string holding each of the tables
          $tableString = "";          
              

             // for each table create output-string and finally print it out
          foreach ( $table_array AS $tarrayId => $tarrayVal ) {
                    
                    $outputString[ $tarrayId ] = "";       
                    
                    // print out data about captured files if available                                           
                    if ($tarrayId == 3 && $fileCounter > 0 )
                        $outputString[ $tarrayId ] .= '</td><td class="details">'.$outerFileTable;    
                                                                       
                        
                    // incorporate table design
                    if ($tarrayId == 1) {
                        $outputString[ $tarrayId ] .= "</td> <td ";  
                        if ($fileCounter <= 0)
                            $outputString[ $tarrayId ] .= ' rowspan="2" ';
                        $outputString[ $tarrayId ] .= "class=\"details\">"; //<td rowspan=\"2\" class=\"ID_a\">"; 
                    }
                    
                    if ($tarrayId == 2)
                        $outputString[ $tarrayId ] .= "</td></tr><tr><td class=\"details\">";
                        
                    if ($tarrayId == 3)
                        $outputString[ $tarrayId ] .= "</td></tr>\n <tr><td colspan=\"2\" class=\"details_server\">";                            
                    

       
                    // two initial lines to print out                                             
                    $outputString[ $tarrayId ] .= " <br><table><tr><td class=\"details\"> <b>HTTP-".
                                strtoupper(substr($tarrayVal, 6))." Information:</b> </tr><tr><td class=\"details\">";                                                                             
                    
                    //$row['Value'] = str_replace( '&lt;DIR&gt;', " <br>", $row['Value'] ); // remove string &lt;DIR&gt; for phpshell
                    $valueArray = explode( ";semcl", $row[ $tarrayVal] );   // split into variable pairs x=bla                            
                     
                    // for each row: split it into variable pairs and print them out   
                    foreach ( $valueArray AS $arrKey => $arrVal) { 
                        if ( $arrVal != "" ) {
                            $oneLine = explode( "=", $arrVal , 2);    // split into variable and value   
                            
                            // filter $_SERVER array
                            $leftSide =  isset($oneLine[0]) ? $oneLine[0] : "";
                            $rightSide = isset($oneLine[1]) ? $oneLine[1] : "";                                                        
                            if ( $tarrayId == 3 ) {                                                                                                                             
                                $source = $row['Source'];   
                                $creationTime = $row['Creation'];
                                $addonModuleName = $row['Module'];                                
                                
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
                                                                         
                             if ( isset($oneLine[1] )) 
                                if ( $leftSide != "" ) {
                                    $outputString[$tarrayId] .=  "\t<span class=\"variable_leftpart\">".
                                          wordwrap( $leftSide, 150, "<br>\n" , true ) ." = ".
                                          "</span><span class=\"variable_rightpart\">".
                                          wordwrap( $rightSide, 150, "<br>\n" , true )."</span>";                                                                                                        
                                           
                                    $outputString[$tarrayId] .= "<br>\n"; // one line for each variable
                                    $filteredVars = true;
                              }                                             
                        }                                                                                                                                                                                                  
                                               
                        $outputString[ $tarrayId ] .= ""; //<br>\n"; // one line for each variable
                    } // end: foreach row
                                                              
                    // print out data for this table and id
                    $outputString[ $tarrayId ] .= "<br> </td></tr></table> ";   // div $tarrayVal 
               
                  //echo nl2br($row['Value']); // \n in <br /> umwandeln
                                                     
               
          }//foreach table                                    
        } // end: while
    } else {            
          echo '&nbsp;&nbsp;<b>no data found</b>';
          $noResults = true;
    }
                    

     
    // get string for first two rows of main table, incl. evaluation 
    $tableString .= getTable( $id, $source, $remote_address, $http_user_agent, $creationTime, $referrer, 
                                      $decode_urls, $wwrapReferrer, $detectionStr, true, true, HIGHLIGHT_ATTACKS, HIGHLIGHT_REFERRER );    
    $tableString .= " </table> <br> \n";                                                      
        
    // print out table
    echo $tableString;    
    
             
     // print mapping-button
    echo '<form name="mapIDs" action="mapping.php" method="post" class="formular">
              <input type="hidden" name="hiddenId" value="'.$id.'">
              <input type="hidden" name="hiddenIP" value="'.$remote_address.'">
              <input type="hidden" name="hiddenDetections" value="'.$detectionStr.'">
              <input type="submit" name="submitId" value="-> map" >                                                           
              </form><br>
    ';          
    
    
    
    // start table beginning below 'map' button 
    if (!$noResults)
        echo '<table align="center" width="990px"><tr><td class="source_a"> ';    
    
    
    
    //include "autoDownloader.php";
    //$auto = new autoDownloader(MAX_FILESIZE, 444, "pma_username = wget http://www.gmx.de/index.php;ajklöfetch%20http://www.web.de/index.php?jkls=ka");
    //print_r( $auto->getResultCodeArray());
    
    
    // print out data (get-post-cookie-server) for each array
    foreach ( $table_array AS $tarrayId => $tarrayVal ) {    
        if ( isset( $outputString[ $tarrayId ] ))
              echo $outputString[ $tarrayId ];
    } 
    if (!$noResults)
        echo '</td></tr></table>';
   
    echo "<table align=\"left\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"  bgcolor=\"#D5D5EB\"><tr><td><b>Further DNS-Information:</b><br></td></tr>";
    echo '<tr><td><a href="http://www.dnsstuff.com/tools/whois.ch?ip='.$remote_address.'" target="_blank" >whois information</a></td></tr> ';
    echo '<tr><td><a href="http://www.dnsstuff.com/tools/ipall.ch?ip='.$remote_address.'" target="_blank" >ip information</a> </td></tr>';
    echo '<tr><td><a href="http://cert.uni-stuttgart.de/stats/dns-replication.php?query='.$remote_address.'&submit=Query" target="_blank" >passive-dns-replication</a></td></tr>';
 
    //echo '<a href="http://cert.uni-stuttgart.de/stats/dns-replication.php?query='.$remote_address.'&submit=Query" onmouseover="return escape( \'bla\' )" target="_blank" >reverse-dns information</a>';

    echo '<script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script>';
            
?>
