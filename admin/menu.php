<center>
<a href="seasons.php?sessioid=<?php echo $sessio ?>">Seasons</a> |
<a href="opponents.php?sessioid=<?php echo $sessio ?>">Teams</a> |
<a href="leaguematches.php?sessioid=<?php echo $sessio ?>">League matches</a> |
<a href="password.php?sessioid=<?php echo $sessio ?>">Password</a> |
<a href="preferences.php?sessioid=<?php echo $sessio ?>">Preferences</a> |
<a href="logout.php">Log out</a> |
<a href="../index.php">League table</a>

<hr width="100%">

<?php
if(!session_is_registered('season_name') || !session_is_registered('season_id'))
{
	echo "<form method=\"post\" action=\"seasonselect.php?sessioid=$sessio\">";
	echo '<b>Please choose season: </b>';
	echo '<select name="season_select">';
	$get_seasons = mysql_query("SELECT * FROM tplls_seasonnames ORDER BY SeasonName",$connection)
	or die(mysql_error());

	while($sdata = mysql_fetch_array($get_seasons))
	{
		echo "<option value=\"$sdata[SeasonID]___$sdata[SeasonName]\">$sdata[SeasonName]</option>\n";
	}
	echo '</select> <input type="submit" name="submit" value="go"></form>';


	mysql_free_result($get_seasons);
}
else
{
	$season_name = $_SESSION['season_name'];
	echo "<form method=\"post\" action=\"seasonselect.php?sessioid=$sessio\">";
	echo "<b>Selected season: $season_name</b><br><br>";
	echo 'You may change season by selecting new season from dropdown menu: ';
	echo '<select name="season_select">';

	$get_seasons = mysql_query("SELECT * FROM tplls_seasonnames ORDER BY SeasonName",$connection)
	or die(mysql_error());

	while($sdata = mysql_fetch_array($get_seasons))
	{
		if($sdata['SeasonID'] == $seasonid)
			echo "<option value=\"$sdata[SeasonID]___$sdata[SeasonName]\" SELECTED>$sdata[SeasonName]</option>\n";
		else
			echo "<option value=\"$sdata[SeasonID]___$sdata[SeasonName]\">$sdata[SeasonName]</option>\n";
	}
	echo '</select> <input type="submit" name="submit" value="go"></form>';

	mysql_free_result($get_seasons);
}
?>

<hr width="100%">

</center>