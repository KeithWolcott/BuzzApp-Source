<?php
session_start();
include '../config.php';
include '../functions.php';
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 2)
	header("Location: ../index.php");
$id = null;
$id2 = null;
$name = null;
if (isset($_POST["date"]) && ((!isset($_SESSION["scheduling"]) && isset($_POST["id"])) || isset($_SESSION["scheduling"])) && $conn)
{
	if (!isset($_SESSION["reason"]) && isset($_POST["reason"]))
		$_SESSION["reason"] = $_POST["reason"];
	if (!isset($_SESSION["scheduling"]) && isset($_POST["id"]))
		$_SESSION["scheduling"] = $_POST["id"];
	$date = DateTime::createFromFormat("F j, Y", $_POST["date"]);
	$month = $date->format("m");
	$day = $date->format("d");
	$year = $date->format("Y");
	$_SESSION["year"] = $year;
	$_SESSION["month"] = $month;
	$_SESSION["day"] = $day;
	$query1 = mysqli_query($conn, "select client.firstName, client.lastName, barbershop.barbershopId, serviceId from scheduling inner join client on scheduling.clientEmail = client.email inner join professional on scheduling.professionalEmail = professional.email inner join barbershop on professional.barbershopId = barbershop.barbershopId where md5(scheduling.id) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'");
	if (mysqli_num_rows($query1) > 0)
	{
		while($row=mysqli_fetch_assoc($query1))
		{
			$id = $row["barbershopId"];
			$id2 = $row["serviceId"];
			$name = $row["firstName"] . " " . $row["lastName"];
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Reschedule Appointment</title>
<?php include 'header.php'; ?>
<script>
 $(function() {
            $('.date-picker').datepicker( {
            changeMonth: true,
            changeYear: true,
			minDate: 0,
            showButtonPanel: true,
            dateFormat: 'MM dd, yy',
            onClose: function(dateText, inst) { 
                $(this).datepicker('setDate', $(this).datepicker("getDate"));
            }
            });
 });
</script>
<style>
fieldset
{
	margin-left:30px;
}
</style>
</head>
<body>
<?php
include '../navbar.php';
if (is_null($id))
	die("<p>Invalid barbershop.</p>");
?>
<h2>Reschedule Appointment for <?php echo $name; ?></h2>
<form method="post" onsubmit="return continuetoMakeAppointment(this);"><fieldset><legend>Change Day</legend>
<p><input name="date" id="date" class="date-picker" value="<?php echo $date->format("F d, Y"); ?>" readonly /><span id="datedetail"></span></p><input type="hidden" name="id" value="<?php
if (isset($_SESSION["scheduling"]))
echo $_SESSION["scheduling"];
else
echo $_POST["id"];
?>" /><input type="submit" value="Continue" id="continueappointment" /> <span id="appointmentstatus"></span></form></div>
</fieldset></form><hr>
<?php
// First, find the range.
$curdate = new DateTime("$year-$month-$day");
$now = fixtime(new DateTime("now"));
if ($curdate->format("Y-m-d") != $now->format("Y-m-d") && isPast($curdate))
	die("<p>This date is invalid.</p>");
$dayofweek = $curdate->format("w");
$query2 = mysqli_query($conn, "select open, close from closed where barbershopId = '" . mysqli_real_escape_string($conn, $id) . "' and year='" . mysqli_real_escape_string($conn, $year) . "' and month = '" . mysqli_real_escape_string($conn, $month) . "' and day = '" . mysqli_real_escape_string($conn, $day) . "'");
if (mysqli_num_rows($query2) > 0)
{
	while($row=mysqli_fetch_assoc($query2))
	{
		$open = $row["open"];
		$close = $row["close"];
	}
}
if (isset($open) && is_null($open))
	die("Sorry, this barbershop is closed on this day.");
$query3 = mysqli_query($conn, "select day, open, close from barbershophours where barbershopId = '" . mysqli_real_escape_string($conn, $id) . "' and day='$dayofweek'");
if (mysqli_num_rows($query3) <= 0)
	die("<p>Sorry, this barbershop is closed on " . $curdate->format("l") . "s.</p>");
while($row=mysqli_fetch_assoc($query3))
{
	// If open hasn't been defined yet, the barbershop is not in closed, so the barbershop is assumed to open as normal. If it is in closed, though, use the hours in closed.
	if (!isset($open))
		$open = $row["open"];
	if (!isset($close))
		$close = $row["close"];
}
// Now open is when they open and close is when they close. Now check if the service is valid.
$query4 = mysqli_query($conn, "select * from services where id = '" . mysqli_real_escape_string($conn, $id2) . "' and barbershopId = '" . mysqli_real_escape_string($conn, $id) . "'");
if (mysqli_num_rows($query4) <= 0)
	die("<p>Invalid service.</p>");
while ($row=mysqli_fetch_assoc($query4))
{
	$serviceName = $row["name"];
	$price = $row["price"];
	$description = $row["description"];
	$duration = $row["duration"];
}
// Now check if any professionals are available then.
$professionalstarts = array();
$professionalends = array();
$query5 = mysqli_query($conn, "select professionalEmail, start, end from professionalhours where professionalEmail in (select email from professional where barbershopId = '" . mysqli_real_escape_string($conn, $id) . "') and day='$dayofweek'");
if (mysqli_num_rows($query5) > 0)
{
	while($row=mysqli_fetch_assoc($query5))
	{
		$professionalstarts[$row["professionalEmail"]] = $row["start"];
		$professionalends[$row["professionalEmail"]] = $row["end"];
	}
}
$query6 = mysqli_query($conn, "select professionalEmail, start, end from professionaloff where professionalEmail in (select email from professional where barbershopId = '" . mysqli_real_escape_string($conn, $id) . "') and year='" . mysqli_real_escape_string($conn, $year) . "' and month = '" . mysqli_real_escape_string($conn, $month) . "' and day = '" . mysqli_real_escape_string($conn, $day) . "'");
if (mysqli_num_rows($query6) > 0)
{
	while($row=mysqli_fetch_assoc($query6))
	{
		if (is_null($row["start"]) && array_key_exists($row["professionalEmail"],$professionalstarts) && array_key_exists($row["professionalEmail"],$professionalends)) // they're off the entire day
		{
			unset($professionalstarts[$row["professionalEmail"]]);
			unset($professionalends[$row["professionalEmail"]]);
		}
		else
		{
			$professionalstarts[$row["professionalEmail"]] = $row["start"];
			$professionalends[$row["professionalEmail"]] = $row["end"];
		}
	}
}
if (count($professionalstarts) <= 0)
	die("No one is available that day.");
// Now get the unavailable times, when another professional has an appointment that day. Including unconfirmed.
$unavailables = array();
$query7 = mysqli_query($conn, "select professionalEmail, timestart, duration from scheduling inner join services on scheduling.serviceId = services.id where professionalEmail in (select email from professional where barbershopId = '" . mysqli_real_escape_string($conn, $id) . "') and year='" . mysqli_real_escape_string($conn, $year) . "' and month = '" . mysqli_real_escape_string($conn, $month) . "' and day = '" . mysqli_real_escape_string($conn, $day) . "'");
if (mysqli_num_rows($query7) > 0)
{
	while($row=mysqli_fetch_assoc($query7))
	{
		array_push($unavailables,array($row["professionalEmail"],$row["timestart"],$row["duration"]));
	}
}
// Now get the information for each professional.
$query8 = mysqli_query($conn, "select email, firstName, lastName, avg(rating) avg, count(rating) numReviews from professional left join scheduling on professional.email = scheduling.professionalEmail left join rating on scheduling.id = rating.schedulingId where email in (select email from professional where barbershopId = '" . mysqli_real_escape_string($conn, $id) . "') and accepted='1' group by professional.email");
while ($row=mysqli_fetch_assoc($query8))
{
	$professionalinfo[$row["email"]] = array($row["firstName"],$row["lastName"],$row["avg"],$row["numReviews"]);
}
// Now should have everything to find available times
if ($curdate->format("Y-m-d")==$now->format("Y-m-d"))
{
	$curTime = fixtime(new DateTime());
	$curFinish = fixtime(new DateTime());
}
else
{
	$curTime = new DateTime($open);
	$curFinish = new DateTime($open);
}
$close2 = new DateTime($close);
$curFinish->add(new DateInterval("PT$duration" . "M")); // this is how you add curTime without changing curTime
$times = array();
while ($curFinish <= $close2)
{
	$professionals = array();
	// now check all the professionals available now
	foreach ($professionalstarts as $email=>$time)
	{
		$open = $time;
		$close = $professionalends[$email];
		if ($curTime >= new DateTime($open) && $curFinish <= new DateTime($close))
		{
			array_push($professionals,$email);
		}
	}
	// oh my god this took forever
	foreach ($unavailables as $ar)
	{
		$newStart = new DateTime($ar[1]);
		$newFinish = findend2($ar[1],$ar[2]);
		if (difference_in_minutes($newStart,$curTime) < $duration && ($newFinish > $curTime || $newFinish > $curFinish))
		{
			array_splice($professionals,array_search($ar[0],$professionals),1);
		}
	}
	if (count($professionals) > 0)
	{
		array_push($times,$curTime->format("h:i A"));
	}
	$curTime = $curFinish;
	$curFinish = new DateTime($curTime->format("h:i A"));
	$curFinish->add(new DateInterval("PT$duration" . "M"));
}
if (count($times) == 0)
	echo "<p>No times available this day.</p>";
else
{
	echo "<form method=\"post\" action=\"reschedule.php\"><p>Choose Time:</p><ul>";
	foreach ($times as $time)
	{
		$time2 = md5($time);
		echo "<li><input type=\"radio\" name=\"time\" value=\"$time\" id=\"$time2\" onclick=\"document.getElementById('reschedule').disabled = false;\" /><label for=\"$time2\">$time</label></li>";
	}
	echo "</ul><input type=\"submit\" value=\"Reschedule Appointment\" id=\"reschedule\" disabled /></form>";
}
mysqli_close($conn);
?>
</body>
</html>