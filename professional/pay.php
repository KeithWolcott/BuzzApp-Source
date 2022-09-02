<?php
session_start();
include '../config.php';
include '../functions.php';
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 2)
	header("Location: ../index.php");
?>
<!DOCTYPE html>
<html>
<head>
<title>Pay</title>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="../registersheet.css" />
</head>
<body><?php include 'navbar.php'; ?>
<div class="container">
<form method="post" id="contact" onsubmit="return validVIP();">
<h2>Pay</h2>
<fieldset>
<p>Coming soon</p></div></fieldset></div>
</body>
</html>