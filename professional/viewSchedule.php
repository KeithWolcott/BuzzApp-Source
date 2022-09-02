<?php
session_start();
include '../config.php';
include '../functions.php';
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 2)
	header("Location: ../index.php");
$result = mysqli_query($conn, "select name from barbershop where barbershopId in (select barbershopId from professional where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "')");
if (mysqli_num_rows($result) > 0)
{
	while($row = mysqli_fetch_assoc($result))
	{
		$barbershopname = $row["name"];
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Your Schedule</title>
<?php include 'header.php';
echo "\r\n<script>\r\n";
$valid_dates = array();
$newquery = mysqli_query($conn, "select day, month, year from scheduling where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and cancelled='0' and confirmed='1' order by STR_TO_DATE(concat(day,'/',month,'/',year),'%d/%m/%Y') asc");
if (mysqli_num_rows($newquery) > 0)
{
	echo "var availableDates = [\"";
	while($row = mysqli_fetch_assoc($newquery))
		array_push($valid_dates,fix_leading_zero($row["day"]) . "-" . fix_leading_zero($row["month"]) . "-{$row["year"]}");
	echo implode("\",\"",$valid_dates) . "\"];
function available(date) {
  dmy = date.getDate() + \"-\" + (date.getMonth()+1) + \"-\" + date.getFullYear();
  console.log(dmy+' : '+($.inArray(dmy, availableDates)));
  if ($.inArray(dmy, availableDates) != -1) {
    return [true, \"\",\"Available\"];
  } else {
    return [false,\"\",\"unAvailable\"];
  }
}";
}
?>
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
			$('.maindate').datepicker( {
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            dateFormat: 'MM dd, yy',
			<?php
			if (count($valid_dates) > 0)
				echo "beforeShowDay: available,\r\n            ";
			?>
onClose: function(dateText, inst) { 
                $(this).datepicker('setDate', $(this).datepicker("getDate"));
            }
            });
 });
</script>
</head>
<body><?php include 'navbar.php'; ?>
<div class="center">
<form method="get" onsubmit="return checkdate();">
<h1>Your Schedule for <?php echo $barbershopname; ?></h1>
<?php
if (count($valid_dates) > 0)
{
	$date = DateTime::createFromFormat("j-n-Y", $valid_dates[0]);
}
else
	$date = fixtime(new DateTime("now"));
echo "<input name=\"date\" id=\"month\" class=\"maindate\" value=\"" . $date->format("F d, Y") . "\" readonly />";
?> &nbsp; <input type="submit" value="Change Date" /> <span id="datedetail"></span>
</form></div><hr>
<?php
// Fix month and year.
$now = fixtime(new DateTime("now"));
if (count($valid_dates) > 0)
{
$month = $date->format("m");
$day = $date->format("d");
$year = $date->format("Y");
}
else
{
	$month = $now->format("m");
	$year = $now->format("Y");
	$day = $now->format("d");
}
$query = mysqli_query($conn, "select scheduling.id, clientEmail, firstName, lastName, profilePicture, timestart, duration, services.name from scheduling inner join client on scheduling.clientEmail = client.email inner join services on scheduling.serviceId = services.id where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and scheduling.year = '" . mysqli_real_escape_string($conn, $year) . "' and scheduling.month='" . mysqli_real_escape_string($conn, $month) . "' and scheduling.day='" . mysqli_real_escape_string($conn, $day) . "' and cancelled='0' and confirmed='1'");
if (mysqli_num_rows($query) <= 0)
	echo "<p>No appointments for this day.</p>";
else
{
	echo "<div class=\"reviews\">";
	$newdate = new DateTime("$year-$month-$day");
	$newdate->add(new DateInterval("P1D"));
	while($row = mysqli_fetch_assoc($query))
	{
		echo "<div><span class=\"reviewLink\" onclick=\"revealAppointment(this)\">{$row["firstName"]} {$row["lastName"]} <span style=\"float:right;\">{$row["timestart"]} - " . findend($row["timestart"],$row["duration"]) . "</span></span><div class=\"reviewContents\" name=\"contents\" style=\"display:none;\"><div class=\"pictureandname\">";
		if (is_null($row["profilePicture"]))
			echo "<img src=\"images/defaultavatar.png\" alt=\"No Profile Picture\">";
		else
			echo "<img src=\"{$row["profilePicture"]}\" class=\"profilePicture\" alt=\"Profile Picture\">";
		echo "<br>{$row["firstName"]} {$row["lastName"]}</div><p>{$row["timestart"]} - " . findend($row["timestart"],$row["duration"]) . "<br>Ordered: {$row["name"]}</p>";
		if (!($month == $now->format("m") && $day == $now->format("d") && $year == $now->format("Y")))
		{
			$id2 = md5($row["id"]);
			echo "<form method=\"post\" action=\"rescheduleAppointment.php\" onsubmit=\"return confirmReschedule(this);\"><input type=\"hidden\" name=\"id\" value=\"$id2\" />Reason for Reschedule:<br><textarea name=\"reason\" oninput=\"checkLength(this);\" style=\"width:300px;\" required></textarea><br><br>Day: <input name=\"date\" id=\"date$id2\" class=\"date-picker\" value=\"";
			$newdate->add(new DateInterval("P1D"));
			echo $newdate->format("F d, Y") . "\" readonly /><br><br><input type=\"submit\" value=\"Reschedule Appointment\" name=\"submitForm\" disabled /></form>";
		}
		echo "</div></div>";
	}
	echo "</div>";
}
?>
</body>
</html>