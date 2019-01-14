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




    // set maximum time limit for execution of this script
    set_time_limit( 600 );
    
    // if true, also "http://" is looked for and possible tools are downloaded, else wget + co are required
    $alsoCheck4HTTP = true;
    
    // verbose = true   prints out extra information
    if ( isset($_GET['verbose']))
        $verbose = ($_GET['verbose'] == 'true' ? true : false );
    else
        $verbose = false;

    // checkall = true   checks all entries, even if they have been checked before
    // needs to be defined before, in calling php-file
    $checkall = (defined(CHECKALL) ? ""  : "WHERE download_checked <> 1" );

    // read config files
    include_once "inc/config.php"; 
    // connect to logging-database
    include_once "connect.php";
      
    include "autoDownloader.php";
               
    // create list of tables to search through
    $table_array = array();  
    // server_vars MUST be first -> configure $moduleName
    array_push( $table_array, "Value_Get", "Value_Post", "Value_Cookie" );         
    
    $sql = "SELECT ID, Value_Get, Value_Post, Value_Cookie, Creation, download_checked FROM 
                        main_logs    
                  ".$checkall."                              
                  ORDER BY
                        ID DESC"; 
     $result_serv = mysql_query($sql) OR die(mysql_error());

    $processedIdArray = array(); // array holding ids that have been processed this runtime
    // go through server_vars
   if ( mysql_num_rows( $result_serv )) {         
      while($row = mysql_fetch_assoc( $result_serv )) {            
          $id = abs((int)$row['ID']);                
                
          // SQL-QUERY: get results from DB for each table for this ID
          //$innerresult = getArrayByTableAndId( $table_array, $id );

          // for each table create output-string and finally print it out
          foreach ( $table_array AS $tarrayId => $tarrayVal ) {
              $outputString[ $tarrayId ] = "";
                                  
              $valueArray = explode( ";semcl", $row[ $tarrayVal ] );   // split into variable pairs x=bla                            
               
              $auto = new autoDownloader(MAX_FILESIZE, $id, $row[ $tarrayVal ], $alsoCheck4HTTP, BINARY_TABLE );
              $resultAr = $auto->getResultCodeArray(); 
              
              // print out results
              if ( $verbose )
                  foreach ( $resultAr as $key => $val ) 
                      if ( $val[0] != 0 ) {
                          print_r( $val );
                          echo "  ID:".$id."<br>";
                      }
              
              // add id and never check it again
              array_push( $processedIdArray, $id );
                  
          } //foreach array type 
      
        } // end: while
    } else 
         if ($verbose)
            echo "Nothing left to do";  // end: if                        

   
        
      // insert into table, mark entries that have already been processed   
      $idString = "";
      if ( is_array( $processedIdArray ) &&  count($processedIdArray) > 0 ) {
                foreach ( $processedIdArray as $key => $value )
                    $idString .= ($key == 0) ? (int)$value : (",".(int)$value);
                      
      
                $sql = "UPDATE  main_logs SET download_checked = 1 WHERE ID IN (".$idString.")"; 
                //echo $sql;
                                                                                            
                $writeResult = @mysql_query($sql);
                
                if ($writeResult && $verbose) {                                      
                  echo "<br>Checked IDs successfully: ".$idString."<br>";                                         
                }       
      }
?>

