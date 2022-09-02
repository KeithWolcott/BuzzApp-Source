<?php
session_start();
require('../sendgrid-php/sendgrid-php.php');
include '../config.php';
include '../functions.php';
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2 && $_POST && isset($_POST["id"]) && isset($_POST["confirm"]))
{
	$valid = true;
	if ($_POST["confirm"] == "decline" && (!isset($_POST["reason"]) || strlen($_POST["reason"]) == 0 || strlen($_POST["reason"]) > 500))
	{
		$valid = false;
	}
	if ($valid)
	{
		$query2 = mysqli_query($conn, "select professionalEmail, clientEmail, client.firstName clientFirstName, client.lastName clientLastName, professional.firstName professionalFirstName, professional.lastName professionalLastName, client.notificationAccept, client.notificationDecline, scheduling.year, scheduling.month, scheduling.day, scheduling.timestart, services.name from scheduling inner join client on scheduling.clientEmail = client.email inner join professional on scheduling.professionalEmail = professional.email inner join services on scheduling.serviceId = services.id where md5(scheduling.id) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'");
		if (mysqli_num_rows($query2) > 0)
		{
			while($row=mysqli_fetch_assoc($query2))
			{
				$email = $row["professionalEmail"];
				if ($email == mysqli_real_escape_string($conn, $_SESSION["email"]))
				{
					$clientName = "{$row["clientFirstName"]} {$row["clientLastName"]}";
					$professionalName = "{$row["professionalFirstName"]} {$row["professionalLastName"]}";
					$dateofappointment = new DateTime("{$row["year"]}-{$row["month"]}-{$row["day"]} {$row["timestart"]}");
					if ($_POST["confirm"] == "confirm")
					{
						$query = mysqli_query($conn, "update scheduling set confirmed='1', cancelled='0', byprofessional='0', remind='0' where md5(id) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'");
						if ($row["notificationAccept"] == 1)
							send_notification("Appointment Confirmed","noreply@buzzapp.net",$row["clientEmail"],"Dear $clientName,\n$professionalName confirmed your appointment on " . $dateofappointment->format("F j, Y h:i A") . " for {$row["name"]}!");
					}
					elseif ($_POST["confirm"] == "decline")
					{
						$query = mysqli_query($conn, "update scheduling set confirmed='0', cancelled='1', byprofessional='1', reason='" . htmlentities($_POST["reason"]) . "', remind='1' where md5(id) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'");
						if ($row["notificationDecline"] == 1)
							send_notification("Appointment Declined","noreply@buzzapp.net",$row["clientEmail"],"Dear $clientName,\n$professionalName declined your appointment on " . $dateofappointment->format("F j, Y h:i A") . " for {$row["name"]}.\nThe reason provided is: {$_POST["reason"]}.");
					}
					break;
				}
			}
		}
	}
}
?>