<?php
session_start();
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 1)
	header("Location: ../login.php");
if ($_POST && isset($_POST["id"])) // if the user is logged in as a client and submitted an id
{
	  include '../config.php';
	  if ($conn)
	  {
		  $result = mysqli_query($conn, "update scheduling set madereview='0', remindaboutreview='0' where md5(id) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'"); // don't remind the user again
		  mysqli_close($conn);
	  }
}
?>