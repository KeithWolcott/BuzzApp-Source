<?php
session_start();
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]))
  header("Location: login.php");
include 'config.php';
include 'functions.php';
if ($_POST)
{
	if ($_SESSION["accountType"] == 1)
	{
		mysqli_query($conn, "update client set notificationMadeAppointment = " . fixCheckBox("remindMadeAppointment") . ", notificationProfessionalReschedule = " . fixCheckBox("remindProfessionalReschedule") . ", notificationReschedule = " . fixCheckBox("remindReschedule") . ", notificationAccept = " . fixCheckBox("remindAccept") . ", notificationDecline = " . fixCheckBox("remindDecline") . " where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
	}
	elseif ($_SESSION["accountType"] == 2)
	{
		$query = mysqli_query($conn, "select adminemail from professional left join barbershop on professional.barbershopId = barbershop.barbershopId where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
		if (mysqli_num_rows($query) == 1)
		{
			$row = mysqli_fetch_assoc($query);
			$adminemail = $row["adminemail"];
			if ($adminemail == mysqli_real_escape_string($conn, $_SESSION["email"])) // admin
			{
				mysqli_query($conn, "update professional set notificationJoinRequest = " . fixCheckBox("remindJoinRequest") . ", notificationClientRequest = " . fixCheckBox("remindClientRequest") . ", notificationClientReschedule = " . fixCheckBox("remindClientReschedule") . ", notificationRemindReschedule = " . fixCheckBox("remindReschedule") . " where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
			}
			else
			{
				mysqli_query($conn, "update professional set notificationAccept = " . fixCheckBox("remindAccept") . ", notificationDecline = " . fixCheckBox("remindDecline") . ", notificationClientRequest = " . fixCheckBox("remindClientRequest") . ", notificationClientReschedule = " . fixCheckBox("remindClientReschedule") . ", notificationRemindReschedule = " . fixCheckBox("remindReschedule") . ", notificationDeleteMessage = " . fixCheckBox("remindDeleteMessage") . " where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
			}
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="registersheet.css" />
<title>Configure Notifications</title>
</head>
<body><?php include 'navbar.php'; ?><div class="container">
<form id="contact" method="post"><h3>Configure Notifications</h3>
<?php
if (!$conn)
	echo "<p>Unable to do this.</p>";
else
{
	if ($_SESSION["accountType"] == 1)
	{
		$query = "select notificationMadeAppointment, notificationProfessionalReschedule, notificationReschedule, notificationAccept, 	notificationDecline from client where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'";
		$result = mysqli_query($conn, $query);
		if (mysqli_num_rows($result) == 1)
		{
			$row = mysqli_fetch_assoc($result);
			echo "<ul><li><input type=\"checkbox\" id=\"remindMadeAppointment\" value=\"1\" name=\"remindMadeAppointment\" ";
			if ($row["notificationMadeAppointment"] == 1)
				echo "checked ";
			echo "/><label for=\"remindMadeAppointment\"> Remind You When You Make An Appointment</label></li>
			<li><input type=\"checkbox\" id=\"remindAccept\" value=\"1\" name=\"remindAccept\" ";
			if ($row["notificationAccept"] == 1)
				echo "checked ";
			echo "/><label for=\"remindAccept\"> Notify You When Professional Accepts an Appointment</label></li>
			<li><input type=\"checkbox\" id=\"remindDecline\" value=\"1\" name=\"remindDecline\" ";
			if ($row["notificationDecline"] == 1)
				echo "checked ";
			echo "/><label for=\"remindDecline\"> Notify You When Professional Declines an Appointment</label></li>
			<li><input type=\"checkbox\" id=\"remindReschedule\" value=\"1\" name=\"remindReschedule\" ";
			if ($row["notificationReschedule"] == 1)
				echo "checked ";
			echo "/><label for=\"remindReschedule\"> Remind You When You Reschedule an Appointment</label></li>
			<li><input type=\"checkbox\" id=\"remindProfessionalReschedule\" value=\"1\" name=\"remindProfessionalReschedule\" ";
			if ($row["notificationProfessionalReschedule"] == 1)
				echo "checked ";
			echo "/><label for=\"remindProfessionalReschedule\"> Notify You When Professional Reschedules an Appointment</label></li></ul><br>
			<fieldset><button type=\"submit\" name=\"submit\">Update</button></fieldset>";
		}
	}
	elseif ($_SESSION["accountType"] == 2)
	{
		$query = "select notificationAccept, notificationDecline, notificationJoinRequest, notificationClientReschedule, notificationRemindReschedule, notificationClientRequest, notificationDeleteMessage, adminemail, email from professional left join barbershop on professional.barbershopId = barbershop.barbershopId where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'";
		$result = mysqli_query($conn, $query);
		if (mysqli_num_rows($result) == 1)
		{
			echo "<ul>";
			$row = mysqli_fetch_assoc($result);
			if ($row["adminemail"] == $row["email"]) // admin
			{
				echo "<li><input type=\"checkbox\" id=\"remindJoinRequest\" value=\"1\" name=\"remindJoinRequest\" ";
			if ($row["notificationJoinRequest"] == 1)
				echo "checked ";
			echo "/><label for=\"remindJoinRequest\"> Notify You When A Professional Sends You a Request</label></li>";
			}
			else
			{
				echo "<li><input type=\"checkbox\" id=\"remindAccept\" value=\"1\" name=\"remindAccept\" ";
			if ($row["notificationAccept"] == 1)
				echo "checked ";
			echo "/><label for=\"remindAccept\"> Notify You When An Administrator Accepts Your Request</label></li>
			<li><input type=\"checkbox\" id=\"remindDecline\" value=\"1\" name=\"remindDecline\" ";
			if ($row["notificationDecline"] == 1)
				echo "checked ";
			echo "/><label for=\"remindDecline\"> Notify You When An Administrator Declines Your Request</label></li>
			<li><input type=\"checkbox\" id=\"remindDeleteMessage\" value=\"1\" name=\"remindDeleteMessage\" ";
			if ($row["notificationDeleteMessage"] == 1)
				echo "checked ";
			echo "/><label for=\"remindDeleteMessage\"> Notify You When An Administrator Deletes a Message You Post on Notification Board</label></li>";
			}
			echo "<li><input type=\"checkbox\" id=\"remindClientRequest\" value=\"1\" name=\"remindClientRequest\" ";
			if ($row["notificationClientRequest"] == 1)
				echo "checked ";
			echo "/><label for=\"remindClientRequest\"> Notify You When A Client Sends You a Request</label></li>
			<li><input type=\"checkbox\" id=\"remindClientReschedule\" value=\"1\" name=\"remindClientReschedule\" ";
			if ($row["notificationClientReschedule"] == 1)
				echo "checked ";
			echo "/><label for=\"remindClientReschedule\"> Notify You When a Client Reschedules an Appointment</label></li>
			<li><input type=\"checkbox\" id=\"remindReschedule\" value=\"1\" name=\"remindReschedule\" ";
			if ($row["notificationRemindReschedule"] == 1)
				echo "checked ";
			echo "/><label for=\"remindReschedule\"> Remind You When You Reschedule an Appointment</label></li></ul><br>
			<fieldset><button type=\"submit\" name=\"submit\">Update</button></fieldset>";
		}
	}
}
?>
</form></div>
</body>
</html>