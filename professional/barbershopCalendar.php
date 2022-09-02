<?php
session_start();
include '../config.php';
include '../functions.php';
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 2)
	header("Location: ../index.php");
$result = mysqli_query($conn, "select adminemail, name, barbershopId from barbershop where barbershopId in (select barbershopId from professional where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "')");
if (mysqli_num_rows($result) > 0)
{
	while($row = mysqli_fetch_assoc($result))
	{
		$adminemail = $row["adminemail"];
		$barbershopId = $row["barbershopId"];
		$barbershopname = $row["name"];
	}
}
if ($adminemail != $_SESSION["email"])
	header("Location: ../index.php");
$now = fixtime(new DateTime("now"));
$februraydays = (is_leap_year(date("Y")) ? 29 : 28);
$maxyears = Date("Y", strtotime("+3 years"));
// When they first go to the page, the default year and month is the current one.
if (!isset($_GET["month"]))
	$month = $now->format("m");
else
{
	$ex = explode(" ",$_GET["month"]);
	if (count($ex) == 2)
	{
		$date = date_parse($ex[0]);
		$month = $date["month"];
		while (strlen($month) < 2)
			$month = "0$month";
	}
}
if (!isset($_GET["month"]))
	$year = $now->format("Y");
elseif (count($ex) == 2)
	$year = $ex[1];
$result2 = mysqli_query($conn, "select day, open, close from barbershophours where barbershopId = '$barbershopId'");
$result3 = mysqli_query($conn, "select day, open, close from closed where barbershopId = '$barbershopId' and year='$year' and month='$month'");
$normalhours = array();
$closed = array();
if (mysqli_num_rows($result2) > 0)
{
	while($row = mysqli_fetch_assoc($result2))
	{
		if (!array_key_exists($row["day"],$normalhours))
		{
			$normalhours[$row["day"]] = array($row["open"],$row["close"]);
		}
	}
}
else
	header("Location: barbershopPage.php"); // only if this barbershop has the hours of operation posted.
if (mysqli_num_rows($result3) > 0)
{
	while($row = mysqli_fetch_assoc($result3))
	{
		if (!is_null($row["day"]) && !array_key_exists($row["day"],$closed))
			$closed[$row["day"]] = array($row["open"],$row["close"]);
	}
}
$months = array("01"=>31,"02"=>$februraydays,"03"=>31,"04"=>30,"05"=>31,"06"=>30,"07"=>31,"08"=>31,"09"=>30,"10"=>31,"11"=>30,"12"=>31);
$weeks = array(array());
$date = new DateTime("$year-$month-01");
$firstday = $date->format("w");
while (count($weeks[0]) < $firstday)
	array_push($weeks[0],""); // This fixes the first week so that the 1st day is at the right day of the week.
$currindex = 0;
for ($i=1;$i<=$months[$month];$i++)
{
	array_push($weeks[$currindex],$i);
	if (count(end($weeks)) > 6) // Once the current week is finished.
	{
		array_push($weeks,array());
		$currindex++;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Barbershop Calendar</title>
<?php include 'header.php'; ?>
<style>
.ui-datepicker-calendar {
    display: none;
    }
</style>
</head>
<body onload="loadTimePickers();"><?php include 'navbar.php'; ?>
<div class="center">
<form method="get" onsubmit="return checkdate();">
<h1>Schedule for <?php echo $barbershopname; ?></h1>
<?php
mysqli_close($conn);
if (!isset($_GET["month"]))
{
	$month = $now->format("m");
	$month2 = $now->format("F Y");
}
else
{
	$ex = explode(" ",$_GET["month"]);
	if (count($ex) == 2)
	{
		$date = date_parse($ex[0]);
		$month = $date["month"];
		while (strlen($month) < 2)
			$month = "0$month";
		$month2 = $_GET["month"];
	}
}
if (!isset($_GET["month"]))
	$year = $now->format("Y");
elseif (count($ex) == 2)
	$year = $ex[1];
echo "<script>
var days = [];
var defaultopens = {};
var defaultcloses = {};
var year = $year;
var month = $month;
month -= 1;
$(function() {
            $('.date-picker').datepicker( {
            changeMonth: true,
            changeYear: true,
			minDate: '0M',
			maxDate: '36M',
            showButtonPanel: true,
            dateFormat: 'MM yy',
            onClose: function(dateText, inst) { 
			
                $(this).datepicker('setDate', new Date($(\"#ui-datepicker-div .ui-datepicker-year :selected\").val(), $(\"#ui-datepicker-div .ui-datepicker-month :selected\").val(), 1));
            }
            });
 });
			</script> <input name=\"month\" id=\"month\" class=\"date-picker\" value=\"$month2\" />";
	?> &nbsp; <input type="submit" value="Change Date" /> <span id="datedetail"></span>
</form></div><hr>
<table border='1' style="width:96%;" class="centertable"><tr><th>Sunday</th><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th></tr>
<?php

foreach ($weeks as $week=>$contents)
{
	foreach ($contents as $day)
	{
		echo "<td class=\"day\">";
		if ($day != "")
		{
			echo "<span class=\"dayofmonth\">$day</span>";
			$curdate = new DateTime("$year-$month-$day");
			if (!isPast($curdate))
			{
				echo "<form onsubmit=\"return validate(this)\" method=\"post\">";
				$dayofweek = $curdate->format("w");
				while (strlen($day) < 2)
					$day = "0$day";
				echo "<input type=\"hidden\" name=\"month\" value=\"$month\" /><input type=\"hidden\" name=\"day\" value=\"$day\" /><input type=\"hidden\" name=\"year\" value=\"$year\" />";
				if (array_key_exists($day,$closed)) // If the barbershop is currently closed on the current day
				{
					echo "<input type=\"button\" onclick=\"editDate(this)\" id=\"editbutton\" value=\"Edit\" /><br>
					<div class='greyout' id='dayinfo'><input type=\"checkbox\" value=\"yes\" name=\"closed\" id=\"closed$day\" ";
					if ($closed[$day][0] == null)
						echo "checked ";
					echo "/><label for=\"closed$day\">Closed All Day</label><br>Open: <input type=\"text\" id=\"open$day\" name=\"openhour\" maxlength=\"8\" size=\"8\" />
					<br>Close: <input type=\"text\" id=\"close$day\" name=\"closehour\" maxlength=\"8\" size=\"8\" /><br><input type=\"submit\" value=\"Change\" /><script>
					days.push('$day');";
					if ($closed[$day][0] != null)
					{
						echo "defaultopens['$day'] = new Date(year, month, $day, " . fixpm($closed[$day][0]) . ", 0);
					defaultcloses['$day'] = new Date(year, month, $day, " . fixpm($closed[$day][1]) . ", 0);";
					}
					echo "</script>";
				}
				else
				{
					if (array_key_exists($dayofweek,$normalhours))
					{
						echo "<input type=\"button\" onclick=\"editDate(this)\" id=\"editbutton\" value=\"Edit\" /><br><div class='greyout' id='dayinfo'><input type=\"checkbox\" value=\"yes\" name=\"closed\" id=\"closed$day\" /><label for=\"closed$day\">Closed All Day</label><br>Open: <input type=\"text\" id=\"open$day\" name=\"openhour\" maxlength=\"8\" size=\"8\" />
						<br>Close: <input type=\"text\" id=\"close$day\" name=\"closehour\" maxlength=\"8\" size=\"8\" /><br><input type=\"submit\" value=\"Change\" /><script>
						days.push('$day');
						defaultopens['$day'] = new Date(year, month, $day, " . fixpm($normalhours[$dayofweek][0]) . ", 0);
						defaultcloses['$day'] = new Date(year, month, $day, " . fixpm($normalhours[$dayofweek][1]) . ", 0);
						</script>";
					}
				}
				echo "</form>";
			}
		}
		echo "</td>\r\n";
	}
	echo "</tr>\r\n";
}
?>
</table>
<script>
function validate(f)
{
		var date1 = false;
		var date2 = false;
		var openhour = $(f).find("input[name='openhour']");
		var closehour = $(f).find("input[name='closehour']");
		var closedallday = $(f).find("input[name='closed']");
		var button = $(f).find("#editbutton");
		var day = $(f).find("input[name='day']");
		openhour.removeClass("invalid");
		closehour.removeClass("invalid");
		var valid = true;
		if (!$(closedallday).is(':checked'))
		{
			var opensplit = openhour.val().split(" ");
			var opentime = opensplit[0];
			var openampm = opensplit[1];
			var closesplit = closehour.val().split(" ");
			var closetime = closesplit[0];
			var closeampm = closesplit[1];
			if (opentime.match(new RegExp('^[0-9:]+$')) && !isNaN(parseInt(opentime)))
			{
				date1 = checktime(opentime,openampm);
			}
			else
			{
				openhour.addClass("invalid");
				valid = false;
			}
			if (closetime.match(new RegExp('^[0-9:]+$')) && !isNaN(parseInt(closetime)))
			{
				date2 = checktimej(closetime,closeampm);
			}
			else
			{
				closehour.addClass("invalid");
				valid = false;
			}
			if (date1 != false && date2 != false)
			{
				var diff = date2 - date1;
				if (diff <= 0)
				{
					$(openhour).addClass("invalid");
					$(closehour).addClass("invalid");
					valid = false;
				}
			}
			else
				valid = false;
		}
		if (valid)
		{
			if (valid)
			{
				$.ajax({
					type: 'post',
					url: 'addClose.php',
					data: $(f).serialize(),
					success: function () {
						editDate(button);
					}
				  });
			}
		}
return false;
}
function loadTimePickers()
{
	for (var i = 0; i < days.length; i++)
	{
		var day = days[i];
		$('#open' + day).timepicker({
		'step': 15,
		'timeFormat': 'h:i A'});
		$('#close' + day).timepicker({
		'step': 15,
		'timeFormat': 'h:i A'});
		if (day in defaultopens)
		{
			$('#open' + day).timepicker('setTime', defaultopens[day]);
		}
		if (day in defaultcloses)
		{
			$('#close' + day).timepicker('setTime', defaultcloses[day]);
		}
	}
}
</script>
</body>
</html>