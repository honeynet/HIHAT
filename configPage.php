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


    $versionString = "(c) HIHAT Version 1.0";
    
    $menu_selection = 6;
    session_start();
    
    // default mode for overview-listing
    // Overview-Listing-Modes: 0 = all, 1 = none, 2 = whitelisting, 3 = blacklisting
    $defaultMode = 2;
    
    // shortView: only entries with attacks or filtered variables are displayed
    $shortViewDefault = false;
    
    // read config files
    include "inc/config.php"; 
    // connect to logging-database
    include_once "connect.php";
           
    error_reporting(E_ALL);

    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
    echo "<html>\n";
    echo "    <head>\n";
    echo "        <title>HIHAT - High Interaction Honeypot Analysis Tool</title>\n";
    echo "        <link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n";
    echo '<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
          <link rel="icon" href="images/favicon.ico" type="image/x-icon">';    
    echo "        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\" />\n";
    
        include "javascripts.php"; 
    
    echo "    </head>\n";
    echo "    <body>\n";

    echo "        <div id=\"root\">\n"; // ganz oberer Div-Holder
    echo "            <div id=\"banner\">\n"; // banner
    include "banner.php";    

    echo '<table cellpadding="10" cellspacing="10"><tr><td>';
    echo "<br><b>Configuration:</b><br>"; 

    // set new listing-mode for overview               
    if (isset( $_POST['listing']) && isset( $_POST['submitConfig'])) {
        $overviewMode = $defaultMode;
        $allowedLModes = array( "all", "none", "whitelist", "blacklist"); // Overview-Listing-Modes: 0 = all, 1 = none, 2 = whitelisting, 3 = blacklisting
        $newval = $_POST['listing'];
        foreach ($allowedLModes as $key => $value )
          if ( strcmp( $newval, $value ) == 0 )
              $listingMode = $key;              
    }        
        
    // ensure valid value  	       
        if ( $listingMode < 0 || $listingMode > 3 )
            $listingMode = $defaultMode;    
     
     
    // set variables if save-button was pressed
    if (isset( $_POST['submitConfig'])) {            
        if ( isset( $_POST['shortView']) && $_POST['shortView'] == "shortView" )
              $shortView = true;
            else
                $shortView = false;   
        
        if (isset( $_POST['listing'])) {                     
            $listingMode = $_POST['listing'];
            if (!($listingMode >= 0 && $listingMode <= 3))
                $listingMode = $configFile->getDefaultListingMode();    
        }
        
       if ( isset( $_POST['showBots']) && $_POST['showBots'] == "showBots" )
                $showBots = true;
            else
                $showBots = false;
       
       // save new configuration values to file
       $configFile->setListingMode( $listingMode);
       $configFile->setShortView( $shortView );
       $configFile->setshowBots( $showBots );
       if ( $configFile->writeConfig() )    
          echo "<b>Status<blink>:</blink></b> Configuration has been stored to disk";
        else
            echo "<b>Status:</b> Configuration file \"".$configFile->getFilename()."\" could not be written";  
    }  // variables have been saved
    
    // scan database for files to download (wget, etc.) , even if they have been checked before
    if ( isset($_POST['scan4downloads'])) {
        define('CHECKALL', "true");
        include "check_downloads.php";
    }
    

   
    $check = 'checked="checked"';  // string to add if entry is checked
    echo '<form action="configPage.php" method="post">
          <p>Please set listing-options for overview:</p>
          <p>              
            Listing-mode:<br>
            <input type="radio" name="listing" value="0" '.($listingMode == 0 ? $check : "").'> all<br>
            <input type="radio" name="listing" value="1" '.($listingMode == 1 ? $check : "").'> none<br>
            <input type="radio" name="listing" value="2" '.($listingMode == 2 ? $check : "").'> whitelisting<br>
            <input type="radio" name="listing" value="3" '.($listingMode == 3 ? $check : "").'> blacklisting<br><br>
            
            <div style="cursor:text;text-decoration:none;color:black;" 
                     onmouseover="return escape( \'Short View Mode: Conceal all entries in overview consisting of only two lines. \' )" >        
            Disable displaying of entries without attacks or filtered variables:
            <input type="checkbox" name="shortView" value="shortView" '.($shortView ? $check : "" ).'></div><br>
            
            <div style="cursor:text;text-decoration:none;color:black;" 
                     onmouseover="return escape( \'Notice: Entries are always displayed if attack was successfully detected\' )" >
            Show Entries of Web Spiders:
            <input type="checkbox" name="showBots" value="showBots" '.($showBots ? $check : "" ).'></div><br><br>
            <input type="submit" name="submitConfig" value="save" >     
            <input type="reset" value="cancel">
          </p>
        </form> ';
    
    echo "<div align=\"left\"><br><br><br><br>".$versionString;
    echo '</div> </body> </html>';
    
    echo '</td><td valign="top">';
        
    echo '<br><b>Administrative Actions:</b><br> <form action="configPage.php" method="post">         
              <input type="submit" name="scan4downloads" value="manually scan db and download all binaries again" 
               onmouseover="return escape( \'Scans the entire database for links to malicious tools. If successful, HIHAT tries to download and store the detected binary. Previously examined entries will be checked again!\' )">                  
         </form> ';
    
    echo "</td></table>";
    
    // store configuration-data in session
    //$_SESSION['listingMode'] = $listingMode;
    //$_SESSION['shortView']    = $shortView;
    
    //echo '<a href="http://cert.uni-stuttgart.de/stats/dns-replication.php?query='.$remote_address.'&submit=Query" onmouseover="return escape( \'bla\' )" target="_blank" >reverse-dns information</a>';

    echo '<script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script>';
   
?>
