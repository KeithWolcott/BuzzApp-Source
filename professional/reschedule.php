<?php
session_start();
require('../sendgrid-php/sendgrid-php.php');
include '../config.php';
include '../functions.php';
$changedAppointment = false;
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2 && $_POST && isset($_SESSION["scheduling"]) && isset($_SESSION["reason"]) && strlen($_SESSION["reason"]) < 500 && isset($_SESSION["month"]) && isset($_SESSION["day"]) && isset($_SESSION["year"]) && $conn)
{
	// All of this is to validate the date put in
	$month2 = $_SESSION["month"];
	if (substr($month2,0,1)=="0")
		$month2 = substr($month2,1);
	$day2 = $_SESSION["day"];
	if (substr($day2,0,1)=="0")
		$day2 = substr($day2,1);
	$now = fixtime(new DateTime("now"));
	if (is_numeric($_SESSION["year"]) && $_SESSION["year"] >= $now->format("Y") && is_numeric($month2) && $month2 > 0 && $month2 < 12)
	{
		$februraydays = (is_leap_year($_SESSION["year"]) ? 29 : 28);
		$months = array(0=>31,1=>$februraydays,2=>31,3=>31,4=>31,5=>30,6=>31,7=>31,8=>30,9=>31,10=>30,11=>31);
		if (is_numeric($day2) && $day2 > 0 && $day2 <= $months[$month2])
		{
			$date1 = new DateTime("{$_SESSION["year"]}-{$_SESSION["month"]}-{$_SESSION["day"]} {$_POST["time"]}");
			if (!isPast($date1))
			{
				$query2 = mysqli_query($conn, "select clientEmail, professionalEmail, client.firstName clientFirstName, client.lastName clientLastName, professional.firstName professionalFirstName, professional.lastName professionalLastName, notificationProfessionalReschedule, notificationRemindReschedule from scheduling inner join client on scheduling.clientEmail = client.email inner join professional on scheduling.professionalEmail = professional.email where md5(scheduling.id) = '" . mysqli_real_escape_string($conn, $_SESSION["scheduling"]) . "'");
				if (mysqli_num_rows($query2) > 0)
				{
					while($row=mysqli_fetch_assoc($query2))
					{
						$email = $row["professionalEmail"];
						if ($email == mysqli_real_escape_string($conn, $_SESSION["email"]))
						{
							$professionalName = "{$row["professionalFirstName"]} {$row["professionalLastName"]}";
							$clientName = "{$row["clientFirstName"]} {$row["clientLastName"]}";
							$query = mysqli_query($conn, "update scheduling set year='" . mysqli_real_escape_string($conn, $_SESSION["year"]) . "', month='" . mysqli_real_escape_string($conn, $_SESSION["month"]) . "', day='" . mysqli_real_escape_string($conn, $_SESSION["day"]) . "', timestart='" . mysqli_real_escape_string($conn, $_POST["time"]) . "', cancelled='0', reason='" . htmlentities($_SESSION["reason"],ENT_QUOTES) . "', remind='1', byprofessional='1' where md5(id) = '" . mysqli_real_escape_string($conn, $_SESSION["scheduling"]) . "'");
							if ($query)
							{
								$changedAppointment = true;
								if ($row["notificationProfessionalReschedule"] == 1)
									send_notification("Rescheduled Appointment","noreply@buzzapp.net",$row["clientEmail"],"Dear $clientName,\n$professionalName rescheduled an appointment with you to " . $date1->format("F d, Y") . " at {$_POST["time"]}. The reason provided is: {$_SESSION["reason"]}\nIf you need to change this, <a href=\"http://buzapp.herokuapp.com/\">change it here.</a>");
								if ($row["notificationRemindReschedule"] == 1)
									send_notification("Reminder","noreply@buzzapp.net",$_SESSION["email"],"Dear $professionalName,\nThis is to remind you that you rescheduled your appointment with $clientName to " . $date1->format("F d, Y") . " at {$_POST["time"]}.\nYour reason was: {$_SESSION["reason"]}");
							}
							break;
						}
					}
				}
			}
		}
	}
}
if ($changedAppointment)
	header("Location: viewSchedule.php");
else
	header("Location: ../index.php");
?>