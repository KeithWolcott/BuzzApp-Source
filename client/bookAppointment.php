<?php
session_start();
require('../sendgrid-php/sendgrid-php.php');
include '../config.php';
include '../functions.php';
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 1 && $_POST && isset($_SESSION["day"]) && isset($_SESSION["month"]) && isset($_SESSION["year"]) && isset($_POST["time"]) && isset($_SESSION["scheduling"]) && isset($_POST["professional"]) && $conn)
{
	// All of this is to validate the date put in
	$now = fixtime(new DateTime("now"));
	$month2 = $_SESSION["month"];
	if (substr($month2,0,1)=="0")
		$month2 = substr($month2,1);
	$day2 = $_SESSION["day"];
	if (substr($day2,0,1)=="0")
		$day2 = substr($day2,1);
	if (is_numeric($_SESSION["year"]) && $_SESSION["year"] >= $now->format("Y") && is_numeric($month2) && $month2 > 0 && $month2 < 12)
	{
		$februraydays = (is_leap_year($_SESSION["year"]) ? 29 : 28);
		$months = array(0=>31,1=>$februraydays,2=>31,3=>31,4=>31,5=>30,6=>31,7=>31,8=>30,9=>31,10=>30,11=>31);
		if (is_numeric($day2) && $day2 > 0 && $day2 <= $months[$month2])
		{
			$date1 = new DateTime("{$_SESSION["year"]}-{$_SESSION["month"]}-{$_SESSION["day"]}");
			if (!isPast($date1))
			{
				// Now find out if the professional and service are valid;
				$query1 = mysqli_query($conn, "select firstName, lastName, email, beingdeleted, notificationClientRequest from professional where md5(email) = '" . mysqli_real_escape_string($conn, $_POST["professional"]) . "'");
				$email = "";
				$id = 0;
				$beingdeleted = 0;
				$notifyProfessional = 0;
				if (mysqli_num_rows($query1) > 0)
				{
					while($row=mysqli_fetch_assoc($query1))
					{
						$email = $row["email"];
						$professionalName = "{$row["firstName"]} {$row["lastName"]}";
						$beingdeleted = $row["beingdeleted"];
						$notifyProfessional = $row["notificationClientRequest"];
					}
				}
				if ($beingdeleted == 0)
				{
					$query2 = mysqli_query($conn, "select id, name from services where md5(id) = '" . mysqli_real_escape_string($conn, $_SESSION["scheduling"]) . "'");
					if (mysqli_num_rows($query2) > 0)
					{
						while($row=mysqli_fetch_assoc($query2))
						{
							$id = $row["id"];
							$servicename = $row["name"];
						}
					}
					if ($email != "" && $id != 0)
					{
						// Finally, avoid duplicate.
						$query3 = mysqli_query($conn, "select * from scheduling where clientEmail='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and professionalEmail='" . mysqli_real_escape_string($conn, $email) . "' and year='" . mysqli_real_escape_string($conn, $_SESSION["year"]) . "' and month='" . mysqli_real_escape_string($conn, $_SESSION["month"]) . "' and day='" . mysqli_real_escape_string($conn, $_SESSION["day"]) . "' and timestart='" . mysqli_real_escape_string($conn, $_POST["time"]) . "' and serviceId='" . mysqli_real_escape_string($conn, $id) . "'");
						if (mysqli_num_rows($query3) <= 0)
						{
							$namequery = mysqli_query($conn, "select firstName, lastName, notificationMadeAppointment from client where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
							if (mysqli_num_rows($namequery) > 0)
							{
								$row = mysqli_fetch_assoc($namequery);
								$username = "{$row["firstName"]} {$row["lastName"]}";
								$query4 = mysqli_query($conn, "insert into scheduling(clientEmail, professionalEmail, year, month, day, timestart, serviceId, confirmed, madereview, remindaboutreview) values ('" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "', '" . mysqli_real_escape_string($conn, $email) . "', '" . mysqli_real_escape_string($conn, $_SESSION["year"]) . "', '" . mysqli_real_escape_string($conn, $_SESSION["month"]) . "', '" . mysqli_real_escape_string($conn, $_SESSION["day"]) . "', '" . mysqli_real_escape_string($conn, $_POST["time"]) . "', '" . mysqli_real_escape_string($conn, $id) . "', '0', '0', '1')");
								if ($notifyProfessional == 1)
									send_notification("New Appointment","noreply@buzzapp.net",$email,"Dear $professionalName,\n$username wants to make an appointment with you on BuzzApp. It's on " . $date1->format("F d, Y") . " at {$_POST["time"]} for $servicename.\n<a href=\"http://buzapp.herokuapp.com/\">Confirm it here.</a>");
								if ($row["notificationMadeAppointment"] == 1)
									send_notification("Receipt","noreply@buzzapp.net",$_SESSION["email"],"Dear $username,\nThis is to show that you sent a request to $professionalName for an appointment on " . $date1->format("F d, Y") . " at {$_POST["time"]} for $servicename.");
							}
						}
					}
				}
			}
		}
	}
}
header("Location: ../index.php");
?>