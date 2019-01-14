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
  
   // identifies browser out of user-agent string
   include_once "browserCheck.php"; 
    
    
   /* returns string to print out the first two lines of output-table
              $id....$referrer: Strings
              $decode_urls: activate url-decoding function
              $wwrapReferrer: int that indicates to break referrer after x characters
              $detected: string of attacks detected
              $color Bool : switches between two different colors, for better overview when IP changes
              $inDetailView: true if in Detail-View, adds delete-Button
     requires function browsercheck                                         */  
   function getTable( $id, $source, $remote_address, $http_user_agent, $creationTime, $referrer,
                      $decode_urls, $wwrapReferrer, $detected, $colorBool, $inDetailView, $boldAttacks, $boldReferrer ) {
                    
          // set table color
          $color = $colorBool ? "a" : "b";
                                
          $tableString = '<table border=0px; align="center" class="item">
                            
                              <tr> 
                                  <td align="center" class="ID_'.$color.'">
                                      '.($inDetailView ? "" : '<a href="detailFramework.php?id='.$id.'" title="->view details<-">' ).'
                                      <div width="100%">'.$id.'</div>
                                      '.($inDetailView ? "" : '</a>' ).'
                                  </td>
                                  <td class="source_'.$color.'">'.$source.'</td>
                                  <td class="ip-address_'.$color.'">'.$remote_address.'</td>
                                  <td class="browser_'.$color.'">'.browserCheck( $http_user_agent, true ).'</td>
                                  <td class="time_'.$color.'">'.$creationTime.'</td>                                  
                                  <td class="eval_'.$color.'">'.
                                    ( !$boldAttacks || ($detected  == attacklist::getNoDetectionStr()) ? "" : "<b>" ).$detected.
                                    ( !$boldAttacks || ($detected  == attacklist::getNoDetectionStr()) ? "" : "</b>" ).'</td>
                             </tr>';
                             
          // check if known search engine in referrer
          $knownEngineInReferrer = false;
          $knownEngineList = getKnownSearchEngines();
          if (!strcmp($referrer, 'no referrer') == 0 )                             
              foreach ($knownEngineList as $engine) 
            	   	  if (stristr ($referrer, $engine)) 
            	   	     $knownEngineInReferrer = true;
            	   	  
            		  
           $tableString .= '<tr>
                          		    <td class="blind'.($inDetailView ? "_trash" : "" ).'">'.
                                    ($inDetailView ? "<a href=\"javascript:deletionCheck('".$id."', 'false');\"><img src=\"images/trash.jpg\" border=\"0\" alt=\"DEL\" title=\"Delete Entry\" /></a>" : "" ).
                                  '</td>
                          		    <td colspan="5" class="ref_'.$color.'">'.
                            		        ($knownEngineInReferrer && $boldReferrer ? "<b>" : "" ).
                                        wordwrap( $decode_urls ? urldecode($referrer) : $referrer , $wwrapReferrer , "<br>\n", "true" ).
                                        ($knownEngineInReferrer && $boldReferrer ? "</b>" : "" ).
                                        '</td>                                                                  
                          	</tr>';
      
          //$tableString .= '</table>';
          
          return $tableString;  
  }  
      
?>
