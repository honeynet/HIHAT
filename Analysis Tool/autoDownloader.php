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



/* checks if string contains download-source and performs download if possible
         supports - multi parsing: if one string contains semicolon-seperated commands, each command is checked
                  - http, https, ftp, ftps
                  - different tools like wget, curl, lynx, etc.     */  
    class autoDownloader {
        /* max. amount of kbytes that get downloaded    */
        protected $maxFileSize;
        /* string possibly containing downloadable links  */
        protected $checkString;   
        /* if true all http://...  entries are downloaded as well. No wget, etc. is required  */
        protected $downloadAll;
        /* array of tools to consider */
        protected $toolArray = array( "wget", "curl", "lynx", "fetch", "lwp-download" );
        
        /* resultCode of check:  0  no download found
                                 1  download found + SUCCESSFULLY performed
                                 2  download found but URL couldn't be retrieved or fileSize = 0
                                 3  download found but problem when writing do disk
                                 6  ftp download found, just displayed
                                 4  other error                                               */
        protected $resultCodeArray = array(); // array structure: [0]=resultCode  [1]=resultURL [2]=resultFileSize
        /* downloaded filesize      */
        protected $resultFileSize = array();
        /* filtered URL that was the download-source    */
        protected $resultURL = array();
        /* id corresponding to the current string, matches other table , will get inserted in db  */
        protected $rowId;        
        /* name of table that stores binary files       */
        private $binary_table;
        
        /* create new autoDownloader + set variables                  */
        function __construct(  $maxFileSize, $rowId, $checkString, $downloadAllstuff, $binary_table_name ) {
              $this->downloadAll = $downloadAllstuff;  
              if ($this->downloadAll) {      // if no tool is required, search for any http:// entry to download
                  array_push( $this->toolArray, "http://");
                  //array_push( $this->toolArray, "https://");
              }
              
              $this->binary_table = $binary_table_name;    
              $this->maxFileSize = abs((int)$maxFileSize);              
              $this->checkString = $checkString;      
              $this->rowId = abs((int)$rowId);
              $this->checkAndDownload(  $checkString );  // needs to be last entry                               
        }    
        
        /* checks if string contains download-source and performs download if possible 
            if several commands sperated by semicolons are contained -> each command is cheched
            if one command contains several tools, only the last one with result 1 counts , then result 2, then max(result)
           return : void                                       */  
        private function checkAndDownload( $dlString ) {        
            $commands = explode( ";", $dlString );  
            $results = array();
            foreach( $commands as $key => $dwnLink ) {
                $tmpResult = array();   // if one command contains several tools, only the last one with result 1 counts
                foreach( $this->toolArray as $toolKey => $toolName ) {                    
                    $tmpResultCode[ $toolKey ] = $this->checkSingleString( $dwnLink, $toolName );
                    $tmpResultURL[ $toolKey ]  = $this->getURL();
                    $tmpResultSize[ $toolKey ] = $this->getFileSize();
                    
                    // Selection-Order if more than one tool in one command: Only one entry is counted , order 1, 2, 6,5,4,3,0
                    $tmpKey = array_search( "1", $tmpResultCode );
                    if ( !($tmpKey === false ))
                        $results[ $key ] = array( $tmpResultCode[ $tmpKey ], $tmpResultURL[ $tmpKey ], $tmpResultSize[ $tmpKey ]);                      
                    else {
                        $tmpKey = array_search( "2", $tmpResultCode );
                        if ( !($tmpKey === false ))
                            $results[ $key ] = array( $tmpResultCode[ $tmpKey ], $tmpResultURL[ $tmpKey ], $tmpResultSize[ $tmpKey ]);
                        else{
                            $maxiKey = array_search( max( $tmpResultCode ), $tmpResultCode );
                            $results[ $key ] = array( $tmpResultCode[ $maxiKey ], $tmpResultURL[ $maxiKey ], $tmpResultSize[ $maxiKey ]);
                        }
                    }                    
                }
 
                //print_r( $tmpResult); 
            } // end: inner-foreach
            $this->resultCodeArray = $results;
            
            //print_r( $results);
        }
        // returns results of entire search
        public function getResultCodeArray() {
            return $this->resultCodeArray;
        }
        // returns latest file size
        public function getFileSize() {
            return $this->resultFileSize;
        }
        // returns latest url of last search
        public function getURL() {
            return $this->resultURL;
        }
        
        // returns string with mime-type for the give filename
        private function get_mime( $filename ) {
            $file_extension = strtolower( substr( strrchr( $filename, "." ), 1 ) );
    
            /* Content-Type bestimmen */
            switch( $file_extension ) {
                  case "txt": $ctype="text/plain"; break;
                  case "htm": 
                  case "shtml": 
                  case "html": $ctype="text/html"; break;
                  case "pdf": $ctype="application/pdf"; break;
                  case "exe": $ctype="application/octet-stream"; break;
                  case "zip": $ctype="application/zip"; break;
                  case "tar": $ctype="application/x-tar"; break;
                  case "sh" : $ctype="application/x-sh"; break;    
                  case "doc": $ctype="application/msword"; break;              
                  
                  case "php": $ctype="text/x-php"; break;
                  case "p"  : $ctype="text/x-pascal"; break;                  	
                  case "c++":
                  case "cc":  
                  case "c": $ctype="text/x-c"; break;               
                  case "pl": $ctype="text/x-script.perl"; break;                                                      
                  
                  case "gif": $ctype="image/gif"; break;
                  case "png": $ctype="image/png"; break;                  
                  case "jpeg":
                  case "jpg": $ctype="image/jpg"; break;
                  
                  default: $ctype="application/default-type";
            }
            return $ctype;
        }
        
        /* checks if string contains download-source and performs download if possible  
           param downloadLink= string to check for downloads, toolStr = string containing name of tool to check for
           returns 0  no download found
                   1  download found + SUCCESSFULLY performed
                   2  download found but URL couldn't be retrieved or fileSize = 0
                   3  download found but problem when writing do disk
                   6  ftp download found, just displayed
                   7  download found but URL contains duplicate file -> file isn't stored again
                   4  other error                                               */
        private function checkSingleString( $downloadLink, $toolStr ){
            $max_size_per_line = 1024; // max filesize per line in Bytes
            $success = true;    
            $tempLink = $downloadLink;            
            $returnVal = 5;  // init 5 as default return
            $fileSize = 0;                                           
                        
            $foundTool = (!(stripos( $downloadLink, $toolStr) === false ));
            if ( $foundTool ){
                
                /*  FIND START OF LINK  */                
                if ( !(strcmp( $toolStr, "http://") == 0 || strcmp( $toolStr, "https://") == 0) )   // if no tool is looked for don't cut of beginning
                    $tempLink = substr( $downloadLink, strpos( $downloadLink, $toolStr ) 
                                                                 + strlen($toolStr)); // cut off everything after first "WGET"
                else {    // if no tool used but http:// is looked for   
                    if ( (strpos( $tempLink, "http://") === false) && (strpos( $tempLink, "https://") === false ))     
                        return 0;    
                }
                                                                                      
                $tempLink = ereg_replace("^[^a-zA-Z]*" , "", $tempLink );    // remove starting spaces, %20 etc.                
                if ( !(strpos( $tempLink, "http://") === false ))          // skip parameters "curl -O http://"
                    $tempLink = substr( $tempLink, strpos( $tempLink, "http://" ));
                else if ( !(strpos( $tempLink, "https://") === false ))          
                    $tempLink = substr( $tempLink, strpos( $tempLink, "https://" ));
                else if ( !(strpos( $tempLink, "ftp://") === false ))          
                    $tempLink = substr( $tempLink, strpos( $tempLink, "ftp://" ));
                else if ( !(strpos( $tempLink, "ftps://") === false ))          
                    $tempLink = substr( $tempLink, strpos( $tempLink, "ftps://" ));
                else if ( !(strpos( $tempLink, "www.") === false ))          // skip parameters "curl -O www.ha.."
                    $tempLink = substr( $tempLink, strpos( $tempLink, "www." ));
                else { 
                      //echo "nothing found:".$tempLink." ".$toolStr."<br>"; $success = false;
                }
                
                /*  FIND END OF LINK  */
                if ( !(strpos( $tempLink, "?") === false ))          // split off Get-variables, if any
                    $tempLink = substr( $tempLink, 0, strpos( $tempLink, "?" ));
                if ( !(strpos( $tempLink, ";") === false ))          // split of everything after semicolon
                    $tempLink = substr( $tempLink, 0, strpos( $tempLink, ";" ));
                if ( !(strpos( $tempLink, "%3B") === false ))          // split of everything after semicolon-hex
                    $tempLink = substr( $tempLink, 0, strpos( $tempLink, "%3B" ));            
                if ( !(strpos( $tempLink, " ") === false ))          // split of everything after SPACE
                    $tempLink = substr( $tempLink, 0, strpos( $tempLink, " " ));
                if ( !(strpos( $tempLink, ",") === false ))          // split of everything after comma
                    $tempLink = substr( $tempLink, 0, strpos( $tempLink, "," ));
                     //echo "MIDDLE".ereg_replace( "(\ )*", "", $tempLink )."SPACE".$tempLink."<br>";
                
                /* add "http://" if necessary as default*/
                if ( !((strpos( $tempLink, "https://") === 0 ) || (strpos( $tempLink, "ftp://") === 0 ) || 
                       (strpos( $tempLink, "ftps://") === 0 )  || (strpos( $tempLink, "http://") === 0 )))
                      $tempLink = "http://".$tempLink;
                 
                 /* ftp-links are just stored, not downloaded     */
                 if ((strpos( $tempLink, "ftp://") === 0 ) | (strpos( $tempLink, "ftps://") === 0 ))  
                      return 6;
                                       
                 /* make sure at least one dot -> URL   */
                if ( !(strpos( $tempLink, ".") === false )) {               
                     //$tempLink = "D://albie//vid.avi";
                    $remotefile = @fopen($tempLink,"r");
                    if($remotefile) {         
                        $buffer = "";                                                                                                          
              	             
                        // read file and store in buffer           		    
                        while (!feof( $remotefile ) && (($this->maxFileSize - 1) * $max_size_per_line)  > $fileSize ) {
                             $buffer .= @fgets($remotefile, $max_size_per_line );   
                             $fileSize = strlen( $buffer );                                                                                                                                                                                                   
                        }                            
                        $this->resultFileSize = $fileSize;
                        
                        // close file                                		                                                                                                               		              			                                             			
                  			if (!(@fclose($remotefile)))
                  			   $success = false;         			                    
                
                        // connect to logging-database      
                        //include_once "connect.php";  
                        
                        // write buffer to database  
                        if ($buffer != "" ) { 
                                 //echo "DA:".addslashed($buffer)."END<br><br>";
                                //$data = addslashes(fread(fopen($binFile, "r"), filesize($binFile)));
                                //$strDescription = addslashes(nl2br($txtDescription));
                                $fileName = strtolower( substr( strrchr( $tempLink, "/" ), 1 ) );
                                
                                // check if file already in database
                                $duplicate = false;
                                $sql = "SELECT source_url, filename, filesize, filetype FROM ".$this->binary_table."
                                        WHERE source_url='".addslashes( $tempLink)."' AND filename='".
                                        addslashes($fileName)."' AND filesize='".strlen($buffer)."' AND filetype= '".$this->get_mime( $fileName)."'";                                                                                                                         
                                $readResult = @mysql_query($sql);  
                                if ( $readResult )
                                     if( mysql_num_rows($readResult) > 0 )
                                            $duplicate = true;
                                
                                // insert new file, if not already in db
                                if (!$duplicate) {
                                    $sql = "INSERT INTO ".$this->binary_table." ( id_files, bin_data, source_url, filename, filesize, filetype )
                                            VALUES ('".$this->rowId."', '".addslashes($buffer)."', '".addslashes( $tempLink)."', '".
                                        addslashes($fileName)."','".strlen($buffer)."', '".$this->get_mime( $fileName)."' )";
                                                                                                                             
                                    $writeResult = @mysql_query($sql);
                                    
                                    if ($writeResult) {                                      
                                      //echo "ja". mysql_num_rows($writeResult);
                                      //mysql_free_result($writeResult); // it's always nice to clean up!
                                      //echo "Thank you. The new file was successfully added to our database.".strlen($buffer)."<br><br>";
                                    } else { 
                                          $success = false; 
                                          $returnVal = 3;            		 
                                      		//echo "Couldn't write to DB".$tempLink."<br>";     
                                    } 
                                } else {
                                      $this->resultFileSize = $fileSize;
                                      $this->resultURL = $tempLink;
                                      RETURN 7; // duplicate: url + file found, but both already in db                                  
                                }                                                                
                          } else { 
                                    $success = false; 
                                    $returnVal = 4;            		 
                                		//echo "Buffer or file empty".$tempLink."<br>";     
                          }                                                                                                                                 	   
                  	} else { 
                          $success = false; 
                          $returnVal = 2;            		 
                      		    //echo "URL not found".$tempLink."<br>";     
                    } 
                } else {
                    $success = false; 
                    $returnVal = 0;       
                    //echo "nothing found to download";
                } 
                //echo "Size:".$fileSize." Success:".$success." ".$tempLink." ".$returnVal."<br>";                                       
           } else {
              $success = false; 
              $returnVal = 0;       
              //echo "nothing found to download";
          }     
            
          // cleanup if error
          if ( $fileSize == 0 && $success == true ) {
              $success = false;
          }     
            
          // set results
          $this->resultFileSize = $fileSize;
          //if ( $returnVal != 0 && $success ) 
              $this->resultURL = $tempLink;  
                    
          if ( $success == true && $returnVal == 5 && $fileSize > 0 ) 
              return 1;
          else if ( $success == false & ( $returnVal == 0 ||  $returnVal == 2 || $returnVal == 3 ))
              return $returnVal;
          else if ( $success == true & $fileSize == 0 ) 
              return 3;
          else
              return 4;                                 
          } // end: function checkSingleString
    }

?>
