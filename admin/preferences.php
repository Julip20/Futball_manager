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

//Check the session_id
if(!isset($sessioid) || $sessioid != "$sessio")
{
	print("Authorization failed.<br>
	<a href=\"index.php\">Restart, please</a>");
}
else
{
	//Connect to the database and select used database
	include('user.php');
	$connection = mysql_connect("$host","$user","$password")
	or die(mysql_error());
	mysql_select_db("$txt_db_name",$connection)
	or die(mysql_error());

	$seasonid = $_SESSION['season_id'];
	$seasonname = $_SESSION['season_name'];

	$PHP_SELF = $_SERVER['PHP_SELF'];
	$submit = $_POST['submit'];

	if($submit)
	{
		$teamname = trim($_POST['teamname']);
		$defaultseason = $_POST['defaultseason'];
		$defaultshow = $_POST['defaultshow'];
		$defaultlanguage = $_POST['defaultlanguage'];
		$accept_language = $_POST['accept_language'];
		$defaulttable = $_POST['defaulttable'];
		$printdate = $_POST['printdate'];
		$forwin = trim($_POST['forwin']);
		$fordraw = trim($_POST['fordraw']);
		$forloss = trim($_POST['forloss']);
		$homeid = trim($_POST['homeid']);
		$awayid = trim($_POST['awayid']);
		$topoftable = trim($_POST['topoftable']);
		$bg1 = trim($_POST['bg1']);
		$bg2 = trim($_POST['bg2']);
		$inside = trim($_POST['inside']);
		$bordercolour = trim($_POST['bordercolour']);
		$tablewidth = trim($_POST['tablewidth']);

		if(!isset($accept_language))
		{
			$accept_language = 0;
		}

		mysql_query("UPDATE tplls_preferences SET
		teamname = '$teamname',
		defaultseasonid = '$defaultseason',
		defaultshow = '$defaultshow',
		defaulttable = '$defaulttable',
		defaultlanguage = '$defaultlanguage',
		acceptmultilanguage = '$accept_language',
		printdate = '$printdate',
		forwin = '$forwin',
		fordraw = '$fordraw',
		forloss = '$forloss',
		defaulthomeid = '$homeid',
		defaultawayid = '$awayid',
		topoftable = '$topoftable',
		bg1 = '$bg1',
		bg2 = '$bg2',
		inside = '$inside',
		bordercolour = '$bordercolour',
		tablewidth = '$tablewidth'
		WHERE ID = '1'",$connection)
		or die(mysql_error());
	}

	//Query to get preferences
	$pref = mysql_query("SELECT * FROM tplls_preferences WHERE ID = '1'",$connection)
	or die(mysql_error());
	$pdata = mysql_fetch_array($pref);
	mysql_free_result($pref);

?>
	<html>
	<head>
	<title>
	tplLeagueStats - admin area
	</title>
	<link rel="stylesheet" type="text/css" href="../css/tplss_admin.css">
	</head>
	<body>
	<?php
	include('menu.php');

	?>

	<table align="center" width="600">
	<tr>
	<td>
	<form method="post" action="<?php echo "$PHP_SELF?sessioid=$sessio" ?>">
	<table width="100%" cellspacing="3" cellpadding="3" border="0">

	<tr>

	<td align="left" valign="top" colspan="2">
	<h1>General preferences</h1>
	</td>

	</tr>

	<tr>
	<td align="left" valign="top">
	Team name:
	</td>
	<td align="left" valign="top">
	<input type="text" name="teamname" value="<?php echo $pdata['teamname'] ?>">
	</td>
	</tr>

	<tr>
	<td align="left" valign="top">
	Default season:
	</td>
	<td align="left" valign="top">
	<select name="defaultseason">
	<option value="0">All</option>
	<?php
	$get_seasons = mysql_query("SELECT * FROM tplls_seasonnames WHERE SeasonPublish = '1' ORDER BY SeasonName",$connection)
	or die(mysql_error());

	while($data = mysql_fetch_array($get_seasons))
	{
		if($data['SeasonID'] == $pdata['defaultseasonid'])
			echo "<option value=\"$data[SeasonID]\" SELECTED>$data[SeasonName]</option>\n";
		else
			echo "<option value=\"$data[SeasonID]\">$data[SeasonName]</option>\n";
	}

	mysql_free_result($get_seasons);
	?>
	</select>
	</td>
	</tr>



	<tr>
	<td align="left" valign="top">
	Default language:
	</td>
	<td align="left" valign="top">
	<select name="defaultlanguage">
	<?php

	//
	//Lets get languages
	//1=finnish
	//2=english
	//

	if($pdata['defaultlanguage'] == 2)
		echo '<option value="2" SELECTED>English</option>';
	else
		echo'<option value="2">English</option>';

	if($pdata['defaultlanguage'] == 1)
		echo '<option value="1" SELECTED>Finnish</option>';
	else
		echo'<option value="1">Finnish</option>';

	?>

	</select>

	</td>

	</tr>

	<tr>
	<td align="left" valign="top">
	Accept multilanguage?
	</td>
	<td align="left" valign="top">
	<?php
	if($pdata['acceptmultilanguage'] == 1)
	{
		echo"<input type=\"checkbox\" name=\"accept_language\" value=\"1\" CHECKED>\n";
	}
	else
	{
		echo"<input type=\"checkbox\" name=\"accept_language\" value=\"1\">\n";
	}
	?>
	</td>

	</tr>

	<tr>
	<td align="left" valign="top">
	Default league table:
	</td>
	<td align="left" valign="top">
	<select name="defaulttable">
	<?php

	//
	//1 = traditional
	//2 = mathematical
	//3 = recent form
	//4 = simple
	//

	if($pdata['defaulttable'] == 4)
		echo '<option value="4" SELECTED>Simple</option>';
	else
		echo'<option value="4">Simple</option>';

	if($pdata['defaulttable'] == 1)
		echo '<option value="1" SELECTED>Traditional</option>';
	else
		echo'<option value="1">Traditional</option>';

	if($pdata['defaulttable'] == 2)
		echo '<option value="2" SELECTED>Mathematical</option>';
	else
		echo'<option value="2">Mathematical</option>';

	if($pdata['defaulttable'] == 3)
		echo '<option value="3" SELECTED>Recent form</option>';
	else
		echo'<option value="3">Recent form</option>';

	?>

	</select>

	</td>

	</tr>

	<tr>
	<td align="left" valign="top">
	Match calendar:
	</td>
	<td align="left" valign="top">
	<select name="defaultshow">
	<?php

	//
	//1 = all
	//2 = own only
	//3 = do not show
	//

	if($pdata['defaultshow'] == 1)
		echo '<option value="1" SELECTED>Show all</option>';
	else
		echo'<option value="1">Show all</option>';

	if($pdata['defaultshow'] == 2)
		echo '<option value="2" SELECTED>Show own team matches only</option>';
	else
		echo'<option value="2">Show own team matches only</option>';

	if($pdata['defaultshow'] == 3)
		echo '<option value="3" SELECTED>Do not show</option>';
	else
		echo'<option value="3">Do not show</option>';

	?>

	</select>

	</td>

	</tr>

	<tr>
	<td align="left" valign="top">
	Print date:
	</td>
	<td align="left" valign="top">
	<select name="printdate">
	<?php

	//
	//1 = dd.mm.yyyy
	//2 = mm.dd.yyyy
	//3 = month name date year
	//

	if($pdata['printdate'] == 1)
		echo '<option value="1" SELECTED>dd.mm.yyyy</option>';
	else
		echo'<option value="1">dd.mm.yyyy</option>';

	if($pdata['printdate'] == 2)
		echo '<option value="2" SELECTED>mm.dd.yyyy</option>';
	else
		echo'<option value="2">mm.dd.yyyy</option>';

	if($pdata['printdate'] == 3)
		echo '<option value="3" SELECTED>month name date year</option>';
	else
		echo'<option value="3">month name date year</option>';

	?>

	</select>

	</td>

	</tr>

	<tr>
	<td align="left" valign="top">
	Points for win:
	</td>
	<td align="left" valign="top">
	<input type="text" name="forwin" value="<?= $pdata['forwin'] ?>" size="2">
	</td>
	</tr>

	<tr>
	<td align="left" valign="top">
	Points for draw:
	</td>
	<td align="left" valign="top">
	<input type="text" name="fordraw" value="<?= $pdata['fordraw'] ?>" size="2">
	</td>
	</tr>

	<tr>
	<td align="left" valign="top">
	Points for loss:
	</td>
	<td align="left" valign="top">
	<input type="text" name="forloss" value="<?= $pdata['forloss'] ?>" size="2">
	</td>
	</tr>

	<tr>
	<td align="left" valign="top">
	Default home team:
	</td>
	<td align="left" valign="top">
	<select name="homeid">
	<?php
	$get_teams = mysql_query("
	SELECT DISTINCT
	O.OpponentName AS name,
	O.OpponentID AS id
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE
	O.OpponentID = LM.LeagueMatchHomeID OR
	O.OpponentID = LM.LeagueMatchAwayID
	ORDER BY name
	", $connection)
	or die(mysql_error());

	while($data = mysql_fetch_array($get_teams))
	{
		if($pdata['defaulthomeid'] == $data['id'])
			echo"<option value=\"$data[id]\" SELECTED>$data[name]</option>\n";
		else
			echo"<option value=\"$data[id]\">$data[name]</option>\n";
	}


	?>
	</select>
	</td>
	</tr>

	<tr>
	<td align="left" valign="top">
	Default away team:
	</td>
	<td align="left" valign="top">
	<select name="awayid">
	<?php
	mysql_data_seek($get_teams, 0);

	while($data = mysql_fetch_array($get_teams))
	{
		if($pdata['defaultawayid'] == $data['id'])
			echo"<option value=\"$data[id]\" SELECTED>$data[name]</option>\n";
		else
			echo"<option value=\"$data[id]\">$data[name]</option>\n";
	}

	mysql_free_result($get_teams);

	?>
	</select>
	</td>
	</tr>

	<tr>

	<td align="left" valign="top" colspan="2">
	<br>
	<h1>Layout preferences</h1>
	Use hexadecimal and #-character with colours.
	</td>

	</tr>

	<tr>
	<td align="left" valign="top">
	Top of table colour:
	</td>
	<td align="left" valign="top">
	<input type="text" name="topoftable" value="<?= $pdata['topoftable'] ?>" size="10">
	</td>
	</tr>

	<tr>
	<td align="left" valign="top">
	Cell background colour when listing teams:
	</td>
	<td align="left" valign="top">
	<input type="text" name="bg1" value="<?= $pdata['bg1'] ?>" size="10">
	</td>
	</tr>

	<tr>
	<td align="left" valign="top">
	Cell background behind pld, +/- and Pts:
	</td>
	<td align="left" valign="top">
	<input type="text" name="bg2" value="<?= $pdata['bg2'] ?>" size="10">
	</td>
	</tr>

	<tr>
	<td align="left" valign="top">
	Background colour inside the table:
	</td>
	<td align="left" valign="top">
	<input type="text" name="inside" value="<?= $pdata['inside'] ?>" size="10">
	</td>
	</tr>

	<tr>
	<td align="left" valign="top">
	Border colour of the table:
	</td>
	<td align="left" valign="top">
	<input type="text" name="bordercolour" value="<?= $pdata['bordercolour'] ?>" size="10">
	</td>
	</tr>

	<tr>
	<td align="left" valign="top">
	Width of the table:
	</td>
	<td align="left" valign="top">
	<input type="text" name="tablewidth" value="<?= $pdata['tablewidth'] ?>" size="10">
	</td>
	</tr>


	<tr>

	<td align="left" valign="top" colspan="2">
	<input type="submit" name="submit" value="Save preferences">
	</form>
	</td>

	</tr>

	</table>

	</td>
	</tr>
	</table>

	</body>
	</html>

<?php
mysql_close($connection);
}
?>
