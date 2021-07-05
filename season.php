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
//Preferences
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
$defaulthomeid = $pdata['defaulthomeid'];
$defaultawayid = $pdata['defaultawayid'];
$print_date = $pdata['printdate'];
$top_bg = $pdata['topoftable'];
$bg1 = $pdata['bg1'];
$bg2 = $pdata['bg2'];
$inside_c = $pdata['inside'];
$border_c = $pdata['bordercolour'];
$tb_width = $pdata['tablewidth'];
$accept_ml = $pdata['acceptmultilanguage'];

//
//Check if there are session variables registered
//
if(!session_is_registered('defaultlanguage') || !session_is_registered('defaultseasonid'))
{
	$_SESSION['defaultlanguage'] = $language;
	$_SESSION['defaultseasonid'] = $d_seasonid;
	$defaultlanguage = $_SESSION['defaultlanguage'];
	$defaultseasonid = $_SESSION['defaultseasonid'];
}
else
{
	$defaultlanguage = $_SESSION['defaultlanguage'];
	$defaultseasonid = $_SESSION['defaultseasonid'];
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

	echo'</select> <input type="submit" value=">>" name="submit7">&nbsp;&nbsp;&nbsp;';
}
?>

<?= $lng_moveto ?>: <select name="moveto">
<option value="index.php"><?= $lng_tables_drop ?></option>
<option value="headtohead.php"><?= $lng_headtohead_drop ?></option>
</select> <input type="submit" value=">>" name="submit6">

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

	<tr>

	<td bgcolor="<?= $bg1 ?>" align="left" valign="middle" colspan="2">
	<h1><?= $lng_season_statistics ?></h1>
	<?= $lng_season_filter ?>: <select name="season">
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
	</select> <input type="submit" value=">>" name="submit">
	</td>

	</tr>

	<?php

	//
	//Query to get data from the matches
	//
	$query = mysql_query("
	SELECT
	LM.LeagueMatchHomeGoals AS homegoals,
	LM.LeagueMatchAwayGoals AS awaygoals
	FROM
	tplls_leaguematches LM
	WHERE
	LM.LeagueMatchSeasonID LIKE '$defaultseasonid' AND
	LM.LeagueMatchHomeGoals IS NOT NULL AND
	LM.LeagueMatchAwayGoals IS NOT NULL
	", $connection)
	or die(mysql_error());

	//
	//Sets counter variables into zero
	//
	$home_wins = 0;

	$away_wins = 0;

	$draws = 0;

	$home_goals = 0;
	$away_goals = 0;

	$played = 0;
	$goals = 0;


	//
	//data check
	//
	while($data = mysql_fetch_array($query))
	{
		//
		//Home win
		//
		if($data['homegoals'] > $data['awaygoals'])
		{
			$home_wins++;
			$home_goals = $home_goals + $data['homegoals'];
			$away_goals = $away_goals + $data['awaygoals'];
		}
		//
		//Draw
		//
		elseif($data['homegoals'] == $data['awaygoals'])
		{
			$draws++;
			$home_goals = $home_goals + $data['homegoals'];
			$away_goals = $away_goals + $data['awaygoals'];
		}
		//
		//Away win
		//
		elseif($data['homegoals'] < $data['awaygoals'])
		{
			$away_wins++;
			$home_goals = $home_goals + $data['homegoals'];
			$away_goals = $away_goals + $data['awaygoals'];
		}
	}

	$played = $home_wins + $draws + $away_wins;

	$goals = $home_goals + $away_goals;

	//
	//Avoid divide by zero
	//
	if(mysql_num_rows($query) < 1)
	{
		$home_win_percent = 0;
		$away_win_percent = 0;
		$draw_percent = 0;

		$home_goal_average = 0;
		$away_goal_average = 0;
		$goal_average = 0;

		$home_win_percent_ = number_format($home_win_percent, 2, '.', '');
		$away_win_percent_ = number_format($away_win_percent, 2, '.', '');
		$draw_percent_ = number_format($draw_percent, 2, '.', '');
		$home_goal_average_ = number_format($home_goal_average, 2, '.', '');
		$away_goal_average_ = number_format($away_goal_average, 2, '.', '');
		$goal_average_ = number_format($goal_average, 2, '.', '');
	}
	else
	{
		//
		//Calculates percents and averages
		//
		$home_win_percent = round(100*($home_wins/$played),2);
		$away_win_percent = round(100*($away_wins/$played),2);
		$draw_percent = round(100*($draws/$played),2);

		$home_goal_average = round(($home_goals/$played),2);
		$away_goal_average = round(($away_goals/$played),2);
		$goal_average = round(($goals/$played),2);

		$home_win_percent_ = number_format($home_win_percent, 2, '.', '');
		$away_win_percent_ = number_format($away_win_percent, 2, '.', '');
		$draw_percent_ = number_format($draw_percent, 2, '.', '');
		$home_goal_average_ = number_format($home_goal_average, 2, '.', '');
		$away_goal_average_ = number_format($away_goal_average, 2, '.', '');
		$goal_average_ = number_format($goal_average, 2, '.', '');
	}

    mysql_free_result($query);

	?>

	<tr>

	<td align="left" valign="middle" width="50%">
	<b><?= $lng_matches_played ?></b>
	</td>

	<td align="left" valign="middle" width="50%">
	<?= $played ?>
	</td>

	</tr>

	<tr>

		<td align="left" valign="middle" width="50%">
		<b><?= $lng_home_wins ?></b>
		</td>

		<td align="left" valign="middle" width="50%">
		<?= "$home_wins ($home_win_percent_ %)" ?>
		</td>

	</tr>

	<tr>

		<td align="left" valign="middle" width="50%">
		<b><?= $lng_away_wins ?></b>
		</td>

		<td align="left" valign="middle" width="50%">
		<?= "$away_wins ($away_win_percent_ %)" ?>
		</td>

	</tr>

	<tr>

		<td align="left" valign="middle" width="50%">
		<b><?= $lng_draws ?></b>
		</td>

		<td align="left" valign="middle" width="50%">
		<?= "$draws ($draw_percent_ %)" ?>
		</td>

	</tr>

	<tr>

		<td align="left" valign="middle" width="50%">
		<b><?= $lng_total_goals_scored ?></b>
		</td>

		<td align="left" valign="middle" width="50%">
		<?= "$goals ($lng_average $goal_average_ $lng_per_match)" ?>
		</td>

	</tr>

	<tr>

		<td align="left" valign="middle" width="50%">
		<b><?= $lng_home_team_goals ?></b>
		</td>

		<td align="left" valign="middle" width="50%">
		<?= "$home_goals ($lng_average $home_goal_average_ $lng_per_match)" ?>
		</td>

	</tr>

	<tr>

		<td align="left" valign="middle" width="50%">
		<b><?= $lng_away_team_goals ?></b>
		</td>

		<td align="left" valign="middle" width="50%">
		<?= "$away_goals ($lng_average $away_goal_average_ $lng_per_match)" ?>
		</td>

	</tr>

	</table>

	<table width="100%" cellspacing="1" cellpadding="5" border="0" align="center">

	<tr>

	<td bgcolor="<?= $top_bg ?>" align="left" valign="middle" colspan="4">
	<b><?= $lng_biggest_home_win ?></b>
	</td>

	</tr>


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
	//Max home win
	//
	$maxhomewin = mysql_query("
	SELECT
	MAX(LeagueMatchHomeGoals - LeagueMatchAwayGoals) AS ero
	FROM tplls_leaguematches
	WHERE
	LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	$temp_data = mysql_fetch_array($maxhomewin);
	$temp_number = $temp_data['ero'];

	mysql_free_result($maxhomewin);

	//
	//Query to get all final scores with maximum value from previous query
	//
	$maxhomewin = mysql_query("
	SELECT
	O.OpponentName AS hometeam,
	OP.OpponentName AS awayteam,
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
	(LM.LeagueMatchHomeGoals - LM.LeagueMatchAwayGoals) = '$temp_number' AND
	LeagueMatchSeasonID LIKE '$defaultseasonid'
	ORDER BY LM.LeagueMatchDate
	", $connection)
	or die(mysql_error());

	//
	//Print max home wins
	//
	$i = 0;
	while($data = mysql_fetch_array($maxhomewin))
	{
		if($i % 0)
			$temp_color = $bg1;
		else
			$temp_color = $bg2;

		echo"
		<tr bgcolor=\"$temp_color\">
		<td align=\"left\" valign=\"middle\">
		$data[date]
		</td>

		<td align=\"center\" valign=\"middle\">
		$data[hometeam]
		</td>

		<td align=\"center\" valign=\"middle\">
		$data[awayteam]
		</td>

		<td align=\"center\" valign=\"middle\">
		$data[homegoals] - $data[awaygoals]
		</td>
		</tr>
		";

		$i++;
	}

	mysql_free_result($maxhomewin);

	?>


	<tr>
	<td colspan="4">
	<br>
	</td>
	</tr>

	<tr>

	<td bgcolor="<?= $top_bg ?>" align="left" valign="middle" colspan="4">
	<b><?= $lng_biggest_away_win ?></b>
	</td>

	</tr>


	<?php

	//
	//Max away win
	//
	$maxawaywin = mysql_query("
	SELECT
	MIN(LeagueMatchHomeGoals - LeagueMatchAwayGoals) AS ero
	FROM tplls_leaguematches
	WHERE
	LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	$temp_data = mysql_fetch_array($maxawaywin);
	$temp_number = $temp_data['ero'];

	mysql_free_result($maxawaywin);

	//
	//Query to get all max away wins
	//
	$maxawaywin = mysql_query("
	SELECT
	O.OpponentName AS hometeam,
	OP.OpponentName AS awayteam,
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
	(LM.LeagueMatchHomeGoals - LM.LeagueMatchAwayGoals) = '$temp_number' AND
	LeagueMatchSeasonID LIKE '$defaultseasonid'
	ORDER BY LM.LeagueMatchDate
	", $connection)
	or die(mysql_error());

	//
	//Prints max away wins
	//
	$i = 0;
	while($data = mysql_fetch_array($maxawaywin))
	{
		if($i % 0)
			$temp_color = $bg1;
		else
			$temp_color = $bg2;

		echo"
		<tr bgcolor=\"$temp_color\">
		<td align=\"left\" valign=\"middle\">
		$data[date]
		</td>

		<td align=\"center\" valign=\"middle\">
		$data[hometeam]
		</td>

		<td align=\"center\" valign=\"middle\">
		$data[awayteam]
		</td>

		<td align=\"center\" valign=\"middle\">
		$data[homegoals] - $data[awaygoals]
		</td>
		</tr>
		";

		$i++;
	}

	mysql_free_result($maxawaywin);

	?>


	<tr>
	<td colspan="4">
	<br>
	</td>
	</tr>

	<tr>

	<td bgcolor="<?= $top_bg ?>" align="left" valign="middle" colspan="4">
	<b><?= $lng_highest_aggregate_score ?></b>
	</td>

	</tr>


	<?php
	//
	//Most goals scored in one match
	//
	$maxgoals = mysql_query("
	SELECT
	MAX(LeagueMatchHomeGoals + LeagueMatchAwayGoals) AS summa
	FROM tplls_leaguematches
	WHERE
	LeagueMatchSeasonID LIKE '$defaultseasonid'
	", $connection)
	or die(mysql_error());

	$temp_data = mysql_fetch_array($maxgoals);
	$temp_number = $temp_data['summa'];

	mysql_free_result($maxgoals);

	//
	//Query t get max values
	//
	$maxgoals = mysql_query("
	SELECT
	O.OpponentName AS hometeam,
	OP.OpponentName AS awayteam,
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
	(LM.LeagueMatchHomeGoals + LM.LeagueMatchAwayGoals) = '$temp_number' AND
	LeagueMatchSeasonID LIKE '$defaultseasonid'
	ORDER BY LM.LeagueMatchDate
	", $connection)
	or die(mysql_error());

	//
	//Print max aggregate scores
	//
	$i = 0;
	while($data = mysql_fetch_array($maxgoals))
	{
		if($i % 0)
			$temp_color = $bg1;
		else
			$temp_color = $bg2;

		echo"
		<tr bgcolor=\"$temp_color\">
		<td align=\"left\" valign=\"middle\">
		$data[date]
		</td>

		<td align=\"center\" valign=\"middle\">
		$data[hometeam]
		</td>

		<td align=\"center\" valign=\"middle\">
		$data[awayteam]
		</td>

		<td align=\"center\" valign=\"middle\">
		$data[homegoals] - $data[awaygoals]
		</td>
		</tr>
		";

		$i++;
	}

	mysql_free_result($maxgoals);

	?>


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
