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
	$HTTP_REFERER = $_SERVER['HTTP_REFERER'];
	$action = $_REQUEST['action'];

	$add_submit = $_POST['add_submit'];
	$modify_submit = $_POST['modify_submit'];
	$delete_submit = $_POST['delete_submit'];


	if($add_submit)
	{
		$name = trim($_POST['name']);
		$drawline = trim($_POST['drawline']);

		//Query to check if there are already a submitted season name in the database
		$query = mysql_query("SELECT SeasonName FROM tplls_seasonnames WHERE SeasonName = '$name'",$connection)
		or die(mysql_error());

		if(mysql_num_rows($query) > 0)
		{
			echo "There is already season named: $name in database.<br>Please write another name for the season.";
			exit();
		}

		mysql_free_result($query);

		if($name != '')
		{
			mysql_query("INSERT INTO tplls_seasonnames SET
			SeasonName = '$name',
			SeasonLine = '$drawline'",$connection)
			or die(mysql_error());

			header("Location: $PHP_SELF?sessioid=$sessio");
		}
	}
	elseif($modify_submit)
	{
		$name = trim($_POST['name']);
		$drawline = trim($_POST['drawline']);
		$publish = $_POST['publish'];
		$seasonid = $_POST['seasonid'];

		//
		//If published is checked
		//
		if(!isset($publish))
		{
			$publish = 0;
		}

		if($name != '')
		{
			mysql_query("UPDATE tplls_seasonnames SET
			SeasonName = '$name',
			SeasonLine = '$drawline',
			SeasonPublish = '$publish'
			WHERE SeasonID = '$seasonid'",$connection)
			or die(mysql_error());
		}

		header("Location: $HTTP_REFERER");
	}
	elseif($delete_submit)
	{
		$seasonid = $_POST['seasonid'];

		//
		//Query to check if there are already matches in the season->can't delete
		//
		$query = mysql_query("SELECT M.MatchID
		FROM tplls_leaguematches M, tplls_seasons S
		WHERE M.LeagueMatchSeasonID = '$seasonid'",$connection)
		or die(mysql_error());

		if(mysql_num_rows($query) == 0)
		{
			mysql_query("DELETE FROM tplls_seasonnames WHERE SeasonID = '$seasonid'",$connection)
			or die(mysql_error());
		}
		else
		{
			echo'There is already match booked for the season you wanted to delete. You must delete match first.';
			exit();
		}

		header("Location: $PHP_SELF?sessioid=$sessio");
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
	?>
	<table align="center" width="600">
		<tr>
		<td>
		<?php
		if(!isset($action))
		{
		?>
		<form method="post" action="<?php echo "$PHP_SELF?sessioid=$sessio" ?>">
		<h1>Add season</h1>
		<table width="100%" cellspacing="3" cellpadding="3" border="0">
		<tr>
			<td align="left" valign="top">
			Season name (years):
			</td>
			<td align="left" valign="top">
			<input type="text" name="name">
			</td>
		</tr>

		<tr>
			<td align="left" valign="top">
			Draw line after which pos.:<br>
			(If many, separate by commas)
			</td>
			<td align="left" valign="top">
			<input type="text" name="drawline" value="<?= $pdata['drawline'] ?>" size="10">
			</td>
		</tr>

		</table>
		<input type="submit" name="add_submit" value="Add season">
		</form>
		<?php
		}
		elseif($action == 'modify')
		{
		$seasonid = $_REQUEST['season'];
		$get_season = mysql_query("SELECT * FROM tplls_seasonnames WHERE SeasonID = '$seasonid' LIMIT 1",$connection)
		or die(mysql_error());
		$data = mysql_fetch_array($get_season);
		?>

		<form method="post" action="<?php echo "$PHP_SELF?sessioid=$sessio" ?>">
		<h1>Modify / delete season</h1>
		<table width="100%" cellspacing="3" cellpadding="3" border="0">
		<tr>
			<td align="left" valign="top">
			Season name (years):
			</td>
			<td align="left" valign="top">
			<input type="text" name="name" value="<?php echo $data['SeasonName'] ?>">
			<input type="hidden" name="seasonid" value="<?php echo $data['SeasonID'] ?>">
			</td>
		</tr>

		<tr>
			<td align="left" valign="top">
			Draw line after which pos.:<br>
			(If many, separate by commas)
			</td>
			<td align="left" valign="top">
			<input type="text" name="drawline" value="<?= $data['SeasonLine'] ?>" size="10">
			</td>
		</tr>

		<tr>
			<td align="left" valign="top">
			Published:
			</td>
			<td align="left" valign="top">
			<?php
			//
			//If season is published
			//
			if($data['SeasonPublish'] == 1)
				echo'<input type="checkbox" name="publish" value="1" CHECKED>';
			else
				echo'<input type="checkbox" name="publish" value="1">';

			?>
			</td>
		</tr>

		</table>
		<input type="submit" name="modify_submit" value="Modify season"> <input type="submit" name="delete_submit" value="Delete season">
		</form>

		<a href="<?php echo "$PHP_SELF?sessioid=$sessio" ?>">Add season</a>

		<?php
		mysql_free_result($get_season );
		}
		?>
		</td>

		<td align="left" valign="top">
		<?php
		$get_seasons = mysql_query("SELECT * FROM tplls_seasonnames ORDER BY SeasonName",$connection)
		or die(mysql_error());

		if(mysql_num_rows($get_seasons) < 1)
		{
			echo '<b>No seasons so far in database</b>';
		}
		else
		{
			echo '<b>Seasons so far in database:</b><br><br>';

			while($data = mysql_fetch_array($get_seasons))
			{
				echo "<a href=\"$PHP_SELF?sessioid=$sessio&amp;action=modify&amp;season=$data[SeasonID]\">$data[SeasonName]</a>";

				//
				//Season published?
				//
				if($data['SeasonPublish'] == 0)
					echo" (NB)<br>\n";
				else
					echo"<br>\n";
			}
		}

		?>
		<br><br>
		NB = This season is not published yet.
		</td>
		</tr>
	</table>
	</body>
	</html>

<?php
mysql_close($connection);
}
?>
