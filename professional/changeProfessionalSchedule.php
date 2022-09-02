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
	if (!isset($_POST[$day . "off"]) && isset($_POST[$day . "open"]))
	{
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
				$dates[$i] = array($hour1,$date1->format("A"),$hour2,$date2->format("A"));
			}
		}
		else
			$valid = false;
	}
	if (!$valid)
		break;
}
if ($valid) {
	if ($conn)
	{
		$extraquery = mysqli_query($conn, "delete from professionalbreak where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
		for($i=0;$i<7;$i++)
		{
			if (!array_key_exists($i,$dates))
				$query1 = mysqli_query($conn, "delete from professionalhours where day='$i' and professionalEmail='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
			else
			{
				$datear = $dates[$i];
				$query1 = mysqli_query($conn, "select day from professionalhours where professionalEmail='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and day='$i'");
				if (mysqli_num_rows($query1) > 0)
				{
					$query2 = mysqli_query($conn, "update professionalhours set start='{$datear[0]} {$datear[1]}', end='{$datear[2]} {$datear[3]}' where day='$i' and professionalEmail ='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
				}
				else
				{
					$query2 = mysqli_query($conn, "insert into professionalhours(professionalEmail, day, start, end) values('" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "', '$i', '{$datear[0]} {$datear[1]}', '{$datear[2]} {$datear[3]}')");
				}
			}
		}
	}
}
?>