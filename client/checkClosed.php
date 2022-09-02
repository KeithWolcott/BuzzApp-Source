<?php
session_start();
include '../config.php';
include '../functions.php';
if (isset($_SESSION["email"]) && $conn && isset($_GET["id"]) && isset($_GET["month"]) && isset($_GET["day"]) && isset($_GET["year"]))
{
	$month = $_GET["month"];
	while (strlen($month) < 2)
		$month = "0$month";
	$day = $_GET["day"];
	while (strlen($day) < 2)
		$day = "0$day";
	$year = $_GET["year"];
	$curdate = new DateTime("$year-$month-$day");
	$now = fixtime(new DateTime("now"));
	if ($curdate->format("Y-m-d") != $now->format("Y-m-d") && isPast($curdate))
		echo "past";
	else
	{
		$dayofweek = $curdate->format("w");
		$query = mysqli_query($conn, "select barbershophours.day from barbershophours where md5(barbershopId) = '" . mysqli_real_escape_string($conn, $_GET["id"]) . "' and day='$dayofweek'");
		if (isset($_GET["mode"]) && $_GET["mode"] == 2)
			$query = mysqli_query($conn, "select barbershophours.day from scheduling inner join professional on scheduling.professionalEmail = professional.email inner join barbershop on professional.barbershopId = barbershop.barbershopId inner join barbershophours on barbershop.barbershopId = barbershophours.barbershopId where md5(scheduling.id) = '" . mysqli_real_escape_string($conn, $_GET["id"]) . "' and barbershophours.day='$dayofweek'");
		if (mysqli_num_rows($query) <= 0)
		{
			echo "closed";
		}
		else
		{
				$query2 = mysqli_query($conn, "select open from closed where md5(barbershopId) = '" . mysqli_real_escape_string($conn, $_GET["id"]) . "' and year='" . mysqli_real_escape_string($conn, $year) . "' and month = '" . mysqli_real_escape_string($conn, $month) . "' and day = '" . mysqli_real_escape_string($conn, $day) . "'");
				if ($_GET["mode"] == 2)
					$query2 = mysqli_query($conn, "select open from scheduling inner join professional on scheduling.professionalEmail = professional.email inner join barbershop on professional.barbershopId = barbershop.barbershopId inner join closed on barbershop.barbershopId = closed.barbershopId where md5(scheduling.id) = '" . mysqli_real_escape_string($conn, $_GET["id"]) . "' and closed.year='" . mysqli_real_escape_string($conn, $year) . "' and closed.month = '" . mysqli_real_escape_string($conn, $month) . "' and closed.day = '" . mysqli_real_escape_string($conn, $day) . "'");
				if (mysqli_num_rows($query2) > 0)
				{
					while ($row=mysqli_fetch_assoc($query2))
					{
						if (is_null($row["open"]))
						{
							echo "closed";
						}
						else
						{
							echo "open";
						}
						break;
					}
				}
				else
				{
					echo "open";
				}
		}
	}
}
else
	echo "invalid";
?>