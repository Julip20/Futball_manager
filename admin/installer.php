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

$submit = $_POST['submit'];
$PHP_SELF = $_SERVER['PHP_SELF'];

if($submit)
{
	//Connect to the database and select used database
	include('user.php');
	$connection = mysql_connect("$host","$user","$password")
	or die(mysql_error());

	$username = trim($_POST['username']);
	$password = trim($_POST['password']);
	$password2 = trim($_POST['password2']);
	$season = trim($_POST['season']);
	$team = trim($_POST['team']);

	//
	//Fields filled?
	//
	if($username == '' || $password == '' || $password2 == '' || $season == '' || $team == '')
	{
		echo'Fill all fields.';
		exit();
	}

	//
	//Passwrds are the same?
	//
	if($password != "$password2")
	{
		echo'You didn\'t retype correctly.';
		exit();
	}

	if(strlen($password) < 6)
	{
		echo'Your password must be at least six characters long.';
		exit();
	}


	//
	//Create database if not exists
	//
	mysql_query("CREATE DATABASE IF NOT EXISTS $txt_db_name",$connection)
	or die(mysql_error());

	//
	//Select the database
	//
	mysql_select_db("$txt_db_name");

	//
	//Deducted points table
	//
	mysql_query("
	CREATE TABLE tplls_deductedpoints (
	  id int(11) NOT NULL auto_increment,
	  seasonid int(10) unsigned NOT NULL default '0',
	  teamid smallint(4) unsigned NOT NULL default '0',
	  points tinyint(3) NOT NULL default '0',
	  PRIMARY KEY  (id),
	  KEY seasonid (seasonid),
	  KEY opponentid (teamid)
	) TYPE=MyISAM
	",$connection)
	or die();

	//
	//Matches table
	//
	mysql_query("
	CREATE TABLE tplls_leaguematches (
	  LeagueMatchID int(10) unsigned NOT NULL auto_increment,
	  LeagueMatchSeasonID int(10) unsigned NOT NULL default '0',
	  LeagueMatchDate date NOT NULL default '0000-00-00',
	  LeagueMatchHomeID smallint(4) unsigned NOT NULL default '0',
	  LeagueMatchAwayID smallint(4) unsigned NOT NULL default '0',
	  LeagueMatchHomeWinnerID smallint(4) NOT NULL default '0',
	  LeagueMatchHomeLoserID smallint(4) NOT NULL default '0',
	  LeagueMatchAwayWinnerID smallint(4) NOT NULL default '0',
	  LeagueMatchAwayLoserID smallint(4) NOT NULL default '0',
	  LeagueMatchHomeTieID smallint(4) NOT NULL default '0',
	  LeagueMatchAwayTieID smallint(4) NOT NULL default '0',
	  LeagueMatchHomeGoals tinyint(2) unsigned default '0',
	  LeagueMatchAwayGoals tinyint(2) unsigned default '0',
	  PRIMARY KEY  (LeagueMatchID),
	  KEY LeagueMatchSeasonID (LeagueMatchSeasonID),
	  KEY LeagueMatchHomeID (LeagueMatchHomeID),
	  KEY LeagueMatchAwayID (LeagueMatchAwayID),
	  KEY LeagueMatchHomeWinnerID (LeagueMatchHomeWinnerID),
	  KEY LeagueMatchHomeLoserID (LeagueMatchHomeLoserID),
	  KEY LeagueMatchAwayWinnerID (LeagueMatchAwayWinnerID),
	  KEY LeagueMatchAwayLoserID (LeagueMatchAwayLoserID),
	  KEY LeagueMatchHomeTieID (LeagueMatchHomeTieID),
	  KEY LeagueMatchAwayTieID (LeagueMatchAwayTieID)
	) TYPE=MyISAM
	",$connection)
	or die();

	//
	//Passwords table
	//
	mysql_query("
	CREATE TABLE tplls_passwords (
	  PasswordID int(10) unsigned NOT NULL default '0',
	  PasswordUser varchar(16) NOT NULL default '',
	  PasswordPassword varchar(32) NOT NULL default '',
	  PRIMARY KEY  (PasswordID)
	) TYPE=MyISAM
	",$connection)
	or die();

	//
	//Preferences table
	//
	mysql_query("
	CREATE TABLE tplls_preferences (
	  id tinyint(1) unsigned NOT NULL default '1',
	  teamname varchar(128) NOT NULL default '',
	  defaultseasonid int(10) unsigned NOT NULL default '0',
	  defaultshow tinyint(1) unsigned NOT NULL default '0',
	  defaulttable tinyint(1) unsigned NOT NULL default '0',
	  forwin tinyint(1) unsigned NOT NULL default '0',
	  fordraw tinyint(1) unsigned NOT NULL default '0',
	  forloss tinyint(1) unsigned NOT NULL default '0',
	  printdate tinyint(1) unsigned NOT NULL default '0',
	  defaultlanguage tinyint(3) unsigned NOT NULL default '0',
	  acceptmultilanguage tinyint(1) unsigned NOT NULL default '1',
	  topoftable varchar(7) NOT NULL default '',
	  bg1 varchar(7) NOT NULL default '',
	  bg2 varchar(7) NOT NULL default '',
	  inside varchar(7) NOT NULL default '',
	  bordercolour varchar(7) NOT NULL default '',
	  tablewidth smallint(4) unsigned NOT NULL default '0',
	  defaulthomeid smallint(4) unsigned NOT NULL default '0',
	  defaultawayid smallint(4) unsigned NOT NULL default '0',
      last_updated datetime NOT NULL default '0000-00-00 00:00:00',
	  PRIMARY KEY  (id)
	) TYPE=MyISAM
	",$connection)
	or die();

	//
	//Opponents table
	//
	mysql_query("
	CREATE TABLE tplls_opponents (
	  OpponentID smallint(4) unsigned NOT NULL auto_increment,
	  OpponentName varchar(128) NOT NULL default '',
	  OpponentOwn tinyint(1) unsigned NOT NULL default '0',
	  PRIMARY KEY  (OpponentID)
	) TYPE=MyISAM
	",$connection)
	or die();

	//
	//Season names table
	//
	mysql_query("
	CREATE TABLE tplls_seasonnames (
	  SeasonID int(10) unsigned NOT NULL auto_increment,
	  SeasonName varchar(64) NOT NULL default '',
	  SeasonPublish tinyint(1) unsigned NOT NULL default '1',
	  SeasonLine varchar(32) NOT NULL default '1',
	  PRIMARY KEY  (SeasonID)
	) TYPE=MyISAM
	",$connection)
	or die();



	//
	//Add password into the database
	//
	mysql_query("
	INSERT INTO tplls_passwords (PasswordID, PasswordUser, PasswordPassword)
	VALUES
	('1','$username',MD5('$password'))",$connection)
	or die(mysql_error());

	//
	//Add preferences
	//
	mysql_query("
	INSERT INTO tplls_preferences SET
	id = '1',
	teamname = '$team',
	defaultseasonid = '1',
	defaultshow = '1',
	defaulttable = '1',
	forwin = '3',
	fordraw = '1',
	forloss = '0',
	printdate = '1',
	defaultlanguage = '1',
	acceptmultilanguage = '1',
	topoftable = '#CCCCCC',
	bg1 = '#FFFFFF',
	bg2 = '#FFFFCC',
	inside = '#FFFFFF',
	bordercolour = '#000000',
	tablewidth = '650',
	defaulthomeid = '1',
	defaultawayid = '1'
	",$connection)
	or die(mysql_error());

	//
	//Add seasonname into the database
	//
	mysql_query("
	INSERT INTO tplls_seasonnames (SeasonID, SeasonName, SeasonPublish, SeasonLine)
	VALUES
	('1', '$season', '1', '1')
	",$connection)
	or die(mysql_error());

	echo"Installation is now complete.<br><br>
	Your user name: $username<br>
	Password: $password<br><br>

	Before heading to login-page, remove installer.php from your server.<br><br>

	<a href=\"index.php\">Log in</a>";
	exit();

}
else
{

?>

<html>
<head>
<title>
Install tplLeagueStats
</title>
<link rel="stylesheet" type="text/css" href="../css/tplss_admin.css">
</head>
<body>
<form action="<?php echo $PHP_SELF ?>" method="post">

<table align="center" width="600">

<tr>
<td align="left" valign="middle" colspan="2">
<h1>Install tplLeagueStats</h1>
</td>
</tr>

<tr>
<td align="left" valign="middle" colspan="2">
Before entering data into the form, please check that user.php is correctly modified.
</td>
</tr>

<tr>
<td valign="middle" align="left">
User name to tplLeagueStats admin-area:
</td>

<td valign="middle" align="left">
<input name="username" type="text" size="20">
</td>
</tr>

<tr>
<td valign="middle" align="left">
Desired password into admin-area:<br>
(at least 6 characters)
</td>

<td valign="middle" align="left">
<input name="password" type="password" size="20">
</td>
</tr>

<tr>
<td valign="middle" align="left">
Retype password:
</td>

<td valign="middle" align="left">
<input name="password2" type="password" size="20">
</td>
</tr>

<tr>
<td valign="middle" align="left">
First modified season name:
</td>

<td valign="middle" align="left">
<input name="season" type="text" size="20">
</td>
</tr>

<tr>
<td valign="middle" align="left">
Team name:
</td>

<td valign="middle" align="left">
<input name="team" type="text" size="20">
</td>
</tr>

<tr>
<td align="left" valign="middle" colspan="2">
<input type="submit" name="submit" value="Install">
</td>
</tr>

</table>

</form>
</body>
</html>
<?php
}
?>
