<?php
session_start();
require('../sendgrid-php/sendgrid-php.php');
include '../config.php';
include '../functions.php';
if ($_POST && isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2)
{
	$result = mysqli_query($conn, "select * from professionalhours where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
	if (mysqli_num_rows($result) > 0)
	{
		// All of this is to validate the date put in
		$month2 = $_POST["month"];
		if (substr($month2,0,1)=="0")
			$month2 = substr($month2,1);
		$day2 = $_POST["day"];
		if (substr($day2,0,1)=="0")
			$day2 = substr($day2,1);
		$now = fixtime(new DateTime("now"));
		if (is_numeric($_POST["year"]) && $_POST["year"] >= $now->format("Y") && is_numeric($month2) && $month2 > 0 && $month2 < 12)
		{
			$februraydays = (is_leap_year($_POST["year"]) ? 29 : 28);
			$months = array(0=>31,1=>$februraydays,2=>31,3=>31,4=>31,5=>30,6=>31,7=>31,8=>30,9=>31,10=>30,11=>31);
			if (is_numeric($_POST["day"]) && $_POST["day"] > 0 && $_POST["day"] <= $months[$month2])
			{
				$date1 = new DateTime("{$_POST["year"]}-$month2-$day2");
				if (!isPast($date1))
				{
					$result = mysqli_query($conn, "select year, month, day from professionaloff where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and year='{$_POST["year"]}' and month='{$_POST["month"]}' and day='{$_POST["day"]}'");
					$valid = true;
					$opensplit = explode(" ",$_POST["openhour"]);
					$closesplit = explode(" ",$_POST["closehour"]);
					$opentime = $opensplit[0];
					$openampm = $opensplit[1];
					$closetime = $closesplit[0];
					$closeampm = $closesplit[1];
					$date1 = false;
					$date2 = false;
					if (!isset($_POST["closed"]))
					{
						if (preg_match('/^[0-9:]+$/',$opentime) && is_numeric(str_replace(":","",$opentime)))
							$date1 = checktime($opentime,$openampm);
						else
							$valid = false;
						if (preg_match('/^[0-9:]+$/',$closetime) && is_numeric(str_replace(":","",$opentime)))
							$date2 = checktime($closetime,$closeampm);
						else
							$valid = false;
						if ($date1 != false && $date2 != false)
						{
							if ($date1 >= $date2)
								$valid = false;
						}
					}
					if ($valid)
					{
						if (mysqli_num_rows($result) <= 0)
						{
							if (isset($_POST["closed"])) // Entire day off
								$query = mysqli_query($conn,"insert into professionaloff(professionalEmail, year, month, day) values ('" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "', '{$_POST["year"]}', '{$_POST["month"]}', '{$_POST["day"]}')");
							else
							{
								$hour1 = $date1->format("h:i");
								$hour2 = $date2->format("h:i");
								if (substr($hour1,0,1) == "0")
									$hour1 = substr($hour1,1);
								if (substr($hour2,0,1) == "0")
									$hour2 = substr($hour2,1);
								$query = mysqli_query($conn,"insert into professionaloff(professionalEmail, year, month, day, start, end) values ('" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "', '{$_POST["year"]}', '{$_POST["month"]}', '{$_POST["day"]}', '$hour1 " . $date1->format("A") . "', '$hour2 " . $date2->format("A") . "')");
							}
						}
						else
						{
							if (isset($_POST["closed"]))
								$query = mysqli_query($conn,"update professionaloff set start=null, end=null where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and year='{$_POST["year"]}' and month='{$_POST["month"]}' and day='{$_POST["day"]}'");
							else
							{
								$hour1 = $date1->format("h:i");
								$hour2 = $date2->format("h:i");
								if (substr($hour1,0,1) == "0")
									$hour1 = substr($hour1,1);
								if (substr($hour2,0,1) == "0")
									$hour2 = substr($hour2,1);
								$query = mysqli_query($conn,"update professionaloff set start='$hour1 " . $date1->format("A") . "', end='$hour2 " . $date2->format("A") . "' where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and year='{$_POST["year"]}' and month='{$_POST["month"]}' and day='{$_POST["day"]}'");
							}
						}
						$namequery = mysqli_query($conn, "select firstName, lastName from professional where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
						if (mysqli_num_rows($namequery) == 1)
						{
							$row = mysqli_fetch_assoc($namequery);
							$professionalName = "{$row["firstName"]} {$row["lastName"]}";
							if (isset($_POST["closed"]))
							{
								$query = mysqli_query($conn, "select clientEmail, scheduling.day, scheduling.month, scheduling.year, scheduling.timestart, services.name, firstName, lastName from scheduling inner join client on scheduling.clientEmail = client.email inner join services on scheduling.serviceId = services.id where scheduling.year = '{$_POST["year"]}' and scheduling.month = '{$_POST["month"]}' and scheduling.day = '{$_POST["day"]}'");
								$thedate = new DateTime("{$_POST["year"]}-$month2-$day2");
								if (mysqli_num_rows($query) > 0)
								{
									while($row = mysqli_fetch_assoc($query))
									{
										$clientName = "{$row["firstName"]} {$row["lastName"]}";
										send_notification("Reschedule Needed","noreply@buzzapp.net",$row["clientEmail"],"Dear $clientName,\n$professionalName has taken " . $thedate->format("F d, Y") . " off. You have an appointment for {$row["name"]} at {$row["timestart"]} that day.\n<a href=\"http://buzapp.herokuapp.com\">Be sure to reschedule this appointment!</a>");
									}
								}
							}
							else
							{
								$hour1 = $date1->format("h:i");
								$hour2 = $date2->format("h:i");
								if (substr($hour1,0,1) == "0")
									$hour1 = substr($hour1,1);
								if (substr($hour2,0,1) == "0")
									$hour2 = substr($hour2,1);
								$time1 = "$hour1 " . $date1->format("A");
								$time2 = "$hour2 " . $date2->format("A");
								$thedate = new DateTime("{$_POST["year"]}-$month2-$day2");
								$query = mysqli_query($conn, "select clientEmail, scheduling.day, scheduling.month, scheduling.year, scheduling.timestart, services.name, firstName, lastName from scheduling inner join client on scheduling.clientEmail = client.email inner join services on scheduling.serviceId = services.id where scheduling.year = '{$_POST["year"]}' and scheduling.month = '{$_POST["month"]}' and scheduling.day = '{$_POST["day"]}'");
								if (mysqli_num_rows($query) > 0)
								{
									while($row = mysqli_fetch_assoc($query))
									{
										$timestart = new DateTime($row["timestart"]);
										$time1d = new DateTime($time1);
										$time2d = new DateTime($time2);
										if (($timestart > $time2d && $timestart > $time1d) || ($timestart < $time1d && $timestart < $time2d))
										{
											$clientName = "{$row["firstName"]} {$row["lastName"]}";
											send_notification("Reschedule Needed","noreply@buzzapp.net",$row["clientEmail"],"Dear $clientName,\nThe hours of $professionalName has changed for " . $thedate->format("F d, Y") . " to $time1 - $time2. You have an appointment for {$row["name"]} at {$row["timestart"]} that day.\n<a href=\"http://buzapp.herokuapp.com\">Be sure to reschedule this appointment!</a>");
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
	mysqli_close($conn);
}
?>