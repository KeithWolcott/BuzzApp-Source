<?php
session_start();
include '../config.php';
include '../functions.php';
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 1)
	header("Location: ../index.php");
?>
<!DOCTYPE html>
<html>
<head>
<title>Change Appointments</title>
<?php include 'header.php'; ?>
<script>
 $(function() {
            $('.date-picker').datepicker( {
            changeMonth: true,
            changeYear: true,
			minDate: '1D',
            showButtonPanel: true,
            dateFormat: 'MM dd, yy',
            onClose: function(dateText, inst) {
                $(this).datepicker('setDate', $(this).datepicker("getDate"));
            }
            });
 });
</script>
</head>
<body><?php include 'navbar.php'; ?>
<div class="center">
<h1>Change Appointments</h1>
</div><hr>
<?php
$now = fixtime(new DateTime("now"));
$query = mysqli_query($conn, "select scheduling.id, professionalEmail, firstName, lastName, scheduling.year, scheduling.month, scheduling.day, timestart, duration, services.name from scheduling inner join professional on scheduling.professionalEmail = professional.email inner join services on scheduling.serviceId = services.id where clientEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and confirmed='1' and not (scheduling.day = '" . $now->format("d") . "' and scheduling.month = '" . $now->format("m") . "' and scheduling.year = '" . $now->format("Y") . "')");
if (mysqli_num_rows($query) <= 0)
	echo "<p>Ah. You have no confirmed appointments.</p>";
else
{
	echo "<div class=\"reviews\">";
	while($row = mysqli_fetch_assoc($query))
	{
		$id2 = md5($row["id"]);
		$newdate = new DateTime("{$row["year"]}-{$row["month"]}-{$row["day"]}");
		echo "<div><span class=\"reviewLink\" onclick=\"revealAppointment(this)\">{$row["firstName"]} {$row["lastName"]} <span style=\"float:right;\">" . $newdate->format("F j, Y") . " {$row["timestart"]} - " . findend($row["timestart"],$row["duration"]) . "</span></span><div class=\"reviewContents\" name=\"contents\" style=\"display:none;\"><form method=\"post\" onsubmit=\"return confirmDeletion(this);\"><input type=\"hidden\" name=\"id\" value=\"$id2\" /><p>{$row["timestart"]} - " . findend($row["timestart"],$row["duration"]) . "<br>Ordered: {$row["name"]}</p>Reason:<br><textarea name=\"reason\" style=\"width:300px;\" required></textarea><ul>
		<li><input type=\"radio\" id=\"reschedule$id2\" name=\"cancel\" value=\"reschedule\" onchange=\"enableChange(this)\" /><label for=\"reschedule$id2\">Reschedule Appointment</label></li>
		<li><input type=\"radio\" id=\"cancel$id2\" name=\"cancel\" value=\"cancel\" onchange=\"enableChange(this)\" /><label for=\"cancel$id2\">Cancel Appointment</label></li>
		</ul><div name=\"chooseDay\" style=\"display:none;\">Day: <input name=\"date\" id=\"date$id2\" class=\"date-picker\" value=\"";
		$newdate->add(new DateInterval("P1D"));
		echo $newdate->format("F d, Y") . "\" readonly /><br><br></div><input type=\"submit\" value=\"Change Appointment\" name=\"submitForm\" disabled /></form></div></div>";
	}
	echo "</div>";
}
?>
</body>
</html>