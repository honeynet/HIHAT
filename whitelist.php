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

  /* returns array with whitelist-elements - these are potentially dangerous patterns
  
     index 0: whitelist-string
     index 1: true if string should only be accepted as a word on its on, not when contained in another word / string
     index 2: true if case-sensitivity is activated for this entry  */
  function whitelist( ){
    $num_of_elements = 55;
    $strictWholeWordDefault = false;  // Default index 1: false
    $caseSensitivityDefault = false;  // Default index 2: false
    for ( $i = 0; $i < $num_of_elements; $i++ ) {
      $white[ $i ][ 1 ] = $strictWholeWordDefault;
      $white[ $i ][ 2 ] = $caseSensitivityDefault;
    }
    
    $white[0][0]  = "username";  
    $white[1][0]  = "password"; $white[1][1] = false; $white[1][2] = false;
    $white[2][0]  = "pwd"; $white[2][1] = true;
    $white[3][0]  = "login";
    $white[4][0]  = "table"; $white[4][1] = true; $white[4][2] = false;
    $white[5][0]  = "query"; 
    $white[6][0]  = "key"; $white[6][1] = true;
    $white[7][0]  = "script"; // was: script
    
    $white[8][0]  = "SELECT"; 
    $white[9][0]  = "UNION";
    $white[10][0] = "FROM";
    $white[11][0] = "OR";    $white[11][1] = true; $white[11][2] = true;     
    $white[12][0] = "WHERE"; $white[12][1] = true;    
    $white[13][0] = "INSERT";        
    
    $white[14][0] = "admin"; $white[14][1] = true;
    $white[15][0] = "root";
    $white[16][0] = "echo";
    $white[17][0] = "include";
    $white[18][0] = "system"; $white[18][1] = true;       
    $white[19][0] = "exec";
    
    $white[20][0] = "id";  $white[20][1] = false;    
    $white[21][0] = "uname";
    $white[22][0] = "screen"; $white[22][1] = true;         
    
    $white[23][0] = "/..";                        $white[23][2] = true;
    $white[24][0] = "../";                        $white[24][2] = true;
    
    $white[25][0] = "wget";
    $white[26][0] = "curl";
    $white[27][0] = "lynx";
    $white[28][0] = "fetch";
    $white[29][0] = "lwp-download";
    $white[30][0] = "get";  $white[30][1] = true;        
    
    $white[31][0] = ".txt";
    $white[32][0] = ".php";
    $white[33][0] = ".gif";
    $white[34][0] = ".jpg";
    $white[35][0] = ".png";
    $white[36][0] = ".exe";    
    $white[37][0] = ".php";
    

    $white[38][0] = "who";
    $white[39][0] = "uname";
    $white[40][0] = "ifconfig";
    $white[41][0] = "/bin";     // 2 be removed?
    
    $white[42][0] = "http://";    
    $white[43][0] = "https://";    
    $white[44][0] = "DELETE";   // SQL command    
    $white[45][0] = "'";       
    $white[46][0] = "`";
    $white[47][0] = "´";
    
    $white[48][0] = "ls";  $white[48][1] = true;
    $white[49][0] = "trigger";      // SQL trigger
    $white[50][0] = "xml";      
    
    $white[51][0] = "open";
    $white[52][0] = "passthru";
    
    $white[53][0] = "INTO OUTFILE";
    $white[54][0] = "nc";   $white[54][1] = true; $white[54][2] = true; // netcat
      
    
    // Hochkomma einfügen?
          
    return $white;
  }
  
  
  
  

?>
