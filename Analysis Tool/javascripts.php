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

    // range limitation function
    echo '  
    <script language="JavaScript">
          <!--
           function goTo( range ) {
             //alert( document.location );     
             document.location = "overview.php?range=" + range;        
          }
          
          function alertmsg() {
              alert( document.location );
          }
          
                  
          function deletionCheck(ID, binary ){    
                if ( binary == "false" )
                    text2display = "Delete ID " + ID + " permanently and continue with overview?";
                else
                    text2display = "Delete ID " + ID + " permanently?";
                Check =       confirm( text2display );                          
                if ( Check != false ) {
                        document.location= "deleteSingleEntry.php?binary=" + binary + "&id=" + ID;
                }              
          }  				   		
      
          //-->
    </script> ';
          
    // import calendar functions      
    echo '  
      <!-- European format dd-mm-yyyy -->
      <script language="JavaScript" src="calendar1.js"></script>
            
      <!-- American format mm/dd/yyyy -->
      <script language="JavaScript" src="calendar2.js"></script> '; 

?>
