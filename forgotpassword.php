<?php
require('sendgrid-php/sendgrid-php.php');
  session_start();
  if (isset($_SESSION["email"]))
	  header("Location: index.php");
  $passwordchanged = false;
  $sentemail = false;
  include 'functions.php';
  if ($_POST)
  {
	  include 'config.php';
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
	  $connectionfailed = false;
	  if ($accountType != "invalid") // if account type is 1 or 2
	  {
		if ($conn)
		{
		$result = mysqli_query($conn, "select firstName, password from $accountType where email='" . mysqli_real_escape_string($conn, $_POST["email"]) . "'");
		if (mysqli_num_rows($result) > 0) { // if at least 1 row, then the account exists.
			$accountExists = true;
			while($row = mysqli_fetch_assoc($result)) {
				$oldpassword = $row["password"];
				// Right now it creates a randomly generated password, sets the user's password to that, and sends an email to the user with the new password. I would rather the password doesn't change, with the email instead containing a link - that is a form where the user can choose their password. But, it's just a prototype...
				$newpassword = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(10/strlen($x)) )),1, 10); // Source: https://stackoverflow.com/questions/4356289/php-random-string-generator
				$newpassword2 = password_hash($newpassword, PASSWORD_DEFAULT); // create the hashed password from the random string
				$query = "UPDATE $accountType set password = '$newpassword2' where email='{$_POST["email"]}'";
				if (mysqli_query($conn, $query))
				{
					$passwordchanged = true;
					$subject = "BuzzApp - Password Recovery";
					$from = new SendGrid\Email(null, "buzzapp@herokumanager.com"); // who the email is from
					$to = new SendGrid\Email(null, $_POST["email"]);
					// Create Sendgrid content
					$content = new SendGrid\Content("text/html","Dear " . $row["firstName"] . ",<br />your password has been reset to $newpassword. <a href=\"http://buzapp.herokuapp.com/login.php\">Log in to your account</a> and change your password.<br />Thank you,<br />BuzzApp.");
					// Create a mail object
					$mail = new SendGrid\Mail($from, $subject, $to, $content);
					
					$sg = new \SendGrid('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');
					$response = $sg->client->mail()->send()->post($mail);
					if ($response->statusCode() == 202) { // 202 means the email was sent.
					 $sentemail = true;
					}
				}
			}
		}
	    mysqli_close($conn);
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
<script src="forgotpassword.js"></script>
<title>BuzzApp - Forgot Password</title>
</head>
<body<?php
if ($_POST && isset($_POST["email"])) // if submission failed, recheck if the account exists
	echo " onload=\"check()\"";
?>><?php include 'navbar.php'; ?><div class="container">
    <?php
	if ($_POST)
	{
		if ($connectionfailed)
			die("Connection failed: " . mysqli_connect_error());
		if (!$passwordchanged)
		{
			echo "<form id=\"contact\" method=\"post\" onsubmit=\"return valid()\"><h3>Forgot Password</h3>";
			if ($accountType == "invalid")
				echo "<p>That account type is invalid.</p>";
			else
			{
				if ($accountExists)
				{
					echo "<p>Unable to generate a temporary password.</p>";
				}
				else
				{
					echo "<p>That account does not exist.</p>";
				}
			}
		}
		else
		{
			if ($sentemail)
				echo "<form id=\"contact\" method=\"get\" action=\"login.php\"><h3>Forgot Password</h3><p>Okay. You should have received an email with a temporary password.</p>";
			else
				echo "<form id=\"contact\" method=\"post\" onsubmit=\"return valid()\"><h3>Forgot Password</h3><p>The email didn't send, so your password didn't change.</p>";
		}
	}
	else
	{
		echo "<form id=\"contact\" method=\"post\" onsubmit=\"return valid()\"><h3>Forgot Password</h3>";
	}
	if (!$passwordchanged || !$sentemail)
	{
		echo "<fieldset>
		  <input placeholder=\"Email Address\" type=\"email\" id=\"email\" name=\"email\" tabindex=\"1\" onchange=\"check()\" required style=\"width:87%;\"";	  if ($_POST && isset($_POST["email"]))
			  echo " value=\"" . str_replace('"',"&quot;",$_POST["email"]) . "\"";
		  echo "/> <span id=\"checkstatus\"></span>
		</fieldset>
		<div class=\"accountType\"><p>Select Account type: 
	  <select name=\"accountType\" id=\"accountType\" tabindex=\"3\" style=\"z-index:10;width:165px;\" onchange=\"check()\">
		<option value=\"1\">Client</option>
		<option value=\"2\"";
		if ($_POST && isset($_POST["accountType"]) && $_POST["accountType"]==2)
			echo " selected";
		echo ">Professional</option>
	  </select></p></div>
		<fieldset>
		  <button name=\"submit\" type=\"submit\" id=\"contact-submit\" tabindex=\"4\" data-submit=\"...Sending\">Send Recovery Email</button>
		</fieldset>";
	}
	else
	{
		echo "<fieldset><button name=\"submit\" type=\"submit\" id=\"contact-submit\" tabindex=\"4\" data-submit=\"...Sending\">Sign In</button>
		</fieldset>";
	}
	?>
  </form>
</div>
</body>
</html>