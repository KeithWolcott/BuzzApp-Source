<?php
session_start();
include '../config.php';
include '../functions.php';
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2)
{
	echo "<table><tr><td>Hours of Operations</td></tr>";
	if ($conn)
	{
		$result = mysqli_query($conn, "select barbershophours.day, open, close from (barbershop natural left join barbershophours), professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId=professional.barbershopId");
		$openhours = array();
		$closehours = array();
		$days = array();
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
		if (count($days) > 0)
		{
			foreach ($days as $day)
			{
				echo "<tr><td>$day</td><td>{$openhours[$day]} - {$closehours[$day]}</td></tr>\r\n";
			}
		}
		else
			echo "<tr><td>No hours of operations available</td></tr>";

	}
	else
		echo "<tr><td>Unable to get hours of operations</td></tr>";
	echo "</table>";
}
?>