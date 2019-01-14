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




    $menu_selection = 3;
    
    session_start();

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
    
    echo '<style type="text/css">
            a:visited {text-decoration: underline; color:#000000;}
            a:focus {text-decoration: underline; color:#000000;}
            a:link {text-decoration: underline; color:#000000;}
            a:active {text-decoration: underline; color:#000000;}
            a:hover {text-decoration: underline; color:#000000;} 
      </style>';
      
        include "javascripts.php"; 
    
    echo "    </head>\n";
    echo "    <body>\n";

    echo "        <div id=\"root\">\n"; // ganz oberer Div-Holder
    echo "            <div id=\"banner\">\n"; // banner
    include "banner.php";    

    echo "<br><b>Downloaded Files:</b><br>"; 

    $sql = "SELECT * FROM ".BINARY_TABLE." ORDER BY id_files DESC";    
    $result = mysql_query($sql );
    
    if ( mysql_num_rows( $result )) {                    
        $rows = mysql_num_rows($result);
        
        echo "<table align=\"center\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" width=\"980px\" bgcolor=\"#E4E4E4\"> \n  ";
        echo " <tr>\n";
        echo "  <td>ID</td>\n";
        echo "  <td>Filename</td>\n";
        echo "  <td>Filetype</td>\n";
        echo "  <td>Size (kbyte)</td>\n";
        echo "  <td colspan=\"3\">URL</td>\n";
        //echo "  <td> </td><td></td>\n";
        echo " </tr>\n";          
        
        for ($i = 0; $i < $rows; $i++) {
            $data = mysql_fetch_object($result);          
            echo " <tr>\n";
            echo "  <td align=\"center\"> <a href='detailFramework.php?id=".$data->id_files."'>$data->id_files</a> </td>\n";
            echo "  <td>$data->filename</td>\n";
            echo "  <td>$data->filetype</td>\n";
            echo "  <td>".(max(round(((int)$data->filesize) / 1024.0, 1 ),0.1))."</td>\n"; // REMINDER: <p> results in BIG table
            echo "  <td>" . stripslashes($data->source_url) . "</td>\n";
            echo "  <td align=\"center\"><a href=\"javascript:deletionCheck('".(int)$data->id_files."', 'true');\"><img src=\"images/trash.jpg\" border=\"0\" alt=\"DEL\" /></a>            
                    </td>\n";
            echo "  <td align=\"center\"> <a href='downloadTools.php?id=$data->id_files'>Download</a> </td>\n";
            echo " </tr>\n";
        }
        echo '</table><br>
              </body>
              </html>';
   }
   @mysql_free_result($result);
  
?>
