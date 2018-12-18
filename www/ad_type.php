<?php
/**
Maintain the ad_type table
*/

include("/coastfm/phplib/general.php");

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//return error message if values passed are not valid
//---------------------------------------------------------------------------
function chk_values($name, $plays_allowed, $fudge_factor, $multiplier)
{

$error_msg = '';

if (empty($multiplier))
{
  $error_msg = $error_msg . "<font class='base'>Invalid multiplier&nbsp;</font><br>";
}
if (fudge_factor == '')
{
  $error_msg = $error_msg . "<font class='base'>Invalid Fudge Factor.&nbsp;</font><br>";
}
if ($plays_allowed = '')
{
  $error_msg = $error_msg . "<font class='base'>Invalid Plays per Day.&nbsp;</font><br>";
}
if (empty($name))
{
  $error_msg = $error_msg . "<font class='base'>Invalid Name.&nbsp;</font><br>";
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
$passwd = 'ads wonderful ads';
$error_msg = '';
$ad_type_id = '';

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
/*+++++
Just display what we have already, no action to be taken
-----*/

  $name = '';
  $plays_allowed = '';
  $fudge_factor = '';
  $multiplier = 1;

  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';
} elseif ($_POST['ACTION'] == 'Edit')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Read row and display ready for updating
---------------------------------------------------------------------------
*/
  $ad_type_id = $_POST['ad_type_id'];
  $sql = 'select ad_type_id, name, plays_allowed, fudge_factor, multiplier from ad_type where ad_type_id = ' . $ad_type_id;
  $q = $dbh->query($sql);
  $f = $q->fetch();
  $name = $f['name'];
  $plays_allowed = $f['plays_allowed'];
  $fudge_factor = $f['fudge_factor'];
  $multiplier = $f['multiplier'];
  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Update" /><input type="submit" name="SUBMIT" value="Update" /></td>';
} elseif ($_POST['ACTION'] == 'Update')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Updating table with new values.
Check that they are valid or at least present
---------------------------------------------------------------------------
*/
  $ad_type_id = $_POST["ad_type_id"];
  $name = $_POST["name"];
  $plays_allowed = $_POST["plays_allowed"];
  $fudge_factor = $_POST["fudge_factor"];
  $multiplier = $_POST["multiplier"];
  $sql = sprintf('update ad_type set name = "%s", plays_allowed = %s, fudge_factor = %s, multiplier = %s where ad_type_id = %s', 
                 $_POST["name"], $_POST["plays_allowed"], $_POST["fudge_factor"], $_POST["multiplier"], $_POST["ad_type_id"]);

  $error_msg = chk_values($_POST["name"], $_POST["plays_allowed"], $_POST["fudge_factor"], $_POST["multiplier"]);

  if (empty($error_msg))
  {
    try
    {
      $q = $dbh->prepare($sql);
      $q->execute();
      $name = '';
      $plays_allowed = '';
      $fudge_factor = '';
      $multiplier = 1;
      $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';
    } catch (PDOException $e)
    {
      $error_msg = "Update failed, check values!";
      $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Update" /><input type="submit" name="SUBMIT" value="Update" /></td>';
echo $e;
echo $sql;
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
  $name = $_POST["name"];
  $plays_allowed = $_POST["plays_allowed"];
  $fudge_factor = $_POST["fudge_factor"];
  $multiplier = $_POST["multiplier"];

  $sql = sprintf('insert into ad_type (name, plays_allowed, fudge_factor, multiplier) values ("%s", %s, %s, %s)',
                 $name, $plays_allowed, $fudge_factor, $multiplier);

  $error_msg = chk_values($_POST["name"], $_POST["plays_allowed"], $_POST["fudge_factor"], $_POST["multiplier"]);
  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';

  if (empty($error_msg))
  {
    try
    {
      $q = $dbh->prepare($sql);
      $q->execute();
      $name = '';
      $plays_allowed = '';
      $fudge_factor = '';
      $multiplier = 1;
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
<h1>Ad type</h1>

<?php
if (!empty($error_msg))
{
  echo $error_msg;
}
?>
<div class='post' align='center'>
<h2>Add an ad type</h2>


<form method='post' action='/ads/ad_type.php'>
<table width='100%'>
<tr>
    <td class='base'>Ad Type:&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='name' value='<?php echo $name ?>' /></td>
    <td class='base'>Plays per day (Numeric):&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='plays_allowed' value='<?php echo $plays_allowed?>' /></td>
</tr><tr>
    <td class='base'>Fudge Factor (Numeric):&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='fudge_factor' value='<?php echo $fudge_factor ?>' /></td>
    <td class='base'>Multiplier (Numeric):&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='multiplier' value='<?php echo $multiplier ?>' /></td>
</tr>
</table><br>
<hr />
<table width='100%'>
<tr>
    <td class='base' width='50%'><img src='/ads/images/blob.gif' align='top' alt='*' />&nbsp;Required field</td>
<?php
echo "<input type='hidden' name='ad_type_id' value='", $ad_type_id, "' />";
echo $submit_button;
?>
</tr>
</table>
</form>
</div>

<div class='post'>
<h2>Current Ad types</h2>

<table class='tbl' style='width:100%;'>
  <tr>
    <th><b>Type</b></th>
    <th>Plays allowed</th>
    <th>Fudge factor</th>
    <th>Multiplier</th>
    <th>Action</th>
  </tr>

<?php
$sql = 'select ad_type_id, name, plays_allowed, fudge_factor, multiplier from ad_type';

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
  echo $row["name"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["plays_allowed"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["fudge_factor"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style="text-align:center;">';
  echo $row["multiplier"];
  echo '</td>';

  echo "<td align='center' bgcolor='", $line_color, "'>";
  echo "<form method='post' action='/ads/ad_type.php'>";
  echo "<input type='hidden' name='ACTION' value='Edit' />";
  echo "<input type='image' name='Edit' src='/ads/images/edit.gif' alt='Edit' title='Edit' />";
  echo "<input type='hidden' name='ad_type_id' value='", $row["ad_type_id"], "' />";
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
