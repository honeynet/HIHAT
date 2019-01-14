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

    // Handles the control of modules , DANGER! INJECTION of ARBITRARY CODE! 
    class moduleHandler {
        // directory containing the modules
        private $moduleDir = "modules";
        // directory separator character
        private $dirSeparator = "/"; 
        
        // main array containing the modules
        private $moduleArray;      // position 0 = module class, 1 = name of class + module  
        private $moduleNr; 
        
        // includes each php-file in the modules directory and creates a new class with it's filename
        function __construct() {   
            $this->moduleNr = 0;
            $path = ".".$this->dirSeparator.$this->moduleDir.$this->dirSeparator;                
            if( $dir = opendir( $path )) { 
                 while( false !== ($file = readdir($dir))) {
                    if ( !is_dir( $file ) && $file != "." && $file != ".." && substr( $file, -4 ) == ".php" ){                          
                            include_once $path . $file;   // DANGER! INJECTION of ARBITRARY CODE HERE! 
                            $newClass = (substr( $file, 0, strlen( $file ) - 4 ));   
                            if ( class_exists( $newClass )) {                                                         
                                $this->moduleArray[ $this->moduleNr ][ 0 ] = new $newClass;
                                $this->moduleArray[ $this->moduleNr ][ 1 ] = $newClass;
                                $this->moduleNr++;
                             } else 
                                echo "<b>".$newClass." is not a valid class. Please remove this module! </b><br>";                                                                                                      
                    }
                 }
                 closedir($dir);
            }                                                                                                    
        } 
        
        /* gives modules for given String $moduleName
           returns first module if $moduleName matches name of a module in array, else NULL   */ 
        public function getModule( $moduleName ) {
            for( $i = 0; $i < $this->moduleNr; $i++ ) 
                if ( strcasecmp( $this->moduleArray[ $i ][ 1 ] , $moduleName ) == 0 )
                    return $this->moduleArray[ $i ][ 0];                        
                                
            return NULL;
        }
        // returns the current number of modules in the array
        public function getModuleNr() {
            return $this->moduleNr;
        }
    }
?>
