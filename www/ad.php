<?php
/**
Maintain the ads table
*/

include("/coastfm/phplib/general.php");

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//return error message if values passed are not valid
//---------------------------------------------------------------------------
function chk_values($length_s, $path, $start, $end, $multiplier)
{

$error_msg = '';

if (empty($length_s))
{
  $error_msg = $error_msg . "<font class='base'>Invalid Length.&nbsp;</font><br>";
}

if (!file_exists($path))
{
  $error_msg = $error_msg . "<font class='base'>File does not exist.&nbsp;</font><br>";
}

if (empty($start))
{
  $error_msg = $error_msg . "<font class='base'>Invalid Start Date.&nbsp;</font><br>";
}
if (empty($end))
{
  $error_msg = $error_msg . "<font class='base'>Invalid End Date.&nbsp;</font><br>";
}
if ($multiplier == '')
{
  $error_msg = $error_msg . "<font class='base'>Invalid Multiplier.&nbsp;</font><br>";
}

if (!empty($error_msg))
{
  return "<div class='post'><h2>Error messages</h2>" . $error_msg . "</div>";
}

}
//---------------------------------------------------------------------------

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//Use the id passed to build the ad type drop down
//---------------------------------------------------------------------------
function ad_type_selector($dbh, $ad_type_id)
{

if ($ad_type_id == '')
{
  $ad_type_id = 0;
}

$sql = "select name, ad_type_id, if(ad_type_id = " . $ad_type_id . ", 'selected ', '') 'selected' from ad_type order by name";

$html = "<select name='ad_type_id'>";

if ($ad_type_id == 0)
{
  $html = $html . "<option selected value=''>Choose</option>";
}

$q = $dbh->query($sql);
while ($f = $q->fetch())
{
  $html = $html . "<option " . $f['selected'] . "value='" . $f['ad_type_id'] . "'>" . $f['name'] . "</option>";
}

$html = $html . "</select>";

return $html;
}
//---------------------------------------------------------------------------

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//Use the id passed to build the genre drop down
//---------------------------------------------------------------------------
function genre_selector($dbh, $genre_id)
{

if ($genre_id == '')
{
  $genre_id = 0;
}

$sql = "select genre_id, genre, if(genre_id = " . $genre_id. ", 'selected ', '') 'selected' from genre order by genre";

$html = "<select name='genre_id'>";

if ($genre_id == 0)
{
  $html = $html . "<option selected value=''>Choose</option>";
}

$q = $dbh->query($sql);
while ($f = $q->fetch())
{
  $html = $html . "<option " . $f['selected'] . "value='" . $f['genre_id'] . "'>" . $f['genre'] . "</option>";
}

$html = $html . "</select>";

return $html;
}
//---------------------------------------------------------------------------

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//Use the id passed to build the customer drop down
//---------------------------------------------------------------------------
function customer_selector($dbh, $cust_id)
{

if ($cust_id == '')
{
  $cust_id = 0;
}

$sql = "select cust_id, name, if(cust_id = " . $cust_id . ", 'selected ', '') 'selected' from customers order by name";

$html = "<select name='cust_id'>";

if ($cust_id == 0)
{
  $html = $html . "<option selected value=''>Choose</option>";
}

$q = $dbh->query($sql);
while ($f = $q->fetch())
{
  $html = $html . "<option " . $f['selected'] . "value='" . $f['cust_id'] . "'>" . $f['name'] . "</option>";
}

$html = $html . "</select>";

return $html;
}
//---------------------------------------------------------------------------

logit(0, "starting");
$dsn = 'mysql:dbname=ads;host=database';
$user = 'ads_o';
$passwd = 'ads wonderful ads';
$error_msg = '';
$ad_id = '';

/*
echo "<pre>";
var_dump($_POST);
echo "</pre>";
*/

try
{
  $dbh = new PDO($dsn, $user, $passwd);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
  echo "Connection failed $user $passwd $dsn";
}
$sql = "select value from settings where setting = 'start_time'";
try
{
  $q = $dbh->query($sql);
  $f = $q->fetch();
} catch (PDOException $e)
{
  $error_msg = "We are stuffed";
}

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

