<?php

include("/coastfm/phplib/general.php");

$dsn = 'mysql:dbname=ads;host=database';
$user = 'ads_r';
$passwd = 'all the ads';

try
{
  $dbh = new PDO($dsn, $user, $passwd);
}
catch (PDOException $e)
{
  echo "Connection failed $user $passwd $dsn";
}
$sql = "select value from settings where setting = 'start_time'";
$q = $dbh->query($sql);
$f = $q->fetch();
$core_start_time = $f['value'];
if (!$core_start_time)
{
  echo "Bugger! Missing 'start_time' setting";
}

$sql = "select value from settings where setting = 'end_time'";
$q = $dbh->query($sql);
$f = $q->fetch();
$core_end_time = $f['value'];
if (!$core_end_time)
{
  echo "Bugger! Missing 'end_time' setting";
}

?>
<!DOCTYPE html>
<html>
	<head>
	<title>adamatic - Main page</title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<link rel="shortcut icon" href="/favicon.ico" />

	<script type="text/javascript">
		function swapVisibility(id) {
			$('#' + id).toggle();
		}
	</script>
        <link href="/ads/themes/include/css/style.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<div id="header" class="fixed">
			<div id="logo">
					<img src="/ads/images/coastfm_logo.png" style="float:left; margin-left: -3px; margin-top: -3px;"/>
<h1>ADAMATIC</h1>			</div>
		</div>
<div id="cssmenu" class="bigbox fixed">

<ul>
<?php echo MENU ?>

</ul></li></ul></div>	

<div class="bigbox fixed">
<div id="main_inner" class="fixed">
<h1>Ads played today</h1>

<div class='post' align='center'>
<h2>Ads played in core hours (<?php echo date("d-M-Y")?>)</h2>
<table class='tbl' style='width:90%;'>
  <tr>
    <th style='background-color:#D6D6D6;'>Customer</th>
    <th style='background-color:#D6D6D6;'>Ad</th>
    <th style='background-color:#D6D6D6;'>Type</th>
    <th style='background-color:#D6D6D6;'>Genre</th>
    <th style='background-color:#D6D6D6;'>Times Played</th>
    <th style='background-color:#D6D6D6;'>Plays Allowed</th>
    <th style='background-color:#D6D6D6;'>Good or Bad?</th>
  </tr>

<?php
$sql = 'select a.ad_id, a.name "ad name", at.name "ad type", g.genre, at.plays_allowed, count(*) "ads_played", if(at.plays_allowed > count(*), "Boo hiss:-(", "Yay\!\!:-)") "good_or_bad", c.name "cust_name" ' .
       'from ads a, ad_played ap, ad_type at, genre g, customers c ' .
       "where a.ad_id = ap.ad_id and at.ad_type_id = a.ad_type_id and a.genre_id = g.genre_id " .
       "and ap.played between date(now()) + interval $core_start_time hour and date(now()) + interval $core_end_time hour " .
       "and now() between a.start and a.end and a.cust_id = c.cust_id group by (ap.ad_id)";

