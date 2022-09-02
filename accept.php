<?php
session_start();
require('sendgrid-php/sendgrid-php.php');
include 'config.php';
include 'functions.php';
if ($_POST && $conn && isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2) // if user is logged in and is a professional
{
	$result = mysqli_query($conn, "select barbershop.barbershopId, name, adminemail, firstName, lastName from barbershop inner join professional on barbershop.adminemail = professional.email where barbershop.barbershopId in (select barbershopId from professional where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "')"); // to find out if the user is an admin
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_assoc($result))
		{
			$admin = $row["adminemail"];
			$barbershopName = $row["name"];
			$id = $row["barbershopId"];
			$adminname = "{$row["firstName"]} {$row["lastName"]}";
		}
		if ($admin == $_SESSION["email"]) // if the professional is the admin of a barbershop
		{
			$query1 = mysqli_query($conn, "select firstName, lastName, notificationAccept, email from professional where md5(email) = '" . mysqli_real_escape_string($conn, $_POST["user"]) . "'");
			if (mysqli_num_rows($query1) > 0)
			{
				$row = mysqli_fetch_assoc($query1);
				$query = mysqli_query($conn, "update professional set accepted='1' where md5(email)='" . mysqli_real_escape_string($conn, $_POST["user"]) . "'"); //the requested user will now be able to access the barbershop
				$username = "{$row["firstName"]} {$row["lastName"]}";
				if ($row["notificationAccept"] == 1)
					send_notification("Request Accepted","noreply@buzzapp.net",$row["email"],"Dear $username,\n$adminname accepted your request to join $barbershopName on BuzzApp! <a href=\"http://buzapp.herokuapp.com/\">Get started.</a>");
			}
		}
	}
	mysqli_close($conn);
}
?>