<?php
session_start();
if (!isset($_SESSION["email"]) || $_SESSION["accountType"] != 2)
	header("Location: ../login.php");
include '../functions.php';
if ($_POST && isset($_POST["post"]))
{
	  include '../config.php';
	  if ($conn)
	  {
		$now = fixtime(new DateTime("now"));
		$query = "insert into notificationBoard (professionalEmail, message, year, month, day, time, upcomingonly) values ('" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "', '" . htmlspecialchars($_POST["post"],ENT_QUOTES) . "', '" . $now->format("Y") . "', '" . $now->format("m") . "', '" . $now->format("d") . "', '" . $now->format("h:i A") . "'";
		if (isset($_POST["appointmentsonly"]))
			$query .= ", 1";
		else
			$query .= ", 0";
		$result = mysqli_query($conn, "$query)");	
		mysqli_close($conn);
	  }
}	
  ?>