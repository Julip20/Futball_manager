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

include('admin/user.php');
$connection = mysql_connect("$host","$user","$password")
or die(mysql_error());
mysql_select_db("$txt_db_name",$connection)
or die(mysql_error());

//
//Includes preferences
//
$pref = mysql_query("SELECT * FROM tplls_preferences WHERE ID = '1'",$connection)
or die(mysql_error());
$pdata = mysql_fetch_array($pref);
mysql_free_result($pref);

$team_name = $pdata['teamname'];
$d_seasonid = $pdata['defaultseasonid'];
$show_all_or_one = $pdata['defaultshow'];
$show_table = $pdata['defaulttable'];
$language = $pdata['defaultlanguage'];
$for_win = $pdata['forwin'];
$for_draw = $pdata['fordraw'];
$for_lose = $pdata['forloss'];

$print_date = $pdata['printdate'];
$top_bg = $pdata['topoftable'];
$bg1 = $pdata['bg1'];
$bg2 = $pdata['bg2'];
$inside_c = $pdata['inside'];
$border_c = $pdata['bordercolour'];
$tb_width = $pdata['tablewidth'];
$accept_ml = $pdata['acceptmultilanguage'];

//
//If session variables are registered
//
if(!session_is_registered('defaultlanguage') || !session_is_registered('defaultseasonid') || !session_is_registered('defaulthomeid') || !session_is_registered('defaultawayid'))
{
	$_SESSION['defaultlanguage'] = $language;
	$defaulthomeid = $pdata['defaulthomeid'];
	$defaultawayid = $pdata['defaultawayid'];
	$_SESSION['defaultseasonid'] = $d_seasonid;
	$_SESSION['defaulthomeid'] = $defaulthomeid;
	$_SESSION['defaultawayid'] = $defaultawayid;
	$defaultlanguage = $_SESSION['defaultlanguage'];
	$defaultseasonid = $_SESSION['defaultseasonid'];
	$defaulthomeid = $_SESSION['defaulthomeid'];
	$defaultawayid = $_SESSION['defaultawayid'];
}
else
{
	$defaultlanguage = $_SESSION['defaultlanguage'];
	$defaultseasonid = $_SESSION['defaultseasonid'];
	$defaulthomeid = $_SESSION['defaulthomeid'];
	$defaultawayid = $_SESSION['defaultawayid'];
}

//
//If All is chosen from season, lets set default value for %
//
if($defaultseasonid == 0)
	$defaultseasonid = '%';

//
//Gets seasons and match types for dropdowns
//
$get_seasons = mysql_query("SELECT * FROM tplls_seasonnames WHERE SeasonPublish = '1' ORDER BY SeasonName",$connection)
or die(mysql_error());