if (empty($_POST['ACTION']))
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Just display what we have already, no action to be taken
---------------------------------------------------------------------------*/

  $cust_name = '';
  $ad_name = '';
  $start = '';
  $end = '';
  $length_s = '';
  $path = '';
  $multiplier = '';
  $cust_id = '';
  $ad_type_selector = ad_type_selector($dbh, 0);
  $genre_selector = genre_selector($dbh, 0);
  $customer_selector = customer_selector($dbh, 0);

  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';
} elseif ($_POST['ACTION'] == 'Edit')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Read row and display ready for updating
---------------------------------------------------------------------------
*/
  $ad_id = $_POST['ad_id'];
  $sql = "select c.name 'cust_name', a.name 'ad_name', a.length_s, a.path, a.start, a.end, a.genre_id, a.multiplier, a.ad_type_id, a.cust_id " .
         "from ads a, customers c where c.cust_id = a.cust_id and a.ad_id = " . $ad_id;

  $q = $dbh->query($sql);
  $f = $q->fetch();

  $cust_name = $f["cust_name"];
  $ad_name = $f["ad_name"];
  $length_s = $f["length_s"];
  $path = $f["path"];
  $start = $f["start"];
  $end = $f["end"];

  $cust_id = $f["cust_id"];
  $customer_selector = customer_selector($dbh, $cust_id);

  $genre_id = $f["genre_id"];
  $genre_selector = genre_selector($dbh, $genre_id);

  $multiplier = $f["multiplier"];

  $ad_type_id = $f["ad_type_id"];
  $ad_type_selector = ad_type_selector($dbh, $ad_type_id);

  $cust_id = $f["cust_id"];
  $customer_selector = customer_selector($dbh, $cust_id);

  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Update" /><input type="submit" name="SUBMIT" value="Update" /></td>';
} elseif ($_POST['ACTION'] == 'Update')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Updating table with new values.
Check that they are valid or at least present
---------------------------------------------------------------------------
*/
  $ad_id = $_POST["ad_id"];
  $cust_id = $_POST["cust_id"];
  $ad_name = $_POST["ad_name"];
  $length_s = $_POST["length_s"];
  $path = $_POST["path"];
  $start = $_POST["start"];
  $end = $_POST["end"];
  $genre_id = $_POST["genre_id"];
  $multiplier  = $_POST["multiplier"];
  $ad_type_id = $_POST["ad_type_id"];

  $customer_selector = customer_selector($dbh, $cust_id);
  $ad_type_selector = ad_type_selector($dbh, $ad_type_id);
  $genre_selector = genre_selector($dbh, genre_id);

  $sql = sprintf('update ads set name = "%s", length_s = %s, path = "%s", start = "%s", end = "%s", genre_id = %s, multiplier = %s, ad_type_id = %s, cust_id = %s where ad_id = %s', 
                 $ad_name, $length_s, $path, $start, $end, $genre_id, $multiplier, $ad_type_id, $cust_id, $ad_id);
  $error_msg = chk_values($length_s, $path, $start, $end, $multiplier);

  if (empty($error_msg))
  {
    try
    {
      $q = $dbh->prepare($sql);
      $q->execute();

      $ad_id = '';
      $cust_id = '';
      $ad_name = '';
      $length_s = '';
      $path = '';
      $start = '';
      $end = '';
      $genre_id = '';
      $multiplier  = '';
      $ad_type_id = '';

      $customer_selector = customer_selector($dbh, 0);
      $ad_type_selector = ad_type_selector($dbh, 0);
      $genre_selector = genre_selector($dbh, 0);

      $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';
    } catch (PDOException $e)
    {
      $error_msg = "Update failed, check values!";
      $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Update" /><input type="submit" name="SUBMIT" value="Update" /></td>';
    }
  } else
  {
    $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Update" /><input type="submit" name="SUBMIT" value="Update" /></td>';
  }


} elseif ($_POST['ACTION'] == 'Add')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Add row to table
Check that they are valid or at least present
---------------------------------------------------------------------------
*/
  $cust_id = $_POST["cust_id"];
  $ad_name = $_POST["ad_name"];
  $length_s = $_POST["length_s"];
  $path = $_POST["path"];
  $start = $_POST["start"];
  $end = $_POST["end"];
  $genre_id = $_POST["genre_id"];
  $multiplier  = $_POST["multiplier"];
  $ad_type_id = $_POST["ad_type_id"];

  $customer_selector = customer_selector($dbh, $cust_id);
  $ad_type_selector = ad_type_selector($dbh, $ad_type_id);
  $genre_selector = genre_selector($dbh, $genre_id);


  $sql = sprintf('insert into ads (name, length_s, path, start, end, genre_id, multiplier, ad_type_id, cust_id) values ("%s", %s, "%s", "%s", "%s", %s, %s, %s, %s)',
                  $ad_name, $length_s, $path, $start, $end, $genre_id, $multiplier, $ad_type_id, $cust_id);
             
  $error_msg = chk_values($length_s, $path, $start, $end, $multiplier);
  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';

  if (empty($error_msg))
  {
    try
    {
      $q = $dbh->prepare($sql);
      $q->execute();
      $ad_id = '';
      $cust_id = '';
      $ad_name = '';
      $length_s = '';
      $path = '';
      $start = '';
      $end = '';
      $genre_id = '';
      $multiplier  = '';
      $ad_type_id = '';

    } catch (PDOException $e)
    {
      $error_msg = "Update failed, check values!";
    }
  }
}

