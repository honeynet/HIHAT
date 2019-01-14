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



    if (isset($menu_selection)) {
        if (!( $menu_selection > 0 && $menu_selection < 7 ))
          $menu_selection = 0; 
        else
            $menu_selection = (int)$menu_selection;
    }
    else
        $menu_selection = 0;
            
    // color selection  , select "cold" or "hot"        
    $default_off = "cold";
    $default_active = "hot";
    
    
    echo '<table width="100%" cellpadding="0" cellspacing="0" border="0">';     
    // leave out logo for mapping
    if (!isset($no_logo))        
       echo '<tr align="center">
          		  <td bgcolor="#6587ab"><img src="images/logo.png" border="0"></td>
          	 </tr>';
          	 
      echo "<tr align=\"center\">
            		<td valign=\"middle\" bgcolor=\"#3d5167\"><a href=\"overview.php?delete=true\" alt=\"Overview\" ><img src=\"images/menu-1-".($menu_selection == 1 ? $default_active : $default_off ).".png\" alt=\"Overview\" border=\"0\"></a><a href=\"search.php?delete=true\" alt=\"Search\" ><img src=\"images/menu-2-".($menu_selection == 2 ? $default_active : $default_off ).".png\" alt=\"Search\" border=\"0\"></a><a href=\"binaryData.php\" alt=\"Downloaded Binaries\" ><img src=\"images/menu-3-".($menu_selection == 3 ? $default_active : $default_off ).".png\" alt=\"Downloads\" border=\"0\"></a><a href=\"mapping.php?delete=true\" alt=\"Mapping\" ><img src=\"images/menu-4-".($menu_selection == 4 ? $default_active : $default_off ).".png\" alt=\"Mapping\" border=\"0\"></a><a href=\"showStats.php?delete=true\" alt=\"Statistics\" ><img src=\"images/menu-5-".($menu_selection == 5? $default_active : $default_off ).".png\" alt=\"Statistics\" border=\"0\"></a><a href=\"configPage.php\" alt=\"Configuration\" ><img src=\"images/menu-6-".($menu_selection == 6 ? $default_active : $default_off ).".png\" alt=\"Config\" border=\"0\"></a></td>
        	   </tr>
        </table>
        ";
    
   /* echo '<tr align="center">
            		<td bgcolor="#3d5167"><a href="overview.php?delete=true" alt="Overview" ><img src="images/menu-1-'.($menu_selection == 1 ? $default_active : $default_off ).'.png" alt="Overview" border="0"></a><a href="search.php?delete=true" alt="Search" ><img src="images/menu-2-'.($menu_selection == 2 ? $default_active : $default_off ).'.png" alt="Search" border="0"></a><a href="binaryData.php" alt="Downloaded Binaries" ><img src="images/menu-3-'.($menu_selection == 3 ? $default_active : $default_off ).'.png" alt="Downloads" border="0"></a><a href="mapping.php?delete=true" alt="Mapping" ><img src="images/menu-4-'.($menu_selection == 4 ? $default_active : $default_off ).'.png" alt="Mapping" border="0"></a><a href="showStats.php" alt="Statistics" ><img src="images/menu-5-'.($menu_selection == 5? $default_active : $default_off ).'.png" alt="Statistics" border="0"></a><a href="configPage.php" alt="Configuration" ><img src="images/menu-6-'.($menu_selection == 6 ? $default_active : $default_off ).'.png" alt="Config" border="0"></a></td>
        	   </tr>
        </table>
        ';
*/
?>


