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

    define( 'SEARCH_SECURE', 'active' );
    
    $menu_selection = 2;
    error_reporting(E_ALL);
    session_start();
    
    include "inc/config.php"; // die Konfigurationsdateien lesen.

    // connect to logging-database
    include_once "connect.php";   

    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
    echo "<html>\n";
    echo "    <head>\n";
    echo "        <title>Meine Seite</title>\n";
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
    
        echo "            <div id=\"links\">\n"; // linkes Menu
    //include "navigation.php";
    echo "            </div>\n";

    echo "            </div>\n";
    echo "            <div id=\"mitte\">\n"; // In der Mitte der Inhalt
    include "searchMain.php";
    echo "            </div>\n";
    

    
    echo "            <br style=\"clear:both;\" />\n"; // css-float beenden
    echo "       </div>\n";
    

    echo "    </body>\n";
    echo "</html>\n";

?>