$q = $dbh->query($sql);
$line = 1;
while ($row = $q->fetch())
{
  echo '<tr>';

  if (($line / 2) == (intval($line / 2)))
  {
    $line_color = '#D6D6D6';
  } else
  {
    $line_color = '#F0F0F0';
  }

  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["cust_name"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["ad name"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["ad type"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["genre"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["ads_played"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["plays_allowed"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["good_or_bad"];
  echo '</td>';

  echo '<tr>';
  $line = $line + 1;
}
?>
</table>
</div>

<div class='post' align='center'>
<h2>Ads played in non-core hours (<?php echo date("d-M-Y")?>)</h2>

<table class='tbl' style='width:90%;'>
  <tr>
        <th style='background-color:#D6D6D6;'>Customer</th>
        <th style='background-color:#D6D6D6;'>Ad</th>
        <th style='background-color:#D6D6D6;'>Type</th>
        <th style='background-color:#D6D6D6;'>Genre</th>
        <th style='background-color:#D6D6D6;'>Times Played</th>
  </tr>

<?php

$sql = 'select a.ad_id, a.name "ad_name", at.name "at_type_name", g.genre "genre", count(*) "ads_played", c.name "cust name"' .
       "from ads a, ad_played ap, ad_type at, genre g, customers c " .
       "where a.ad_id = ap.ad_id and at.ad_type_id = a.ad_type_id and a.genre_id = g.genre_id and a.cust_id = c.cust_id and date(ap.played) = curdate() " .
       "and now() between a.start and a.end and (ap.played < date(now()) + interval $core_start_time hour or ap.played > date(now()) + interval $core_end_time hour) group by (ap.ad_id)";

$q = $dbh->query($sql);
$line = 1;
while ($row = $q->fetch())
{
  echo '<tr>';

  if (($line / 2) == (intval($line / 2)))
  {
    $line_color = '#D6D6D6';
  } else
  {
    $line_color = '#F0F0F0';
  }

  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["cust name"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["ad_name"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["at_type_name"];
  echo '</td>';  

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["genre"];
  echo '</td>';  

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["ads_played"];
  echo '</td>';

  echo '<tr>';
  $line = $line + 1;
}

?>
</table>
</div>

<div class='post' align='center'>
<h2>Ads played in core hours yesterday (<?php echo date("d-M-Y", time() - 60*60*24)?>)</h2>
<table class='tbl' style='width:90%;'>
  <tr>
    <th style='background-color:#D6D6D6;'>Customer</th>
    <th style='background-color:#D6D6D6;'>Ad</th>
    <th style='background-color:#D6D6D6;'>Type</th>
    <th style='background-color:#D6D6D6;'>Genre</th>
    <th style='background-color:#D6D6D6;'>Times Played</th>
    <th style='background-color:#D6D6D6;'>Plays Allowed</th>
    <th style='background-color:#D6D6D6;'>Good or Bad?</th>
  </tr>

<?php
$sql = 'select a.ad_id, a.name "ad name", at.name "ad type", g.genre, at.plays_allowed, count(*) "ads_played", if(at.plays_allowed > count(*), "Boo hiss:-(", "Yay\!\!:-)") "good_or_bad", c.name "cust_name" ' .
       'from ads a, ad_played ap, ad_type at, genre g, customers c ' .
       "where a.ad_id = ap.ad_id and at.ad_type_id = a.ad_type_id and a.genre_id = g.genre_id " .
       "and ap.played between date(now() - interval 1 day) + interval $core_start_time hour and date(now() - interval 1 day) + interval $core_end_time hour " .
       "and now() between a.start and a.end and a.cust_id = c.cust_id group by (ap.ad_id)";

$q = $dbh->query($sql);
$line = 1;
while ($row = $q->fetch())
{
  echo '<tr>';

  if (($line / 2) == (intval($line / 2)))
  {
    $line_color = '#D6D6D6';
  } else
  {
    $line_color = '#F0F0F0';
  }

  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["cust_name"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["ad name"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["ad type"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["genre"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["ads_played"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["plays_allowed"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["good_or_bad"];
  echo '</td>';

  echo '<tr>';
  $line = $line + 1;
}
?>
</table>
</div>

<div class='post' align='center'>
<h2>Ads played in non-core hours yesterday (<?php echo date("d-M-Y", time() - 60*60*24)?>)</h2>

<table class='tbl' style='width:90%;'>
  <tr>
        <th style='background-color:#D6D6D6;'>Customer</th>
        <th style='background-color:#D6D6D6;'>Ad</th>
        <th style='background-color:#D6D6D6;'>Type</th>
        <th style='background-color:#D6D6D6;'>Genre</th>
        <th style='background-color:#D6D6D6;'>Times Played</th>
  </tr>

<?php

$sql = 'select a.ad_id, a.name "ad_name", at.name "at_type_name", g.genre "genre", count(*) "ads_played", c.name "cust name" ' .
       "from ads a, ad_played ap, ad_type at, genre g, customers c " .
       "where a.ad_id = ap.ad_id and at.ad_type_id = a.ad_type_id and a.genre_id = g.genre_id and a.cust_id = c.cust_id " .
       "and date(ap.played) = curdate() - interval 1 day and (ap.played < date(now() - interval 1 day) + interval $core_start_time hour or ap.played > date(now() - interval 1 day) + interval $core_end_time hour) " .
       "and now() between a.start and a.end group by (ap.ad_id)";

$q = $dbh->query($sql);
$line = 1;
while ($row = $q->fetch())
{
  echo '<tr>';

  if (($line / 2) == (intval($line / 2)))
  {
    $line_color = '#D6D6D6';
  } else
  {
    $line_color = '#F0F0F0';
  }

  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["cust name"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["ad_name"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["at_type_name"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["genre"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["ads_played"];
  echo '</td>';

  echo '<tr>';
  $line = $line + 1;
}

?>
</table>
</div>

</body>
</html>
