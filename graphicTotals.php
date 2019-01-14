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

include ("grahpicLibraries/jpgraph.php");
include ("grahpicLibraries/jpgraph_log.php");
include ("grahpicLibraries/jpgraph_line.php");

$ydata = array();
if (isset($_GET)) {
    foreach( $_GET as $key => $value )
        array_push($ydata, abs((int)$value));
}

$arrayElements = count($ydata);

// avoid emtpy array
if ( $arrayElements < 12 )
    for( $i = 0; $i < 12 - $arrayElements; $i++ )
        array_push($ydata, 0 );

        
$datax=array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep", "Oct", "Nov", "Dec" );

//$ydata = array(11,3,8,12,5,1,9,13,5,7);
//$ydata2 = array(1,19,15,7,22,14,5,9,21,13);



// Create the graph. These two calls are always required
$graph = new Graph(500,200,"auto");	
$graph->SetScale("textlin");


// Create the linear plot
$lineplot=new LinePlot($ydata);

//$lineplot2=new LinePlot($ydata2);

// Add the plot to the graph
$graph->Add($lineplot);
//$graph->Add($lineplot2);

$graph->img->SetMargin(60,25,30,50);
$graph->title->Set("Total Hits Over Time");
$graph->xaxis->title->Set("Time");
$graph->yaxis->title->Set("Hits");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

$lineplot->SetColor("blue");
$lineplot->SetWeight(2);

//$lineplot2->SetColor("orange");
//$lineplot2->SetWeight(2);

//graph->yaxis->SetColor("red");
//$graph->yaxis->SetWeight(2);
//$graph->SetShadow();

$graph->xaxis->SetTickLabels($datax);
$graph->xaxis->SetTextTickInterval(2);

// Display the graph
$graph->Stroke();
?>
