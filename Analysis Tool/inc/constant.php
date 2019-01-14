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


// constant.php
// configure HIHAT in this file

    define('MYSQL_HOST', 'localhost');                  // host with sql-database
    define('MYSQL_USER', 'user');                       // username for sql-database
    define('MYSQL_PASS', 'pass');                       // passowrd for sql-database    
    define('MYSQL_DATABASE', 'DB');               // name of database with logentries
    define('MYSQL_IPLOOKUP_DB', 'ip2location');         // name of database with ip2location data
    define('BINARY_TABLE', 'binary_tools');             // name of database to store malicious tools in    
    define('GOOGLE_MAPS_KEY', 'KEY');  // key for google-maps, has to match URL of website 
    define( 'PATH_CONFIGFILE_SAVDIR', '');    // path where configuration-file will be stored, should end with "/"
    define( 'MAX_FILESIZE', 1024  );          // max. filesize for tools to automatically download in kbytes
    
    define('URL_DECODE', false);             /* apply function urldecode before printing http-referer, 
                                                enabling may result in security risk, default: false        */                                        
    define('HIGHLIGHT_ATTACKS',  true);      // print detected attacks bold
    define('HIGHLIGHT_REFERRER', true);      // print HTTP-referrer bold if known search engine detected 
?>
