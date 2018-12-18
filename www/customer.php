<?php
/**
Maintain the customer table
*/

include("/coastfm/phplib/general.php");

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//return error message if values passed are not valid
//---------------------------------------------------------------------------
function chk_values($name, $email, $phone, $address, $contact)
{

$error_msg = '';

if (empty($name))
{
  $error_msg = $error_msg . "<font class='base'>Invalid Name.&nbsp;</font><br>";
}
if (empty($email))
{
  $error_msg = $error_msg . "<font class='base'>Invalid Email Address.&nbsp;</font><br>";
}
if ($phone == '')
{
  $error_msg = $error_msg . "<font class='base'>Invalid Phone Number.&nbsp;</font><br>";
}
if (empty($address))
{
  $error_msg = $error_msg . "<font class='base'>Invalid Customer Address.&nbsp;</font><br>";
}
if (empty($contact))
{
  $error_msg = $error_msg . "<font class='base'>Invalid Customer Contact.&nbsp;</font><br>";
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
$cust_id = '';

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

  $name = '';
  $email = '';
  $phone = '';
  $address = '';
  $contact = '';

  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';
} elseif ($_POST['ACTION'] == 'Edit')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Read row and display ready for updating
---------------------------------------------------------------------------
*/
  $cust_id = $_POST['cust_id'];
  $sql = 'select cust_id, name, email, phone, contact, address from customers where cust_id = ' . $cust_id;
  $q = $dbh->query($sql);
  $f = $q->fetch();
  $name = $f['name'];
  $email = $f["email"];
  $phone = $f["phone"];
  $contact = $f["contact"];
  $address = $f["address"];

  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Update" /><input type="submit" name="SUBMIT" value="Update" /></td>';
} elseif ($_POST['ACTION'] == 'Update')
{
/*
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Updating table with new values.
Check that they are valid or at least present
---------------------------------------------------------------------------
*/
  $cust_id = $_POST["cust_id"];
  $name = $_POST['name'];
  $email = $_POST["email"];
  $phone = $_POST["phone"];
  $contact = $_POST["contact"];
  $address = $_POST["address"];

  $sql = sprintf('update customers set name = "%s", email = "%s", phone = "%s", contact = "%s", address = "%s" where cust_id = %s', 
                 $_POST["name"], $_POST["email"], $_POST["phone"], $_POST["contact"], $_POST["address"], $_POST["cust_id"]);

  $error_msg = chk_values($name, $email, $phone, $contact, $address);

  if (empty($error_msg))
  {
    try
    {
      $q = $dbh->prepare($sql);
      $q->execute();
      $name = '';
      $email = '';
      $phone = '';
      $address = '';
      $contact = '';

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
  $name = $_POST['name'];
  $email = $_POST["email"];
  $phone = $_POST["phone"];
  $contact = $_POST["contact"];
  $address = $_POST["address"];

  $sql = sprintf('insert into customers (name, email, phone, contact, address) values ("%s", "%s", "%s", "%s", "%s")',
                 $name, $email, $phone, $contact, $address);

  $error_msg = chk_values($name, $email, $phone, $contact, $address);
  $submit_button = '<td width="50%" align="right"><input type="hidden" name="ACTION" value="Add" /><input type="submit" name="SUBMIT" value="Add" /></td>';

  if (empty($error_msg))
  {
    try
    {
      $q = $dbh->prepare($sql);
      $q->execute();
      $name = '';
      $email = '';
      $phone = '';
      $address = '';
      $contact = '';
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
<h1>Customers</h1>

<?php
if (!empty($error_msg))
{
  echo $error_msg;
}
?>
<div class='post' align='center'>
<h2>Add a Customer</h2>

<form method='post' action='/ads/customer.php'>
<table width='100%'>
<tr>
    <td class='base'>Name:&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='name' value='<?php echo $name ?>' /></td>

    <td class='base'>Contact:&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='contact' value='<?php echo $contact ?>' /></td>
</tr>
<tr>
    <td class='base'>Email:&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='email' value='<?php echo $email ?>' /></td>

    <td class='base'>Phone:&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td><input type='text' name='phone' value='<?php echo $phone ?>' /></td>
<tr>

    <td class='base'>Address:&nbsp;<img src='/ads/images/blob.gif' alt='*' /></td>
    <td colspan='3'><input type='text' size="100" name='address' value='<?php echo $address ?>' /></td>
</tr>
</table><br>
<hr />
<table width='100%'>
<tr>
    <td class='base' width='50%'><img src='/ads/images/blob.gif' align='top' alt='*' />&nbsp;Required field</td>
<?php
echo "<input type='hidden' name='cust_id' value='", $cust_id, "' />";
echo $submit_button;
?>
</tr>
</table>
</form>
</div>

<div class='post'>
<h2>Current Customers</h2>

<table class='tbl' style='width:100%;'>
  <tr>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Contact</th>
    <th>Action</th>
  </tr>

<?php
$sql = 'select cust_id, name, email, phone, address, contact from customers';

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

  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["email"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["phone"];
  echo '</td>';

  echo '<td bgcolor="', $line_color, '" style=text-align:center;">';
  echo $row["contact"];
  echo '</td>';

  echo "<td align='center' bgcolor='", $line_color, "'>";
  echo "<form method='post' action='/ads/customer.php'>";
  echo "<input type='hidden' name='ACTION' value='Edit' />";
  echo "<input type='image' name='Edit' src='/ads/images/edit.gif' alt='Edit' title='Edit' />";
  echo "<input type='hidden' name='cust_id' value='", $row["cust_id"], "' />";
  echo "</form>";
  echo "</td>";
  echo '</tr>';

  echo '<tr>';
  echo '<td colspan="5" bgcolor="', $line_color, '" style=text-align:left;">';
  echo $row["address"];
  echo '</td>';
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
