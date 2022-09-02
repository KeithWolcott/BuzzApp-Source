<?php
session_start();
include 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
<title>Contact Support</title>
<?php
include 'header.php';
require('sendgrid-php/sendgrid-php.php');
?>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="center"><h1>Contact Support</h1>
<?php
$error = false;
$msgs = array();
if ($_POST)
{
	if (strlen($_POST["msg"]) <= 0)
	{
		$error = true;
		array_push($msgs,"Message is empty.");
	}
	if (strlen($_POST["msg"]) > 75000)
	{
		$error = true;
		array_push($msgs,"Message is too long. 75000 character limit.");
	}
	if (count($msgs) == 0)
	{
		$subject = "BuzzApp - Support Email";
		if (!isset($_POST["email"]) || $_POST["email"] == "")
			$from = new SendGrid\Email(null, "buzzapp@herokumanager.com");
		else
			$from = new SendGrid\Email(null, $_POST["email"]);
		$to = new SendGrid\Email(null, "wolcott.keith@gmail.com");
		// Create Sendgrid content
		$content = new SendGrid\Content("text/html",nl2br($_POST["msg"]));
		// Create a mail object
		$mail = new SendGrid\Mail($from, $subject, $to, $content);
		
		$sg = new \SendGrid('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');
		$response = $sg->client->mail()->send()->post($mail);
		if ($response->statusCode() != 202) { // 202 means the email was sent.
		 $error = true;
		 array_push($msgs,"Message wasn't sent.");
		}
	}
	if (count($msgs) > 0)
	{
		echo "<ul>";
		foreach ($msgs as $msg)
			echo "<li>$msg</li>\r\n";
		echo "</ul><hr>";
	}
	else
		echo "<p>Message sent!</p><hr>";
}
?>
<form method="post"><p>Your Email Address (optional): <input type="email" name="email" /></p>
<p>Write your message to us here:</p><textarea name="msg" maxlength="75000" rows="7" required><?php
if ($_POST && $error)
{
	echo htmlspecialchars($_POST["msg"]);
}
?></textarea><br><br>
<input type="submit" value="Send Email" /></form></div>
</body>
</html>