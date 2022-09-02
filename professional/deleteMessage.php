<?php
session_start();
if ($_POST)
{
	if (!isset($_SESSION["email"]) || $_SESSION["accountType"] != 2)
		header("Location: ../login.php");
	require('../sendgrid-php/sendgrid-php.php');
	include '../config.php';
	include '../functions.php';
	if ($conn)
	{
		$result = mysqli_query($conn, "select postid, professionalEmail, message, notificationBoard.year, notificationBoard.day, notificationBoard.month, notificationBoard.time, firstName, lastName, notificationDeleteMessage from notificationBoard, professional where postid='{$_POST["post"]}' and professionalEmail in (select email from barbershop, professional where professional.barbershopId in (select professional.barbershopId from professional, barbershop where professional.email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "')) and professional.email = professionalEmail");
		if (mysqli_num_rows($result) > 0)
		{
			$result2 = mysqli_query($conn, "select adminemail, firstName, lastName from barbershop left join professional on barbershop.barbershopId = professional.barbershopId where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
			if (mysqli_num_rows($result2) > 0)
			{
				while($row = mysqli_fetch_assoc($result2)) {
					$admin = $row["adminemail"];
					$adminname = "{$row["firstName"]} {$row["lastName"]}";
				}
				while($row = mysqli_fetch_assoc($result)) {
					$postid = $row["postid"];
					$email = $row["professionalEmail"];
					$professionalName = "{$row["firstName"]} {$row["lastName"]}";
					$dateofmessage = new DateTime("{$row["year"]}-{$row["month"]}-{$row["day"]} {$row["time"]}");
					if ($email == $_SESSION["email"] || $admin == $_SESSION["email"])
					{
						$result3 = mysqli_query($conn, "delete from notificationBoard where postid=$postid");
						if ($admin == mysqli_real_escape_string($_SESSION["email"]) && $email != mysqli_real_escape_string($_SESSION["email"]) && $row["notificationDeleteMessage"] == 1)
							send_notification("Message Deleted","noreply@buzzapp.net",$email,"Dear $professionalName,\n$adminname deleted your message from " . $dateofmessage->format("F d, Y h:i A") . ".\nContents: {$row["message"]}");
					}
				}
			}
		}
		mysqli_close($conn);
	}
}
?>