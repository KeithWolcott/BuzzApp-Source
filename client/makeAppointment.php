<?php
session_start();
include '../config.php';
include '../functions.php';
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 1)
	header("Location: ../index.php");
$name = null;
if (isset($_POST["date"]) && isset($_POST["id"]) && isset($_POST["service"]) && $conn)
{
	$date = DateTime::createFromFormat("F j, Y", $_POST["date"]);
	$month = $date->format("m");
	$day = $date->format("d");
	$year = $date->format("Y");
	$_SESSION["scheduling"] = $_POST["service"];
	$_SESSION["year"] = $year;
	$_SESSION["month"] = $month;
	$_SESSION["day"] = $day;
	$query1 = mysqli_query($conn, "select name from barbershop where md5(barbershopId) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'");
	if (mysqli_num_rows($query1) > 0)
	{
		while($row=mysqli_fetch_assoc($query1))
		{
			$name = $row["name"];
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Make Appointment</title>
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
if (is_null($name))
	die("<p>Invalid barbershop.</p>");
?>
<h2>Make Appointment for <?php echo $name; ?></h2>
<form method="post" onsubmit="return continuetoMakeAppointment(this);"><fieldset><legend>Change Settings</legend>
<p>Day: <input name="date" id="date" class="date-picker" value="<?php echo $date->format("F d, Y"); ?>" readonly /><span id="datedetail"></span></p><p>Service: <select name="service"><?php
$services = mysqli_query($conn, "select id, name, price from services where md5(barbershopId) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "' order by price");
$serviceId = 0;
while($row = mysqli_fetch_assoc($services)) {
	$id2 = md5($row["id"]);
	echo "<option value=\"$id2\"";
	if ($_POST["service"] == $id2)
	{
		echo " selected";
		$serviceId = $id2;
	}
	echo ">{$row["name"]} - $" . $row["price"] . "</option>\r\n";
}
echo "</select></p><input type=\"hidden\" name=\"id\" value=\"{$_POST["id"]}\" /><input type=\"submit\" value=\"Continue\" id=\"continueappointment\" />  <span id=\"appointmentstatus\"></span></form></div>";
?>
</fieldset></form><hr>
<?php
// First, find the range.
$curdate = new DateTime("$year-$month-$day");
$now = fixtime(new DateTime("now"));
if ($curdate->format("Y-m-d") != $now->format("Y-m-d") && isPast($curdate))
	die("<p>This date is invalid.</p>");
$dayofweek = $curdate->format("w");
$query2 = mysqli_query($conn, "select open, close from closed where md5(barbershopId) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "' and year='" . mysqli_real_escape_string($conn, $year) . "' and month = '" . mysqli_real_escape_string($conn, $month) . "' and day = '" . mysqli_real_escape_string($conn, $day) . "'");
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
$query3 = mysqli_query($conn, "select day, open, close from barbershophours where md5(barbershopId) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "' and day='$dayofweek'");
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
$query4 = mysqli_query($conn, "select * from services where md5(id) = '" . mysqli_real_escape_string($conn, $_POST["service"]) . "' and md5(barbershopId) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'");
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
$unavailables = array();
$query5 = mysqli_query($conn, "select professionalEmail, start, end from professionalhours where professionalEmail in (select email from professional where md5(barbershopId) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "') and day='$dayofweek'");
if (mysqli_num_rows($query5) > 0)
{
	while($row=mysqli_fetch_assoc($query5))
	{
		$professionalstarts[$row["professionalEmail"]] = $row["start"];
		$professionalends[$row["professionalEmail"]] = $row["end"];
	}
}
// Lunch break
$extraquery = mysqli_query($conn, "select professionalEmail, start, end from professionalbreak where professionalEmail in (select email from professional where md5(barbershopId) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "') and day='$dayofweek'");
if (mysqli_num_rows($extraquery) > 0)
{
	while($row=mysqli_fetch_assoc($extraquery))
	{
		$start = fixtime(new DateTime($row["start"]));
		$end = fixtime(new DateTime($row["end"]));
		$durationofbreak = difference_in_minutes($start,$end);
		array_push($unavailables,array($row["professionalEmail"],$row["start"],$durationofbreak));
	}
}
// Taking the day off
$query6 = mysqli_query($conn, "select professionalEmail, start, end from professionaloff where professionalEmail in (select email from professional where md5(barbershopId) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "') and year='" . mysqli_real_escape_string($conn, $year) . "' and month = '" . mysqli_real_escape_string($conn, $month) . "' and day = '" . mysqli_real_escape_string($conn, $day) . "'");
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
$query7 = mysqli_query($conn, "select professionalEmail, timestart, duration from scheduling inner join services on scheduling.serviceId = services.id where professionalEmail in (select email from professional where md5(barbershopId) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "') and year='" . mysqli_real_escape_string($conn, $year) . "' and month = '" . mysqli_real_escape_string($conn, $month) . "' and day = '" . mysqli_real_escape_string($conn, $day) . "'");
if (mysqli_num_rows($query7) > 0)
{
	while($row=mysqli_fetch_assoc($query7))
	{
		array_push($unavailables,array($row["professionalEmail"],$row["timestart"],$row["duration"]));
	}
}
$professionalinfo = array();
	// Now get the information for each professional.
	$query8 = mysqli_query($conn, "select email, firstName, lastName, avg(rating) avg, count(rating) numReviews from professional left join scheduling on professional.email = scheduling.professionalEmail left join rating on scheduling.id = rating.schedulingId where email in (select email from professional where md5(barbershopId) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "') and accepted='1' and beingdeleted='0' and (paid = '1' or (paid = '0' and DATEDIFF(NOW(), STR_TO_DATE(concat(professional.day,'/',professional.month,'/',professional.year),'%d/%m/%Y')) < 30)) group by professional.email");
	while ($row=mysqli_fetch_assoc($query8))
	{
		$professionalinfo[$row["email"]] = array($row["firstName"],$row["lastName"],$row["avg"],$row["numReviews"]);
	}
	if (count($professionalinfo) <= 0)
		die("No one is available that day.");
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
echo "<div class=\"reviews\">";
while ($curFinish <= $close2)
{
	$professionals = array();
	// now check all the professionals available now
	foreach ($professionalstarts as $email=>$time)
	{
		$open = $time;
		$close = $professionalends[$email];
		if ($curTime >= new DateTime($open) && $curFinish <= new DateTime($close) && array_key_exists($email, $professionalinfo))
		{
			array_push($professionals,$email);
		}
	}
	foreach ($unavailables as $ar)
	{
		$newStart = new DateTime($ar[1]);
		$newFinish = findend2($ar[1],$ar[2]);
		// and it still might not work
		if ((difference_in_minutes($newStart,$curTime) < $duration && ($newFinish > $curTime || $newFinish > $curFinish)) || ($curTime > $newStart && $curFinish <= $newFinish))
		{
			array_splice($professionals,array_search($ar[0],$professionals),1);
		}
	}
	if (count($professionals) > 0)
	{
		echo "<div><span class=\"reviewLink\" onclick=\"revealAppointment(this)\">" . $curTime->format("h:i A") . "</span></span><div class=\"reviewContents\" name=\"contents\" style=\"text-align:left;display:none;\"><form method=\"post\" action=\"bookAppointment.php\" /><input type=\"hidden\" name=\"time\" value=\"" . $curTime->format("h:i A") . "\" /><ul>";
		foreach ($professionals as $email)
		{
			$info = $professionalinfo[$email];
			$email2 = md5($email);
			$time2 = md5($curTime->format("h:i A"));
			echo "<li><input type=\"radio\" name=\"professional\" value=\"$email2\" id=\"$time2" . "$email2\" onclick=\"enableButton(this)\" /><label for=\"$time2" . "$email2\" />" . $info[0] . " " . $info[1] . " ";
			if (!is_null($info[2]))
				echo "(" . round($info[2],2) . " based on " . $info[3] . " review" . extra_s($info[3]) . ".)";
			else
				echo "(No reviews yet)";
			echo "</li>\r\n";
		}
		echo "</ul><input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
<input type=\"hidden\" name=\"business\" value=\"$email\">
<input type=\"hidden\" name=\"lc\" value=\"US\">
<input type=\"hidden\" name=\"button_subtype\" value=\"services\">
<input type=\"hidden\" name=\"no_note\" value=\"0\">
<input type=\"hidden\" name=\"currency_code\" value=\"USD\">
<input type=\"hidden\" name=\"bn\" value=\"PP-BuyNowBF:btn_buynowCC_LG.gif:NonHostedGuest\">
<input type=\"image\" src=\"https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif\" border=\"0\" name=\"book\" alt=\"PayPal - The safer, easier way to pay online!\">
<img alt=\"\" border=\"0\" src=\"https://www.paypalobjects.com/en_US/i/scr/pixel.gif\" width=\"1\" height=\"1\"></form></div></div>";
	}
	$curTime = $curFinish;
	$curFinish = new DateTime($curTime->format("h:i A"));
	$curFinish->add(new DateInterval("PT$duration" . "M"));
	
}
echo "</div>";
mysqli_close($conn);
?>
</body>
</html>