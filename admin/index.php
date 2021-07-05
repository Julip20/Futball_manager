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

//
//Is installer removed?
//
if(file_exists('installer.php'))
{
	echo'Remove installer.php from your server.';
	exit();
}


//Connect to the database and select used database
include('user.php');
$connection = mysql_connect("$host","$user","$password")
or die(mysql_error());
mysql_select_db("$txt_db_name",$connection)
or die(mysql_error());

$submit = $_POST['submit'];
$PHP_SELF = $_SERVER['PHP_SELF'];

$kysely = mysql_query("SELECT * FROM tplls_passwords WHERE PasswordID = '1'",$connection)
or die(mysql_error());

$data = mysql_fetch_array($kysely);

$check = 0;

if($submit)
{
	$user = $_POST['user'];
	$password = $_POST['password'];
	$season = $_POST['season'];
	if($user == "$data[PasswordUser]" && md5($password) == "$data[PasswordPassword]")
	{
		session_start();
		//unset the session variable if exists
		unset($_SESSION['sessio']);

		//Unique sessioid
		srand((double)microtime()*1000000);
		$sessio = md5(rand(0,9999));

		$tmp = explode("___",$season);
		$_SESSION['season_id'] = $tmp[0];
		$_SESSION['season_name'] = $tmp[1];

		$_SESSION['sessio'] = $sessio;

		header("Location:leaguematches.php?sessioid=$sessio");
	}
	else
		$check=1;


}
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
if($check == 1)
	echo '<font color="red">WRONG PASSWORD OR USERNAME</font><br><br>';
?>
<h1>Log in</h1>
<form action="<?php echo $PHP_SELF ?>" method="post">
Username:<br>
<input type="text" name="user" size="20"><br><br>
Password:<br>
<input type="password" name="password"><br><br>
Select season:<br>
<select name="season">
<?php
$get_seasons = mysql_query("SELECT * FROM tplls_seasonnames ORDER BY SeasonName",$connection)
	or die(mysql_error());

	while($sdata = mysql_fetch_array($get_seasons))
	{
		echo "<option value=\"$sdata[SeasonID]___$sdata[SeasonName]\">$sdata[SeasonName]</option>\n";
	}
	mysql_free_result($get_seasons);
?>
</select><br><br>
<input type="submit" name="submit" value="Log in">
</form>
</body>
</html>
