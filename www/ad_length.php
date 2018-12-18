<?php
/**
Maintain the ad_length table
*/

include("/coastfm/phplib/general.php");

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//return error message if values passed are not valid
//---------------------------------------------------------------------------
function chk_values($length_s, $start, $end)
{

$error_msg = '';

if (empty($length_s))
{
  $error_msg = $error_msg . "<font class='base'>Invalid Ad length.&nbsp;</font><br>";
}
if (empty($start) or !strtotime($start))
{
  $error_msg = $error_msg . "<font class='base'>Invalid Start time;</font><br>";
}
if (empty($end) or !strtotime($end))
{
  $error_msg = $error_msg . "<font class='base'>Invalid End time;</font><br>";
}

if (!empty($error_msg))
{
  return "<div class='post'><h2>Error messages</h2>" . $error_msg . "</div>";
}

}
//---------------------------------------------------------------------------


logit(0, "starting");
$dsn = 'mysql:dbname=ads;host=database';
$user = 'ads_o';
$passwd = '????';
$error_msg = '';
$ad_length_id = '';

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

  $length_s = '';
  $start = '';
  $end = '';

  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';

} elseif ($_POST['ACTION'] == 'Edit')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Read row and display ready for updating
---------------------------------------------------------------------------
*/
  $ad_length_id = $_POST['ad_length_id'];
  $sql = 'select ad_length_id, length_s, start, end from ad_length where ad_length_id = ' . $ad_length_id;
  $q = $dbh->query($sql);
  $f = $q->fetch();
  $length_s = $f['length_s'];
  $start = $f['start'];
  $end = $f['end'];
  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Update" /><input type="submit" name="SUBMIT" value="Update" /></td>';

} elseif ($_POST['ACTION'] == 'Update')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Updating table with new values.
Check that they are valid or at least present
---------------------------------------------------------------------------
*/
  $ad_length_id = $_POST["ad_length_id"];
  $length_s = $_POST["length_s"];
  $start = $_POST["start"];
  $end = $_POST["end"];
  $sql = sprintf('update ad_length set length_s = %s, start = "%s", end = "%s" where ad_length_id = %s', 
                 $_POST["length_s"], $_POST["start"], $_POST["end"], $_POST["ad_length_id"]);
echo $sql;

  $error_msg = chk_values($_POST["length_s"], $_POST["start"], $_POST["end"]);

  if (empty($error_msg))
  {
    try
    {
      $q = $dbh->prepare($sql);
      $q->execute();
      $length_s = '';
      $start = '';
      $end = '';
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

} elseif ($_POST['ACTION'] == 'Remove')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Delete a row
---------------------------------------------------------------------------
*/
  $sql = sprintf("delete from ad_length where ad_length_id = %s", $_POST["ad_length_id"]);

  try
  {
    $q = $dbh->prepare($sql);
    $q->execute();
  } catch (PDOException $e)
  {
    $error_msg = "Delete failed. Call an adult";
  }
  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';
} elseif ($_POST['ACTION'] == 'Add')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Add row to table
Check that they are valid or at least present
---------------------------------------------------------------------------
*/
  $length_s = $_POST["length_s"];
  $start = $_POST["start"];
  $end = $_POST["end"];

  $sql = sprintf('insert into ad_length (length_s, start, end) values (%s, "%s", "%s")',
                 $length_s, $start, $end);

  $error_msg = chk_values($_POST["length_s"], $_POST["start"], $_POST["end"]);
  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';

  if (empty($error_msg))
  {
    try
    {
      $q = $dbh->prepare($sql);
      $q->execute();
      $length_s = '';
      $start = '';
      $end = '';
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
	<title>adamatic - Ad Type</title>
	
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
<h1>Ad Length</h1>

<?php
if (!empty($error_msg))
{
  echo $error_msg;
}
?>
<div class='post' align='center'>
<h2>Add an Ad length</h2>


<form method='post' action='/ads/ad_length.php'>
<table width='100%'>
<tr>
    <td class='base'>Start time hh:mm:ss:&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='start' value='<?php echo $start ?>' /></td>
    <td class='base'>End time hh:mm:ss:&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='end' value='<?php echo $end ?>' /></td>
</tr>
<tr>
    <td class='base'>Ad length in seconds (Numeric):&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='length_s' value='<?php echo $length_s ?>' /></td>
</tr>
</table><br>
<hr />
<table width='100%'>
<tr>
    <td class='base' width='50%'><img src='/ads/images/blob.gif' align='top' alt='*' />&nbsp;Required field</td>
<?php
echo "<input type='hidden' name='ad_length_id' value='", $ad_length_id, "' />";
echo $submit_button;
?>
</tr>
</table>
</form>
</div>

<div class='post'>
<h2>Current Ad Lengths</h2>

<table class='tbl' style='width:100%;'>
  <tr>
    <th>Start time</th>
    <th>End time</th>
    <th>Length</th>
    <th colspan='2' width='10%'>Action</th>
  </tr>

<?php
$sql = 'select ad_length_id, length_s, start, end from ad_length';

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
  echo $row["start"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["end"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["length_s"];
  echo '</td>';

  if ($row["start"] == "00:00:00" and $row["end"] == "23:59:59")
  {
  } else
  {
    echo "<td align='center' bgcolor='", $line_color, "'>";
    echo "<form method='post' action='/ads/ad_length.php'>";
    echo "<input type='hidden' name='ACTION' value='Edit' />";
    echo "<input type='image' name='Edit' src='/ads/images/edit.gif' alt='Edit' title='Edit' />";
    echo "<input type='hidden' name='ad_length_id' value='", $row["ad_length_id"], "' />";
    echo "</form>";
    echo "</td>";

    echo "<td align='center' bgcolor='", $line_color, "'>";
    echo "<form method='post' action='/ads/ad_length.php'>";
    echo "<input type='hidden' name='ACTION' value='Remove' />";
    echo "<input type='image' name='Remove' src='/ads/images/delete.gif' alt='Delete' title='Remove' />";
    echo "<input type='hidden' name='ad_length_id' value='", $row["ad_length_id"], "' />";
  }
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
    <td><img src='/ads/images/delete.gif' alt='Delete' /></td>
    <td class='base'>Delete</td>
</tr>
</table>
</div>

</body>
</html>
