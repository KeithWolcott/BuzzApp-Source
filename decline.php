<?php
session_start();
require('sendgrid-php/sendgrid-php.php');
include 'config.php';
include 'functions.php';
if ($_POST && $conn && isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2) // if from form and the logged in user is a professional
{
	$result = mysqli_query($conn, "select barbershop.barbershopId, name, adminemail, firstName, lastName from barbershop inner join professional on barbershop.adminemail = professional.email where barbershop.barbershopId in (select barbershopId from professional where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "')");
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_assoc($result))
		{
			$admin = $row["adminemail"];
			$barbershopName = $row["name"];
			$id = $row["barbershopId"];
			$adminname = "{$row["firstName"]} {$row["lastName"]}";
		} // to check if professional is an administrator
		if ($admin == $_SESSION["email"])
		{
			$query2 = mysqli_query($conn, "select barbershopId, firstName, lastName, email, notificationDecline, accepted from professional where md5(email) = '" . mysqli_real_escape_string($conn, $_POST["user"]) . "'");
			if (mysqli_num_rows($query2) > 0)
			{
				while ($row = mysqli_fetch_assoc($query2))
				{
					if ($row["barbershopId"] == $id && $row["accepted"] != 1) // if professional hasn't been accepted to the admin's barbershop
					{
						$username = "{$row["firstName"]} {$row["lastName"]}";
						$query = mysqli_query($conn, "update professional set barbershopId=null, accepted='0' where md5(email)='" . mysqli_real_escape_string($conn, $_POST["user"]) . "'");
						if ($row["notificationDecline"] == 1)
							send_notification("Request Declined","noreply@buzzapp.net",$row["email"],"Dear $username,\n$adminname declined your request to join $barbershopName on BuzzApp. Sorry.");
						break;
					}
				}
			}
		}
	}
	mysqli_close($conn);
}
?>