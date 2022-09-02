<?php
session_start();
include '../functions.php';
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2)
{
	if ($_POST)
	{
		include '../config.php';
		$result = mysqli_query($conn, "select barbershopId, adminemail from barbershop where barbershopId in (select barbershopId from professional where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "')");
		if (mysqli_num_rows($result) > 0)
		{
			while ($row = mysqli_fetch_assoc($result))
			{
				$admin = $row["adminemail"];
				$id = $row["barbershopId"];
			}
			if ($admin == $_SESSION["email"])
			{
				$month = $_POST["month"];
				$day = $_POST["day"];
				while (strlen($month) < 2)
					$month = "0$month";
				while (strlen($day) < 2)
					$day = "0$day";
				if (isset($_POST["allday"]))
					$query = mysqli_query($conn, "insert into closed(barbershopId, year, month, day) values ($id, '" . mysqli_real_escape_string($conn, $_POST["year"]) . "', '" . mysqli_real_escape_string($conn, $month) . "', '" . mysqli_real_escape_string($conn, $day) . "')");
				else
				{
					$date1 = false;
					$date2 = false;
					$openhour = $_POST["holidayopen"];
					$closehour = $_POST["holidayclose"];
					if (preg_match('/^[0-9:]+$/',$openhour) && is_numeric(str_replace(":","",$openhour)))
						$date1 = checktime($openhour,$_POST["holidayopenampm"]);
					else
						$valid = false;
					if (preg_match('/^[0-9:]+$/',$closehour) && is_numeric(str_replace(":","",$closehour)))
						$date2 = checktime($closehour,$_POST["holidaycloseampm"]);
					else
						$valid = false;
					if ($date1 != false && $date2 != false)
					{
						if ($date1 < $date2)
						{
							$hour1 = $date1->format("h:i");
							$hour2 = $date2->format("h:i");
							if (substr($hour1,0,1) == "0")
								$hour1 = substr($hour1,1);
							if (substr($hour2,0,1) == "0")
								$hour2 = substr($hour2,1);
							$query = mysqli_query($conn, "insert into closed(barbershopId, year, month, day, open, close) values ($id, '" . mysqli_real_escape_string($conn, $_POST["year"]) . "', '" . mysqli_real_escape_string($conn, $month) . "', '" . mysqli_real_escape_string($conn, $day) . "', '$hour1 " . $date1->format("A") . "', '$hour2 " . $date2->format("A") . "')");
						}
					}
				}
			}
		}
	}
}
?>