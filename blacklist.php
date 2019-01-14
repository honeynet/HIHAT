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



 
    /* returns array with whitelist-elements
     index 0: whitelist-string
     index 1: true if string should only be accepted as a word on its on, not when contained in another word / string
     index 2: true if case-sensitivity is activated for this entry  */
  function blacklist( ){
    $num_of_elements = 4;
    $strictWholeWordDefault = false;  
    $caseSensitivityDefault = false;
    
    for ( $i = 0; $i < $num_of_elements; $i++ ) {
      $black[ $i ][ 1 ] = $strictWholeWordDefault;
      $black[ $i ][ 2 ] = $caseSensitivityDefault;
    }
    
    $black[0][0] = "token"; $black[0][1] = true; 
    $black[1][0] = "rows"; $black[1][1] = true; $black[1][2] = false;  
    $black[2][0] = "columns"; $black[2][1] = true;    
    $black[3][0] = "PHPSESSID"; $black[3][1] = true; $black[3][2] = true;  
    
    
    return $black;
  }

?>
