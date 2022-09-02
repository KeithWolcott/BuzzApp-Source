<?php
  session_start();
  if (!isset($_SESSION["email"])) // if not logged in
	  header("Location: index.php");
  $passwordchanged = false;
  $passwordwrong = false;
  $passworddontmatch = false;
  $sameascurrent = false;
  include 'config.php';
  include 'functions.php';
  if ($_POST)
  {
	  $accountType = "invalid";
	  if ($_SESSION["accountType"] == 1)
	  {
		  $accountType = "client";
	  }
	  elseif ($_SESSION["accountType"] == 2)
	  {
		  $accountType = "professional";
	  } // check if account is a valid type
	  $accountExists = false;
	  $connectionfailed = false;
	  if ($accountType != "invalid")
	  {
		if ($conn) // if database connected
		{
			$result = mysqli_query($conn, "select password from $accountType where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
			if (mysqli_num_rows($result) > 0) {
				while($row = mysqli_fetch_assoc($result)) {
					if (password_verify($_POST["currentpassword"],$row["password"])) // if the "current password" is correct
					{
						if ($_POST["newpassword"] == $_POST["confirmpassword"]) // and the new and confirm passwords match
						{
							if (password_verify($_POST["newpassword"],$row["password"])) //if the new password is the same as the current password
								$sameascurrent = true;
							else
							{
								$query = "UPDATE $accountType set password = '" . password_hash($_POST["newpassword"], PASSWORD_DEFAULT) . "' where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'"; // change the user's password to the new password
								if (mysqli_query($conn, $query))
									$passwordchanged = true;
							}
						}
						else
							$passworddontmatch = true;
					}
					else
						$passwordwrong = true;
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
<script src="changepassword.js"></script>
<title>BuzzApp - Change Password</title>
</head>
<body><?php include 'navbar.php'; 
mysqli_close($conn);
?><div class="container">
    <?php
	if ($_POST && $connectionfailed) // if couldn't connect to database
		 die("Connection failed: " . mysqli_connect_error());
	if (!$passwordchanged)
		echo "<form id=\"contact\" method=\"post\" onsubmit=\"return valid();\"><h3>Change Password</h3>";
	if ($_POST) // if submitted the page.
	{
		if ($passwordchanged)
		{
			echo "<form id=\"contact\" method=\"get\" action=\"index.php\"><h3>Change Password</h3><p>Password changed!</p><fieldset><button name=\"submit\" type=\"submit\" id=\"contact-submit\" tabindex=\"4\" data-submit=\"...Sending\">Return to Menu</button></fieldset>";
		}
		else
		{
			if ($passwordwrong)
				echo "<p>Incorrect password.</p>";
			elseif ($passworddontmatch)
				echo "<p>Passwords do not match.</p>";
			elseif ($sameascurrent)
				echo "<p>That is your current password.</p>";
		}
	}
	if (!$passwordchanged)
	{
			echo "<fieldset>
      <input placeholder=\"Current Password\" type=\"password\" id=\"currentpassword\" name=\"currentpassword\" tabindex=\"1\" required style=\"width:87%;\" />
    </fieldset>
	<fieldset>
      <input placeholder=\"New Password\" type=\"password\" id=\"newpassword\" name=\"newpassword\" tabindex=\"2\" onchange=\"check();\" required style=\"width:87%;\" />
    </fieldset>
	<fieldset>
      <input placeholder=\"Confirm Password\" type=\"password\" id=\"confirmpassword\" name=\"confirmpassword\" tabindex=\"2\" onchange=\"check();\" required style=\"width:87%;\" /> <span id=\"checkpassword\"></span>
    </fieldset>
		<fieldset>
		  <button name=\"submit\" type=\"submit\" id=\"contact-submit\" tabindex=\"4\" data-submit=\"...Sending\">Change Password</button>
		</fieldset>";
		}
	?>
  </form>
</div>
</body>
</html>