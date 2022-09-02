<?php
session_start();
if (!isset($_SESSION["email"]) || $_SESSION["accountType"] != 1)
	header("Location: ../login.php");
include '../config.php';
include '../functions.php';
?>
<html>
<head>
<?php include 'header.php'; ?>
<title>View Notification Board</title>
<body onload="loadMessages();">
<?php include '../navbar.php';
if (!$conn)
	die("<br>Connection failed: " . mysqli_connect_error());
if (!$_GET || !isset($_GET["id"]))
	echo "<br><p>No results.</p>";
else
{
	$result = mysqli_query($conn, "select name from barbershop where barbershopId = '" . mysqli_real_escape_string($conn, $_GET["id"]) . "'");
	if (mysqli_num_rows($result) <= 0) {
		echo "<br><p>Invalid barbershop</p>";
	}
	else
	{
		while($row = mysqli_fetch_assoc($result)) {
			echo "<h2>Notification Board for {$row["name"]}</h2>";
			$result2 = mysqli_query($conn, "SELECT message, notificationBoard.year, notificationBoard.month, notificationBoard.day, notificationBoard.time, upcomingonly, firstName, lastName FROM notificationBoard, professional where email in (select email from barbershop, professional where professional.barbershopId = '" . mysqli_real_escape_string($conn, $_GET["id"]) . "') and email = professionalEmail and (paid = '1' or (paid = '0' and DATEDIFF(NOW(), STR_TO_DATE(concat(professional.day,'/',professional.month,'/',professional.year),'%d/%m/%Y')) < 30)) order by postid desc");
			if (mysqli_num_rows($result2) <= 0)
				echo "<p>No posts on notification board yet.</p>";
			else
			{
				$result3 = mysqli_query($conn, "select * from scheduling inner join services on scheduling.serviceId = services.id where clientEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
				$hasAppointment = false;
				if (mysqli_num_rows($result3) > 0)
				{
					while ($row = mysqli_fetch_assoc($result3))
					{
						$year = $row["year"];
						$month = $row["month"];
						$day = $row["day"];
						$timestart = $row["timestart"];
						$timeend = findend($row["timestart"],$row["duration"]);
						$date2 = new DateTime("$year-$month-$day $timeend");
						if (!isPast($date2) && $row["confirmed"] == 1 && $row["cancelled"] == 0)
						{
							$hasAppointment = true;
							break;
						}
					}
				}
				while($row = mysqli_fetch_assoc($result2)) {
					$date2 = new DateTime("{$row["year"]}-{$row["month"]}-{$row["day"]} {$row["time"]}");
					$datestr = datedifference($date2);
					if ($row["upcomingonly"] == 0 || ($row["upcomingonly"] == 1 && $hasAppointment))
						echo "<div class=\"messagePost\"><p>{$row["firstName"]} {$row["lastName"]}</p><div class=\"center\"><p>{$row["message"]}</p></div><p><span class=\"timeago\" title=\"=\"{$row["month"]}-{$row["day"]}-{$row["year"]} {$row["time"]}\">$datestr</span></p></div><br>";
				}
			}
		}
	}
}
mysqli_close($conn);
?>
</body>
</html>