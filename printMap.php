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

    if ( !defined( 'MAP_SECURE' ))
        die('You cannot call this file directly!');
        
    echo " <noscript><b>JavaScript must be enabled in order for you to use the MAP.<br></b>However, it seems JavaScript is either disabled or not supported by your browser. To view the MAP, enable JavaScript by changing your browser options, and then try again.</noscript>";     
    echo "<script src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=".GOOGLE_MAPS_KEY."\"";
    echo " type=\"text/javascript\"></script><script type=\"text/javascript\"> ";
    
    echo ' //<![CDATA[     
      
              function load() {
              
      if (GBrowserIsCompatible()) {
      
        var map = new GMap2(document.getElementById("map"));
        
      // ====== Restricting the range of Zoom Levels =====
      // Get the list of map types      
      var mt = map.getMapTypes();
      // Overwrite the getMinimumResolution() and getMaximumResolution() methods
      for (var i=0; i<mt.length; i++) {
        mt[i].getMinimumResolution = function() {return 1;}
        mt[i].getMaximumResolution = function() {return 6;}
      }
      // ====== Restricting the range of Zoom Levels =====

        
        map.addControl(new GLargeMapControl());
        map.addControl(new GMapTypeControl());
        map.addControl(new GScaleControl());
      	map.setCenter(new GLatLng(26.341, 7.71141), 2);  // 20.341, 14.1141), 2);
      	map.setMapType( G_HYBRID_MAP );


        // Our info window content
        var infoTabs = [
          new GInfoWindowTab("Info", "This is tab #1 content"),
	      new GInfoWindowTab("Attacks", "SQL: This is tab #2 contentWe<i>have</i> have<br>been developing a lot of stuff, but however")
        ];
        
          // create base Icon for custom marker
          var baseIcon = new GIcon();
          baseIcon.iconSize=new GSize(32,32);
          baseIcon.shadowSize=new GSize(0,0); //56,32);
          baseIcon.iconAnchor=new GPoint(14, 27); //16,32);
          baseIcon.infoWindowAnchor=new GPoint(16,0);             
        

        // Creates a marker at the given point with the given number label
        function createMarker(point, country, region, city , ip, detections, attackIDS ) {          
          var Icon = new GIcon(baseIcon, "images/icon13.png", null, "images/icon13s.png");
        
          var marker = new GMarker(point, Icon);
          GEvent.addListener(marker, "click", function() {                      
              var infoTabs = [ 
                  new GInfoWindowTab("Location", "<span class=map_content><table><tr><td><b>Country:</b></td><td> " + country + " </td></tr><tr><td>Region: </td><td>" 
                                            + region + " </td></tr><tr><td>City: </td><td>" + city + " </td></tr><tr><td>IP: </td><td>" + ip + "</td></tr></table></span>" )
                  , new GInfoWindowTab("Attacks", "<span class=map_content>IP: " + ip + 
                                       "<br>Attacks from this IP: " + detections + " <br>Attack-ID(s): " + attackIDS + "</span>" )
              ];
              marker.openInfoWindowTabsHtml(infoTabs);
          });          
          GEvent.addListener(marker, "mouseout", function() {              
              //map.closeInfoWindow();
          });          
          return marker;
        }  ';     
        
        // Add markers to the map    
        foreach ( $ipData as $key => $val ) {
            $countryL = isset($val["countryLONG"]) ? trim( $val["countryLONG"]) : "";
            $region   = isset($val["ipREGION"])    ? trim( $val["ipREGION"] )   : "";
            $city     = isset($val["ipCITY"])      ? trim( $val["ipCITY"] )   : "";
            $ip       = isset($val["IP"])          ? trim( $val["IP"] )   : "";
            $lat      = isset($val["ipLATITUDE"])  ? trim( $val["ipLATITUDE"] )   : "";
            $long     = isset($val["ipLONGITUDE"]) ? trim( $val["ipLONGITUDE"] )   : "";
            $attackAr = isset($val["attacks"])     ?       $val["attacks"]   : "";
            $attackIDAr = isset($val["ids"])     ?       $val["ids"]   : "";
            
            $detectionStr = "";
            if ( is_array( $attackAr ))
                foreach( $attackAr as $attackKey => $attackValue )    // create detection-string
                    $detectionStr .= $attackValue." ";
            else
                $detectionStr = "-";  // no attack found: default
                            
            $attackIDString = "";   
            if ( is_array( $attackIDAr )) {
                foreach( $attackIDAr as $attackIDKey => $attackIDValue ) {    // create detection-string
                    $attackIDString .= $attackIDValue; 
                    if ( count($attackIDAr) - $attackIDKey > 1 )  // if not last entry: add comma
                        $attackIDString .= ", ";
                }
            } else
                $attackIDString = "-.-";  // no attack found: default
                   
            if ( $lat != ""  && $long != "" )                         // print marker
                echo '
                  var point = new GLatLng( '.$val["ipLATITUDE"].','.$val["ipLONGITUDE"].');
                  map.addOverlay(createMarker(point, "'.$countryL.'", "'.$region.'", "'.$city.'","'.$ip.'", "'.$detectionStr .'", "'.$attackIDString.'" )); ';            
        }
              
       echo ' 
       } else {
          alert("Sorry, the MAPS function is not compatible with this browser");
      }
    }

    //]]>
    </script>  
    
  <body onload="load()" onunload="GUnload()"> ';
?>
