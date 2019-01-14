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


  /* module for application PhpMyAdmin, contains specified white-and-blacklist  
     remark: name of this file MUST MATCH name of class it contains           */
 class phpBB {                                   
        // name of this module, used for matching 
        private   $name = "phpBB";   
                    
        protected $wlistElements = 5; // Number of Elements in Whitelist
        protected $blistElements = 15; // Number of Elements in Blacklist

        /* array structure: index 0 = pattern
                            index 1 = strictWholeWord   true means: only accept pattern, 
                                      if it occurs as a single word, seperated by space characteres
                            index 2 = caseSensitivity
                                      true means: only accept pattern, if it has correct case-sensitive spelling        */
                                                                   
        // main array containing whitelist for this module
        protected $whiteArray = NULL; 
        // main array containing blacklist for this module
        protected $blackArray = NULL;
        
        protected $strictWholeWordDef = false;  // Default index 1: false
        protected $caseSensitivityDef = false;  // Default index 2: false

        // constructor creates the arrays and sets varialbes                   
        function __construct() {                                         
            for ( $i = 0; $i < $this->wlistElements; $i++ ) {
                $this->whiteArray[ $i ][ 1 ] = $this->strictWholeWordDef;
                $this->whiteArray[ $i ][ 2 ] = $this->caseSensitivityDef;
            }                             
            $this->whiteArray[ 0 ][ 0 ] = "email";
            $this->whiteArray[ 1 ][ 0 ] = "website";    $this->whiteArray[1][1] = false; $this->whiteArray[1][2] = false;
            $this->whiteArray[ 2 ][ 0 ] = "redirect";
            $this->whiteArray[ 3 ][ 0 ] = "message";
            $this->whiteArray[ 4 ][ 0 ] = "email";    

            // ------------------------------------------------------------------
            
             for ( $i = 0; $i < $this->blistElements; $i++ ) {
                $this->blackArray[ $i ][ 1 ] = $this->strictWholeWordDef;
                $this->blackArray[ $i ][ 2 ] = $this->caseSensitivityDef;
            }                             
            $this->blackArray[ 0 ][ 0 ]  = "f"; $this->blackArray[0][1] = true; $this->blackArray[0][2] = true;
            $this->blackArray[ 1 ][ 0 ]  = "f"; $this->blackArray[0][1] = true; $this->blackArray[0][2] = true;
            $this->blackArray[ 2 ][ 0 ]  = "icq";
            $this->blackArray[ 3 ][ 0 ]  = "aim";
            $this->blackArray[ 4 ][ 0 ]  = "yim";
            $this->blackArray[ 5 ][ 0 ]  = "msn";         $this->blackArray[5][1] = true; $this->blackArray[5][2] = true;
            $this->blackArray[ 6 ][ 0 ]  = "location";    $this->blackArray[6][1] = true; $this->blackArray[6][2] = true;
            $this->blackArray[ 7 ][ 0 ]  = "occupation";  $this->blackArray[7][1] = true; $this->blackArray[7][2] = true;
            $this->blackArray[ 8 ][ 0 ]  = "u";           $this->blackArray[8][1] = true; $this->blackArray[8][2] = true;
            $this->blackArray[ 9 ][ 0 ]  = "mode";        $this->blackArray[9][1] = true; $this->blackArray[9][2] = true;
            $this->blackArray[ 10 ][ 0 ] = "agreed";      $this->blackArray[10][1] = true; $this->blackArray[10][2] = true;
            $this->blackArray[ 11 ][ 0 ] = "p";           $this->blackArray[11][1] = true; $this->blackArray[11][2] = true;
            $this->blackArray[ 12 ][ 0 ] = "coppa";
            $this->blackArray[ 13 ][ 0 ] = "view";
            $this->blackArray[ 14 ][ 0 ] = "folder";
            
           
                                                                                             
        } 
        
        // returns whitelist-array or NULL if nothing has been set 
        public function getWhiteArray() {  
            return $this->whiteArray;        
        }        
        // returns blacklist-array or NULL if nothing has been set
        public function getBlackArray() {  
            return $this->blackArray;  
        } 
        
        // returns number of elements in whitelist
        public function getWhiteElementNr() {
            return $this->wlistElements;
        }
        // returns number of elements in blacklist
        public function getBlackElementNr() {
            return $this->blistElements;
        }
        
        // returns the name of this module
        public function getName() {
            return $this->name;
        }                 
    } // end: class
  
?>
