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


if ( isset($_GET['id']))
  $id_files = abs((int)$_GET['id']);
else
  $id_files = "disable";

    
if ( is_int( $id_files)) {
    
    // read config files
    include_once "inc/config.php"; 
    // connect to logging-database
    include_once "connect.php";
    
    // fetch data    
    $sql = "SELECT bin_data, filename, filesize FROM ".BINARY_TABLE." WHERE id_files=".$id_files;  	
    $result = mysql_query($sql);
    
    if ( mysql_num_rows($result)) {
        while ($row = mysql_fetch_assoc($result)) {
            
              $data = isset( $row["bin_data"] ) ? $row["bin_data"] : "";
              $name = isset( $row["filename"] ) ? $row["filename"] : "unknown";
              $size = isset( $row["filesize"] ) ? $row["filesize"] : "0";
              $type = isset( $row["filetype"] ) ? $row["filetype"] : "text/plain";
            	
            	// put data into new header -> file is sent to browser
              header("Content-type: $type");
              header("Content-length: $size");
              header("Content-Disposition: attachment; filename=$name");
              header("Content-Description: PHP Generated Data");    
              echo $data;
        }
    } else 
        echo "file not found ";
} else echo "begone";
?>
