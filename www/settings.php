<?php
/**
Maintain the setting table
*/

include("/coastfm/phplib/general.php");

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//return error message if values passed are not valid
//---------------------------------------------------------------------------
function chk_values($setting, $value)
{

$error_msg = '';

if (empty($setting))
{
  $error_msg = $error_msg . "<font class='base'>Invalid setting name.&nbsp;</font><br>";
}
if (empty($value))
{
  $error_msg = $error_msg . "<font class='base'>Invalid setting value;</font><br>";
}

if (!empty($error_msg))
{
  return "<div class='post'><h2>Error messages</h2>" . $error_msg . "</div>";
}

}
//---------------------------------------------------------------------------


$dsn = 'mysql:dbname=ads;host=database';
$user = 'ads_o';
$passwd = '????';
$error_msg = '';
$setting_id = '';


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

  $setting = '';
  $value = '';

#  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';

} elseif ($_POST['ACTION'] == 'Edit')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Read row and display ready for updating
---------------------------------------------------------------------------
*/
  $setting_id = $_POST['setting_id'];
  $sql = 'select setting_id, setting, value from settings where setting_id = ' . $setting_id;

  $q = $dbh->query($sql);
  $f = $q->fetch();
  $setting = $f["setting"];
  $value = $f["value"];
  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Update" /><input type="submit" name="SUBMIT" value="Update" /></td>';

} elseif ($_POST['ACTION'] == 'Update')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Updating table with new values.
Check that they are valid or at least present
---------------------------------------------------------------------------
*/
  $setting_id = $_POST["setting_id"];
  $setting = $_POST["setting"];
  $value = $_POST["value"];
  $sql = sprintf('update settings set setting = "%s", value = "%s" where setting_id = %s', 
                 $_POST["setting"], $_POST["value"], $_POST["setting_id"]);
echo $sql;

  $error_msg = chk_values($_POST["setting"], $_POST["value"]);

  if (empty($error_msg))
  {
    try
    {
      $q = $dbh->prepare($sql);
      $q->execute();
      $setting = '';
      $value = '';
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
  $sql = sprintf("delete from settings where setting_id = %s", $_POST["setting_id"]);

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
  $setting = $_POST["setting"];
  $value = $_POST["value"];

  $sql = sprintf('insert into settings (setting, value) values ("%s", "%s")',
                 $setting, $value);
  $error_msg = chk_values($_POST["setting"], $_POST["value"]);
  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';

  if (empty($error_msg))
  {
    try
    {
      $q = $dbh->prepare($sql);
      $q->execute();
      $setting = '';
      $value = '';
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
<h1>Settings</h1>

<?php
if (!empty($error_msg))
{
  echo $error_msg;
}
?>
<div class='post' align='center'>
<h2>Add a Setting</h2>


<form method='post' action='/ads/settings.php'>
<table width='100%'>
<tr>
    <td class='base'>Setting Name:&nbsp;</td>
    <td><input type='text' disabled name='setting' value='<?php echo $setting ?>' /></td>
    <td class='base'>Value&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='value' value='<?php echo $value ?>' /></td>
</tr>
</table><br>
<hr />
<table width='100%'>
<tr>
    <td class='base' width='50%'><img src='/ads/images/blob.gif' align='top' alt='*' />&nbsp;Required field</td>
<?php
echo "<input type='hidden' name='setting_id' value='", $setting_id, "' />";
echo $submit_button;
?>
</tr>
</table>
</form>
</div>

<div class='post'>
<h2>Current Settings</h2>

<table class='tbl' style='width:100%;'>
  <tr>
    <th>Setting Name</th>
    <th>Value</th>
    <th colspan='2' width='10%'>Action</th>
  </tr>

<?php
$sql = 'select setting_id, setting, value from settings';

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
  echo $row["setting"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["value"];
  echo '</td>';

  echo "<td align='center' bgcolor='", $line_color, "'>";
  echo "<form method='post' action='/ads/settings.php'>";
  echo "<input type='hidden' name='ACTION' value='Edit' />";
  echo "<input type='image' name='Edit' src='/ads/images/edit.gif' alt='Edit' title='Edit' />";
  echo "<input type='hidden' name='setting_id' value='", $row["setting_id"], "' />";
  echo "</form>";
  echo "</td>";

  echo "<td align='center' bgcolor='", $line_color, "'>";
  echo "<form method='post' action='/ads/settings.php'>";
  echo "<input type='hidden' name='setting_id' value='", $row["setting_id"], "' />";
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