?>
<!DOCTYPE html>
<html>
	<head>
	<title>adamatic - Advert</title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<link rel="shortcut icon" href="/favicon.ico" />
        <script type="text/javascript" src="/ads/include/jquery.js"></script>

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
<h1>Ads</h1>

<?php
if (!empty($error_msg))
{
  echo $error_msg;
}
?>
<div class='post' align='center'>
<h2>Add an Ad</h2>


<form method='post' action='/ads/ad.php'>
<table width='100%'>
<tr>
    <td class='base'>Ad:&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td colspan="3"><input size='81' type='text' name='ad_name' value='<?php echo $ad_name ?>' /></td>
</tr>
<tr>
    <td class='base'>path:&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td colspan="3"><input size='81' type='text' name='path' value='<?php echo $path ?>' /></td>
</tr>

<tr>
    <td class='base'>Customer</td>
    <td><?php echo $customer_selector ?></td>
</tr>

<tr>
    <td class='base'>Length (seconds):&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='length_s' value='<?php echo $length_s ?>' /></td>
</tr>
<tr>
    <td class='base'>Start:&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='start' value='<?php echo $start ?>' /></td>

    <td class='base'>End:&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='end' value='<?php echo $end ?>' /></td>
</tr>
<tr>
    <td class='base'>Multiplier (Numeric):</td>
    <td><input type='text' name='multiplier' value='<?php echo $multiplier ?>' /></td>
</tr>

<tr>
    <td class='base'>Ad Type:</td>
    <td><?php echo $ad_type_selector ?></td>
    <td class='base'>Genre:&nbsp;</td>
    <td><?php echo $genre_selector ?></td>

</table><br>
<hr />
<table width='100%'>
<tr>
    <td class='base' width='50%'><img src='/ads/images/blob.gif' align='top' alt='*' />&nbsp;Required field</td>
</tr>
<?php
echo "<input type='hidden' name='ad_id' value='", $ad_id, "' />";
//echo "<input type='hidden' name='cust_id' value='", $cust_id, "' />";
echo $submit_button;
?>
</table>
</form>
</div>

<div class='post'>
<h2>Current Ads</h2>

<table class='tbl' style='width:100%;'>
  <tr>
    <th width="2%">Active</th>
    <th>Customer</th>
    <th>Ad</th>
    <th>Start</th>
    <th>End</th>
    <th width="6%">Action</th>
  </tr>

<?php
$sql = 'select a.ad_id, a.name "ad_name", c.name "cust_name", a.start, a.end, if(now() < a.start or now() > a.end, "no","yes") "active" from ads a, customers c where a.cust_id = c.cust_id';

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
  if ($row["active"] == 'yes')
  {
    echo '<img src="/ads/images/green_tick.png">';
  } else
  {
    echo '<img src="/ads/images/red_cross.png">';
  }
  echo "</td>";

  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["cust_name"];
  echo '</td>';
  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["ad_name"];
  echo '</td>';
  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["start"];
  echo '</td>';
  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["end"];
  echo '</td>';

  echo "<td align='center' bgcolor='", $line_color, "'>";
  echo "<form method='post' action='/ads/ad.php'>";
  echo "<input type='hidden' name='ACTION' value='Edit' />";
  echo "<input type='image' name='Edit' src='/ads/images/edit.gif' alt='Edit' title='Edit' />";
  echo "<input type='hidden' name='ad_id' value='", $row["ad_id"], "' />";
  echo "</form>";
  echo "</td>";

  echo '</tr>';
  $line = $line + 1;
}
?>
</table>

<table>
<tr>
    <td class='boldbase'>&nbsp;<b>Legend:&nbsp;</b></td>
    <td><img src='/ads/images/edit.gif' alt='Edit' /></td>
    <td class='base'>Edit</td>
</tr>
</table>
</div>

</body>
</html>
