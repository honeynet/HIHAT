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

   if ( !defined( 'SEARCH_SECURE' ))
        die('You cannot call this file directly!');


    // SESSION-MANAGEMENT
    //-------------------
    // erase old session
    if ( isset( $_GET['delete'] )){
        unset( $_SESSION['sIdArray'] );
        unset( $_SESSION['sMode'] );
        unset( $_SESSION['sStart'] );
        unset( $_SESSION['sEnd'] );
        unset( $_SESSION['sString'] );        
        //unset( $_SESSION['ip'] );            
    } ;     

    // load tooltip description
    include 'searchEngine.php';
    $searchDescription = searchEngine::getSearchDescription(); 
    $search4TimeDescription = "Click icons to select time- and date range. <br> Alternatively enter timestamp according to following format: \'16-01-2012 12:51:42\'";
    
    echo '<br><form name="rangeStart" action="overview.php" method="post" class="formular">';
              //<div style="cursor:text;text-decoration:none;color:black;" 
              //       onmouseover="return escape( \''.$searchDescription.' \' )" >
    echo '             Search for id(s), ip or attack:
              <input type="text" name="attackStr" value="enter id(s), ip or attack"               
                                onclick="this.form.attackStr.value = \'\' " 
                                onmouseover="return escape( \''.$searchDescription.'\' )"  />
            
              <input type="submit" name="submitSearch" value="Search" 
                     onmouseover="return escape( \''.$searchDescription.'\' )"/>
            &nbsp;&nbsp;&nbsp;      
            
            <input type="Text" name="range1" value="" onmouseover="return escape( \''.$search4TimeDescription.'\' )" />
    		        <a href="javascript:cal7.popup();"><img src="img/cal.gif" width="16" height="16" border="0" alt="Click to enter date"></a>    		        
            <input type="Text" name="range2" value="" onmouseover="return escape( \''.$search4TimeDescription.'\' )" />
    		        <a href="javascript:cal8.popup();"><img src="img/cal.gif" width="16" height="16" border="0" alt="Click to enter date"></a>
          
              <input type="submit" name="submitRanges" value="Search Dates" onmouseover="return escape( \''.$search4TimeDescription.'\' )"/>                        
      </form>

      <script language="JavaScript">
  			<!-- // create calendar object(s) just after form tag closed
  				 // specify form element as the only parameter (document.forms[\'formname\'].elements[\'inputname\']);
  				 // note: you can have as many calendar objects as you need for your application
  				var cal7 = new calendar1(document.forms[\'rangeStart\'].elements[\'range1\']);
  				cal7.year_scroll = true;
  				cal7.time_comp = true;
          
          var cal8 = new calendar1(document.forms[\'rangeStart\'].elements[\'range2\']);
  				cal8.year_scroll = true;
  				cal8.time_comp = true;					
  			//-->
			</script>

';
           
    //echo '<a href="http://cert.uni-stuttgart.de/stats/dns-replication.php?query='.$remote_address.'&submit=Query" onmouseover="return escape( \'bla\' )" target="_blank" >reverse-dns information</a>';

    echo '<script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script>';    


?>
