<?php
session_start();
include '../config.php';
include '../functions.php';
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 1)
	header("Location: ../index.php");
if ($conn)
{
	$result = mysqli_query($conn, "select vip from client where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
	$vip = 0;
	if (mysqli_num_rows($result) > 0)
	{
		while($row = mysqli_fetch_assoc($result)) {
			$vip = $row["vip"];
		}
	}
	if ($vip == 1)
		header("Location: manageVip.php");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Become VIP</title>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="../registersheet.css" />
</head>
<body><?php include 'navbar.php'; ?>
<div class="container">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" id="contact">
<h2>Upgrade to VIP</h2>
<fieldset>
<p>Benefits:</p>
<input type="hidden" name="cmd" value="_xclick-subscriptions">
<input type="hidden" name="business" value="wolcott.keith@gmail.com">
<input type="hidden" name="lc" value="US">
<input type="hidden" name="item_name" value="BuzzApp Professional">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="a1" value="0.00">
<input type="hidden" name="p1" value="1">
<input type="hidden" name="t1" value="M">
<input type="hidden" name="src" value="1">
<input type="hidden" name="a3" value="9.99">
<input type="hidden" name="p3" value="1">
<input type="hidden" name="t3" value="M">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="bn" value="PP-SubscriptionsBF:btn_subscribeCC_LG.gif:NonHostedGuest">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"></form></div></fieldset></div>
</body>
</html>