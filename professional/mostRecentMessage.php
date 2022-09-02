<?php
include '../functions.php';
session_start();
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2)
{
	include '../config.php';
	if (!$conn)
		die("Unable to connect to database.");
	$result = mysqli_query($conn, "SELECT postid, message, notificationBoard.year, notificationBoard.month, notificationBoard.day, notificationBoard.time, professionalEmail, firstName, lastName FROM notificationBoard, professional where email in (select email from barbershop, professional where professional.barbershopId in (select professional.barbershopId from professional, barbershop where professional.email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "')) and email = professionalEmail order by postid desc limit 1");
	if (mysqli_num_rows($result) <= 0)
		echo "<p>No posts on notification board yet.</p>";
	else
	{
		$result2 = mysqli_query($conn, "select adminemail from barbershop where barbershopId in (select barbershopId from professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "')");
		if (mysqli_num_rows($result2) > 0)
		{
			while($row = mysqli_fetch_assoc($result2)) {
				$admin = $row["adminemail"];
			}
		}
		while($row = mysqli_fetch_assoc($result)) {
			$date2 = new DateTime("{$row["year"]}-{$row["month"]}-{$row["day"]} {$row["time"]}");
			$datestr = datedifference($date2);
			echo "<div class=\"messagePost\"><form><input type=\"hidden\" name=\"post\" value=\"{$row["postid"]}\" /><p>{$row["firstName"]} {$row["lastName"]}</p><div class=\"center\"><p>{$row["message"]}</p></div><p><span class=\"timeago\" title=\"{$row["month"]}-{$row["day"]}-{$row["year"]} {$row["time"]}\">$datestr</span>";
			if ($row["professionalEmail"] == $_SESSION["email"] || $admin == $_SESSION["email"])
				echo "<span class=\"deleteButton\"><input type=\"button\" onclick=\"deleteMessage(this)\" value=\"Delete Message\" /></span>";
		}
		echo "</form></div><p><a href=\"manageNotificationBoard.php\">Manage this barbershop's notification board</a></p></div>";
	}
	mysqli_close($conn);
}
?>