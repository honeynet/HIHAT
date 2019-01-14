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

   
  include_once "whitelist.php";
  include_once "blacklist.php";
  
  // filtering modes: white, black, all, none
  function filterVars( $stringArrayToCheck, $mode, $addOnArray = NULL ){        
  
      if ( $mode == "all" )
         return 1;
      else if ( $mode == "none")
        return 0;
      else if ( $mode == "white" )
         return ListChecking( $stringArrayToCheck, $mode, $addOnArray );
      else if ( $mode == "black")
         return ListChecking( $stringArrayToCheck, $mode, $addOnArray );
      else
         die ("Unknown Filtering Mode ");
  }
  
  function ListChecking( $stringArray, $mode, $module ) {
      if ( $mode == "white")
          $whitemode = true;
      else if ( $mode == "black")
          $whitemode = false;
      else die( "Unknown mode in filtering system");      
          
      $positiveListArray = ( $whitemode ? whitelist() : blacklist() ); // black-or-whitelist array
      if ( isset( $module ) && !( $module == NULL )) {
          $startIndex = count( $positiveListArray );
                    
          $addOnArray = $whitemode ? $module->getWhiteArray() : $module->getBlackArray() ;
          $addOnArrayElements = $whitemode ? $module->getWhiteElementNr() : $module->getBlackElementNr() ;
          //echo $addOnArray[ 1 ][ 0 ]."AHA:";
          //if ( $addOnArray[ 1 ][ 1 ] == true )
              
          for ( $k = 0; $k < $addOnArrayElements; $k++ ) {
              $positiveListArray[ $startIndex + $k ][ 0 ] = $addOnArray[ $k ][ 0 ];
              $positiveListArray[ $startIndex + $k ][ 1 ] = $addOnArray[ $k ][ 1 ];
              $positiveListArray[ $startIndex + $k ][ 2 ] = $addOnArray[ $k ][ 2 ];
          }     
      }   
      
      foreach( $stringArray as $strkey => $strValue ) {   // check entire array           
          for( $i = 0; $i < count( $positiveListArray ); $i++ ) {    // check for each entry in whitelist              
              if ( $positiveListArray[ $i ][ 1 ] == true & $positiveListArray[ $i ][ 2 ] == true ) {  // strict + case sensitive
                  if ( strcmp( $strValue, $positiveListArray[ $i ][ 0 ] ) == 0 )
                          return $whitemode;                 
              } else if ( $positiveListArray[ $i ][ 1 ] == true & $positiveListArray[ $i ][ 2 ] == false ) {    // only strict
                  if ( strcasecmp($strValue, $positiveListArray[ $i ][ 0 ] ) == 0 )
                          return $whitemode;
              } else if ( $positiveListArray[ $i ][ 1 ] == false & $positiveListArray[ $i ][ 2 ] == false ) {                  
                  if ( !(stripos( $strValue, $positiveListArray[ $i ][ 0 ] ) === false) )  // non case-sens non-strict-checking: check if string is CONTAINED 
      	                 return $whitemode;    
              } else if ( $positiveListArray[ $i ][ 1 ] == false & $positiveListArray[ $i ][ 2 ] == true ) {
                  if ( !(strpos( $strValue, $positiveListArray[ $i ][ 0 ] ) === false) )  // non-strict case-sensitive checking 
      	              return $whitemode;   
              }                                                                                                        	     
          }     
      }  
      return !$whitemode;    
  }
    
  /* compares the two Strings with the DetectionArray in the Obj.     
     returns -1 if no attack found, else image or simple-string out of Attack-Obj is returned    */
  function attackChecking( $strLeft, $strRight, $attacks_obj, $image = true) {         
      $attckListArray = $attacks_obj->getDetectionArray();     
      $detected = false;  // bollean, true as soon as at least one pattern was found
      $resultStr = "";    // default return value if nothing is found
      $resultArr = array(); // array with indices of detected patterns
        
      $stringArrayL = explode( " ", $strLeft );
      $stringArrayR = explode( " ", $strRight );      
      $strArrayLR = array_merge( $stringArrayL , $stringArrayR );
      
      foreach( $strArrayLR as $strkey => $strValue ) {   // check entire array           
          for( $i = 0; $i < $attacks_obj->getElementNr(); $i++ ) {    // check for each entry in Detection array                                     
                  if ( $attckListArray[ $i ][ 1 ] == true & $attckListArray[ $i ][ 2 ] == true ) {  // strict + case sensitive
                      if ( strcmp( $strValue, $attckListArray[ $i ][ 0 ] ) == 0 )
                              array_push( $resultArr, $i );                                                                                         	     
                  } else if ( $attckListArray[ $i ][ 1 ] == true & $attckListArray[ $i ][ 2 ] == false ) {   // only strict
                       if ( strcasecmp( $strValue, $attckListArray[ $i ][ 0 ] ) == 0 )
                              array_push( $resultArr, $i );                                                                                                 	     
                  } else if ( $attckListArray[ $i ][ 1 ] == false & $attckListArray[ $i ][ 2 ] == false ) {  // non case-sens non-strict-checking: check if string is CONTAINED 
                      if ( !(stripos( $strValue, $attckListArray[ $i ][ 0 ] ) === false) )  
                              array_push( $resultArr, $i );                                                                                         	     
                  } else if ( $attckListArray[ $i ][ 1 ] == false & $attckListArray[ $i ][ 2 ] == true ) {  // non-strict case-sensitive checking 
                      if ( !(strpos( $strValue, $attckListArray[ $i ][ 0 ] ) === false) )   
                              array_push( $resultArr, $i );                                                                                        	     
                  }    
          }
      }
      
      // evaluate results
      if ( count ( $resultArr ) < 1 )
          return -1;
      else {       
          $resultArr = array_unique( $resultArr );  //  avoid duplicate entries of detected attacks
          while( count($resultArr) > 0 ) {
              $element = array_shift( $resultArr);                                 
              $newStr = $image ? $attacks_obj->getImgVal( $element ) : 
                                 $attacks_obj->getStrVal( $element );
           
              if ( (stripos( $resultStr, $newStr ) === false ) )
                  $resultStr .=  $newStr ." ";
          }
          return substr( $resultStr, 0, strlen( $resultStr ) -1 ); // cut off last space
      }
  }
  /* checks if $detcted = false. If YES: $detcted is set true and $strToDelete is erased    
     This function ensures that initial value of $string is deleted once an attack was found    */
  function setDetected( $detected, $strToDelete ) {
        if ( !$detected ) {
            $detected = true;
            $strToDelete = "";
        }
    }  
   
  
?>
