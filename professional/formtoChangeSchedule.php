<?php
session_start();
include '../config.php';
include '../functions.php';
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2 && $conn)
{
	  $openhours = array();
	  $closehours = array();
	  $days = array();
	  $result = mysqli_query($conn, "select barbershophours.day, open, close from (barbershop natural left join barbershophours), professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId=professional.barbershopId");
	  if (mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
			  if (!is_null($row["day"]))
			  {
				  $openhours[convertday($row["day"])] = $row["open"];
				  $closehours[convertday($row["day"])] = $row["close"];
				  array_push($days, convertday($row["day"]));
			  }
			}
	  }
	$daysoftheweek = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	$result2 = mysqli_query($conn, "select day, start, end from professionalhours where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' order by day");
	if (mysqli_num_rows($result2) <= 0) {
		echo "<div style=\"border:1px solid black;padding:5px 10px;\" id=\"professionalhours\"><p>Put in your hours:</p><form id=\"professionalhoursform\" onsubmit=\"return changeHours(this);\"><ul>";
		foreach ($daysoftheweek as $day)
		{
			$day2 = strtolower($day);
			if (in_array($day,$days))
			{
				echo "<li>$day: <input type=\"checkbox\" id=\"{$day2}professional\" value=\"off\" onclick=\"allowFor(this)\" name=\"{$day2}off\" ";
			if (!in_array($day,$days))
				echo "checked ";
			echo "/><label for=\"{$day2}professional\" />Off</label><span id=\"{$day2}professionalinfo\"";
			if (!in_array($day,$days))
				echo " class=\"greyout\" ";
			echo "> &nbsp; Open: <input type=\"text\" name=\"{$day2}open\" id=\"{$day2}professionalopenhour\" value=\"";
				if (array_key_exists($day,$openhours))
				{
					echo $openhours[$day];
				}
				echo "\" maxlength=\"8\" size=\"8\" /> &nbsp; Close: <input type=\"text\" name=\"{$day2}close\" value=\"";
				if (array_key_exists($day,$closehours))
				{
					echo $closehours[$day];
				}
				echo "\" maxlength=\"8\" size=\"8\" id=\"{$day2}professionalclosehour\" /></span></li>\r\n
				<script>
				$('#{$day2}professionalopenhour').timepicker({
				'step': 15,
				'timeFormat': 'h:i A',
				'minTime': '{$openhours[$day]}',
				'maxTime': '{$closehours[$day]}',
				'scrollDefault': 'now'});
				$('#{$day2}professionalclosehour').timepicker({
				'step': 15,
				'timeFormat': 'h:i A',
				'minTime': '{$openhours[$day]}',
				'maxTime': '{$closehours[$day]}',
				'scrollDefault': 'now'});</script>";
			}
		}
		echo "</ul><input type=\"submit\" value=\"Submit\" /></form></div>";
	}
	else
	{
	echo "<p>Do you need to change your lunch break schedule? <a href=\"addLunchBreak.php\">Go here</a>.</p><p>Has the hours you work changed? &nbsp; <input type=\"button\" onclick=\"showChangeHours();\" value=\"Fix it\" /></p><div id=\"changeHours\" style=\"display:none;\"><form id=\"changeHoursForm\" onsubmit=\"return changeHours(this)\"><ul>";
		$currentdays = array();
		while($row = mysqli_fetch_assoc($result2)) {
			$currentdays[convertday($row["day"])] = array($row["start"],$row["end"]);
		}
		foreach ($daysoftheweek as $day)
		{
			$day2 = strtolower($day);
			if (in_array($day,$days))
			{
				echo "<li>$day: <input type=\"checkbox\" id=\"{$day2}professional\" value=\"off\" onclick=\"allowFor(this)\" name=\"{$day2}off\" ";
			if (!array_key_exists($day,$currentdays))
				echo "checked ";
			echo "/><label for=\"{$day2}professional\" />Off</label><span id=\"{$day2}professionalinfo\"";
			if (!array_key_exists($day,$currentdays))
				echo " class=\"greyout\" ";
			echo "> &nbsp; Open: <input type=\"text\" name=\"{$day2}open\" id=\"{$day2}professionalopenhour\" value=\"";
				if (array_key_exists($day,$currentdays))
				{
					echo $currentdays[$day][0];
				}
				echo "\" maxlength=\"8\" size=\"8\" /> &nbsp; Close: <input type=\"text\" name=\"{$day2}close\" value=\"";
				if (array_key_exists($day,$currentdays))
				{
					echo $currentdays[$day][1];
				}
				echo "\" maxlength=\"8\" size=\"8\" id=\"{$day2}professionalclosehour\" /></span></li>\r\n
				<script>
				$('#{$day2}professionalopenhour').timepicker({
				'step': 15,
				'timeFormat': 'h:i A',
				'minTime': '{$openhours[$day]}',
				'maxTime': '{$closehours[$day]}',
				'scrollDefault': 'now'});
				$('#{$day2}professionalclosehour').timepicker({
				'step': 15,
				'timeFormat': 'h:i A',
				'minTime': '{$openhours[$day]}',
				'maxTime': '{$closehours[$day]}',
				'scrollDefault': 'now'});</script>";
			}
		}
		echo "</ul><input type=\"submit\" value=\"Fix\" /><p>You'll need to add your lunch break hours again after changing your schedule.</p></form></div><p>Is your schedule going to be different on an upcoming date? <A href=\"professionalCalendar.php\">Indicate it.</a></p>";
	}
}
?>