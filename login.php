<?php
session_start();
$connectionfailed = false;
include 'config.php';
include 'functions.php';
if ($_POST)
{
	$conn = mysqli_connect($servername, $username, $password, $db, $port);
	$loggedin = false;
	  $accountType = "invalid";
	  if ($_POST["accountType"] == 1)
	  {
		  $accountType = "client";
	  }
	  elseif ($_POST["accountType"] == 2)
	  {
		  $accountType = "professional";
	  }
	  $accountExists = false;
	  if ($accountType != "invalid")
	  {
		if ($conn)
		{
			$result = mysqli_query($conn, "select password from $accountType where email='" . mysqli_real_escape_string($conn, $_POST["email"]) . "'");
			if (mysqli_num_rows($result) > 0) {
				$accountExists = true;
				while($row = mysqli_fetch_assoc($result)) {
					if (password_verify($_POST["password"],$row["password"]))
					{
						$_SESSION["email"] = $_POST["email"];
						$_SESSION["accountType"] = $_POST["accountType"];
						header("Location: index.php");
					}
				}
			}
		}
		else
			$connectionfailed = true;
	  }
}
?>
<!DOCTYPE html>
<html>
<head>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="registersheet.css" />
<script src="login.js"></script>
<title>BuzzApp - Sign In</title>
</head>
<body><?php include 'navbar.php'; ?>
<div class="container">  
<form id="contact" method="post" onsubmit="return valid()">
<h3>Sign In</h3>
<?php
if ($connectionfailed)
	 die("Connection failed: " . mysqli_connect_error());
if ($_POST && !$loggedin)
{
	if ($accountType == "invalid")
		echo "<p>That account type is invalid.</p>";
	else
	{
		if ($accountExists)
		{
			echo "<p>Incorrect password.</p>";
		}
		else
		{
			echo "<p>That account does not exist.</p>";
		}
	}
}
mysqli_close($conn);
?>
<fieldset>
  <input placeholder="Email Address" type="email" id="email" name="email" tabindex="1" required style="width:87%;"<?php
  if ($_POST && isset($_POST["email"]))
	  echo " value=\"" . str_replace('"',"&quot;",$_POST["email"]) . "\"";
  ?> />
</fieldset>
<fieldset>
  <input placeholder="Password" type="password" id="password" name="password" tabindex="2" required style="width:87%;" />
</fieldset>
<div class="accountType"><p>Select Account type: 
<select name="accountType" id="accountType" tabindex="3" style="z-index:10;width:208px;">
<option value="1">Client</option>
<option value="2"<?php
if ($_POST && isset($_POST["accountType"]) && $_POST["accountType"]==2)
	echo " selected";
?>>Professional</option>
</select></p></div>
<p><a href="forgotpassword.php">Forgot Password?</a></p>
<fieldset>
  <button name="submit" type="submit" id="contact-submit" tabindex="4" data-submit="...Sending">Log In</button>
</fieldset>
</form>
</div>
</body>
</html>