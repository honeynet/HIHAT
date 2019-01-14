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


    session_start();

    // read config files
    include_once "inc/config.php"; 
    // connect to logging-database
    include_once "connect.php";
             
    error_reporting(E_ALL);
    
    // init value with error-default 
    $id = -1; 
    $delBinary = -1;
    // check parameters
    if ( isset( $_GET['binary'] )){
          if ( strcmp( $_GET['binary'], "false") === 0)
              $delBinary = 0;
          else if ( strcmp( $_GET['binary'], "true") === 0)
              $delBinary = 1;         
          
          if ( isset( $_GET['id'] ))
              $id = abs((int)$_GET['id']);       
    } ;                            


    if ( $delBinary != -1 && $id != -1 ) {   // if parameter correct
        if ( $delBinary == 0 ) {
                 // delete logentries
                $sql = "DELETE FROM main_logs WHERE ID = ".$id;  
                $result = mysql_query($sql );          
        } else if ( $delBinary == 1 ) {
                $sql = "DELETE FROM ".BINARY_TABLE." WHERE id_files = ".$id;   // delete binary
                $result = mysql_query($sql ); 
        } else; // error        
   
    }
    // continue with overview if source=details
    header("Location: ".( $delBinary == 1 ? "binaryData.php" : "overview.php"));
  
?>