//
//Query to get teams from choosed season
//
$get_teams = mysql_query("
SELECT DISTINCT
O.OpponentName AS name,
O.OpponentID AS id
FROM tplls_opponents O, tplls_leaguematches LM
WHERE LM.LeagueMatchSeasonID LIKE '$defaultseasonid' AND
(O.OpponentID = LM.LeagueMatchHomeID OR
O.OpponentID = LM.LeagueMatchAwayID)
ORDER BY name
", $connection)
or die(mysql_error());

?>

<?php
//
//INCLUDES HEADER.PHP
//
include('header.php');

//
//Check, which language is used
//
switch($defaultlanguage)
{
	case 1:
		include('lng/fin.inc');
	break;

	case 2:
		include('lng/eng.inc');
	break;
}

//
//Width of the line
//
$templine_width = $tb_width-25;


//
//Query to get team names
//
$get_names = mysql_query("
SELECT O.OpponentName AS homename,
OP.OpponentName AS awayname
FROM tplls_opponents O, tplls_opponents OP
WHERE
O.OpponentID = '$defaulthomeid' AND
OP.OpponentID = '$defaultawayid'
LIMIT 1
", $connection)
or die(mysql_error());

$namedata = mysql_fetch_array($get_names);

mysql_free_result($get_names);

?>

<!-- All the data print begin -->
<form method="post" action="change.php">

<table align="center" width="<?php echo $tb_width ?>" cellspacing="0" cellpadding="0" border="0" bgcolor="<?= $border_c ?>">
<tr>
<td>
<table width="100%" cellspacing="1" cellpadding="5" border="0">
<tr>
<td bgcolor="<?= $inside_c ?>" align="center">
<?php
//
//Accept multilanguage?
//
if($accept_ml == 1)
{
	echo"$lng_change_language:
	<select name=\"language\">";

	if($defaultlanguage == 2)
		echo '<option value="2" SELECTED>English</option>';
	else
		echo'<option value="2">English</option>';

	if($defaultlanguage == 1)
		echo '<option value="1" SELECTED>Finnish</option>';
	else
		echo'<option value="1">Finnish</option>';

	echo'</select> <input type="submit" value=">>" name="submit7">
	<br>';
}
?>

<?= $lng_change_season ?>:
<select name="season">
<option value="0"><?= $lng_all ?></option>
<?php
while($data = mysql_fetch_array($get_seasons))
{
	if($data['SeasonID'] == $defaultseasonid)
	{
		echo "<option value=\"$data[SeasonID]\" SELECTED>$data[SeasonName]</option>\n";
		$draw_line = explode(",", $data['SeasonLine']);
	}
	else
		echo "<option value=\"$data[SeasonID]\">$data[SeasonName]</option>\n";
}
mysql_free_result($get_seasons);

?>
</select>
<input type="submit" value=">>" name="submit">
&nbsp;&nbsp;&nbsp;
<?= $lng_moveto ?>: <select name="moveto">
<option value="index.php"><?= $lng_tables_drop ?></option>
<option value="season.php"><?= $lng_season_statistics_drop ?></option>
</select> <input type="submit" value=">>" name="submit6">
<br>
<?= $lng_home_team ?>:
<select name="home_id">
<?php
while($data = mysql_fetch_array($get_teams))
{
	if($data['id'] == $defaulthomeid)
		echo"<option value=\"$data[id]\" SELECTED>$data[name]</option>\n";
	else
		echo"<option value=\"$data[id]\">$data[name]</option>\n";
}
?>
</select> <input type="submit" value=">>" name="submit4">
&nbsp;&nbsp;&nbsp;
<?= $lng_away_team ?>:
<select name="away_id">
<?php
mysql_data_seek($get_teams, 0);
while($data = mysql_fetch_array($get_teams))
{
	if($data['id'] == $defaultawayid)
		echo"<option value=\"$data[id]\" SELECTED>$data[name]</option>\n";
	else
		echo"<option value=\"$data[id]\">$data[name]</option>\n";
}

mysql_free_result($get_teams);
?>
</select> <input type="submit" value=">>" name="submit5">

</td>
</tr>
</table>
</td>
</tr>
</table>

<table align="center" width="<?php echo $tb_width ?>" cellspacing="0" cellpadding="0" border="0" bgcolor="<?php echo $border_c ?>">
<tr>
<td>
	<table width="100%" cellspacing="1" cellpadding="5" border="0">
	<tr>
	<td bgcolor="<?php echo $inside_c ?>" align="center">

	<table width="100%" cellspacing="1" cellpadding="5" border="0" align="center">

	<?php

	//
	//How to print date?
	//
	if($print_date == 1)
	{
		$print_date = '%d.%m.%Y';
	}
	elseif($print_date == 2)
	{
		$print_date = '%m.%d.%Y';
	}
	elseif($print_date == 3)
	{
		$print_date = '%b %D %Y';
	}

	//
	//Query to get hometeam data
	//
	$query = mysql_query("
	SELECT
	LM.LeagueMatchHomeID AS homeid,
	LM.LeagueMatchAwayID AS awayid,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM
	tplls_leaguematches LM
	WHERE
	(LM.LeagueMatchHomeID = '$defaulthomeid' OR
	LM.LeagueMatchAwayID = '$defaulthomeid') AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid' AND
	LM.LeagueMatchHomeGoals IS NOT NULL AND
	LM.LeagueMatchAwayGoals IS NOT NULL
	", $connection)
	or die(mysql_error());

	//
	//Query to get away team data
	//
	$query2 = mysql_query("
	SELECT
	LM.LeagueMatchHomeID AS homeid,
	LM.LeagueMatchAwayID AS awayid,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM
	tplls_leaguematches LM
	WHERE
	(LM.LeagueMatchHomeID = '$defaultawayid' OR
	LM.LeagueMatchAwayID = '$defaultawayid') AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid' AND
	LM.LeagueMatchHomeGoals IS NOT NULL AND
	LM.LeagueMatchAwayGoals IS NOT NULL
	", $connection)
	or die(mysql_error());

	//
	//Table variables: hometeam values into index 0 and awayteam into index 1
	//
	$home_wins[0] = 0;
	$home_draws[0] = 0;
	$home_loses[0] = 0;
	$home_goals[0] = 0;
	$home_goalsagainst[0] = 0;
	$away_wins[0] = 0;
	$away_draws[0] = 0;
	$away_loses[0] = 0;
	$away_goals[0] = 0;
	$away_goalsagainst[0] = 0;
	$total_wins[0] = 0;
	$total_draws[0] = 0;
	$total_loses[0] = 0;
	$total_goals[0] = 0;
	$total_goalsagainst[0] = 0;

	$home_wins[1] = 0;
	$home_draws[1] = 0;
	$home_loses[1] = 0;
	$home_goals[1] = 0;
	$home_goalsagainst[1] = 0;
	$away_wins[1] = 0;
	$away_draws[1] = 0;
	$away_loses[1] = 0;
	$away_goals[1] = 0;
	$away_goalsagainst[1] = 0;
	$total_wins[1] = 0;
	$total_draws[1] = 0;
	$total_loses[1] = 0;
	$total_goals[1] = 0;
	$total_goalsagainst[1] = 0;


	//
	//Lets check hometeam
	//
	while($data = mysql_fetch_array($query))
	{
		//
		//Home or away game?
		//
		//
		//Home match
		//
		if($data['homeid'] == $defaulthomeid)
		{
			//
			//Win
			//
			if($data['homegoals'] > $data['awaygoals'])
			{
				$home_wins[0]++;
				$home_goals[0] = $home_goals[0] + $data['homegoals'];
				$home_goalsagainst[0] = $home_goalsagainst[0] + $data['awaygoals'];
			}
			//
			//Draw
			//
			elseif($data['homegoals'] == $data['awaygoals'])
			{
				$home_draws[0]++;
				$home_goals[0] = $home_goals[0] + $data['homegoals'];
				$home_goalsagainst[0] = $home_goalsagainst[0] + $data['awaygoals'];
			}
			//
			//Lost
			//
			elseif($data['homegoals'] < $data['awaygoals'])
			{
				$home_loses[0]++;
				$home_goals[0] = $home_goals[0] + $data['homegoals'];
				$home_goalsagainst[0] = $home_goalsagainst[0] + $data['awaygoals'];
			}
		}
		//
		//Away mathc
		//
		else
		{
			//
			//Win
			//
			if($data['awaygoals'] > $data['homegoals'])
			{
				$away_wins[0]++;
				$away_goals[0] = $away_goals[0] + $data['awaygoals'];
				$away_goalsagainst[0] = $away_goalsagainst[0] + $data['homegoals'];
			}
			//
			//Draw
			//
			elseif($data['awaygoals'] == $data['homegoals'])
			{
				$away_draws[0]++;
				$away_goals[0] = $away_goals[0] + $data['awaygoals'];
				$away_goalsagainst[0] = $away_goalsagainst[0] + $data['homegoals'];
			}
			//
			//Lost
			//
			elseif($data['awaygoals'] < $data['homegoals'])
			{
				$away_loses[0]++;
				$away_goals[0] = $away_goals[0] + $data['awaygoals'];
				$away_goalsagainst[0] = $away_goalsagainst[0] + $data['homegoals'];
			}
		}
	}


	//
	//Lets check away team
	//
	while($data = mysql_fetch_array($query2))
	{
		//
		//Home match
		//
		if($data['homeid'] == $defaultawayid)
		{
			//
			//Win
			//
			if($data['homegoals'] > $data['awaygoals'])
			{
				$home_wins[1]++;
				$home_goals[1] = $home_goals[1] + $data['homegoals'];
				$home_goalsagainst[1] = $home_goalsagainst[1] + $data['awaygoals'];
			}
			//
			//Draw
			//
			elseif($data['homegoals'] == $data['awaygoals'])
			{
				$home_draws[1]++;
				$home_goals[1] = $home_goals[1] + $data['homegoals'];
				$home_goalsagainst[1] = $home_goalsagainst[1] + $data['awaygoals'];
			}
			//
			//Lost
			//
			elseif($data['homegoals'] < $data['awaygoals'])
			{
				$home_loses[1]++;
				$home_goals[1] = $home_goals[1] + $data['homegoals'];
				$home_goalsagainst[1] = $home_goalsagainst[1] + $data['awaygoals'];
			}
		}
		//
		//Away match
		//
		else
		{
			//
			//Win
			//
			if($data['awaygoals'] > $data['homegoals'])
			{
				$away_wins[1]++;
				$away_goals[1] = $away_goals[1] + $data['awaygoals'];
				$away_goalsagainst[1] = $away_goalsagainst[1] + $data['homegoals'];
			}
			//
			//Draw
			//
			elseif($data['awaygoals'] == $data['homegoals'])
			{
				$away_draws[1]++;
				$away_goals[1] = $away_goals[1] + $data['awaygoals'];
				$away_goalsagainst[1] = $away_goalsagainst[1] + $data['homegoals'];
			}
			//
			//Lost
			//
			elseif($data['awaygoals'] < $data['homegoals'])
			{
				$away_loses[1]++;
				$away_goals[1] = $away_goals[1] + $data['awaygoals'];
				$away_goalsagainst[1] = $away_goalsagainst[1] + $data['homegoals'];
			}
		}
	}

	//
	//Calculates home team data
	//
	$home_played[0] = $home_wins[0] + $home_draws[0] + $home_loses[0];
	$away_played[0] = $away_wins[0] + $away_draws[0] + $away_loses[0];

	$total_wins[0] = $home_wins[0] + $away_wins[0];
	$total_draws[0] = $home_draws[0] + $away_draws[0];
	$total_loses[0] = $home_loses[0] + $away_loses[0];
	$total_goals[0] = $home_goals[0] + $away_goals[0];
	$total_goalsagainst[0] = $home_goalsagainst[0] + $away_goalsagainst[0];
	$total_played[0] = $total_wins[0] + $total_draws[0] + $total_loses[0];
	$total_points[0] = ($for_win*$total_wins[0]) + ($for_draw*$total_draws[0]) + ($for_lost*$total_lose[0]);

	$total_gd[0] = $total_goals[0] - $total_goalsagainst[0];
	$home_gd[0] = $home_goals[0] - $home_goalsagainst[0];
	$away_gd[0] = $away_goals[0] - $away_goalsagainst[0];

	//
	//Calculates away team data
	//
	$home_played[1] = $home_wins[1] + $home_draws[1] + $home_loses[1];
	$away_played[1] = $away_wins[1] + $away_draws[1] + $away_loses[1];

	$total_wins[1] = $home_wins[1] + $away_wins[1];
	$total_draws[1] = $home_draws[1] + $away_draws[1];
	$total_loses[1] = $home_loses[1] + $away_loses[1];
	$total_goals[1] = $home_goals[1] + $away_goals[1];
	$total_goalsagainst[1] = $home_goalsagainst[1] + $away_goalsagainst[1];
	$total_played[1] = $total_wins[1] + $total_draws[1] + $total_loses[1];
	$total_points[1] = ($for_win*$total_wins[1]) + ($for_draw*$total_draws[1]) + ($for_lost*$total_lose[1]);

	$total_gd[1] = $total_goals[1] - $total_goalsagainst[1];
	$home_gd[1] = $home_goals[1] - $home_goalsagainst[1];
	$away_gd[1] = $away_goals[1] - $away_goalsagainst[1];

	mysql_free_result($query);
	mysql_free_result($query2);

	//
	//Query to get head-to-head data
	//
	$headtohead_query = mysql_query("
	SELECT
	O.OpponentName AS hometeam,
	OP.OpponentName AS awayteam,
	LM.LeagueMatchHomeID AS homeid,
	LM.LeagueMatchAwayID AS awayid,
	DATE_FORMAT(LM.LeagueMatchDate, '$print_date') AS date,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM
	tplls_leaguematches AS LM,
	tplls_opponents O,
	tplls_opponents OP
	WHERE
	O.OpponentID = LM.LeagueMatchHomeID AND
	OP.OpponentID = LM.LeagueMatchAwayID AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid' AND
    LM.LeagueMatchHomeGoals IS NOT NULL AND
    LM.LeagueMatchAwayGoals IS NOT NULL AND
	((LM.LeagueMatchHomeID = '$defaulthomeid' AND LM.LeagueMatchAwayID = '$defaultawayid') OR
	(LM.LeagueMatchHomeID = '$defaultawayid' AND LM.LeagueMatchAwayID = '$defaulthomeid'))
	ORDER BY LM.LeagueMatchDate DESC
	", $connection)
	or die(mysql_error());


	//
	//Sets zero for head-to-head variables
	//Also checks the data in while-loop
	//

	$hth_home_wins = 0;
	$hth_home_draws = 0;
	$hth_home_loses = 0;
	$hth_home_goals = 0;
	$hth_home_goals_against = 0;

	$hth_away_wins = 0;
	$hth_away_draws = 0;
	$hth_away_loses = 0;
	$hth_away_goals = 0;
	$hth_away_goals_against = 0;

	$i = 0;
	while($data = mysql_fetch_array($headtohead_query))
	{
		//
		//Maximum five games into variables
		//
		if($i < 5)
		{
			$hth_matches_date[$i] = $data['date'];
			$hth_matches_home[$i] = $data['hometeam'];
			$hth_matches_away[$i] = $data['awayteam'];
			$hth_matches_score[$i] = $data['homegoals'] . " - " . $data['awaygoals'];

			$i++;
		}

		//
		//hometeams home match
		//
		if($data['homeid'] == $defaulthomeid)
		{
			if($data['homegoals'] > $data['awaygoals'])
			{
				$hth_home_wins++;
				$hth_away_loses++;
			}
			elseif($data['homegoals'] == $data['awaygoals'])
			{
				$hth_home_draws++;
				$hth_away_draws++;
			}
			elseif($data['homegoals'] < $data['awaygoals'])
			{
				$hth_home_loses++;
				$hth_away_wins++;
			}

			$hth_home_goals = $hth_home_goals + $data['homegoals'];
			$hth_home_goals_against = $hth_home_goals_against + $data['awaygoals'];
			$hth_away_goals = $hth_away_goals + $data['awaygoals'];
			$hth_away_goals_against = $hth_away_goals_against + $data['homegoals'];

		}
		elseif($data['homeid'] == $defaultawayid)
		{
			if($data['homegoals'] > $data['awaygoals'])
			{
				$hth_away_wins++;
				$hth_home_loses++;
			}
			elseif($data['homegoals'] == $data['awaygoals'])
			{
				$hth_away_draws++;
				$hth_home_draws++;
			}
			elseif($data['homegoals'] < $data['awaygoals'])
			{
				$hth_away_loses++;
				$hth_home_wins++;
			}

			$hth_away_goals = $hth_away_goals + $data['homegoals'];
			$hth_away_goals_against = $hth_away_goals_against + $data['awaygoals'];
			$hth_home_goals = $hth_home_goals + $data['awaygoals'];
			$hth_home_goals_against = $hth_home_goals_against + $data['homegoals'];
		}
	}

	mysql_free_result($headtohead_query);


	?>

	<tr>
	<td align="left" valign="middle" width="35%">
	<font class="bigname"><?= $namedata['homename'] ?></font>
	</td>

	<td align="center" valign="middle">
	<?= $lng_vs ?>
	</td>

	<td align="right" valign="middle" width="35%">
	<font class="bigname"><?= $namedata['awayname'] ?></font>
	</td>
	</tr>

	<tr>
	<td colspan="3" align="left" valign="middle" bgcolor="<?= $top_bg ?>">
	<b><?= $lng_headtohead ?></b>
	</td>
	</tr>

	<tr>

	<td align="left" valign="middle">
	<b><?= "$hth_home_wins-$hth_home_draws-$hth_home_loses" ?></b>
	</td>

	<td align="center" valign="middle">
	<?= $lng_w_d_l ?>
	</td>

	<td align="right" valign="middle">
	<b><?= "$hth_away_wins-$hth_away_draws-$hth_away_loses" ?></b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b><?= "$hth_home_goals-$hth_home_goals_against" ?></b>
	</td>

	<td align="center" valign="middle">
	<?= $lng_goal_difference ?>
	</td>

	<td align="right" valign="middle">
	<b><?= "$hth_away_goals-$hth_away_goals_against" ?></b>
	</td>

	</tr>

	<tr>
	<td colspan="3" align="left" valign="middle" bgcolor="<?= $top_bg ?>">
	<b><?= $lng_latest_headtohead_matches ?></b>
	</td>
	</tr>

	<tr>
	<td colspan="3" align="left" valign="middle">

	<?php

	for($j = 0 ; $j < $i ; $j++)
	{
		echo"$hth_matches_date[$j]: $hth_matches_home[$j] - $hth_matches_away[$j]&nbsp;&nbsp;&nbsp;$hth_matches_score[$j]<br>";
	}

	?>

	</td>
	</tr>

	<tr>
	<td colspan="3" align="left" valign="middle" bgcolor="<?= $top_bg ?>">
	<b><?= $lng_overall_match_statistics ?></b>
	</td>
	</tr>

	<tr>

	<td align="left" valign="middle">
	<b><?= $total_points[0] ?></b>
	</td>

	<td align="center" valign="middle">
	<?= $lng_points_earned ?>
	</td>

	<td align="right" valign="middle">
	<b><?= $total_points[1] ?></b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b><?= $total_played[0] ?></b>
	</td>

	<td align="center" valign="middle">
	<?= $lng_matches_played_overall ?>
	</td>

	<td align="right" valign="middle">
	<b><?= $total_played[1] ?></b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($total_played[0] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($total_wins[0]/$total_played[0]));
	}

	echo"$total_wins[0]</b> ($temp %)";

	?>
	</td>

	<td align="center" valign="middle">
	<?= $lng_matches_won_overall ?>
	</td>

	<td align="right" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($total_played[1] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($total_wins[1]/$total_played[1]));
	}

	echo"$total_wins[1]</b> ($temp %)";

	?>
	</b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($total_played[0] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($total_draws[0]/$total_played[0]));
	}

	echo"$total_draws[0]</b> ($temp %)";

	?>
	</td>

	<td align="center" valign="middle">
	<?= $lng_matches_drawn_overall ?>
	</td>

	<td align="right" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($total_played[1] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($total_draws[1]/$total_played[1]));
	}

	echo"$total_draws[1]</b> ($temp %)";

	?></b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($total_played[0] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($total_loses[0]/$total_played[0]));
	}

	echo"$total_loses[0]</b> ($temp %)";

	?>
	</td>

	<td align="center" valign="middle">
	<?= $lng_matches_lost_overall ?>
	</td>

	<td align="right" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($total_played[1] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($total_loses[1]/$total_played[1]));
	}

	echo"$total_loses[1]</b> ($temp %)";

	?>
	</b>
	</td>

	</tr>

	<tr>
	<td align="center" valign="middle" colspan="3">
	<img src="images/line.gif" width="<?= $templine_width ?>" height="5" ALT=""><br>
	</td>
	</tr>



	<tr>

	<td align="left" valign="middle">
	<b><?= $home_played[0] ?></b>
	</td>

	<td align="center" valign="middle">
	<?= $lng_home_matches_played_overall ?>
	</td>

	<td align="right" valign="middle">
	<b><?= $home_played[1] ?></b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($home_played[0] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($home_wins[0]/$home_played[0]));
	}

	echo"$home_wins[0]</b> ($temp %)";

	?>
	</td>

	<td align="center" valign="middle">
	<?= $lng_home_matches_won_overall ?>
	</td>

	<td align="right" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($home_played[1] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($home_wins[1]/$home_played[1]));
	}

	echo"$home_wins[1]</b> ($temp %)";

	?></b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($home_played[0] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($home_draws[0]/$home_played[0]));
	}

	echo"$home_draws[0]</b> ($temp %)";

	?>
	</td>

	<td align="center" valign="middle">
	<?= $lng_home_matches_drawn_overall ?>
	</td>

	<td align="right" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($home_played[1] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($home_draws[1]/$home_played[1]));
	}

	echo"$home_draws[1]</b> ($temp %)";

	?></b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($home_played[0] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($home_loses[0]/$home_played[0]));
	}

	echo"$home_loses[0]</b> ($temp %)";

	?>
	</td>

	<td align="center" valign="middle">
	<?= $lng_home_matches_lost_overall ?>
	</td>

	<td align="right" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($home_played[1] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($home_loses[1]/$home_played[1]));
	}

	echo"$home_loses[1]</b> ($temp %)";

	?></b>
	</td>

	</tr>

	<tr>
	<td align="center" valign="middle" colspan="3">
	<img src="images/line.gif" width="<?= $templine_width ?>" height="5" ALT=""><br>
	</td>
	</tr>

	<tr>

	<td align="left" valign="middle">
	<b><?= $away_played[0] ?></b>
	</td>

	<td align="center" valign="middle">
	<?= $lng_away_matches_played_overall ?>
	</td>

	<td align="right" valign="middle">
	<b><?= $away_played[1] ?></b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($away_played[0] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($away_wins[0]/$away_played[0]));
	}

	echo"$away_wins[0]</b> ($temp %)";

	?>
	</td>

	<td align="center" valign="middle">
	<?= $lng_away_matches_won_overall ?>
	</td>

	<td align="right" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($away_played[1] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($away_wins[1]/$away_played[1]));
	}

	echo"$away_wins[1]</b> ($temp %)";

	?></b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($away_played[0] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($away_draws[0]/$away_played[0]));
	}

	echo"$away_draws[0]</b> ($temp %)";

	?>
	</td>

	<td align="center" valign="middle">
	<?= $lng_away_matches_drawn_overall ?>
	</td>

	<td align="right" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($away_played[1] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($away_draws[1]/$away_played[1]));
	}

	echo"$away_draws[1]</b> ($temp %)";

	?></b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($away_played[0] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($away_loses[0]/$away_played[0]));
	}

	echo"$away_loses[0]</b> ($temp %)";

	?>
	</td>

	<td align="center" valign="middle">
	<?= $lng_away_matches_lost_overall ?>
	</td>

	<td align="right" valign="middle">
	<b>
	<?php

	//
	//Avoid divide by zero
	//
	if($away_played[1] == 0)
	{
		$temp = 0;
	}
	else
	{
		$temp = round(100*($away_loses[1]/$away_played[1]));
	}

	echo"$away_loses[1]</b> ($temp %)";

	?></b>
	</td>

	</tr>


	<tr>
	<td align="center" valign="middle" colspan="3">
	<img src="images/line.gif" width="<?= $templine_width ?>" height="5" ALT=""><br>
	</td>
	</tr>


	<tr>

	<td align="left" valign="middle">
	<b><?php

	if($total_gd[0] >= 0)
		echo'+';

	echo"$total_gd[0]</b> ($total_goals[0] - $total_goalsagainst[0])";

	?>
	</td>

	<td align="center" valign="middle">
	<?= $lng_goal_difference_overall ?>
	</td>

	<td align="right" valign="middle">
	<b><?php

	if($total_gd[1] >= 0)
		echo'+';

	echo"$total_gd[1]</b> ($total_goals[1] - $total_goalsagainst[1])";

	?></b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b><?php

	if($home_gd[0] >= 0)
		echo'+';

	echo"$home_gd[0]</b> ($home_goals[0] - $home_goalsagainst[0])";

	?>
	</td>

	<td align="center" valign="middle">
	<?= $lng_goal_difference_in_home ?>
	</td>

	<td align="right" valign="middle">
	<b><?php

	if($home_gd[1] >= 0)
		echo'+';

	echo"$home_gd[1]</b> ($home_goals[1] - $home_goalsagainst[1])";

	?></b>
	</td>

	</tr>

	<tr>

	<td align="left" valign="middle">
	<b><?php

	if($away_gd[0] >= 0)
		echo'+';

	echo"$away_gd[0]</b> ($away_goals[0] - $away_goalsagainst[0])";

	?>
	</td>

	<td align="center" valign="middle">
	<?= $lng_goal_difference_in_away ?>
	</td>

	<td align="right" valign="middle">
	<b><?php

	if($away_gd[1] >= 0)
		echo'+';

	echo"$away_gd[1]</b> ($away_goals[1] - $away_goalsagainst[1])";

	?></b>
	</td>

	</tr>

	<?php
	//
	//Query to get biggest home win/lost/aggr for hometeam
	//
	$query = mysql_query("
	SELECT
	MAX(LeagueMatchHomeGoals - LeagueMatchAwayGoals) AS maxhomewin,
	MAX(LeagueMatchAwayGoals - LeagueMatchHomeGoals) AS maxhomelost,
	MAX(LeagueMatchHomeGoals + LeagueMatchAwayGoals) AS maxhomeaggregate
	FROM tplls_leaguematches
	WHERE LeagueMatchHomeID = '$defaulthomeid' AND
	LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	$maxhomedata_hometeam = mysql_fetch_array($query);

	mysql_free_result($query);

	//
	//Query to get biggest away win/lost/aggr for hometeam
	//
	$query = mysql_query("
	SELECT
	MAX(LeagueMatchAwayGoals - LeagueMatchHomeGoals) AS maxawaywin,
	MAX(LeagueMatchHomeGoals - LeagueMatchAwayGoals) AS maxawaylost,
	MAX(LeagueMatchHomeGoals + LeagueMatchAwayGoals) AS maxawayaggregate
	FROM tplls_leaguematches
	WHERE LeagueMatchAwayID = '$defaulthomeid' AND
	LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	$maxawaydata_hometeam = mysql_fetch_array($query);

	mysql_free_result($query);

	//
	//Query to get biggest home win/lost/aggr for awayteam
	//
	$query = mysql_query("
	SELECT
	MAX(LeagueMatchHomeGoals - LeagueMatchAwayGoals) AS maxhomewin,
	MAX(LeagueMatchAwayGoals - LeagueMatchHomeGoals) AS maxhomelost,
	MAX(LeagueMatchHomeGoals + LeagueMatchAwayGoals) AS maxhomeaggregate
	FROM tplls_leaguematches
	WHERE LeagueMatchHomeID = '$defaultawayid' AND
	LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	$maxhomedata_awayteam = mysql_fetch_array($query);

	mysql_free_result($query);

	//
	//Query to get biggest away win/lost/aggr for awayteam
	//
	$query = mysql_query("
	SELECT
	MAX(LeagueMatchAwayGoals - LeagueMatchHomeGoals) AS maxawaywin,
	MAX(LeagueMatchHomeGoals - LeagueMatchAwayGoals) AS maxawaylost,
	MAX(LeagueMatchHomeGoals + LeagueMatchAwayGoals) AS maxawayaggregate
	FROM tplls_leaguematches
	WHERE LeagueMatchAwayID = '$defaultawayid' AND
	LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	$maxawaydata_awayteam = mysql_fetch_array($query);

	mysql_free_result($query);

	//
	//Lets put max results into variables
	//
	$maxhomewin_home = $maxhomedata_hometeam['maxhomewin'];
	$maxhomelost_home = $maxhomedata_hometeam['maxhomelost'];
	$maxhomeaggregate_home = $maxhomedata_hometeam['maxhomeaggregate'];
	$maxawaywin_home = $maxawaydata_hometeam['maxawaywin'];
	$maxawaylost_home = $maxawaydata_hometeam['maxawaylost'];
	$maxawayaggregate_home = $maxawaydata_hometeam['maxawayaggregate'];

	$maxhomewin_away = $maxhomedata_awayteam['maxhomewin'];
	$maxhomelost_away = $maxhomedata_awayteam['maxhomelost'];
	$maxhomeaggregate_away = $maxhomedata_awayteam['maxhomeaggregate'];
	$maxawaywin_away = $maxawaydata_awayteam['maxawaywin'];
	$maxawaylost_away = $maxawaydata_awayteam['maxawaylost'];
	$maxawayaggregate_away = $maxawaydata_awayteam['maxawayaggregate'];

	?>

	<tr>
	<td colspan="3" align="left" valign="middle" bgcolor="<?= $top_bg ?>">
	<b><?= $lng_biggest_home_win ?></b>
	</td>
	</tr>

	<tr>

	<td align="left" valign="top">
	<?php
	//
	//Query to get all the biggest home wins: home
	//
	$query = mysql_query("
	SELECT
	O.OpponentName AS name,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE O.OpponentID = LM.LeagueMatchAwayID AND
	LM.LeagueMatchHomeID = '$defaulthomeid' AND
	(LM.LeagueMatchHomeGoals - LM.LeagueMatchAwayGoals) = '$maxhomewin_home' AND
	(LM.LeagueMatchHomeGoals - LM.LeagueMatchAwayGoals) > 0 AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	//
	//If there are no home wins->print none
	//
	if(mysql_num_rows($query) == 0)
	{
		echo"$lng_none";
	}
	else
	{
		while($data = mysql_fetch_array($query))
		{
			echo"$data[homegoals] - $data[awaygoals] $lng_vs $data[name]<br>\n";
		}
	}

	mysql_free_result($query);

	?>
	</td>

	<td align="center" valign="middle">
	&nbsp;
	</td>

	<td align="right" valign="top">
	<?php
	//
	//Query to get all the biggest home wins: away
	//
	$query = mysql_query("
	SELECT
	O.OpponentName AS name,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE O.OpponentID = LM.LeagueMatchAwayID AND
	LM.LeagueMatchHomeID = '$defaultawayid' AND
	(LM.LeagueMatchHomeGoals - LM.LeagueMatchAwayGoals) = '$maxhomewin_away' AND
	(LM.LeagueMatchHomeGoals - LM.LeagueMatchAwayGoals) > 0 AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	//
	//If there are no home wins->print none
	//
	if(mysql_num_rows($query) == 0)
	{
		echo"$lng_none";
	}
	else
	{
		while($data = mysql_fetch_array($query))
		{
			echo"$data[homegoals] - $data[awaygoals] $lng_vs $data[name]<br>\n";
		}
	}

	mysql_free_result($query);

	?>
	</td>

	</tr>

	<tr>
	<td colspan="3" align="left" valign="middle" bgcolor="<?= $top_bg ?>">
	<b><?= $lng_biggest_home_lost ?></b>
	</td>
	</tr>


	<tr>

	<td align="left" valign="top">
	<?php
	//
	//Query to get all the biggest home losses: home
	//
	$query = mysql_query("
	SELECT
	O.OpponentName AS name,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE O.OpponentID = LM.LeagueMatchAwayID AND
	LM.LeagueMatchHomeID = '$defaulthomeid' AND
	(LM.LeagueMatchAwayGoals - LM.LeagueMatchHomeGoals) = '$maxhomelost_home' AND
	(LM.LeagueMatchAwayGoals - LM.LeagueMatchHomeGoals) > 0 AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	//
	//If there are no home loses->print none
	//
	if(mysql_num_rows($query) == 0)
	{
		echo"$lng_none";
	}
	else
	{
		while($data = mysql_fetch_array($query))
		{
			echo"$data[homegoals] - $data[awaygoals] $lng_vs $data[name]<br>\n";
		}
	}

	mysql_free_result($query);

	?>
	</td>

	<td align="center" valign="middle">
	&nbsp;
	</td>

	<td align="right" valign="top">
	<?php
	//
	//Query to get all the biggest home loses: away
	//
	$query = mysql_query("
	SELECT
	O.OpponentName AS name,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE O.OpponentID = LM.LeagueMatchAwayID AND
	LM.LeagueMatchHomeID = '$defaultawayid' AND
	(LM.LeagueMatchAwayGoals - LM.LeagueMatchHomeGoals) = '$maxhomelost_away' AND
	(LM.LeagueMatchAwayGoals - LM.LeagueMatchHomeGoals) > 0 AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	//
	//If there are no home loses->print none
	//
	if(mysql_num_rows($query) == 0)
	{
		echo"$lng_none";
	}
	else
	{
		while($data = mysql_fetch_array($query))
		{
			echo"$data[homegoals] - $data[awaygoals] $lng_vs $data[name]<br>\n";
		}
	}

	mysql_free_result($query);

	?>
	</td>

	</tr>

	<tr>
	<td colspan="3" align="left" valign="middle" bgcolor="<?= $top_bg ?>">
	<b><?= $lng_highest_aggregate_in_home ?></b>
	</td>
	</tr>

	<tr>

	<td align="left" valign="top">
	<?php
	//
	//Query to get all the biggest home aggregate: home
	//
	$query = mysql_query("
	SELECT
	O.OpponentName AS name,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE O.OpponentID = LM.LeagueMatchAwayID AND
	LM.LeagueMatchHomeID = '$defaulthomeid' AND
	(LM.LeagueMatchHomeGoals + LM.LeagueMatchAwayGoals) = '$maxhomeaggregate_home' AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	while($data = mysql_fetch_array($query))
	{
		echo"$data[homegoals] - $data[awaygoals] $lng_vs $data[name]<br>\n";
	}

	mysql_free_result($query);

	?>
	</td>

	<td align="center" valign="middle">
	&nbsp;
	</td>

	<td align="right" valign="top">
	<?php
	//
	//Query to get all the biggest home aggregate: away
	//
	$query = mysql_query("
	SELECT
	O.OpponentName AS name,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE O.OpponentID = LM.LeagueMatchAwayID AND
	LM.LeagueMatchHomeID = '$defaultawayid' AND
	(LM.LeagueMatchHomeGoals + LM.LeagueMatchAwayGoals) = '$maxhomeaggregate_away' AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	while($data = mysql_fetch_array($query))
	{
		echo"$data[homegoals] - $data[awaygoals] $lng_vs $data[name]<br>\n";
	}

	mysql_free_result($query);

	?>
	</td>

	</tr>

	<tr>
	<td colspan="3" align="left" valign="middle" bgcolor="<?= $top_bg ?>">
	<b><?= $lng_biggest_away_win ?></b>
	</td>
	</tr>

	<tr>

	<td align="left" valign="top">
	<?php
	//
	//Query to get all the biggest away wins: home
	//
	$query = mysql_query("
	SELECT
	O.OpponentName AS name,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE O.OpponentID = LM.LeagueMatchHomeID AND
	LM.LeagueMatchAwayID = '$defaulthomeid' AND
	(LM.LeagueMatchAwayGoals - LM.LeagueMatchHomeGoals) = '$maxawaywin_home' AND
	(LM.LeagueMatchAwayGoals - LM.LeagueMatchHomeGoals) > 0 AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	//
	//If there are no away wins->print none
	//
	if(mysql_num_rows($query) == 0)
	{
		echo"$lng_none";
	}
	else
	{
		while($data = mysql_fetch_array($query))
		{
			echo"$data[awaygoals] - $data[homegoals] $lng_vs $data[name]<br>\n";
		}
	}

	mysql_free_result($query);

	?>
	</td>

	<td align="center" valign="middle">
	&nbsp;
	</td>

	<td align="right" valign="top">
	<?php
	//
	//Query to get all the biggest away wins: away
	//
	$query = mysql_query("
	SELECT
	O.OpponentName AS name,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE O.OpponentID = LM.LeagueMatchHomeID AND
	LM.LeagueMatchAwayID = '$defaultawayid' AND
	(LM.LeagueMatchAwayGoals - LM.LeagueMatchHomeGoals) = '$maxawaywin_away' AND
	(LM.LeagueMatchAwayGoals - LM.LeagueMatchHomeGoals) > 0 AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	//
	//If there are no away wins->print none
	//
	if(mysql_num_rows($query) == 0)
	{
		echo"$lng_none";
	}
	else
	{
		while($data = mysql_fetch_array($query))
		{
			echo"$data[awaygoals] - $data[homegoals] $lng_vs $data[name]<br>\n";
		}
	}

	mysql_free_result($query);

	?>
	</td>

	</tr>

	<tr>
	<td colspan="3" align="left" valign="middle" bgcolor="<?= $top_bg ?>">
	<b><?= $lng_biggest_away_lost ?></b>
	</td>
	</tr>

	<tr>

	<td align="left" valign="top">
	<?php
	//
	//Query to get all the biggest away loses: home
	//
	$query = mysql_query("
	SELECT
	O.OpponentName AS name,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE O.OpponentID = LM.LeagueMatchHomeID AND
	LM.LeagueMatchAwayID = '$defaulthomeid' AND
	(LM.LeagueMatchHomeGoals - LM.LeagueMatchAwayGoals) = '$maxawaylost_home' AND
	(LM.LeagueMatchHomeGoals - LM.LeagueMatchAwayGoals) > 0 AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	//
	//If there are no away loses->print none
	//
	if(mysql_num_rows($query) == 0)
	{
		echo"$lng_none";
	}
	else
	{
		while($data = mysql_fetch_array($query))
		{
			echo"$data[awaygoals] - $data[homegoals] $lng_vs $data[name]<br>\n";
		}
	}

	mysql_free_result($query);

	?>
	</td>

	<td align="center" valign="middle">
	&nbsp;
	</td>

	<td align="right" valign="top">
	<?php
	//
	//Query to get all the biggest away loses: away
	//
	$query = mysql_query("
	SELECT
	O.OpponentName AS name,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE O.OpponentID = LM.LeagueMatchHomeID AND
	LM.LeagueMatchAwayID = '$defaultawayid' AND
	(LM.LeagueMatchHomeGoals - LM.LeagueMatchAwayGoals) = '$maxawaylost_away' AND
	(LM.LeagueMatchHomeGoals - LM.LeagueMatchAwayGoals) > 0 AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	//
	//If there are no away wins->print none
	//
	if(mysql_num_rows($query) == 0)
	{
		echo"$lng_none";
	}
	else
	{
		while($data = mysql_fetch_array($query))
		{
			echo"$data[awaygoals] - $data[homegoals] $lng_vs $data[name]<br>\n";
		}
	}

	mysql_free_result($query);

	?>
	</td>

	</tr>


	<tr>
	<td colspan="3" align="left" valign="middle" bgcolor="<?= $top_bg ?>">
	<b><?= $lng_highest_aggregate_in_away ?></b>
	</td>
	</tr>

	<tr>

	<td align="left" valign="top">
	<?php
	//
	//Query to get all the biggest away aggregate: home
	//
	$query = mysql_query("
	SELECT
	O.OpponentName AS name,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE O.OpponentID = LM.LeagueMatchHomeID AND
	LM.LeagueMatchAwayID = '$defaulthomeid' AND
	(LM.LeagueMatchAwayGoals + LM.LeagueMatchHomeGoals) = '$maxawayaggregate_home' AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	while($data = mysql_fetch_array($query))
	{
		echo"$data[awaygoals] - $data[homegoals] $lng_vs $data[name]<br>\n";
	}

	mysql_free_result($query);

	?>
	</td>

	<td align="center" valign="middle">
	&nbsp;
	</td>

	<td align="right" valign="top">
	<?php
	//
	//Query to get all the biggest away aggregate: away
	//
	$query = mysql_query("
	SELECT
	O.OpponentName AS name,
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM tplls_opponents O, tplls_leaguematches LM
	WHERE O.OpponentID = LM.LeagueMatchHomeID AND
	LM.LeagueMatchAwayID = '$defaultawayid' AND
	(LM.LeagueMatchAwayGoals + LM.LeagueMatchHomeGoals) = '$maxawayaggregate_away' AND
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	while($data = mysql_fetch_array($query))
	{
		echo"$data[awaygoals] - $data[homegoals] $lng_vs $data[name]<br>\n";
	}

	mysql_free_result($query);

	?>
	</td>

	</tr>


	</table>

	</td>
	</tr>
	</table>
</td>
</tr>
</table>


<?php
include('bottom.txt');
?>
</form>

<?php
//
//INCLUDES FOOTER.PHP
//
include('footer.php');
?>
