<?php
session_start();
if (!isset($_SESSION["email"]))
	header("Location: ../login.php");
elseif (!$_POST || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 2)
	header("Location: ../index.php");
include '../config.php';
include '../functions.php';
$days = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
$valid = true;
$dates = array();
for ($i = 0; $i < count($days); $i++)
{
	$day = $days[$i];
	if (!isset($_POST[$day . "closed"]))
	{
		$date1 = false;
		$date2 = false;
		$opensplit = explode(" ",$_POST[$day . "openhour"]);
		$closesplit = explode(" ",$_POST[$day . "closehour"]);
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
				$dates[$i] = array($hour1,$date1->format("A"),$hour2,$date2->format("A"));
			}
		}
	}
}
if ($valid) {
	if ($conn)
	{
		for($i=0;$i<7;$i++)
		{
			if (!array_key_exists($i,$dates))
				$query1 = mysqli_query($conn, "delete from barbershophours where day='$i' and barbershopId in (select barbershop.barbershopId from barbershop, professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId = professional.barbershopId)");
			else
			{
				$datear = $dates[$i];
				$query1 = mysqli_query($conn, "select barbershophours.day from barbershophours, professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershophours.barbershopId = professional.barbershopId and barbershophours.day='$i'");
				if (mysqli_num_rows($query1) > 0)
				{
					$query2 = mysqli_query($conn, "update barbershophours set open='{$datear[0]} {$datear[1]}', close='{$datear[2]} {$datear[3]}' where day='$i' and barbershopId in (select barbershop.barbershopId from barbershop, professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId = professional.barbershopId)");
				}
				else
				{
					$query2 = mysqli_query($conn, "insert into barbershophours(barbershopId, day, open, close) select barbershop.barbershopId, '$i', '{$datear[0]} {$datear[1]}', '{$datear[2]} {$datear[3]}' from barbershop, professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId = professional.barbershopId");
				}
				// Now all this is to fix professionalhours, professionaloff, and closed, if their hours are now wrong (if they are open before the barbershop actually opens or they close after the barbershop actually closes)
				$query3 = mysqli_query($conn, "update professionalhours set start='{$datear[0]} {$datear[1]}' where day='$i' and start < '{$datear[1]} {$datear[2]}' and professionalEmail in (select email from professional, barbershop where professional.barbershopId = barbershop.barbershopId and barbershop.barbershopId in (select barbershop.barbershopId from barbershop, professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId = professional.barbershopId))");
				$query4 = mysqli_query($conn, "update professionalhours set end='{$datear[2]} {$datear[3]}' where day='$i' and end > '{$datear[2]} {$datear[3]}' and professionalEmail in (select email from professional, barbershop where professional.barbershopId = barbershop.barbershopId and barbershop.barbershopId in (select barbershop.barbershopId from barbershop, professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId = professional.barbershopId))");
				$dayofweek = $i+1;
				$query5 = mysqli_query($conn, "update professionaloff set start='{$datear[0]} {$datear[1]}' where dayofweek(concat(year,'-',month,'-',day))='$dayofweek' and start < '{$datear[0]} {$datear[1]}' and professionalEmail in (select email from professional, barbershop where professional.barbershopId = barbershop.barbershopId and barbershop.barbershopId in (select barbershop.barbershopId from barbershop, professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId = professional.barbershopId))");
				$query6 = mysqli_query($conn, "update professionaloff set end='{$datear[2]} {$datear[3]}' where dayofweek(concat(year,'-',month,'-',day))='$dayofweek' and end > '{$datear[2]} {$datear[3]}' and professionalEmail in (select email from professional, barbershop where professional.barbershopId = barbershop.barbershopId and barbershop.barbershopId in (select barbershop.barbershopId from barbershop, professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId = professional.barbershopId))");
				$query7 = mysqli_query($conn, "update closed set open='{$datear[0]} {$datear[1]}' where dayofweek(concat(year,'-',month,'-',day))='$dayofweek' and open < '{$datear[0]} {$datear[1]}' and barbershopId in (select barbershop.barbershopId from barbershop, professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId = professional.barbershopId)");
				$query8 = mysqli_query($conn, "update closed set close='{$datear[2]} {$datear[3]}' where dayofweek(concat(year,'-',month,'-',day))='$dayofweek' and close > '{$datear[2]} {$datear[3]}' and barbershopId in (select barbershop.barbershopId from barbershop, professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId = professional.barbershopId)");
			}
		}
	}
}
?>