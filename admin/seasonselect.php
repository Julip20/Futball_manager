<?php

/*
***************************************************************************
tplLeagueStats is a league stats software designed for football (soccer)
team.

Copyright (C) 2003  Timo Leppänen / TPL Design

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
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.


email:     info@tpl-design.com
www:       www.tpl-design.com/tplleaguestats

version 1.0 | July 2003
***************************************************************************
*/

session_start();

$sessioid = $_REQUEST['sessioid'];
$sessio = $_SESSION['sessio'];

//Check session_id
if(!isset($sessioid) || $sessioid != "$sessio")
{
	print("Authorization failed.<br>
	<a href=\"index.php\">Restart, please</a>");
}
else
{
	$HTTP_REFERER = $_SERVER['HTTP_REFERER'];
	$season = explode("___",$_POST['season_select']);

	$_SESSION['season_id'] = $season[0];
	$_SESSION['season_name'] = $season[1];

	header("Location: leaguematches.php?sessioid=$sessio");
}
