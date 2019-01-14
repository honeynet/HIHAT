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

include ("grahpicLibraries//jpgraph.php");
include ("grahpicLibraries//jpgraph_pie.php");
include ("grahpicLibraries//jpgraph_pie3d.php");

$this_title = "Attack Summary"; // Default title
$this_xsize = 550;
$this_ysize = 200;

$data = array();
$dataLegend = array();

// if options are set-> store options first, afterwards get data
if (isset($_GET)) {
    if (isset($_GET['title'])) {      
        $this_title = htmlentities($_GET['title']);
        unset($_GET['title']);
    } 
    if (isset($_GET['ysize'])) {
        $this_ysize = (int)($_GET['ysize']);
        unset($_GET['ysize']);
    } 
    if (isset($_GET['xsize'])) {
        $this_xsize = (int)($_GET['xsize']);
        unset($_GET['xsize']);
    } 
    foreach( $_GET as $key => $value ) {
        array_push($data, abs((int)$value));
        array_push($dataLegend, $key);
    }
}


// Some data
//$data = array(20,27,45,75,90);

// Create the Pie Graph.
$graph = new PieGraph($this_xsize,$this_ysize,"auto");
//$graph->SetShadow();

// Set A title for the plot
$graph->title->Set( $this_title );
$graph->title->SetFont(FF_FONT2,FS_NORMAL,16); 
//$graph->title->SetFont(FF_COMIC,FS_BOLD,20);
//$graph->title->SetColor('white');
$graph->title->SetColor("darkblue");
$graph->legend->Pos(0.05,0.22);
$graph->legend->SetShadow(false);

// Specify margins since we put the image in the plot area
$graph->SetMargin(2,2,30,2);
//$graph->SetMarginColor('navy');

// Setup background
$graph->SetBackgroundImage('images/worldmap1.jpg',BGIMG_FILLPLOT);

// Create pie plot
$p1 = new PiePlot3d($data);
$p1->SetTheme("earth");  // sand, pastel, water, earth
$p1->SetCenter(0.3);
$p1->SetColor("darkblue");
$p1->SetAngle(30);
$p1->value->SetFont(FF_FONT1,FS_NORMAL,12);
$p1->SetLegends( $dataLegend ); 

$graph->Add($p1);
$graph->Stroke();

?>


