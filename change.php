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

$HTTP_REFERER = $_SERVER['HTTP_REFERER'];

$submit = $_POST['submit'];
$submit2 = $_POST['submit2'];
$submit3 = $_POST['submit3'];
$submit4 = $_POST['submit4'];
$submit5 = $_POST['submit5'];
$submit6 = $_POST['submit6'];
$submit7 = $_POST['submit7'];

if($submit)
{
	$season = $_POST['season'];

	//New value for session variable
	$_SESSION['defaultseasonid'] = $season;

	header("Location: $HTTP_REFERER");
}
elseif($submit2)
{
	$change = $_POST['change_show'];

	//New value for session variable
	$_SESSION['defaultshow'] = $change;

	header("Location: index.php?sort=pts");
}
elseif($submit3)
{
	$change = $_POST['change_table'];

	//New value for session variable
	$_SESSION['defaulttable'] = $change;

	header("Location: $HTTP_REFERER");
}
elseif($submit4)
{
	$change = $_POST['home_id'];

	//New value for session variable
	$_SESSION['defaulthomeid'] = $change;

	header("Location: $HTTP_REFERER");
}
elseif($submit5)
{
	$change = $_POST['away_id'];

	//New value for session variable
	$_SESSION['defaultawayid'] = $change;

	header("Location: $HTTP_REFERER");
}
elseif($submit6)
{
	$moveto = $_POST['moveto'];

	header("Location: $moveto");
}
elseif($submit7)
{
	$language = $_POST['language'];

	//New value for session variable
	$_SESSION['defaultlanguage'] = $language;

	header("Location: $HTTP_REFERER");
}
else
{
header("Location: index.php?sort=pts");
}

?>