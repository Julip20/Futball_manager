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

//Check the session id
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

		$get_password = mysql_query("SELECT * FROM tplls_passwords WHERE PasswordID = '1'",$connection)
		or die(mysql_error());

		$password = mysql_fetch_array($get_password);

		$new = $_POST['new'];
		$new2 = $_POST['new2'];
		$old = $_POST['old'];

		if($old == '' || $new == '' || $new2 == '')
		{
			$check = 'You must fill all fields.';
		}
		else
		{	$old = md5($old);
			if($password['PasswordPassword'] != "$old")
			{
				$check = 'Your old password was wrong.';
			}
			else
			{
				if($new != "$new2")
				{
					$check = 'You didn\'t retype correctly.';
				}
				else
				{
					$new = md5($new);
					mysql_query("UPDATE tplls_passwords SET PasswordPassword = '$new' WHERE PasswordID = '1'",$connection)
					or die(mysql_error());
					$check = 'Password changed succesfully!';
				}
			}
		}
		mysql_free_result($get_password);

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
	include('menu.php');

	print("<center><font color=\"red\">$check</font></center><br><br>");

	?>

	<table align="center" width="600">
	<tr>
	<td>
	<form method="post" action="<?php echo "$PHP_SELF?sessioid=$sessio" ?>">
	<h1>Change password</h1>
	<table width="100%" cellspacing="3" cellpadding="3" border="0">
	<tr>
	<td align="left" valign="top">
	Old password:
	</td>
	<td align="left" valign="top">
	<input type="password" name="old">
	</td>
	</tr>

	<tr>
	<td align="left" valign="top">
	New password:
	</td>
	<td align="left" valign="top">
	<input type="password" size="20" name="new">
	</td>
	</tr>

	<tr>
	<td align="left" valign="top">
	Retype new password:
	</td>
	<td align="left" valign="top">
	<input type="password" name="new2">
	</td>
	</tr>
	</table>

	<input type="submit" name="submit" value="Change password">
	</form>

	</td>
	</tr>
	</table>

	</body>
	</html>

<?php
mysql_close($connection);
}
?>
