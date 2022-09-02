<?php
session_start();
include '../config.php';
include '../functions.php';
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 2)
	header("Location: ../index.php");
?>
<!DOCTYPE html>
<html>
<head>
<title>Confirm Appointments</title>
<?php include 'header.php'; ?>
</head>
<body><?php include 'navbar.php'; ?>
<div class="center"><h1>Confirm Appointments</h1>
<?php
$query = mysqli_query($conn, "select scheduling.id, firstName, lastName, profilePicture, scheduling.year, scheduling.month, scheduling.day, services.name, timestart, duration from scheduling inner join client on client.email = scheduling.clientEmail inner join services on scheduling.serviceId = services.id where confirmed = '0' and cancelled = '0' and professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
if (mysqli_num_rows($query) <= 0)
{
	echo "<p>No appointments to confirm.</p>";
}
else
{
	echo "<div class=\"reviews\">";
	while($row = mysqli_fetch_assoc($query))
	{
		$date2 = new DateTime("{$row["day"]}-{$row["month"]}-{$row["year"]} {$row["timestart"]}");
		if (!isPast($date2))
		{
			echo "<div class=\"reviewContents\" name=\"contents\"><div style=\"float:left;\">";
			$id2 = md5($row["id"]);
			if (is_null($row["profilePicture"]))
				echo "<img src=\"images/defaultavatar.png\" alt=\"No Profile Picture\">";
			else
				echo "<img src=\"{$row["profilePicture"]}\" class=\"profilePicture\" alt=\"Profile Picture\">";
			echo "<br>{$row["firstName"]} {$row["lastName"]}</div><form method=\"post\" onsubmit=\"return confirmCancellation(this);\"><input type=\"hidden\" name=\"id\" value=\"$id2\" /><ul style=\"list-style-type:none;\"><li><strong>{$row["firstName"]} {$row["lastName"]}</strong> has an appointment</li>
			<li>" . $date2->format("F j, Y") . "</li>
			<li>{$row["timestart"]} - " . findend($row["timestart"],$row["duration"]) . "</li>
			<li>Ordered: {$row["name"]}</li><br>
			<li><input type=\"radio\" name=\"confirm\" value=\"confirm\" id=\"confirm$id2\" onclick=\"showReason(this)\" checked /><label for=\"confirm$id2\">Confirm Appointment</label></li>
			<li><input type=\"radio\" name=\"confirm\" value=\"decline\" id=\"decline$id2\" onclick=\"showReason(this)\" /><label for=\"decline$id2\">Decline Appointment</label></li></ul>
			<div name=\"declineform\" style=\"display:none;\">Reason for Cancellation:<br><textarea name=\"reason\" style=\"width:300px;\" oninput=\"checkLength(this);\"></textarea></div><input type=\"submit\" value=\"Submit\" name=\"submitForm\" /></form></div>";
		}
	}
	echo "</div>";
}
?></div>
<script>
function checkLength(text)
{
	var submitButton = $(text).parent().parent().find("input[name='submitForm']");
	var id = $(text).parent().parent().find("input[name='id']");
	var radio = $("#decline" + id.val());
	if ($(radio).is(":checked"))
	{
		if ($(text).val().length > 0 && $(text).val().length <= 500)
		{
			$(submitButton).prop("disabled", false);
		}
		else
		{
			$(submitButton).prop("disabled", true);
		}
	}
	else
	{
		$(submitButton).prop("disabled", false);
	}
}
</script>
</body>
</html>