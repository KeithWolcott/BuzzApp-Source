<?php
// This one checks if the account provided exists for forgot password
if (strlen($_GET["email"]) > 0) // if provided an email
{
  include 'config.php';
  $accountType = "invalid";
  if ($_GET["accountType"] == 1)
  {
	  $accountType = "client";
  }
  elseif ($_GET["accountType"] == 2)
  {
	  $accountType = "professional";
  }
  if ($conn && $accountType != "invalid")
  {
	  $result = mysqli_query($conn, "select * from $accountType where email='" . mysqli_real_escape_string($conn, $_GET["email"]) . "'"); // find out if account exists in the right table
	  if (mysqli_num_rows($result) == 0) // if account does not exist
		  echo "<img src=\"images/redx.png\" alt=\"Account does not exist\" id=\"accountExistence\" title=\"Account does not exist\">";
	  else
		  echo "<img src=\"images/checkmark.png\" alt=\"Account exists\" id=\"accountExistence\" title=\"Account exists\">";
	  mysqli_close($conn);
  }
  else
	  "<img src=\"images/redx.png\" alt=\"Unable to connect to database\" id=\"accountExistence\" title=\"Unable to connect to database\">";
}
  ?>