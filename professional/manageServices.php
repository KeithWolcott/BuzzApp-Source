<?php
session_start();
include '../config.php';
include '../functions.php';
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 2)
	header("Location: ../index.php");
$result = mysqli_query($conn, "select adminemail, name, barbershopId from barbershop where barbershopId in (select barbershopId from professional where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "')");
if (mysqli_num_rows($result) > 0)
{
	while($row = mysqli_fetch_assoc($result))
	{
		$adminemail = $row["adminemail"];
		$barbershopId = $row["barbershopId"];
		$barbershopname = $row["name"];
	}
}
if ($adminemail != $_SESSION["email"])
	header("Location: ../index.php");
	
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Services</title>
<?php include 'header.php'; ?>
<script src="services.js"></script>
</head>
<body onload="generateServices();"><?php include 'navbar.php'; ?>
<h1>Manage Services</h1>
<fieldset><legend>Add a new one</legend><form id="newservice" method="post" onsubmit="return validnew();">
Name: <input name="serviceName" id="serviceName" size="30" required /><br>
Price: <input name="price" id="price" type="number" step="0.01" min="0.01" value="10.00" required /><br>
Description:<br>
<textarea name="description" id="description" rows="3"></textarea><br>
Duration: <select name="duration">
<option value="5">5 minutes</option>
<option value="10">10 minutes</option>
<option value="15">15 minutes</option>
<option value="20">20 minutes</option>
<option value="25">25 minutes</option>
<option value="30" selected>30 minutes</option>
<option value="35">35 minutes</option>
<option value="40">40 minutes</option>
<option value="45">45 minutes</option>
<option value="50">50 minutes</option>
<option value="55">55 minutes</option>
<option value="60">1 hour</option>
<option value="65">1 hour 5 minutes</option>
<option value="70">1 hour 10 minutes</option>
<option value="75">1 hour 15 minutes</option>
<option value="80">1 hour 20 minutes</option>
<option value="85">1 hour 25 minutes</option>
<option value="90">1 hour 30 minutes</option>
<option value="95">1 hour 35 minutes</option>
<option value="100">1 hour 40 minutes</option>
<option value="105">1 hour 45 minutes</option>
<option value="110">1 hour 50 minutes</option>
<option value="115">1 hour 55 minutes</option>
<option value="120">2 hours</option></select><br>
<input type="checkbox" id="freeselect" value="free" name="free" /><label for="freeselect">Can be free</label><br>
Maximum Discount: <select name="discount">
<option value="0">None</option>
<option value="10">10%</option>
<option value="25">25%</option>
<option value="33">33%</option>
<option value="50">50%</option>
<option value="66">66%</option>
<option value="75">75%</option>
</select><br>
<input type="submit" value="Add" /></form>
</fieldset>
<hr>
<div id="services"></div>
</body>
</html>