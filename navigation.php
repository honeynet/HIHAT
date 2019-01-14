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

   
   // nr of entries to show on one page, these are allowed 
   $allowedRangeValues = array( 50, 200, 500, 1000, 2000 );
   $showRangeDefault = 200;   
              
    // this is just used once when a new range is set           
    if ( isset( $_GET['range'] ))             
          $_SESSION['showRange'] = (int)$_GET['range'];    // store current Range unvalidated in Session  
    
   // this is used each time , sets the $showRange: nr of entries to show per page
   if ( isset( $_SESSION['showRange'] )) { 
        $showRange = abs( (int)$_SESSION['showRange']);
        if ( array_search( $showRange, $allowedRangeValues ) === false)
              $showRange = $showRangeDefault;        
   } else
          $showRange = $showRangeDefault;   
          
   /* allow paging, this is used by SQL-"LIMIT": Start-First Entry to display 
      here the value is transferred from url ($_GET) to Session ,  $_GET needed by Javascript   */  
   if ( isset( $_GET['startEntry'] )) {
              $startEntry = abs( (int)$_GET['startEntry'] );      
              if ( $startEntry % $showRange != 0 )    // only allow Start-Entries acc. to $showRange
                   $startEntry = 0;
    } else
          $startEntry = 0;  // 0 means 'latest entry in db'
                    
      
   // html output for links + Range Selection
   echo '
     <table align="center">
	      <tr>
		      <td><a href="overview.php?startEntry='.(($startEntry - $showRange) >= 0 ? ($startEntry - $showRange) : '0').'">
                  <img src="images/page_previous.png" border="0" alt="previous page"></a>                  
              <a href="overview.php?startEntry='.($startEntry + $showRange).'">
                  <img src="images/page_next.png" border="0" alt="next page">
              </a></td>';

  
  if ( !(isset( $navigation_position ) && $navigation_position == "bottom")) {
          echo '<td>&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-family: Courier New; font-size: 12px; line-height: 12px; color: Black;">Max. Entries per page:</span></td>                
                <td><form name="rangeStart" action="overview.php" method="post" class="formular"> 
                  <select size=1 name="Auswahl" onChange=
                     "goTo( 1*this.form.Auswahl.options[this.form.Auswahl.options.selectedIndex].value)" >
                     
                     //style="width:350px; 
                    // background-color:#FFFFE0;
                     //font-size:9pt; font-family:Arial,sans-serif;" width=350>
                     
                     <option value="'.$showRangeDefault.'" SELECTED >change';
                     
          foreach ( $allowedRangeValues as $key => $val ) 
                  echo '<option value="'.(int)$val.'" >'.(int)$val;   

          
          
          
         echo '</select></td>
                
                <td><input type="hidden" name="attackStr" value="attacks" /> </td>
                <td><input type="hidden" name="submitSearch" value="Search" /> </td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="image" src="images/searchforattacks.png" name="submitSearch" value="List Attacks" alt="Search4Attacks" /> </td> 
                
                </form></td>';
    }  // end: menu top
    
    echo '</tr></table>';
    
?>
