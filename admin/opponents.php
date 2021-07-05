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
	$HTTP_REFERER = $_SERVER['HTTP_REFERER'];
	$action = $_REQUEST['action'];

	$add_submit = $_POST['add_submit'];
	$modify_submit = $_POST['modify_submit'];
	$delete_submit = $_POST['delete_submit'];
	$d_points_add = $_POST['d_points_add'];
	$d_points_modify = $_POST['d_points_modify'];

	//
	//Add
	//
	if($add_submit)
	{
		$opponent = trim($_POST['opponent']);

		//Query to check if there are already a team with submitted name
		$query = mysql_query("SELECT OpponentName FROM tplls_opponents WHERE OpponentName = '$opponent'",$connection)
		or die(mysql_error());

		if(mysql_num_rows($query) > 0)
		{
			echo "There is already opponent named: $opponent in database.";
			exit();
		}

		mysql_free_result($query);

		if($opponent != '')
		{
			mysql_query("INSERT INTO tplls_opponents SET OpponentName = '$opponent'",$connection)
			or die(mysql_error());

			header("Location: $PHP_SELF?sessioid=$sessio");
		}
	}
	//
	//Modify
	//
	elseif($modify_submit)
	{
		$opponent = trim($_POST['opponent']);
		$opponentid = $_POST['opponentid'];
		$own = $_POST['own'];
		//
		//Checked own
		//
		if(!isset($own))
		{
			$own = 0;
		}

		if($opponent != '')
		{
			//
			//If own team->delete the own status from the previous one
			//
			if($own == 1)
			{
				mysql_query("
				UPDATE tplls_opponents SET
				OpponentOwn = '0'
				WHERE OpponentOwn = '1'
				", $connection)
				or die(mysql_error());
			}

			mysql_query("UPDATE tplls_opponents SET
			OpponentName = '$opponent',
			OpponentOwn = '$own'
			WHERE OpponentID = '$opponentid'",$connection)
			or die(mysql_error());
		}

		header("Location: $HTTP_REFERER");
	}
	//
	//Delete
	//
	elseif($delete_submit)
	{
		$opponentid = $_POST['opponentid'];

		//
		//Query to check, if team already exists in the leaguetables
		//
		$query = mysql_query("SELECT LeagueMatchID
		FROM tplls_leaguematches
		WHERE LeagueMatchHomeID = '$opponentid' OR LeagueMatchAwayID = '$opponentid'",$connection)
		or die(mysql_error());

		if(mysql_num_rows($query) == 0)
		{
			mysql_query("DELETE FROM tplls_opponents WHERE OpponentID = '$opponentid'",$connection)
			or die(mysql_error());

			header("Location: $PHP_SELF?sessioid=$sessio");
		}
		else
		{
			echo'Permission to delete is denied!<br>
			Opponent is already in use.<br>
			Push back button to get back';
			exit();
		}
	}
	//
	//Deducted points
	//
	elseif($d_points_add)
	{
		$d_points = $_POST['d_points'];
		$teamid = $_POST['teamid'];

		if(is_numeric($d_points) && $d_points != '')
		{
			//
			//Adds
			//
			mysql_query("
			INSERT INTO tplls_deductedpoints SET
			seasonid = '$seasonid',
			teamid = '$teamid',
			points = '$d_points'
			", $connection)
			or die(mysql_error());
		}

		header("Location: $HTTP_REFERER");
	}
	//
	//Modify of deducted points
	//
	elseif($d_points_modify)
	{
		$d_points = $_POST['d_points'];
		$id = $_POST['id'];

		if(is_numeric($d_points) && $d_points != '')
		{
			//
			//Delete deducted points if zero is written
            //
			if($d_points == 0)
			{
				mysql_query("
				DELETE FROM tplls_deductedpoints
				WHERE id = '$id'
				", $connection)
				or die(mysql_error());
			}
			//
			//Modify if some other number
			//
			else
			{
				mysql_query("
				UPDATE tplls_deductedpoints SET
				points = '$d_points'
				WHERE id = '$id'
				", $connection)
				or die(mysql_error());
			}
		}

		header("Location: $HTTP_REFERER");
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
		<td align="left" valign="top">
		<?php
		if(!isset($action))
		{
		?>
		<form method="post" action="<?php echo "$PHP_SELF?sessioid=$sessio" ?>">
		<h1>Add team</h1>
		<table width="100%" cellspacing="3" cellpadding="3" border="0">
		<tr>
			<td align="left" valign="top">
			Team name:
			</td>
			<td align="left" valign="top">
			<input type="text" name="opponent">
			</td>
		</tr>
		</table>
		<input type="submit" name="add_submit" value="Add team">
		</form>
		<?php
		}
		elseif($action == 'modify')
		{
		$opponentid = $_REQUEST['opponent'];
		$get_opponent = mysql_query("SELECT * FROM tplls_opponents WHERE OpponentID = '$opponentid' LIMIT 1",$connection)
		or die(mysql_error());
		$data = mysql_fetch_array($get_opponent);
		?>

		<form method="post" action="<?php echo "$PHP_SELF?sessioid=$sessio" ?>">
		<h1>Modify / delete team</h1>
		<table width="100%" cellspacing="3" cellpadding="3" border="0">
		<tr>
			<td align="left" valign="top">
			Team name:
			</td>
			<td align="left" valign="top">
			<input type="text" name="opponent" value="<?php echo $data['OpponentName'] ?>">
			<input type="hidden" name="opponentid" value="<?php echo $data['OpponentID'] ?>">
			</td>
		</tr>

		<tr>
			<td align="left" valign="top">
			Your team?
			</td>
			<td align="left" valign="top">
			<?php

			if($data['OpponentOwn'] == 1)
				echo"<input type=\"checkbox\" name=\"own\" value=\"1\" CHECKED>\n";
			else
				echo"<input type=\"checkbox\" name=\"own\" value=\"1\">\n";

			?>
			</td>
		</tr>

		</table>
		<input type="submit" name="modify_submit" value="Modify team"> <input type="submit" name="delete_submit" value="Delete team">
		</form>

		<a href="<?php echo "$PHP_SELF?sessioid=$sessio" ?>">Add team</a>

		<h1>Deducted points for this team</h1>

		<?php

		//
		//Check if there are deducted points
		//

		echo"<b>$seasonname</b><br><br>";

		$get_deduct = mysql_query("
		SELECT points, id
		FROM tplls_deductedpoints
		WHERE seasonid = '$seasonid' AND teamid = '$opponentid'
		LIMIT 1
		", $connection)
		or die(mysql_error());

		if(mysql_num_rows($get_deduct) == 0)
		{
			echo"
			<form method=\"POST\" action=\"$PHP_SELF?sessioid=$sessio\">
			Add deducted points for this team:
			<input type=\"text\" size=\"2\" name=\"d_points\">
			<input type=\"hidden\" value=\"$opponentid\" name=\"teamid\">
			<input type=\"submit\" value=\"Add\" name=\"d_points_add\">
			</form>
			";
		}
		else
		{
			$data = mysql_fetch_array($get_deduct);

			echo"
			<form method=\"POST\" action=\"$PHP_SELF?sessioid=$sessio\">
			Modify deducted points for this team:
			<input type=\"text\" size=\"2\" name=\"d_points\" value=\"$data[points]\">
			<input type=\"hidden\" value=\"$data[id]\" name=\"id\">
			<input type=\"submit\" value=\"Modify\" name=\"d_points_modify\">
			</form>
			";
		}

		mysql_free_result($get_deduct);

		?>

		<?php
		mysql_free_result($get_opponent);
		}
		?>
		</td>

		<td align="left" valign="top">
		<?php
		$get_opponents = mysql_query("SELECT * FROM tplls_opponents ORDER BY OpponentName",$connection)
		or die(mysql_error());

		if(mysql_num_rows($get_opponents) < 1)
		{
			echo '<b>No teams so far in database</b>';
		}
		else
		{
			echo '<b>Teams so far in database:</b><br><br>';

			while($data = mysql_fetch_array($get_opponents))
			{
				echo "<a href=\"$PHP_SELF?sessioid=$sessio&amp;action=modify&amp;opponent=$data[OpponentID]\">$data[OpponentName]</a>";

				if($data['OpponentOwn'] == 1)
					echo" (YT)<br>\n";
				else
					echo"<br>\n";
			}
		}

		?>

		<br><br>
		YT = Your team
		</td>
		</tr>
	</table>
	</body>
	</html>

<?php
mysql_close($connection);
}
?>
