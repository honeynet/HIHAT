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




  /* identifies browser out of user-agent string
     $http_user_agent : user agent string from $_SERVER array
     $showPics        : true if thumbnails should be returned, else strings
     return:            short string or thumbnail naming user agent    */
  function browserCheck( $http_user_agent, $showPics ) {    
      // number of browsers detected
      $browserNr = 27;
      // return string if no browser is detected
      $defaultBrowser = "no browser data";
      
      // array with detection signatures
      $detectionArray[ 0 ] = "Firefox";
      $detectionArray[ 1 ] = "MSIE 7.";
      $detectionArray[ 2 ] = "Opera";
      $detectionArray[ 3 ] = "Msnbot";
      $detectionArray[ 4 ] = "MSIE";      // all other versions of MSIE
      $detectionArray[ 5 ] = "Konqueror";
      $detectionArray[ 6 ] = "Netscape";
      $detectionArray[ 7 ] = "Safari";
      $detectionArray[ 8 ] = "Googlebot";  
      $detectionArray[ 9 ] = "Yahoo!";
      $detectionArray[ 10 ] = "Mozilla";
      
      $detectionArray[ 11 ] = "VMBot";  	   // less famous crawler or search-tools
      $detectionArray[ 12 ] = "MaSagool";
      $detectionArray[ 13 ] = "sproose";  
      $detectionArray[ 14 ] = "Krugle";  
      $detectionArray[ 15 ] = "psbot";
      
      $detectionArray[ 16 ] = "ichiro";  
      $detectionArray[ 17 ] = "Nutch";
      
      $detectionArray[ 18 ] = "WISEnutbot";
      $detectionArray[ 19 ] = "Speedy Spider";  // entireweb.com
      
      $detectionArray[ 20 ] = "majestic12";
      $detectionArray[ 21 ] = "Shim-Crawler";
      $detectionArray[ 22 ] = "Exabot";
      $detectionArray[ 23 ] = "WeRelate";
      $detectionArray[ 24 ] = "SKIZZLE";
      $detectionArray[ 25 ] = "Guruji";
      $detectionArray[ 26 ] = "YahooSeeker";      
      $detectionArray[ 27 ] = "VoilaBot";        
                
      
                          
      
      // array with images to show for each browser
      $imgArray[ 0 ] = "firefox.png";
      $imgArray[ 1 ] = "Internetexplorer.gif";
      $imgArray[ 2 ] = "opera_.png";
      $imgArray[ 3 ] = "msn.png";
      $imgArray[ 4 ] = "inetexplorer.png";
      $imgArray[ 5 ] = "konqueror.png";
      $imgArray[ 6 ] = "netscape.gif";
      $imgArray[ 7 ] = "safari.png";
      $imgArray[ 8 ] = "google.jpg";
      $imgArray[ 9 ] = "yahoo.png";
      $imgArray[ 10 ]= "mozilla_ico.gif";
      
      $imgArray[ 11 ] = "vmlogo.gif";
      $imgArray[ 12 ] = "sagool.gif";
      $imgArray[ 13 ] = "sproose.gif";
      $imgArray[ 14 ] = "krugle.gif";
      $imgArray[ 15 ] = "picsearch.gif";
            
      $imgArray[ 16 ] = "goo.gif";
      $imgArray[ 17 ] = "nutch.gif";   
      $imgArray[ 18 ] = "wise_nut.gif";    
      $imgArray[ 19 ] = "speedyspider.gif";
      $imgArray[ 20 ] = "majestic12.png";
      //$imgArray[ 21 ] = "";     
      $imgArray[ 22 ] = "exalead.gif";
      $imgArray[ 23 ] = "weRelate.gif";
      $imgArray[ 24 ] = "Skizzle.gif";
      $imgArray[ 25 ] = "GurujiLogo.gif";
      $imgArray[ 26 ] = "yahoo.png";        // YahooSeeker
      $imgArray[ 27 ] = "voila.gif";       

      
      // array with String to show (instead of pictures)
      $strArray[ 0 ] = "Firefox";
      $strArray[ 1 ] = "MSIE";
      $strArray[ 2 ] = "Opera";
      $strArray[ 3 ] = "Msnbot";
      $strArray[ 4 ] = "MSIE";
      $strArray[ 5 ] = "Konqueror";
      $strArray[ 6 ] = "Netscape";
      $strArray[ 7 ] = "Safari";
      $strArray[ 8 ] = "Googlebot";
      $strArray[ 9 ] = "Yahoo Slurp";
      $strArray[ 10 ]= "Mozilla";
      
      $strArray[ 11 ]= "VMBot";
      $strArray[ 12 ]= "Sagool";
      $strArray[ 13 ]= "Sproose";
      $strArray[ 14 ]= "Krugle";
      $strArray[ 15 ]= "PSBot";
      
      $strArray[ 16 ]= "goo";
      $strArray[ 17 ]= "Nutch";
      
      $strArray[ 18 ]= "WISEnutbot";      
      $strArray[ 19 ]= "Speedy Spider";
      
      $strArray[ 20 ]= "PicSearch";      
      $strArray[ 21 ]= "Shim-Crawler";
      $strArray[ 22 ]= "Exabot";
      $strArray[ 23 ]= "WeRelate";
      $strArray[ 24 ]= "Skizzle";
      $strArray[ 25 ]= "Guruji";
      $strArray[ 26 ]= "YahooSeeker";
      $strArray[ 27 ]= "Voila";      

      for( $i = 0; $i < $browserNr; $i++ ) {    
          if ( stripos( $http_user_agent, $detectionArray[ $i ] ) !== false ) {
                  $http_user_agent = htmlentities($http_user_agent);
                  if ( $showPics && isset( $imgArray[ $i ] ))
                      $browserResultString = "<img src=\"images/".$imgArray[ $i ]."\" border=\"0\" alt=\"".
                                           $http_user_agent."\" title=\"".$http_user_agent."\" 
                                            style=\"max-height:21px\" style=\"max-width:70px\" />";
                  else
                      $browserResultString = $strArray[ $i ];
                  
                  if ( isWebSpider( $http_user_agent) )
                      $browserResultString .= "<font size=\"-3\">BOT</font>";
                  return isset( $browserResultString ) ? $browserResultString : $http_user_agent;
          }
      }
      
      // if none is detected return default
      if ( strcmp( $http_user_agent, "" ) == 0 )
          return $defaultBrowser;    
      else
          return $http_user_agent;
  } 
  
  // returns true if user-agent string contains signature of known web spider
  function isWebSpider( $http_user_agent ) { 
       if ( stripos( $http_user_agent, "bot") !== false || 
            stripos( $http_user_agent, "crawler") !== false ||
            stripos( $http_user_agent, 'Yahoo! Slurp' ) !== false ||           
           (stripos( $http_user_agent, 'ichiro' ) !== false & stripos( $http_user_agent, 'goo' ) !== false) ||
           (stripos( $http_user_agent, 'Nutch' ) !== false && ( stripos( $http_user_agent, 'bot' ) !== false ||
                                                                stripos( $http_user_agent, 'crawl' ) !== false)) ||
            stripos( $http_user_agent, 'Speedy Spider' ) !== false || 
            stripos( $http_user_agent, 'SKIZZLE' ) !== false )
          return true;
      else
          return false;
  }
  
  function getKnownSearchEngines() {
      return array ('lycos.com', 'google.', 'yahoo.', 'altavista.', 'msn.com', 'picsearch', 'krugle', 'ichiro',
                    'entireweb.com', 'nutch', 'WISEnutbot', 'majestic' , 'VMBot', 'Majestic12', 'entireweb.com', 
                    'SKIZZLE', 'fireball.de');
  }
?>
