<?php
session_start();
include '../config.php';
include '../functions.php';
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2)
{
	if ($conn)
	{
		$professionalStr = "";
		if ($_GET && isset($_GET["professional"]) && $_GET["professional"] == "yes")
			$professionalStr = "professional";
		$initialQuery = "select barbershophours.day, open, close, adminemail from (barbershop natural left join barbershophours), professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId=professional.barbershopId";
		$tempresult = mysqli_query($conn, "select * from professionalhours where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
		if ($professionalStr != "" && mysqli_num_rows($tempresult) > 0)
			$initialQuery = "";
		$result = mysqli_query($conn, $initialQuery);
		$openhours = array();
		$closehours = array();
		$days = array();
		if (mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
			  $adminemail = $row["adminemail"];
			  if (!is_null($row["day"]))
			  {
				  $openhours[convertday($row["day"])] = $row["open"];
				  $closehours[convertday($row["day"])] = $row["close"];
				  array_push($days, convertday($row["day"]));
			  }
			}
		}
		if ($adminemail == $_SESSION["email"])
		{
			if ($professionalStr == "")
				echo "<form id=\"hoursform\" onsubmit=\"return changeHours(this);\" method=\"post\"><ul>";
			else
				echo "<p>Put in your hours:</p><form id=\"professionalhoursform\" onsubmit=\"return changeHours(this);\" method=\"post\"><ul>";
			$daysoftheweek = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
			if ($professionalStr == "")
			{
					foreach ($daysoftheweek as $day)
					{
						$day2 = strtolower($day);
						echo "<li>$day: <input type=\"checkbox\" id=\"$day2\" value=\"closed\" onclick=\"allowFor(this)\" name=\"{$day2}closed\" ";
						if (!in_array($day,$days))
							echo "checked ";
						echo "/><label for=\"$day2\" />Closed</label><span id=\"{$day2}info\"";
						if (!in_array($day,$days))
							echo " class=\"greyout\" ";
						echo "> &nbsp; Open: <input type=\"text\" name=\"{$day2}openhour\" id=\"{$day2}openhour\" value=\"";
						if (array_key_exists($day,$openhours))
						{
							echo $openhours[$day];
						}
						echo "\" maxlength=\"8\" size=\"8\" /> &nbsp; Close: <input type=\"text\" name=\"{$day2}closehour\" value=\"";
						if (array_key_exists($day,$closehours))
						{
							echo $closehours[$day];
						}
						echo "\" maxlength=\"8\" size=\"8\" id=\"{$day2}closehour\" /></span></li>\r\n
						<script>
							$('#{$day2}openhour').timepicker({
							'step': 15,
							'timeFormat': 'h:i A'});
							$('#{$day2}closehour').timepicker({
							'step': 15,
							'timeFormat': 'h:i A'});</script>";
					}
			}
			elseif (mysqli_num_rows($tempresult) <= 0)
			{
				foreach ($daysoftheweek as $day)
				{
					$day2 = strtolower($day);
					if (in_array($day,$days))
					{
						echo "<li>$day: &nbsp; Open: <input type=\"text\" name=\"{$day2}openhour\" id=\"{$day2}openhour\" value=\"";
						if (array_key_exists($day,$openhours))
						{
							$sp = explode(" ",$openhours[$day]);
							echo $sp[0];
						}
						echo "\" maxlength=\"5\" size=\"6\" /> <select name=\"{$day2}openampm\" id=\"{$day2}openampm\"><option value=\"AM\">AM</option><option value=\"PM\"";
						if (array_key_exists($day,$openhours))
						{
							if ($sp[1] == "PM")
								echo " selected";
						}
						echo ">PM</option></select> &nbsp; Close: <input type=\"text\" name=\"{$day2}closehour\" value=\"";
						if (array_key_exists($day,$closehours))
						{
							$sp = explode(" ",$closehours[$day]);
							echo $sp[0];
						}
						echo "\" maxlength=\"5\" size=\"6\" id=\"{$day2}closehour\" /> <select name=\"{$day2}closeampm\" id=\"{$day2}closeampm\"><option value=\"AM\">AM</option><option value=\"PM\"";
						if (array_key_exists($day,$closehours))
						{
							if ($sp[1] == "PM")
								echo " selected";
						}
						else
							echo " selected";
						echo ">PM</option></select></span></li>\r\n";
					}
				}
			}
			echo "</ul><input type=\"submit\" value=\"Submit\" /></form>";
		}
	}
}
?>