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
	$modifyall_submit = $_POST['modifyall_submit'];
	$delete_submit = $_POST['delete_submit'];

	//
	//Exit check, if there are less than 2 teams in database
	//
	$query = mysql_query("
	SELECT OpponentID FROM tplls_opponents
	", $connection)
	or die(mysql_error());

	if(mysql_num_rows($query) < 2)
	{
		echo"Add at least two teams into the database.<br><br>
		<a href=\"opponents.php?sessioid=$sessio\">Add teams.</a>";
		exit();
	}

	mysql_free_result($query);


	if($add_submit)
	{
		$year = $_POST['year'];
		$month = $_POST['month'];
		$day = $_POST['day'];
		$dateandtime = $year."-".$month."-".$day;

		//
		//Updates the last_updated column in preferences
		//
		mysql_query("UPDATE tplls_preferences SET
		last_updated = CURRENT_TIMESTAMP", $connection)
		or die(mysql_error());

		//
		//Check the data of the submitted form
		//
		$i = 0;

		while($i < 15)
		{
			$home = $_POST['home'];	//hometeam id
			$away = $_POST['away'];	//awayteam id
			$home_goals = $_POST['home_goals'];
			$away_goals = $_POST['away_goals'];

			//
			//Set the default
			//
			$home_winner = -1;
			$home_loser = -1;
			$home_tie = -1;
			$away_winner = -1;
			$away_loser = -1;
			$away_tie = -1;

			//
			//If home and away are not the same
			//
			if($home[$i] != $away[$i])
			{
				//
				//Hometeam wins
				//
				if($home_goals[$i] > $away_goals[$i])
				{
					$home_winner = $home[$i];
					$away_loser = $away[$i];
				}

				//
				//Away win
				//
				elseif($home_goals[$i] < $away_goals[$i])
				{
					$away_winner = $away[$i];
					$home_loser = $home[$i];
				}

				//
				//Draw
				//
				elseif($home_goals[$i] == $away_goals[$i])
				{
					$home_tie = $home[$i];
					$away_tie = $away[$i];
				}

				//
				//Query to check if homea or away team already exists in the current day
				//
				$query = mysql_query("
				SELECT LM.LeagueMatchID FROM
				tplls_leaguematches LM
				WHERE
				(LM.LeagueMatchHomeID = '$home[$i]' OR
				LM.LeagueMatchAwayID = '$home[$i]' OR
				LM.LeagueMatchHomeID = '$away[$i]' OR
				LM.LeagueMatchAwayID = '$away[$i]') AND
				LM.LeagueMatchDate = '$dateandtime'
				", $connection)
				or die(mysql_error());

				if(mysql_num_rows($query) == 0)
				{
					//
					//Writes the data
					//
					if($home_goals[$i] == '' || $away_goals[$i] == '' || !is_numeric($home_goals[$i]) || !is_numeric($away_goals[$i]))
					{
						mysql_query("
						INSERT INTO tplls_leaguematches SET
						LeagueMatchSeasonID = '$seasonid',
						LeagueMatchDate = '$dateandtime',
						LeagueMatchHomeID = '$home[$i]',
						LeagueMatchAwayID = '$away[$i]',
						LeagueMatchHomeWinnerID = '-1',
						LeagueMatchHomeLoserID = '-1',
						LeagueMatchAwayWinnerID = '-1',
						LeagueMatchAwayLoserID = '-1',
						LeagueMatchHomeTieID = '-1',
						LeagueMatchAwayTieID = '-1',
						LeagueMatchHomeGoals = NULL,
						LeagueMatchAwayGoals = NULL
						", $connection)
						or die(mysql_error());
					}
					else
					{
						mysql_query("
						INSERT INTO tplls_leaguematches SET
						LeagueMatchSeasonID = '$seasonid',
						LeagueMatchDate = '$dateandtime',
						LeagueMatchHomeID = '$home[$i]',
						LeagueMatchAwayID = '$away[$i]',
						LeagueMatchHomeWinnerID = '$home_winner',
						LeagueMatchHomeLoserID = '$home_loser',
						LeagueMatchAwayWinnerID = '$away_winner',
						LeagueMatchAwayLoserID = '$away_loser',
						LeagueMatchHomeTieID = '$home_tie',
						LeagueMatchAwayTieID = '$away_tie',
						LeagueMatchHomeGoals = '$home_goals[$i]',
						LeagueMatchAwayGoals = '$away_goals[$i]'
						", $connection)
						or die(mysql_error());
					}
				}
			}



			$i++;
		}

		header("Location: $PHP_SELF?sessioid=$sessio");
	}
	elseif($modifyall_submit)
	{
		//
		//Updates the last_updated column in preferences
		//
		mysql_query("UPDATE tplls_preferences SET
		last_updated = CURRENT_TIMESTAMP", $connection)
		or die(mysql_error());


		$year = $_POST['year'];
		$month = $_POST['month'];
		$day = $_POST['day'];
		$dateandtime = $year."-".$month."-".$day;
		$qty = $_POST['qty'];

		//
		//Delete old data from selected date
		//
		mysql_query("
		DELETE FROM tplls_leaguematches
		WHERE LeagueMatchDate = '$dateandtime'
		", $connection)
		or die(mysql_error());

		//
		//Check the submitted form
		//
		$i = 0;

		while($i < $qty)
		{
			$home = $_POST['home'];	//hometeam id
			$away = $_POST['away'];	//awayteam id
			$home_goals = $_POST['home_goals'];
			$away_goals = $_POST['away_goals'];

			//
			//Set default
			//
			$home_winner = -1;
			$home_loser = -1;
			$home_tie = -1;
			$away_winner = -1;
			$away_loser = -1;
			$away_tie = -1;

			//
			//Home wins
			//
			if($home_goals[$i] > $away_goals[$i])
			{
				$home_winner = $home[$i];
				$away_loser = $away[$i];
			}
			//
			//Away wins
   			//
			elseif($home_goals[$i] < $away_goals[$i])
			{
				$away_winner = $away[$i];
				$home_loser = $home[$i];
			}
			//
			//Draw
			//
			elseif($home_goals[$i] == $away_goals[$i])
			{
				$home_tie = $home[$i];
				$away_tie = $away[$i];
			}

			//
			//New data to the base
			//
			if($home_goals[$i] == '' || $away_goals[$i] == '' || !is_numeric($home_goals[$i]) || !is_numeric($away_goals[$i]))
			{
				mysql_query("
				INSERT INTO tplls_leaguematches SET
				LeagueMatchSeasonID = '$seasonid',
				LeagueMatchDate = '$dateandtime',
				LeagueMatchHomeID = '$home[$i]',
				LeagueMatchAwayID = '$away[$i]',
				LeagueMatchHomeWinnerID = '-1',
				LeagueMatchHomeLoserID = '-1',
				LeagueMatchAwayWinnerID = '-1',
				LeagueMatchAwayLoserID = '-1',
				LeagueMatchHomeTieID = '-1',
				LeagueMatchAwayTieID = '-1',
				LeagueMatchHomeGoals = NULL,
				LeagueMatchAwayGoals = NULL
				", $connection)
				or die(mysql_error());
			}
			else
			{
				mysql_query("
				INSERT INTO tplls_leaguematches SET
				LeagueMatchSeasonID = '$seasonid',
				LeagueMatchDate = '$dateandtime',
				LeagueMatchHomeID = '$home[$i]',
				LeagueMatchAwayID = '$away[$i]',
				LeagueMatchHomeWinnerID = '$home_winner',
				LeagueMatchHomeLoserID = '$home_loser',
				LeagueMatchAwayWinnerID = '$away_winner',
				LeagueMatchAwayLoserID = '$away_loser',
				LeagueMatchHomeTieID = '$home_tie',
				LeagueMatchAwayTieID = '$away_tie',
				LeagueMatchHomeGoals = '$home_goals[$i]',
				LeagueMatchAwayGoals = '$away_goals[$i]'
				", $connection)
				or die(mysql_error());
			}

			$i++;
		}

		header("Location: $PHP_SELF?sessioid=$sessio");
	}
	elseif($modify_submit)
	{
		//
		//Updates the last_updated column in preferences
		//
		mysql_query("UPDATE tplls_preferences SET
		last_updated = CURRENT_TIMESTAMP", $connection)
		or die(mysql_error());


		$mid = $_POST['mid'];
		$homeid = $_POST['homeid'];
		$awayid = $_POST['awayid'];
		$year = $_POST['year'];
		$month = $_POST['month'];
		$day = $_POST['day'];
		$dateandtime = $year."-".$month."-".$day;

		$home = $_POST['home'];	//kotijoukkueen id
		$away = $_POST['away'];	//vierasjoukkueen id
		$home_goals = $_POST['home_goals'];
		$away_goals = $_POST['away_goals'];

		//
		//Set default
		//
		$home_winner = -1;
		$home_loser = -1;
		$home_tie = -1;
		$away_winner = -1;
		$away_loser = -1;
		$away_tie = -1;

		//
		//Check that home and away are not the same
		//
		if($home != $away)
		{
			//
			//Home wins
			//
			if($home_goals > $away_goals)
			{
				$home_winner = $home;
				$away_loser = $away;
			}

			//
			//Away wins
			//
			elseif($home_goals < $away_goals)
			{
				$away_winner = $away;
				$home_loser = $home;
			}

			//
			//Draw
			//
			elseif($home_goals == $away_goals)
			{
				$home_tie = $home;
				$away_tie = $away;
			}

			//
			//Query to check if home or away team already exists in the current day
			//
			$query = mysql_query("
			SELECT LM.LeagueMatchID FROM
			tplls_leaguematches LM
			WHERE
			(LM.LeagueMatchHomeID = '$home' OR
			LM.LeagueMatchAwayID = '$home' OR
			LM.LeagueMatchHomeID = '$homeid' OR
			LM.LeagueMatchAwayID = '$homeid' OR
			LM.LeagueMatchHomeID = '$away' OR
			LM.LeagueMatchAwayID = '$away' OR
			LM.LeagueMatchHomeID = '$awayid' OR
			LM.LeagueMatchAwayID = '$awayid') AND
			LM.LeagueMatchDate = '$dateandtime'
			", $connection)
			or die(mysql_error());

			if(mysql_num_rows($query) < 2)
			{
				//
				//Writes the data
				//
				if($home_goals == '' || $away_goals == '' || !is_numeric($home_goals) || !is_numeric($away_goals))
				{
					mysql_query("
					UPDATE tplls_leaguematches SET
					LeagueMatchSeasonID = '$seasonid',
					LeagueMatchDate = '$dateandtime',
					LeagueMatchHomeID = '$home',
					LeagueMatchAwayID = '$away',
					LeagueMatchHomeWinnerID = '-1',
					LeagueMatchHomeLoserID = '-1',
					LeagueMatchAwayWinnerID = '-1',
					LeagueMatchAwayLoserID = '-1',
					LeagueMatchHomeTieID = '-1',
					LeagueMatchAwayTieID = '-1',
					LeagueMatchHomeGoals = NULL,
					LeagueMatchAwayGoals = NULL
					WHERE LeagueMatchID = '$mid'
					LIMIT 1
					", $connection)
					or die(mysql_error());
				}
				else
				{
					mysql_query("
					UPDATE tplls_leaguematches SET
					LeagueMatchSeasonID = '$seasonid',
					LeagueMatchDate = '$dateandtime',
					LeagueMatchHomeID = '$home',
					LeagueMatchAwayID = '$away',
					LeagueMatchHomeWinnerID = '$home_winner',
					LeagueMatchHomeLoserID = '$home_loser',
					LeagueMatchAwayWinnerID = '$away_winner',
					LeagueMatchAwayLoserID = '$away_loser',
					LeagueMatchHomeTieID = '$home_tie',
					LeagueMatchAwayTieID = '$away_tie',
					LeagueMatchHomeGoals = '$home_goals',
					LeagueMatchAwayGoals = '$away_goals'
					WHERE LeagueMatchID = '$mid'
					LIMIT 1
					", $connection)
					or die(mysql_error());
				}
			}

		}
		header("Location: $PHP_SELF?sessioid=$sessio");
	}
	elseif($delete_submit)
	{
		//
		//Updates the last_updated column in preferences
		//
		mysql_query("UPDATE tplls_preferences SET
		last_updated = CURRENT_TIMESTAMP", $connection)
		or die(mysql_error());


		$mid = $_POST['mid'];
		mysql_query("DELETE FROM tplls_leaguematches WHERE LeagueMatchID = '$mid' LIMIT 1", $connection)
		or die(mysql_error());
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
	<table align="center" width="700">
		<tr>
		<td align="left" valign="top">
		<form method="post" action="<?php echo "$PHP_SELF?sessioid=$sessio" ?>">
		<?php
		if(!isset($action))
		{
		?>
		<h1>Add match</h1>
		If you can't find a specific team, check that it is available in opponents.<br><br>

		Date:
		<select name="day">
		<?php
		//print the days
		for($i = 1 ; $i < 32 ; $i++)
		{
			if($i<10)
			{
				$i = "0".$i;
			}
			if($i == "01")
				echo "<option value=\"$i\" SELECTED>$i</option>\n";
			else
				echo "<option value=\"$i\">$i</option>\n";
		}
		?>
		</select>&nbsp;/&nbsp;

		<select name="month">
		<?php
		//print the months
		for($i = 1 ; $i < 13 ; $i++)
		{
			if($i<10)
			{
				$i = "0".$i;
			}
			if($i == "01")
				echo "<option value=\"$i\" SELECTED>$i</option>\n";
			else
				echo "<option value=\"$i\">$i</option>\n";
		}
		?>
		</select>&nbsp;/&nbsp;

		<select name="year">
		<?php
		//print the years
		for($i = 1950 ; $i < 2010 ; $i++)
		{
			if($i<10)
			{
				$i = "0".$i;
			}
			if($i == "2003")
				echo "<option value=\"$i\" SELECTED>$i</option>\n";
			else
				echo "<option value=\"$i\">$i</option>\n";
		}
		?>
		</select><br><br>
		Add as much matches as you want, max 15 per one time. Matches with goals filled in the form are added to the database.<br><br>

		<table width="100%" cellspacing="3" cellpadding="3" border="0">
		<tr>

		<td align="left" valign="middle"><b>Hometeam</b></td>
		<td align="left" valign="middle"><b>Awayteam</b></td>
		<td align="center" valign="middle"><b>GH</b></td>
		<td align="center" valign="middle"><b>GA</b></td>

		</tr>

		<?php

		//
		//Query to get all the teams
		//
		$get_opponents = mysql_query("
		SELECT OpponentID AS id,
		OpponentName AS name
		FROM tplls_opponents
		ORDER BY OpponentName
		", $connection)
		or die(mysql_error());

		//
		//Prints 15 forms
		//
		$i=0;

		while($i < 15)
		{
			//
			//Query back to row 0 if not the first time in the loop
			//
			if($i>0)
				mysql_data_seek($get_opponents, 0);

			echo'
			<tr>
			<td align="left" valign="middle">
			';

			echo"<select name=\"home[$i]\">";

			while($data = mysql_fetch_array($get_opponents))
			{
				echo"<option value=\"$data[id]\">$data[name]</option>\n";
			}

			echo'
			</select>
			</td>
			<td align="left" valign="middle">
			';

			//
			//Back to line 0in the query
			//
			mysql_data_seek($get_opponents, 0);

			echo"<select name=\"away[$i]\">";

			while($data = mysql_fetch_array($get_opponents))
			{
				echo"<option value=\"$data[id]\">$data[name]</option>\n";
			}

			echo"
			</select>
			</td>
			<td align=\"center\" valign=\"middle\"><input type=\"text\" name=\"home_goals[$i]\" size=\"2\"></td>
			<td align=\"center\" valign=\"middle\"><input type=\"text\" name=\"away_goals[$i]\" size=\"2\"></td>

			</tr>
			";




			$i++;
		}

		mysql_free_result($get_opponents);

		?>

		</table><br><br>

		<input type="submit" name="add_submit" value="Add matches">
		</form>
		<?php
		}
		elseif($action == 'modifyall')
		{
		$date = $_REQUEST['date'];

		$get_matches = mysql_query("
		SELECT DAYOFMONTH(LM.LeagueMatchDate) AS dayofmonth,
		MONTH(LM.LeagueMatchDate) AS month,
		YEAR(LM.LeagueMatchDate) AS year,
		LM.LeagueMatchHomeID AS homeid,
		LM.LeagueMatchAwayID AS awayid,
		LM.LeagueMatchHomeGoals AS homegoals,
		LM.LeagueMatchAwayGoals AS awaygoals
		FROM tplls_leaguematches LM
		WHERE LM.LeaguematchDate = '$date'
		", $connection)
		or die(mysql_error());

		//
		//Query to get date
		//
		$get_match = mysql_query("
		SELECT DAYOFMONTH(LM.LeagueMatchDate) AS dayofmonth,
		MONTH(LM.LeagueMatchDate) AS month,
		YEAR(LM.LeagueMatchDate) AS year
		FROM tplls_leaguematches LM
		WHERE LM.LeaguematchDate = '$date'
		LIMIT 1
		", $connection)
		or die(mysql_error());

		$datedata = mysql_fetch_array($get_match);

		mysql_free_result($get_match);

		$get_opponents = mysql_query("
		SELECT OpponentID AS id,
		OpponentName AS name
		FROM tplls_opponents
		ORDER BY OpponentName
		", $connection)
		or die(mysql_error());

		?>

		<form method="post" action="<?php echo "$PHP_SELF?sessioid=$sessio" ?>">
		<h1>Modify matches</h1>

		<table width="100%" cellspacing="3" cellpadding="3" border="0">

			<tr>
				<td align="left" valign="top">
				Date and time:
				</td>
				<td align="left" valign="top">

				<select name="day">
				<?php
				//Print the days
				for($i = 1 ; $i < 32 ; $i++)
				{
					if($i<10)
					{
						$i = "0".$i;
					}
					if($datedata['dayofmonth'] == $i)
						echo "<option value=\"$i\" SELECTED>$i</option>\n";
					else
						echo "<option value=\"$i\">$i</option>\n";
				}
				?>
				</select>&nbsp;/&nbsp;

				<select name="month">
				<?php
				//Print the months
				for($i = 1 ; $i < 13 ; $i++)
				{
					if($i<10)
					{
						$i = "0".$i;
					}
					if($datedata['month'] == $i)
						echo "<option value=\"$i\" SELECTED>$i</option>\n";
					else
						echo "<option value=\"$i\">$i</option>\n";
				}
				?>
				</select>&nbsp;/&nbsp;

				<select name="year">
				<?php
				//Print the years
				for($i = 1950 ; $i < 2010 ; $i++)
				{
					if($i<10)
					{
						$i = "0".$i;
					}
					if($datedata['year'] == $i)
						echo "<option value=\"$i\" SELECTED>$i</option>\n";
					else
						echo "<option value=\"$i\">$i</option>\n";
				}
				?>
			</select>
			</td>
		</tr>

		</table>

		<table width="100%" cellspacing="3" cellpadding="3" border="0">
		<tr>

		<td align="left" valign="middle"><b>Hometeam</b></td>
		<td align="left" valign="middle"><b>Awayteam</b></td>
		<td align="center" valign="middle"><b>GH</b></td>
		<td align="center" valign="middle"><b>GA</b></td>

		</tr>

		<?php

		//
		//Lets get all the matches from selected date to the form
		//
		$i = 0;
		while($matchdata = mysql_fetch_array($get_matches))
		{
			//
			//Back to line 0 in the query if not the first loop
			//
			if($i>0)
				mysql_data_seek($get_opponents, 0);

			echo'
			<tr>
			<td align="left" valign="middle">
			';

			echo"<select name=\"home[$i]\">";

			while($data = mysql_fetch_array($get_opponents))
			{
				if($matchdata['homeid'] == $data['id'])
					echo"<option value=\"$data[id]\" SELECTED>$data[name]</option>\n";
			}

			echo'
			</select>
			</td>
			<td align="left" valign="middle">
			';

			//
			//Back to line 0 in the query
			//
			mysql_data_seek($get_opponents, 0);

			echo"<select name=\"away[$i]\">";

			while($data = mysql_fetch_array($get_opponents))
			{
				if($matchdata['awayid'] == $data['id'])
					echo"<option value=\"$data[id]\" SELECTED>$data[name]</option>\n";
			}

			echo"
			</select>
			</td>
			<td align=\"center\" valign=\"middle\"><input type=\"text\" name=\"home_goals[$i]\" size=\"2\" value=\"$matchdata[homegoals]\"></td>
			<td align=\"center\" valign=\"middle\"><input type=\"text\" name=\"away_goals[$i]\" size=\"2\" value=\"$matchdata[awaygoals]\"></td>

			</tr>
			";




			$i++;
		}

		mysql_free_result($get_matches);
		mysql_free_result($get_opponents);

		?>

		</table>

		You can't change home or away team in this mode. Click the match to modify home/away team.<br><br>
		<input type="hidden" name="qty" value="<?= $i ?>">
		<br><input type="submit" name="modifyall_submit" value="Click here to modify the matches">
		</form>

		<?php
		}
		elseif($action == 'modify')
		{
		$id = $_REQUEST['id'];

		$get_match = mysql_query("
		SELECT DAYOFMONTH(LM.LeagueMatchDate) AS dayofmonth,
		MONTH(LM.LeagueMatchDate) AS month,
		YEAR(LM.LeagueMatchDate) AS year,
		LM.LeagueMatchHomeID AS homeid,
		LM.LeagueMatchAwayID AS awayid,
		LM.LeagueMatchHomeGoals AS homegoals,
		LM.LeagueMatchAwayGoals AS awaygoals
		FROM tplls_leaguematches LM
		WHERE LM.LeaguematchID = '$id'
		LIMIT 1
		", $connection)
		or die(mysql_error());

		$get_opponents = mysql_query("
		SELECT OpponentID AS id,
		OpponentName AS name
		FROM tplls_opponents
		ORDER BY OpponentName
		", $connection)
		or die(mysql_error());

		$matchdata = mysql_fetch_array($get_match);

		mysql_free_result($get_match);

		?>
		<form method="post" action="<?php echo "$PHP_SELF?sessioid=$sessio" ?>">
		<h1>Modify match</h1>

		<table width="100%" cellspacing="3" cellpadding="3" border="0">

			<tr>
				<td align="left" valign="top">
				Date and time:
				</td>
				<td align="left" valign="top">

				<select name="day">
				<?php
				//Print the days
				for($i = 1 ; $i < 32 ; $i++)
				{
					if($i<10)
					{
						$i = "0".$i;
					}
					if($matchdata['dayofmonth'] == $i)
						echo "<option value=\"$i\" SELECTED>$i</option>\n";
					else
						echo "<option value=\"$i\">$i</option>\n";
				}
				?>
				</select>&nbsp;/&nbsp;

				<select name="month">
				<?php
				//Print the months
				for($i = 1 ; $i < 13 ; $i++)
				{
					if($i<10)
					{
						$i = "0".$i;
					}
					if($matchdata['month'] == $i)
						echo "<option value=\"$i\" SELECTED>$i</option>\n";
					else
						echo "<option value=\"$i\">$i</option>\n";
				}
				?>
				</select>&nbsp;/&nbsp;

				<select name="year">
				<?php
				//Print the years
				for($i = 1950 ; $i < 2010 ; $i++)
				{
					if($i<10)
					{
						$i = "0".$i;
					}
					if($matchdata['year'] == $i)
						echo "<option value=\"$i\" SELECTED>$i</option>\n";
					else
						echo "<option value=\"$i\">$i</option>\n";
				}
				?>
			</select>
			</td>
		</tr>

		</table>

		<table width="100%" cellspacing="3" cellpadding="3" border="0">
		<tr>

		<td align="left" valign="middle"><b>Hometeam</b></td>
		<td align="left" valign="middle"><b>Awayteam</b></td>
		<td align="center" valign="middle"><b>GH</b></td>
		<td align="center" valign="middle"><b>GA</b></td>

		</tr>

		<tr>
		<td align="left" valign="middle">

		<select name="home">
		<?php

			while($data = mysql_fetch_array($get_opponents))
			{
				if($matchdata['homeid'] == $data['id'])
					echo"<option value=\"$data[id]\" SELECTED>$data[name]</option>\n";
				else
					echo"<option value=\"$data[id]\">$data[name]</option>\n";
			}

		?>
		</select>
		</td>
		<td align="left" valign="middle">

		<select name="away">
		<?php

		mysql_data_seek($get_opponents, 0);

		while($data = mysql_fetch_array($get_opponents))
		{
			if($matchdata['awayid'] == $data['id'])
				echo"<option value=\"$data[id]\" SELECTED>$data[name]</option>\n";
			else
				echo"<option value=\"$data[id]\">$data[name]</option>\n";
		}

		?>
		</select>
		</td>
		<td align="center" valign="middle"><input type="text" name="home_goals" size="2" value="<?= $matchdata['homegoals'] ?>"></td>
		<td align="center" valign="middle"><input type="text" name="away_goals" size="2" value="<?= $matchdata['awaygoals'] ?>"></td>

		</tr>

		</table>


		<input type="hidden" name="mid" value="<?= $id ?>">
		<input type="hidden" name="homeid" value="<?= $matchdata['awayid'] ?>">
		<input type="hidden" name="awayid" value="<?= $matchdata['homeid'] ?>">
		<br><input type="submit" name="modify_submit" value="Click here to modify the match">
		<br><br><br><br><br>
		<input type="submit" name="delete_submit" value="Delete (can't be undone)">
		</form>

		<?php
		}
		?>
		</td>

		<td align="left" valign="top" width="250">

		<table width="250">
		<?php
		$get_matches = mysql_query("
		SELECT O.OpponentName AS hometeam,
		OP.OpponentName AS awayteam,
		LM.LeagueMatchHomeGoals AS goals_home,
		LM.LeagueMatchAwayGoals AS goals_away,
		LM.LeagueMatchID AS id,
		LM.LeagueMatchDate AS defaultdate,
		DATE_FORMAT(LM.LeagueMatchDate, '%b %D %Y') AS date
		FROM tplls_leaguematches LM, tplls_opponents O, tplls_opponents OP
		WHERE O.OpponentID = LM.LeagueMatchHomeID AND
		OP.OpponentID = LM.LeagueMatchAwayID AND
		LeagueMatchSeasonID = '$seasonid'
		ORDER BY LM.LeagueMatchDate",$connection)
		or die(mysql_error());

		if(mysql_num_rows($get_matches) < 1)
		{
			echo "<b>No matches yet in $seasonname.</b>";
		}
		else
		{
			echo "<b>Matches in $seasonname</b><br><br>";

			$i = 0;
			$temp = '';

			while($data = mysql_fetch_array($get_matches))
			{
				if($i == 0)
				{
					echo"
					<tr>
					<td align=\"left\" colspan=\"2\">
					<b><a href=\"$PHP_SELF?sessioid=$sessio&amp;action=modifyall&amp;date=$data[defaultdate]\">$data[date]</a></b>
					</td>
					</tr>
					";
				}

				if($data['date'] != "$temp" && $i > 0)
				{
					echo"
					<tr>
					<td align=\"left\" colspan=\"2\">
					<br><br>
					<b><a href=\"$PHP_SELF?sessioid=$sessio&amp;action=modifyall&amp;date=$data[defaultdate]\">$data[date]</a></b>
					</td>
					</tr>
					";
				}

				echo "
				<tr>
				<td align=\"left\" valign=\"top\" width=\"230\">
				<a href=\"$PHP_SELF?sessioid=$sessio&amp;action=modify&amp;id=$data[id]\">$data[hometeam] - $data[awayteam]</a>
				</td>
				<td align=\"left\" valign=\"top\" width=\"20\">";

				if(!is_null($data['goals_home']))
					echo"$data[goals_home]-$data[goals_away]";
				else
					echo'&nbsp;';


				echo"
				</td>
				</tr>";

				$temp = "$data[date]";

				$i++;
			}
		}

		mysql_free_result($get_matches);

		?>
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
