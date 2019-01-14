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


      /* creates a new configuration file, containing default options     */
      class configFile {
            private $config_filename = 'HIHAT.conf';
            // directory to save the config file in, must end with slash
            protected $savedir;            
            
            
            protected $listingMode;     // Overview-Listing-Modes: 0 = all, 1 = none, 2 = whitelisting, 3 = blacklisting
            protected $shortView;   // only entries with attacks or values-found-by-filter will be display
            protected $showBots;    // show empty variables in overview
            
            protected $ListingModeStr = "ListingMode";
            protected $ShortViewStr   = "ShortView";
            protected $showBotsStr   = "showBots";     
            
            // set DEFAULT values , these values will be used if config-file cannot be read or has invalid values
            private $defaultListingMode = 2;
            private $defaultShortView = false;
            private $defaultshowBots = true;                   
            
            // constructer, takes directory to save config file in
            function __construct( $savedir ) {
                 $this->listingMode = $this->defaultListingMode;
                 $this->shortView   = $this->defaultShortView;
                 $this->showBots   = $this->defaultshowBots;
                 $this->savedir     = $savedir;    
            }
            
            // returns path + filename of config-file
            public function getFilename() {
                return $this->savedir .$this->config_filename;
            }
            
            // set vars
            public function setListingMode( $mode ) {
                $this->listingMode = $mode;
            }
            public function setShortView( $shortView ) {
                $this->shortView = $shortView;
            }
            public function setshowBots( $showBots ) {
                $this->showBots = $showBots;
            }
            
            // get vars                            
            public function getListingMode() {
                return $this->listingMode;
            }
            public function getShortView() {
                return $this->shortView;
            }
            public function getshowBots() {
                return $this->showBots;
            }
            
            // set default values
            public function getDefaultListingMode() {
                return $this->defaultListingMode;
            }
            public function getDefaultShortView() {
                return $this->defaultShortView;
            }
            public function getDefaultshowBots() {
                return $this->defaultshowBots();
            }
            
            /* reads the configuration from file and sets vars
               defaults are set if invalid values are found   
               returns  true if success                     */
            public function readConfig() {
                $max_size_per_line = 1024;
                $error = false;
                $filename = $this->savedir .$this->config_filename;
                if ( file_exists( $filename )) {
                    $readfile = @fopen( $this->savedir .$this->config_filename ,"r");
                    if( $readfile ) {                    
                        while (!feof($readfile)) {   // go through each line of config-file
                            $buffer = @fgets($readfile );
                            $tempLine = explode( "=", $buffer );
                            if (isset($tempLine[0]) && isset($tempLine[1])) {      // check which value is being set in this line                
                              
                                if ( strcasecmp( $tempLine[0], $this->ListingModeStr ) == 0 ) {   // set Listing-mode
                                    $lmode = abs((int)$tempLine[1]);
                                    if ( $lmode >= 0 && $lmode <= 3 )                                    
                                        $this->listingMode = $lmode;
                                     else
                                        ;//echo "invalid data1".$tempLine[1];                                         
                                 } else if ( strcasecmp( $tempLine[0], $this->ShortViewStr ) == 0 ) {   // set shortView                                                                
                                        $this->shortView = (bool)(int)$tempLine[1];                                  
                                 } else if ( strcasecmp( $tempLine[0], $this->showBotsStr ) == 0 ) {   // set shortView                                                               
                                        $this->showBots = (bool)(int) $tempLine[1];                                         
                                 } else
                                      $error = true;  // empty config-file          
                            } 
                        } // end: while
                            
                        // CLOSE Filehandle
                        @fclose( $readfile );                            
                    } else
                        $error = true;   
                } else  
                        $error = true; 
                                       
                return $error;
                
            }
            
            /* write the current configuration to logfile
               returns true if success                                    */
            public function writeConfig() {
                $error = false;
                $stringFileContent  = "ListingMode=".$this->listingMode."\n";
                $stringFileContent .= "ShortView=".$this->shortView."\n";
                $stringFileContent .= "showBots=".$this->showBots."\n";
                
                $outfile = @fopen( $this->savedir .$this->config_filename ,"w");
                if( $outfile ) {
                    @fputs( $outfile, $stringFileContent, strlen( $stringFileContent ));
                    @fclose( $outfile );     
                } else
                    $error = true;                 
                return !$error;
            }
      }
?>
