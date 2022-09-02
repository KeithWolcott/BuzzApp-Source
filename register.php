<?php
  session_start();
  $accountcreated = false;
  $passwordmatch = false;
  $validphonenumber = true;
  $connectionfailed = false;
  include 'config.php'; // Initialize database
  include 'functions.php'; // For fixtime
  if ($_POST)
  {
	  $now = fixtime(new DateTime("now"));
	  $accountType = "invalid";
	  if ($_POST["accountType"] == 1) // client number
	  {
		  $accountType = "client";
	  }
	  elseif ($_POST["accountType"] == 2) // professional number
	  {
		  $accountType = "professional";
	  }
	  if ($accountType != "invalid") // if accountType is either 1 or 2
	  {
		if ($conn) // if database connected
		{
			$result = mysqli_query($conn, "select * from $accountType where email='{$_POST["email"]}'"); // find out if account is in use
			if (mysqli_num_rows($result) <= 0) { // account doesn't exist
				if ($_POST["password"] == $_POST["confirmpassword"]) // if the password and confirm password match
				{
					$passwordmatch = true;
					$password = password_hash($_POST["password"], PASSWORD_DEFAULT);
					$query = "insert into $accountType (firstName, lastName, password, email, year, month, day, time)
						VALUES ('" . mysqli_real_escape_string($conn, $_POST["firstName"]) . "', '" . mysqli_real_escape_string($conn, $_POST["lastName"]) . "', '$password', '" . mysqli_real_escape_string($conn, $_POST["email"]) . "', '" . $now->format("Y") . "', '" . $now->format("m") . "', '" . $now->format("d") . "', '" . $now->format("h:i A") . "')"; // insert with no phone number
					/*if (isset($_POST["phone"]) && $_POST["phone"] != "") // if phone number provided
					{
						if (!preg_match('/(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})/',$_POST["phone"])) // validate phone
						{
							$validphonenumber = false;
							$query = "";
						}
						else
							$query = "insert into $accountType (firstName, lastName, password, email, phone, year, month, day, time)
						VALUES ('" . mysqli_real_escape_string($conn, $_POST["firstName"]) . "', '" . mysqli_real_escape_string($conn, $_POST["lastName"]) . "', '$password', '" . mysqli_real_escape_string($conn, $_POST["email"]) . "', '{$_POST["phone"]}', '" . $now->format("Y") . "', '" . $now->format("m") . "', '" . $now->format("d") . "', '" . $now->format("h:i A") . "')"; // insert with phone
					} // Yeah no longer need that, so...*/
					if ($query != "" && mysqli_query($conn, $query)) // if everything is okay
					{
						$accountcreated = true;
						$_SESSION["email"] = $_POST["email"];
						$_SESSION["accountType"] = $_POST["accountType"];
						mysqli_close($conn);
						header("Location: index.php"); // redirect to home page
					}
				}
			}
		  }
  }
  else // if account is neither 1 or 2
	$connectionfailed = true;
  }
?>
<!DOCTYPE html>
<html>
<head>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="registersheet.css" />
<script src="register.js"></script>
<title>BuzzApp Registration</title>
</head>
<body<?php
if ($_POST && isset($_POST["email"])) // If something was invalid recheck if the account exists
	echo " onload=\"check()\"";
?>><?php include 'navbar.php'; ?>
<div class="container">  
  <form id="contact" method="post" onsubmit="return valid()">
    <h3>BuzzApp Registration</h3>
    <?php
	if ($connectionfailed)
		die("Connection failed: " . mysqli_connect_error());
	if ($_POST && !$accountcreated) // If something was invalid
	{
		if ($accountType == "invalid") // Account number was neither 1 or 2
			echo "<p>That account type is invalid.</p>";
		elseif (!$passwordmatch) // Password and confirm password didn't match
			echo "<p>The passwords do not match.</p>";
		elseif (!$validphonenumber) // Phone number wasn't right format
			echo "<p>That's not a valid phone number.</p>";
		else
			echo "<p>That account already exists.</p>";
	}
			mysqli_close($conn);
	?>
    <fieldset>
      <input placeholder="First name" type="text" name="firstName" id="firstName" style="width:40%;" tabindex="1" required autofocus<?php
	  if ($_POST && isset($_POST["firstName"])) // reshow first name
		  echo " value=\"" . str_replace('"',"&quot;",$_POST["firstName"]) . "\"";
	  ?> /> <input placeholder="Last name" name="lastName" id="lastName" type="text" style="width:46%;" tabindex="2" required <?php
	  if ($_POST && isset($_POST["lastName"])) // reshow last name
		  echo " value=\"" . str_replace('"',"&quot;",$_POST["lastName"]) . "\"";
	  ?> />
    </fieldset>
    <fieldset>
      <input placeholder="Your Email Address" type="email" id="email" name="email" tabindex="3" onchange="check()" required style="width:87%;"<?php
	  if ($_POST && isset($_POST["email"])) // reshow email
		  echo " value=\"" . str_replace('"',"&quot;",$_POST["email"]) . "\"";
	  ?> /> <span id="checkstatus"></span>
    </fieldset>
	<fieldset>
      <input placeholder="Password" type="password" id="password" name="password" tabindex="4" onchange="checkconfirm();" required style="width:87%;" />
    </fieldset>
	<fieldset>
      <input placeholder="Confirm Password" type="password" id="confirmpassword" name="confirmpassword" tabindex="4" required style="width:87%;" onchange="checkconfirm();" /> <span id="checkpassword"></span>
    </fieldset>
	<div class="accountType"><p>Select Account type: 
  <select name="accountType" id="accountType" style="z-index:10;width:164px;" tabindex="6" onchange="check()">
    <option value="1">Client</option>
    <option value="2"<?php
	if ($_POST && isset($_POST["accountType"]) && $_POST["accountType"]==2) // client will be selected by default. Reshow professional otherwise.
		echo " selected";
	?>>Professional</option>
  </select></p></div>
    <fieldset>
      <button name="submit" type="submit" id="contact-submit" tabindex="7" data-submit="...Sending">Submit</button>
    </fieldset>
  </form>
</div>
</body>
</html>