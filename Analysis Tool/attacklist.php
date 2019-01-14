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



  /* List with all attack-patterns to search for
     Contains names and html-thumbnail-strings for each attack to display
     
     Supports checking for word-segments or whole words ($strictWholeWordDef), 
     supports case-sensitive and non-sensitive checking ($caseSensitivityDef)      */     
  class attacklist {                                           
          
        protected $num_of_elements = 36;    // Number of attack-patterns in array

        /* detection-array structure: index 0 = pattern
                            index 1 = strictWholeWord   true means: only accept pattern, 
                                      if it occurs as a single word, seperated by space characteres
                            index 2 = caseSensitivity
                                      true means: only accept pattern, if it has exact case-sensitive spelling        */
        
        // main array containing the attack patterns to look for
        protected $detectArray = NULL;        
        protected $strictWholeWordDef = false;  // Default index 1: false
        protected $caseSensitivityDef = false;  // Default index 2: false
        
        // array containing the thumbnail-strings with html to return
        protected $imgArray = NULL;
        // array containing the short string-names of the attacks to return
        protected $strArray = NULL;
        
        private static $noDetectionStr = "no attack found";
         
        // constructor creates the arrays and sets varialbes          
        function __construct() {                                         
            for ( $i = 0; $i < $this->num_of_elements; $i++ ) {
                $this->detectArray[ $i ][ 1 ] = $this->strictWholeWordDef;
                $this->detectArray[ $i ][ 2 ] = $this->caseSensitivityDef;
            }                             
            // array with detection signatures    - MANDATORY
            $this->detectArray[ 0 ][ 0 ] = htmlentities( "<script>" );
            $this->detectArray[ 1 ][ 0 ] = htmlentities( "</script>");
            
            $this->detectArray[ 2 ][ 0 ] = "../";
            $this->detectArray[ 3 ][ 0 ] = "/..";
            
            $this->detectArray[ 4 ][ 0 ] = "OR";  $this->detectArray[4][1] = true; $this->detectArray[4][2] = true;  
            $this->detectArray[ 5 ][ 0 ] = "select"; $this->detectArray[5][1] = true;
            $this->detectArray[ 6 ][ 0 ] = "insert";  
            $this->detectArray[ 7 ][ 0 ] = "union";
            $this->detectArray[ 8 ][ 0 ] = "delete";
            $this->detectArray[ 9 ][ 0 ] = "where"; $this->detectArray[9][1] = true;
            
            $this->detectArray[ 10 ][ 0 ] = "wget";        
            $this->detectArray[ 11 ][ 0 ] = "curl"; 
            $this->detectArray[ 12 ][ 0 ] = "lynx"; 
            $this->detectArray[ 13 ][ 0 ] = "fetch"; 
            $this->detectArray[ 14 ][ 0 ] = "lwp-download"; 
            
            $this->detectArray[ 15 ][ 0 ] = "echo"; $this->detectArray[15][1] = true;
                                            
            $this->detectArray[ 16 ][ 0 ] = "javascript";
            
            $this->detectArray[ 17 ][ 0 ] = "`id`";     $this->detectArray[17][2] = true;              
            $this->detectArray[ 18 ][ 0 ] = "uname";    
            $this->detectArray[ 19 ][ 0 ] = "who";      $this->detectArray[19][1] = true;        
            $this->detectArray[ 20 ][ 0 ] = "ifconfig";  
            
            $this->detectArray[ 21 ][ 0 ] = htmlentities( "'" );   $this->detectArray[21][2] = true;    
            $this->detectArray[ 22 ][ 0 ] = htmlentities( "`" );   $this->detectArray[22][2] = true;
            $this->detectArray[ 23 ][ 0 ] = htmlentities( "´" );   $this->detectArray[23][2] = true;
             
            $this->detectArray[ 24 ][ 0 ] = "http://";            
            $this->detectArray[ 25 ][ 0 ] = "https://";            
            $this->detectArray[ 26 ][ 0 ] = "include";
            
            $this->detectArray[ 27 ][ 0 ] = "select%";  // select in general causes too many false positives     
            $this->detectArray[ 28 ][ 0 ] = "select/";
            $this->detectArray[ 29 ][ 0 ] = "select#";  
            $this->detectArray[ 30 ][ 0 ] = "select*";      
            $this->detectArray[ 31 ][ 0 ] = "select\\";
            $this->detectArray[ 32 ][ 0 ] = "trigger";
            $this->detectArray[ 33 ][ 0 ] = "OUTFILE";
            
            $this->detectArray[ 34 ][ 0 ] = "passthru";  $this->detectArray[34][2] = true;
            $this->detectArray[ 35 ][ 0 ] = "exec";      $this->detectArray[35][2] = true;
                                        
                        
            // array with thumbnails to show                          - OPTIONAL
            //$this->imgArray[ 5 ] = "<img src=\"images/sql.gif\" border=0 alt=\"SQL detected\" />";
            //$this->imgArray[ 1 ] = "inetexplorer.png";       
                
            // array with attack-names to show (instead of pictures)  - MANDATORY
            $this->strArray[ 0 ] = "XSS";
            $this->strArray[ 1 ] = "XSS";
            
            $this->strArray[ 2 ] = "DIR-Change";
            $this->strArray[ 3 ] = "DIR-Change";     
            
            $this->strArray[ 4 ] = "SQL";      
            $this->strArray[ 5 ] = "SQL";
            $this->strArray[ 6 ] = "SQL"; 
            $this->strArray[ 7 ] = "SQL";
            $this->strArray[ 8 ] = "SQL";
            $this->strArray[ 9 ] = "SQL";
            
            $this->strArray[ 10 ] = "WGET";         
            $this->strArray[ 11 ] = "CURL";
            $this->strArray[ 12 ] = "LYNX";
            $this->strArray[ 13 ] = "FETCH";
            $this->strArray[ 14 ] = "LWP";
            
            $this->strArray[ 15 ] = "DEFACE";
            
            $this->strArray[ 16 ] = "XSS";
            
            $this->strArray[ 17 ] = "INJECTION";
            $this->strArray[ 18 ] = "INJECTION";
            $this->strArray[ 19 ] = "INJECTION";
            $this->strArray[ 20 ] = "INJECTION";
            
            $this->strArray[ 21 ] = "INJECT";
            $this->strArray[ 22 ] = "INJECT";
            $this->strArray[ 23 ] = "INJECT";
            
            $this->strArray[ 24 ] = "INCLUSION";
            $this->strArray[ 25 ] = "INCLUSION";            
            $this->strArray[ 26 ] = "INCLUSION";
            
            $this->strArray[ 27 ] = "SQL";
            $this->strArray[ 28 ] = "SQL";
            $this->strArray[ 29 ] = "SQL";
            $this->strArray[ 30 ] = "SQL";
            $this->strArray[ 31 ] = "SQL";
            $this->strArray[ 32 ] = "SQL";
            $this->strArray[ 33 ] = "SQL";    
            
            $this->strArray[ 34 ] = "INCLUSION";
            $this->strArray[ 35 ] = "INCLUSION";                                                                                         
        } 
        // returns the main detection-array or NULL if nothing has been set
        public function getDetectionArray() {  
            return $this->detectArray;        
        }
        // returns the number of attack patterns detected
        public function getElementNr() {
            return $this->num_of_elements;
        }
        /* returns thumbnail-strings with html for specified $id
                   if no thumbnail-html-string was found, the short name is given back,
                   else error msg is returned                     */
        public function getImgVal( $id ) {  
            if ( isset( $this->imgArray[ $id] ))
                return $this->imgArray[ $id];
            else if ( isset( $this->strArray[ $id ]))                
                return $this->strArray[ $id];
            else 
                return "unknown id:".$id;        
        }
        /* returns short-name-strings for specified $id
                   if no short-name-string was found, error msg is returned */
        public function getStrVal( $id ) {  
            if ( isset( $this->strArray[ $id ]))                
                return $this->strArray[ $id];
            else 
                return "unknown id:".$id;      
        }
        /* returns default-string to display when no attack was found   */
        public static function getNoDetectionStr() {
            return self::$noDetectionStr;
        }
        
    } // end: class

?>
