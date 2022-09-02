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
<title>Configure Lunch Break</title>
<?php include 'header.php'; ?>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="center"><h1>Configure Lunch Break</h1>
<?php
// First, check if they are registered for a barbershop.
$query = mysqli_query($conn, "select barbershopId from professional where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
if (mysqli_num_rows($query) <= 0)
	echo "<p>You must <a href=\"setupBarbershop.php\">find your barbershop first</a>.</p>";
else
{
	$query2 = mysqli_query($conn, "select day, start, end from professionalhours where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
	if (mysqli_num_rows($query2) <= 0)
		echo "<p>You must <a href=\"barbershopPage.php\">put in your schedule for your barbershop first</a>.</p>";
	else
	{
		if ($_POST)
		{
			$valid = true;
			while ($row = mysqli_fetch_assoc($query2))
			{
				$day = strtolower(convertday($row["day"]));
				if ($_POST[$day . "open"] == "" && $_POST[$day . "close"] == "")
					continue;
				$date1 = false;
				$date2 = false;
				$opensplit = explode(" ",$_POST[$day . "open"]);
				$closesplit = explode(" ",$_POST[$day . "close"]);
				$opentime = $opensplit[0];
				$openampm = $opensplit[1];
				$closetime = $closesplit[0];
				$closeampm = $closesplit[1];
				if (preg_match('/^[0-9:]+$/',$opentime) && is_numeric(str_replace(":","",$opentime)))
					$date1 = checktime($opentime,$openampm);
				else
					$valid = false;
				if (preg_match('/^[0-9:]+$/',$closetime) && is_numeric(str_replace(":","",$closetime)))
					$date2 = checktime($closetime,$closeampm);
				else
					$valid = false;
				if ($date1 != false && $date2 != false)
				{
					if ($date1 >= $date2)
						$valid = false;
					else
					{
						$hour1 = $date1->format("h:i");
						$hour2 = $date2->format("h:i");
						if (substr($hour1,0,1) == "0")
							$hour1 = substr($hour1,1);
						if (substr($hour2,0,1) == "0")
							$hour2 = substr($hour2,1);
						$starttime = $hour1 . " " . $date1->format("A");
						$endtime = $hour2 . " " . $date2->format("A");
						$insertquery = mysqli_query($conn, "insert into professionalbreak(professionalEmail, day, start, end) values ('" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "', '{$row["day"]}', '$starttime', '$endtime') on duplicate key update start='$starttime', end='$endtime'");
					}
				}
			}
			if (!$valid)
				echo "<p>Your times were not successfully added.</p>";
			else
				echo "<p>Your times were successfully added!</p>";
			echo "<hr>";
		}
		$query2 = mysqli_query($conn, "select day, start, end from professionalhours where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
		$query3 = mysqli_query($conn, "select * from professionalbreak where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
	echo "<form method=\"post\" onsubmit=\"return validateBreak();\"><ul class=\"breakList\">";
		$currentdays = array();
		if (mysqli_num_rows($query3) > 0)
		{
			while($row = mysqli_fetch_assoc($query3)) {
				$currentdays[convertday($row["day"])] = array($row["start"],$row["end"]);
			}
		}
		while ($row = mysqli_fetch_assoc($query2))
		{
			$day = convertday($row["day"]);
			$day2 = strtolower($day);
			echo "<li>$day: &nbsp; Start of break: <input type=\"text\" name=\"{$day2}open\" id=\"{$day2}breakstarthour\" onchange=\"checkToEnable(this);\" oninput=\"checkToEnable(this);\"  value=\"";
			if (array_key_exists($day,$currentdays))
			{
				echo $currentdays[$day][0];
			}
			echo "\" maxlength=\"8\" size=\"8\" /> &nbsp; End of break: <input type=\"text\" name=\"{$day2}close\" onchange=\"checkToEnable(this);\" oninput=\"checkToEnable(this);\" value=\"";
			if (array_key_exists($day,$currentdays))
			{
				echo $currentdays[$day][1];
			}
			echo "\" maxlength=\"8\" size=\"8\" id=\"{$day2}breakendhour\" /> &nbsp; <input type=\"button\" onclick=\"autoFillIn(this)\" ";
			if (!array_key_exists($day,$currentdays))
				echo "disabled ";
			echo "value=\"Fill out Rest\" /></li>\r\n
			<script>
			$('#{$day2}breakstarthour').timepicker({
			'step': 10,
			'timeFormat': 'h:i A',
			'minTime': '{$row["start"]}',
			'maxTime': '{$row["end"]}',
			'scrollDefault': 'now'});
			$('#{$day2}breakendhour').timepicker({
			'step': 10,
			'timeFormat': 'h:i A',
			'minTime': '{$row["start"]}',
			'maxTime': '{$row["end"]}',
			'scrollDefault': 'now'});</script>";
		}
		echo "</ul><input type=\"submit\" value=\"";
		if (mysqli_num_rows($query3) <= 0)
			echo "Add";
		else
			echo "Change";
		echo "\" /></form>";
	}
}
?></div>
</body>
</html>