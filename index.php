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
//Query to get last updated date and time
//
//Use site http://www.mysql.com/doc/en/Date_and_time_functions.html (DATE_FORMAT)
//to return date format that fits to your site
//
$updated_query = mysql_query("
SELECT DATE_FORMAT(last_updated, '%d.%m.%Y at %H:%i') AS last_updated FROM tplls_preferences WHERE ID = '1'", $connection)
or die(mysql_error());
$ludata = mysql_fetch_array($updated_query);
$last_update = $ludata['last_updated'];
mysql_free_result($updated_query);

//
//If session variables are registered
//
if(!session_is_registered('defaultlanguage') || !session_is_registered('defaultseasonid') || !session_is_registered('defaultshow') || !session_is_registered('defaulttable'))
{
	$_SESSION['defaultlanguage'] = $language;
	$_SESSION['defaultseasonid'] = $d_seasonid;
	$_SESSION['defaultshow'] = $show_all_or_one;
	$_SESSION['defaulttable'] = $show_table;
	$defaultlanguage = $_SESSION['defaultlanguage'];
	$defaultseasonid = $_SESSION['defaultseasonid'];
	$defaultshow = $_SESSION['defaultshow'];
	$defaulttable = $_SESSION['defaulttable'];
}
else
{
	$defaultlanguage = $_SESSION['defaultlanguage'];
	$defaultseasonid = $_SESSION['defaultseasonid'];
	$defaultshow = $_SESSION['defaultshow'];
	$defaulttable = $_SESSION['defaulttable'];
}

//
//Gets seasons and match types for dropdowns
//
$get_seasons = mysql_query("SELECT * FROM tplls_seasonnames WHERE SeasonPublish = '1' ORDER BY SeasonName",$connection)
or die(mysql_error());

//
//Sort by points, sort variable is not set
//
$sort = $_REQUEST['sort'];
if(!isset($sort))
{
	$sort = 'pts';
}


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
<option value="headtohead.php"><?= $lng_headtohead_drop ?></option>
<option value="season.php"><?= $lng_season_statistics_drop ?></option>
</select> <input type="submit" value=">>" name="submit6">

<br>
<?= $lng_match_calendar ?>:
<select name="change_show">
<?php

if($defaultshow == 1)
{
	echo"<option value=\"1\" SELECTED>$lng_show_all</option>
	<option value=\"2\">$lng_show_own_only</option>
	<option value=\"3\">$lng_do_not_show</option>";
}
elseif($defaultshow == 2)
{
	echo"<option value=\"1\">$lng_show_all</option>
	<option value=\"2\" SELECTED>$lng_show_own_only</option>
	<option value=\"3\">$lng_do_not_show</option>";
}
elseif($defaultshow == 3)
{
	echo"<option value=\"1\">$lng_show_all</option>
	<option value=\"2\">$lng_show_own_only</option>
	<option value=\"3\" SELECTED>$lng_do_not_show</option>";
}

//
//If all is chosen from season selector, set default to %
//
if($defaultseasonid == 0)
	$defaultseasonid = '%';

?>
</select>
<input type="submit" value=">>" name="submit2">
&nbsp;&nbsp;&nbsp;
<?= $lng_table ?>:
<select name="change_table">
<?php
if($defaulttable == 1)
{
	echo"<option value=\"4\">$lng_simple</option>
	<option value=\"1\" SELECTED>$lng_traditional</option>
	<option value=\"2\">$lng_mathematical</option>
	<option value=\"3\">$lng_recent_form</option>";
}
elseif($defaulttable == 2)
{
	echo"<option value=\"4\">$lng_simple</option>
	<option value=\"1\">$lng_traditional</option>
	<option value=\"2\" SELECTED>$lng_mathematical</option>
	<option value=\"3\">$lng_recent_form</option>";
}
elseif($defaulttable == 3)
{
	echo"<option value=\"4\">$lng_simple</option>
	<option value=\"1\">$lng_traditional</option>
	<option value=\"2\">$lng_mathematical</option>
	<option value=\"3\" SELECTED>$lng_recent_form</option>";
}
elseif($defaulttable == 4)
{
	echo"
	<option value=\"4\" SELECTED>$lng_simple</option>
	<option value=\"1\">$lng_traditional</option>
	<option value=\"2\">$lng_mathematical</option>
	<option value=\"3\">$lng_recent_form</option>";
}
?>
</select> <input type="submit" value=">>" name="submit3">

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

		<!-- last updated table -->
		<table width="100%" cellspacing="1" cellpadding="2" border="0">
		<tr>
		<td align="left" valign="middle">
		<?= "$lng_last_update: $last_update" ?>
		</td>
		</tr>
		</table>

		<?php
		//
		//Tarkastetaan, mikä taulukko tulostetaan
		//
		if($defaulttable == 1 || $defaulttable == 3)
		{
		?>

		<table width="100%" cellspacing="1" cellpadding="2" border="0">

		<tr>

		<td align="center" valign="middle" colspan="3">
		&nbsp;
		</td>

		<td align="center" valign="middle" colspan="5" bgcolor="<?php echo $top_bg ?>">
		<b><i><?= $lng_overall ?></i></b>
		</td>

		<td align="center" valign="middle" colspan="5" bgcolor="<?php echo $top_bg ?>">
		<b><i><?= $lng_home ?></i></b>
		</td>

		<td align="center" valign="middle" colspan="5" bgcolor="<?php echo $top_bg ?>">
		<b><i><?= $lng_away ?></i></b>
		</td>

		<td align="center" valign="middle" colspan="2">
		&nbsp;
		</td>

		</tr>

		<tr>

		<td align="left" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<b><?= $lng_position_short ?></b>
		</td>

		<td align="left" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<b><?= $lng_team ?></b>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=pld"><?= $lng_played_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=tw"><?= $lng_wins_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=td"><?= $lng_draws_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=tl"><?= $lng_loses_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=tf"><?= $lng_goals_for_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=ta"><?= $lng_goals_against_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=hw"><?= $lng_wins_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=hd"><?= $lng_draws_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=hl"><?= $lng_loses_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=hf"><?= $lng_goals_for_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=ha"><?= $lng_goals_against_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=aw"><?= $lng_wins_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=ad"><?= $lng_draws_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=al"><?= $lng_loses_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=af"><?= $lng_goals_for_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=aa"><?= $lng_goals_against_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=d">+/-</a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=pts"><?= $lng_points_short ?></a>
		</td>
		</tr>
		<?php
		}
		elseif($defaulttable == 2)
		{
		?>
		<table width="100%" cellspacing="1" cellpadding="2" border="0">

		<tr>

		<td align="left" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<b><?= $lng_position_short ?></b>
		</td>

		<td align="left" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<b><?= $lng_team ?></b>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=pld"><?= $lng_played_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=a_pts"><?= $lng_average_points_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=w"><?= $lng_win_percent ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=d"><?= $lng_draw_percent ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=l"><?= $lng_loss_percent ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=af"><?= $lng_average_goals_for_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=aa"><?= $lng_average_goals_against_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=agd"><?= $lng_average_goal_difference_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=pts"><?= $lng_points_short ?></a>
		</td>

		</tr>

		<?php
		}
		elseif($defaulttable == 4)
		{
		?>
		<table width="100%" cellspacing="1" cellpadding="2" border="0">

		<tr>

		<td align="left" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<b><?= $lng_position_short ?></b>
		</td>

		<td align="left" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<b><?= $lng_team ?></b>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=pld"><?= $lng_played_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=tw"><?= $lng_wins_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=td"><?= $lng_draws_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=tl"><?= $lng_loses_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=tf"><?= $lng_goals_for_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=ta"><?= $lng_goals_against_short ?></a>
		</td>

		<td align="center" valign="middle" bgcolor="<?php echo $top_bg ?>">
		<a href="?sort=pts"><?= $lng_points_short ?></a>
		</td>
		</tr>
		<?php
		}
		?>

		<?php

		//
		//Query to get teams from selected season
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

		//
		//Lets read teams into the table
		//
		$i = 0;
		while($data = mysql_fetch_array($get_teams))
		{
			$team[$i] = $data['name'];
			$teamid[$i] = $data['id'];

			//
			//Which table style is chosen
			//
			if($defaulttable == 1 || $defaulttable == 2 || $defaulttable == 4)
			{
				//
				//Home data
				//
				$query = mysql_query("
				SELECT
				COUNT(DISTINCT LM.LeagueMatchID) AS homewins
				FROM
				tplls_leaguematches LM
				WHERE
				LM.LeagueMatchHomeWinnerID = '$teamid[$i]' AND
				LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
				", $connection)
				or die(mysql_error());

				//
				//Home wins into the table
				//
				$mdata = mysql_fetch_array($query);
				$homewins[$i] = $mdata['homewins'];

				mysql_free_result($query);

				$query = mysql_query("
				SELECT
				COUNT(DISTINCT LM.LeagueMatchID) AS homedraws
				FROM
				tplls_leaguematches LM
				WHERE
				LM.LeagueMatchHomeTieID = '$teamid[$i]' AND
				LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
				", $connection)
				or die(mysql_error());

				//
				//Home draws into the table
				//
				$mdata = mysql_fetch_array($query);
				$homedraws[$i] = $mdata['homedraws'];

				mysql_free_result($query);

				$query = mysql_query("
				SELECT
				COUNT(DISTINCT LM.LeagueMatchID) AS homeloses
				FROM
				tplls_leaguematches LM
				WHERE
				LM.LeagueMatchHomeLoserID = '$teamid[$i]' AND
				LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
				", $connection)
				or die(mysql_error());

				//
				//Home loses into the table
				//
				$mdata = mysql_fetch_array($query);
				$homeloses[$i] = $mdata['homeloses'];

				mysql_free_result($query);


				//
				//Away data
				//
				$query = mysql_query("
				SELECT
				COUNT(DISTINCT LM.LeagueMatchID) AS awaywins
				FROM
				tplls_leaguematches LM
				WHERE
				LM.LeagueMatchAwayWinnerID = '$teamid[$i]' AND
				LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
				", $connection)
				or die(mysql_error());

				//
				//Away wins into the table
				//
				$mdata = mysql_fetch_array($query);
				$awaywins[$i] = $mdata['awaywins'];

				mysql_free_result($query);

				$query = mysql_query("
				SELECT
				COUNT(DISTINCT LM.LeagueMatchID) AS awaydraws
				FROM
				tplls_leaguematches LM
				WHERE
				LM.LeagueMatchAwayTieID = '$teamid[$i]' AND
				LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
				", $connection)
				or die(mysql_error());

				//
				//Away draws into the table
				//
				$mdata = mysql_fetch_array($query);
				$awaydraws[$i] = $mdata['awaydraws'];

				mysql_free_result($query);

				$query = mysql_query("
				SELECT
				COUNT(DISTINCT LM.LeagueMatchID) AS awayloses
				FROM
				tplls_leaguematches LM
				WHERE
				LM.LeagueMatchAwayLoserID = '$teamid[$i]' AND
				LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
				", $connection)
				or die(mysql_error());

				//
				//Away loses into the table
				//
				$mdata = mysql_fetch_array($query);
				$awayloses[$i] = $mdata['awayloses'];

				mysql_free_result($query);

				//
				//Query to get goals
				//

				$query = mysql_query("
				SELECT
				SUM( LM.LeagueMatchHomeGoals) AS homegoals
				FROM
				tplls_leaguematches LM
				WHERE
				LM.LeagueMatchHomeID = '$teamid[$i]' AND
				LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
				", $connection)
				or die(mysql_error());

				//
				//Goals scored in hom
				//
				$mdata = mysql_fetch_array($query);
				if(is_null($mdata['homegoals']))
					$homegoals[$i] = 0;
				else
					$homegoals[$i] = $mdata['homegoals'];

				mysql_free_result($query);

				$query = mysql_query("
				SELECT
				SUM( LM.LeagueMatchAwayGoals) AS homegoalsagainst
				FROM
				tplls_leaguematches LM
				WHERE
				LM.LeagueMatchHomeID = '$teamid[$i]' AND
				LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
				", $connection)
				or die(mysql_error());

				//
				//Goals against in home
				//
				$mdata = mysql_fetch_array($query);
				if(is_null($mdata['homegoalsagainst']))
					$homegoalsagainst[$i] = 0;
				else
					$homegoalsagainst[$i] = $mdata['homegoalsagainst'];

				mysql_free_result($query);


				$query = mysql_query("
				SELECT
				SUM( LM.LeagueMatchAwayGoals) AS awaygoals
				FROM
				tplls_leaguematches LM
				WHERE
				LM.LeagueMatchAwayID = '$teamid[$i]' AND
				LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
				", $connection)
				or die(mysql_error());

				//
				//Goals scored in away
				//
				$mdata = mysql_fetch_array($query);
				if(is_null($mdata['awaygoals']))
					$awaygoals[$i] = 0;
				else
					$awaygoals[$i] = $mdata['awaygoals'];

				mysql_free_result($query);

				$query = mysql_query("
				SELECT
				SUM( LM.LeagueMatchHomeGoals) AS awaygoalsagainst
				FROM
				tplls_leaguematches LM
				WHERE
				LM.LeagueMatchAwayID = '$teamid[$i]' AND
				LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
				", $connection)
				or die(mysql_error());

				//
				//Goals against in away
				//
				$mdata = mysql_fetch_array($query);
				if(is_null($mdata['awaygoalsagainst']))
					$awaygoalsagainst[$i] = 0;
				else
					$awaygoalsagainst[$i] = $mdata['awaygoalsagainst'];

				mysql_free_result($query);
			}
			//
			//Recent form
			//
			elseif($defaulttable == 3)
			{
				//
				//Counter are set to zero
				//
				$homewins[$i] = 0;
				$homedraws[$i] = 0;
				$homeloses[$i] = 0;
				$awaywins[$i] = 0;
				$awaydraws[$i] = 0;
				$awayloses[$i] = 0;
				$homegoals[$i] = 0;
				$homegoalsagainst[$i] = 0;
				$awaygoals[$i] = 0;
				$awaygoalsagainst[$i] = 0;

				//
				//Query to get latest 6 matches
				//
				$query = mysql_query("
				SELECT
				LM.LeagueMatchHomeID AS homeid,
				LM.LeagueMatchAwayID AS awayid,
				LM.LeagueMatchHomewinnerID AS homewinner,
				LM.LeagueMatchHomeLoserID AS homeloser,
				LM.LeagueMatchAwayWinnerID AS awaywinner,
				LM.LeagueMatchAwayLoserID AS awayloser,
				LM.LeagueMatchHomeTieID AS hometie,
				LM.LeagueMatchAwayTieID AS awaytie,
				LM.LeagueMatchHomeGoals AS homegoals,
				LM.LeagueMatchAwayGoals AS awaygoals
				FROM
				tplls_leaguematches LM
				WHERE
				(LM.LeagueMatchHomeWinnerID = '$teamid[$i]' OR
				LM.LeagueMatchHomeLoserID = '$teamid[$i]' OR
				LM.LeagueMatchAwayWinnerID = '$teamid[$i]' OR
				LM.LeagueMatchAwayLoserID = '$teamid[$i]' OR
				LM.LeagueMatchHomeTieID = '$teamid[$i]' OR
				LM.LeagueMatchAwayTieID = '$teamid[$i]') AND
				LM.LeagueMatchSeasonID LIKE '$defaultseasonid'
				ORDER BY LM.LeagueMatchDate DESC
				LIMIT 6
				", $connection)
				or die(mysql_error());

				//
				//Lets use while to get correct numbers
				//
				while($row = mysql_fetch_array($query))
				{
					//
					//If goals are null
					//
					if(is_null($row['homegoals']))
						$row['homegoals'] = 0;

					if(is_null($row['awaygoals']))
						$row['awaygoals'] = 0;


					//
					//Home win
					//
					if($row['homewinner'] == $teamid[$i])
					{
						$homewins[$i]++;
					}
					//
					//Home lost
					//
					elseif($row['homeloser'] == $teamid[$i])
					{
						$homeloses[$i]++;
					}
					//
					//Home draw
					//
					elseif($row['hometie'] == $teamid[$i])
					{
						$homedraws[$i]++;
					}
					//
					//Away win
					//
					elseif($row['awaywinner'] == $teamid[$i])
					{
						$awaywins[$i]++;
					}
					//
					//Away lost
					//
					elseif($row['awayloser'] == $teamid[$i])
					{
						$awayloses[$i]++;
					}
					//
					//Away draw
					//
					elseif($row['awaytie'] == $teamid[$i])
					{
						$awaydraws[$i]++;
					}


					//
					//Calculates goals and goals against
					//
					if($row['homeid'] == $teamid[$i])
					{
						$homegoals[$i] = $homegoals[$i] + $row['homegoals'];
						$homegoalsagainst[$i] = $homegoalsagainst[$i] + $row['awaygoals'];
					}
					else
					{
						$awaygoals[$i] = $awaygoals[$i] + $row['awaygoals'];
						$awaygoalsagainst[$i] = $awaygoalsagainst[$i] + $row['homegoals'];
					}



				}

				mysql_free_result($query);

			}

			//
			//Check what table is used..
			//
			if($defaulttable == 1 || $defaulttable == 3 || $defaulttable == 4)
			{

				//
				//Calculates points and matches
				//

				$wins[$i] = ($homewins[$i]+$awaywins[$i]);
				$draws[$i] = ($homedraws[$i]+$awaydraws[$i]);
				$loses[$i] = ($homeloses[$i]+$awayloses[$i]);
				$goals_for[$i] = ($homegoals[$i] + $awaygoals[$i]);
				$goals_against[$i] = ($homegoalsagainst[$i] + $awaygoalsagainst[$i]);

				//
				//Lets make change in points if there are data in tplls_deductedpoints-table
				//
				if($defaulttable == 1 || $defaulttable == 4)
				{
					$get_deduct = mysql_query("
					SELECT points
					FROM tplls_deductedpoints
					WHERE seasonid LIKE '$defaultseasonid' AND
					teamid = '$teamid[$i]'
					LIMIT 1
					", $connection)
					or die(mysql_error());

					$temp_points = 0;

					if(mysql_num_rows($get_deduct) > 0)
					{
						while($d_points = mysql_fetch_array($get_deduct))
						{
							$temp_points = $temp_points + $d_points['points'];
						}
					}

					mysql_free_result($get_deduct);
				}
				else
				{
					$temp_points = 0;
				}

				$points[$i] = $temp_points + (($homewins[$i]+$awaywins[$i])*$for_win) + (($homedraws[$i]+$awaydraws[$i])*$for_draw) + (($homeloses[$i]+$awayloses[$i])*$for_lose);
				$pld[$i] = $homewins[$i]+$homedraws[$i]+$homeloses[$i]+$awaywins[$i]+$awaydraws[$i]+$awayloses[$i];

				//
				//Calculates goal difference
				//
				$diff[$i] = ($homegoals[$i] + $awaygoals[$i]) - ($homegoalsagainst[$i] + $awaygoalsagainst[$i]);

			}
			elseif($defaulttable == 2)
			{
				$wins[$i] = ($homewins[$i]+$awaywins[$i]);
				$draws[$i] = ($homedraws[$i]+$awaydraws[$i]);
				$loses[$i] = ($homeloses[$i]+$awayloses[$i]);
				$goals_for[$i] = ($homegoals[$i] + $awaygoals[$i]);
				$goals_against[$i] = ($homegoalsagainst[$i] + $awaygoalsagainst[$i]);

				//
				//Lets make change in points if there are data in tplls_deductedpoints-table
				//
				$get_deduct = mysql_query("
				SELECT points
				FROM tplls_deductedpoints
				WHERE seasonid LIKE '$defaultseasonid' AND
				teamid = '$teamid[$i]'
				LIMIT 1
				", $connection)
				or die(mysql_error());

				$temp_points = 0;

				if(mysql_num_rows($get_deduct) > 0)
				{
					while($d_points = mysql_fetch_array($get_deduct))
					{
						$temp_points = $temp_points + $d_points['points'];
					}
				}

				mysql_free_result($get_deduct);

				$points[$i] = $temp_points + (($homewins[$i]+$awaywins[$i])*$for_win) + (($homedraws[$i]+$awaydraws[$i])*$for_draw) + (($homeloses[$i]+$awayloses[$i])*$for_lose);
				$pld[$i] = $homewins[$i]+$homedraws[$i]+$homeloses[$i]+$awaywins[$i]+$awaydraws[$i]+$awayloses[$i];

				//
				//To avoid divide by zero
				//
				if($pld[$i] != 0)
				{
					$win_pros[$i] = round(100*($wins[$i]/$pld[$i]), 2);
					$draw_pros[$i] = round(100*($draws[$i]/$pld[$i]), 2);
					$loss_pros[$i] = round(100*($loses[$i]/$pld[$i]), 2);

					$av_points[$i] = round($points[$i]/$pld[$i], 2);

					$av_for[$i] = round($goals_for[$i]/$pld[$i], 2);
					$av_against[$i] = round($goals_against[$i]/$pld[$i], 2);
				}
				else
				{
					$win_pros[$i] = 0;
					$draw_pros[$i] = 0;
					$loss_pros[$i] = 0;

					$av_points[$i] = 0;

					$av_for[$i] = 0;
					$av_against[$i] = 0;
				}

				$av_diff[$i] = $av_for[$i] - $av_against[$i];

			}

			$i++;
		}

		$qty = mysql_num_rows($get_teams);

		mysql_free_result($get_teams);


		//
		//Which table?
		//
		if($defaulttable == 1 || $defaulttable == 3 || $defaulttable == 4)
		{


			//
			//What sort type is chosen?
			//
			switch($sort)
			{
				case 'pts':
				array_multisort($points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $homeloses, $awaywins, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'd':
				array_multisort($diff, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $homeloses, $awaywins, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'pld':
				array_multisort($pld, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $team, $homewins, $homedraws, $homeloses, $awaywins, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'tw':
				array_multisort($wins, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC,  $goals_for, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses,$pld, SORT_DESC, SORT_NUMERIC, $team, $homedraws, $homeloses,$homewins, $awaywins, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'td':
				array_multisort($draws, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homeloses, $awaywins, $homedraws, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'tl':
				array_multisort($loses, SORT_ASC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $awaywins, $awaydraws, $homeloses, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'tf':
				array_multisort($goals_for, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $homeloses, $awaywins, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'ta':
				array_multisort($goals_against, SORT_ASC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $homeloses, $awaywins, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'hw':
				array_multisort($homewins, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC,  $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses,$pld, SORT_DESC, SORT_NUMERIC, $team, $homedraws, $homeloses, $awaywins, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'hd':
				array_multisort($homedraws, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homeloses, $awaywins, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'hl':
				array_multisort($homeloses, SORT_ASC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $awaywins, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'hf':
				array_multisort($homegoals, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $homeloses, $awaywins, $awaydraws, $awayloses, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'ha':
				array_multisort($homegoalsagainst, SORT_ASC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $homeloses, $awaywins, $awaydraws, $awayloses, $homegoals, $awaygoals, $awaygoalsagainst);
				break;

				case 'aw':
				array_multisort($awaywins, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $homeloses, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'ad':
				array_multisort($awaydraws, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $homeloses, $awaywins, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'al':
				array_multisort($awayloses, SORT_ASC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $homeloses, $awaywins, $awaydraws, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;

				case 'af':
				array_multisort($awaygoals, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $homeloses, $awaywins, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoalsagainst);
				break;

				case 'aa':
				array_multisort($awaygoalsagainst, SORT_ASC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $homeloses, $awaywins, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals);
				break;

				default:
				array_multisort($points, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, SORT_NUMERIC, $goals_for, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $goals_against, SORT_ASC, SORT_NUMERIC, $draws, $loses, $pld, SORT_DESC, SORT_NUMERIC, $team, $homewins, $homedraws, $homeloses, $awaywins, $awaydraws, $awayloses, $homegoals, $homegoalsagainst, $awaygoals, $awaygoalsagainst);
				break;
			}

			if($defaulttable == 1 || $defaulttable == 3)
			{

				//
				//Lets print data
				//
				$j=1;
				$i=0;
				while($i< $qty)
				{
					if(isset($draw_line))
					{
						//
						//Tarkistetaan, piirretäänkö erotusviiva
						//
						for($k = 0 ; $k < sizeof($draw_line) ; $k++)
						{
							if($draw_line[$k] == $i)
							{
								$templine_width = $tb_width-20;
								echo"
								<tr>
								<td height=\"5\" colspan=\"20\" align=\"center\" valign=\"middle\">
								<img src=\"images/line.gif\" width=\"$templine_width\" height=\"5\" ALT=\"\"><br>
								</td>
								</tr>
								";
							}
						}
					}


					echo"
					<tr>

					<td align=\"left\" valign=\"middle\" bgcolor=\"$bg1\">
					<b>$j.</b>
					</td>

					<td align=\"left\" valign=\"middle\" bgcolor=\"$bg1\">
					<b>$team[$i]</b>
					</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg2\">";

					if($sort == 'pld')
						echo'<b>';

					echo"$pld[$i]";

					if($sort == 'pld')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'tw')
						echo'<b>';

					echo"$wins[$i]";

					if($sort == 'tw')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'td')
						echo'<b>';

					echo"$draws[$i]";

					if($sort == 'td')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'tl')
						echo'<b>';

					echo"$loses[$i]";

					if($sort == 'tl')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'tf')
						echo'<b>';

					echo"$goals_for[$i]";

					if($sort == 'tf')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'ta')
						echo'<b>';

					echo"$goals_against[$i]";

					if($sort == 'ta')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'hw')
						echo'<b>';

					echo"$homewins[$i]";

					if($sort == 'hw')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'hd')
						echo'<b>';

					echo"$homedraws[$i]";

					if($sort == 'hd')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'hl')
						echo'<b>';

					echo"$homeloses[$i]";

					if($sort == 'hl')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'hf')
						echo'<b>';

					echo"$homegoals[$i]";

					if($sort == 'hf')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'ha')
						echo'<b>';

					echo"$homegoalsagainst[$i]";

					if($sort == 'ha')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'aw')
						echo'<b>';

					echo"$awaywins[$i]";

					if($sort == 'aw')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'ad')
						echo'<b>';

					echo"$awaydraws[$i]";

					if($sort == 'ad')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'al')
						echo'<b>';

					echo"$awayloses[$i]";

					if($sort == 'al')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'af')
						echo'<b>';

					echo"$awaygoals[$i]";

					if($sort == 'af')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'aa')
						echo'<b>';

					echo"$awaygoalsagainst[$i]";

					if($sort == 'aa')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";
					if($sort == 'd')
						echo'<b>';

					if($diff[$i] > 0)
						echo'+';

					echo"$diff[$i]";

					if($sort == 'd')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg2\">";
					if($sort == 'pts')
						echo'<b>';

					echo"$points[$i]";

					if($sort == 'pts')
						echo'</b>';
					echo"</td>
					</tr>
					";

					$i++;
					$j++;
				}
			}
			//
			//Simple table print
			//
			elseif($defaulttable == 4)
			{
				//
				//Lets print data
				//
				$j=1;
				$i=0;
				while($i< $qty)
				{
					if(isset($draw_line))
					{
						//
						//Tarkistetaan, piirretäänkö erotusviiva
						//
						for($k = 0 ; $k < sizeof($draw_line) ; $k++)
						{
							if($draw_line[$k] == $i)
							{
								$templine_width = $tb_width-20;
								echo"
								<tr>
								<td height=\"5\" colspan=\"20\" align=\"center\" valign=\"middle\">
								<img src=\"images/line.gif\" width=\"$templine_width\" height=\"5\" ALT=\"\"><br>
								</td>
								</tr>
								";
							}
						}
					}


					echo"
					<tr>

					<td align=\"left\" valign=\"middle\" bgcolor=\"$bg1\">
					<b>$j.</b>
					</td>

					<td align=\"left\" valign=\"middle\" bgcolor=\"$bg1\">
					<b>$team[$i]</b>
					</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg2\">";

					if($sort == 'pld')
						echo'<b>';

					echo"$pld[$i]";

					if($sort == 'pld')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'tw')
						echo'<b>';

					echo"$wins[$i]";

					if($sort == 'tw')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'td')
						echo'<b>';

					echo"$draws[$i]";

					if($sort == 'td')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'tl')
						echo'<b>';

					echo"$loses[$i]";

					if($sort == 'tl')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'tf')
						echo'<b>';

					echo"$goals_for[$i]";

					if($sort == 'tf')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

					if($sort == 'ta')
						echo'<b>';

					echo"$goals_against[$i]";

					if($sort == 'ta')
						echo'</b>';

					echo"</td>

					<td align=\"center\" valign=\"middle\" bgcolor=\"$bg2\">";
					if($sort == 'pts')
						echo'<b>';

					echo"$points[$i]";

					if($sort == 'pts')
						echo'</b>';
					echo"</td>
					</tr>
					";

					$i++;
					$j++;
				}
			}
		}
		elseif($defaulttable == 2)
		{

			//
			//What sort type is chosen?
			//
			switch($sort)
			{
				case 'pts':
				array_multisort($points, SORT_DESC, SORT_NUMERIC, $av_diff, SORT_DESC, SORT_NUMERIC, $av_for, SORT_DESC, SORT_NUMERIC, $av_against, SORT_ASC, SORT_NUMERIC, $pld, $av_points, $win_pros, $draw_pros, $loss_pros, $team);
				break;

				case 'a_pts':
				array_multisort($av_points, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $av_diff, SORT_DESC, SORT_NUMERIC, $av_for, SORT_DESC, SORT_NUMERIC, $av_against, SORT_ASC, SORT_NUMERIC, $pld, $win_pros, $draw_pros, $loss_pros, $team);
				break;

				case 'w':
				array_multisort($win_pros, SORT_DESC, SORT_NUMERIC, $av_points, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $av_diff, SORT_DESC, SORT_NUMERIC, $av_for, SORT_DESC, SORT_NUMERIC, $av_against, SORT_ASC, SORT_NUMERIC, $pld, $draw_pros, $loss_pros, $team);
				break;

				case 'd':
				array_multisort($draw_pros, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $av_diff, SORT_DESC, SORT_NUMERIC, $av_for, SORT_DESC, SORT_NUMERIC, $av_against, SORT_ASC, SORT_NUMERIC, $pld, $av_points, $win_pros, $loss_pros, $team);
				break;

				case 'l':
				array_multisort($loss_pros, SORT_ASC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $av_diff, SORT_DESC, SORT_NUMERIC, $av_for, SORT_DESC, SORT_NUMERIC, $av_against, SORT_ASC, SORT_NUMERIC, $pld, $av_points, $win_pros, $draw_pros, $team);
				break;

				case 'af':
				array_multisort($av_for, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $av_diff, SORT_DESC, SORT_NUMERIC, $av_against, SORT_ASC, SORT_NUMERIC, $pld, $av_points, $win_pros, $draw_pros, $loss_pros, $team);
				break;

				case 'aa':
				array_multisort($av_against, SORT_ASC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $av_diff, SORT_DESC, SORT_NUMERIC, $av_for, SORT_DESC, SORT_NUMERIC, $pld, $av_points, $win_pros, $draw_pros, $loss_pros, $team);
				break;

				case 'agd':
				array_multisort($av_diff, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $av_for, SORT_DESC, SORT_NUMERIC, $av_against, SORT_ASC, SORT_NUMERIC, $pld, $av_points, $win_pros, $draw_pros, $loss_pros, $team);
				break;

				case 'pld':
				array_multisort($pld, SORT_DESC, SORT_NUMERIC, $points, SORT_DESC, SORT_NUMERIC, $av_diff, SORT_DESC, SORT_NUMERIC, $av_for, SORT_DESC, SORT_NUMERIC, $av_against, SORT_ASC, SORT_NUMERIC, $av_points, $win_pros, $draw_pros, $loss_pros, $team);
				break;

			}

			//
			//Print data
			//
			$j=1;
			$i=0;
			while($i< $qty)
			{
				//
				//Tehdään numberformatointi
				//
				$av_points[$i] = number_format($av_points[$i], 2, '.', '');
				$av_for[$i] = number_format($av_for[$i], 2, '.', '');
				$av_against[$i] = number_format($av_against[$i], 2, '.', '');
				$av_temp = number_format($av_diff[$i], 2, '.', '');
				$win_pros[$i] = number_format($win_pros[$i], 2, '.', '');
				$draw_pros[$i] = number_format($draw_pros[$i], 2, '.', '');
				$loss_pros[$i] = number_format($loss_pros[$i], 2, '.', '');

				echo"
				<tr>

				<td align=\"left\" valign=\"middle\" bgcolor=\"$bg1\">
				<b>$j.</b>
				</td>

				<td align=\"left\" valign=\"middle\" bgcolor=\"$bg1\">
				<b>$team[$i]</b>
				</td>

				<td align=\"center\" valign=\"middle\" bgcolor=\"$bg2\">";

				if($sort == 'pld')
					echo'<b>';

				echo"$pld[$i]";

				if($sort == 'pld')
					echo'</b>';

				echo"</td>

				<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

				if($sort == 'a_pts')
					echo'<b>';

				echo"$av_points[$i]";

				if($sort == 'a_pts')
					echo'</b>';

				echo"</td>

				<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

				if($sort == 'w')
					echo'<b>';

				echo"$win_pros[$i]";

				if($sort == 'w')
					echo'</b>';

				echo"</td>

				<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

				if($sort == 'd')
					echo'<b>';

				echo"$draw_pros[$i]";

				if($sort == 'd')
					echo'</b>';

				echo"</td>

				<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

				if($sort == 'l')
					echo'<b>';

				echo"$loss_pros[$i]";

				if($sort == 'l')
					echo'</b>';

				echo"</td>

				<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

				if($sort == 'af')
					echo'<b>';

				echo"$av_for[$i]";

				if($sort == 'af')
					echo'</b>';

				echo"</td>

				<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

				if($sort == 'aa')
					echo'<b>';

				echo"$av_against[$i]";

				if($sort == 'aa')
					echo'</b>';

				echo"</td>

				<td align=\"center\" valign=\"middle\" bgcolor=\"$bg1\">";

				if($sort == 'agd')
					echo'<b>';

				if($av_diff[$i] >= 0)
					echo'+';

				echo"$av_temp";

				if($sort == 'agd')
					echo'</b>';

				echo"</td>

				<td align=\"center\" valign=\"middle\" bgcolor=\"$bg2\">";

				if($sort == 'pts')
					echo'<b>';

				echo"$points[$i]";

				if($sort == 'pts')
					echo'</b>';

				echo"</td>
				</tr>
				";

				$i++;
				$j++;
			}

		}

		?>

		</table>


		<?php
		//
		//Check if match calendar want to be shown
		//
		if($defaultshow != 3)
		{
		?>


		<!-- Sitten ottelukalenteri -->
		<br><br>
		<table align="left" width="60%" cellspacing="2" cellpadding="2" border="0">
		<tr>
		<td align="left" valign="top" colspan="2">
		<h1><?= $lng_match_calendar ?></h1>
		</td>
		</tr>

		<?php

		//
		//How to print date
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
		//Check which matche want to be printed
		//
		//All
		//
		if($defaultshow == 1)
		{
			$get_matches = mysql_query("
			SELECT O.OpponentName AS hometeam,
			OP.OpponentName AS awayteam,
			LM.LeagueMatchHomeGoals AS goals_home,
			LM.LeagueMatchAwayGoals AS goals_away,
			LM.LeagueMatchID AS id,
			DATE_FORMAT(LM.LeagueMatchDate, '$print_date') AS date
			FROM tplls_leaguematches LM, tplls_opponents O, tplls_opponents OP
			WHERE O.OpponentID = LM.LeagueMatchHomeID AND
			OP.OpponentID = LM.LeagueMatchAwayID AND
			LeagueMatchSeasonID LIKE '$defaultseasonid'
			ORDER BY LM.LeagueMatchDate",$connection)
			or die(mysql_error());
		}
		//
		//Own only
		//
		else
		{
			$get_matches = mysql_query("
			SELECT O.OpponentName AS hometeam,
			OP.OpponentName AS awayteam,
			LM.LeagueMatchHomeGoals AS goals_home,
			LM.LeagueMatchAwayGoals AS goals_away,
			LM.LeagueMatchID AS id,
			DATE_FORMAT(LM.LeagueMatchDate, '$print_date') AS date
			FROM tplls_leaguematches LM, tplls_opponents O, tplls_opponents OP
			WHERE O.OpponentID = LM.LeagueMatchHomeID AND
			OP.OpponentID = LM.LeagueMatchAwayID AND
			LeagueMatchSeasonID LIKE '$defaultseasonid' AND
			(O.OpponentOwn = '1' OR OP.OpponentOwn = '1')
			ORDER BY LM.LeagueMatchDate",$connection)
			or die(mysql_error());
		}

		if(mysql_num_rows($get_matches) < 1)
		{
			echo "<b>No matches yet.</b>";
		}
		else
		{

			$i = 0;
			$temp = '';

			while($data = mysql_fetch_array($get_matches))
			{
				if($i == 0)
				{
					echo"
					<tr>
					<td align=\"left\" colspan=\"2\">
					<b>$data[date]</b>
					</td>
					</tr>
					";
				}

				if($data['date'] != "$temp" && $i > 0)
				{
					echo"
					<tr>
					<td align=\"left\" colspan=\"2\">
					<br>
					<b>$data[date]</b>
					</td>
					</tr>
					";
				}

				echo "
				<tr>
				<td align=\"left\" valign=\"top\" width=\"90%\">
				$data[hometeam] - $data[awayteam]
				</td>
				<td align=\"left\" valign=\"top\" width=\"10%\">";

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

		<?php
		}
		?>

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