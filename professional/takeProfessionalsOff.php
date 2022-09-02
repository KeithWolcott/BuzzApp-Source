<?php
session_start();
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 2)
	header("Location: ../login.php");
include '../config.php';
include '../functions.php';
$barbershopId = 0;
$today = fixtime(new DateTime());
if ($conn)
{
	$query1 = mysqli_query($conn, "select barbershopId from professional where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
	if (mysqli_num_rows($query1) <= 0)
		header("Location: ../index.php");
	else
	{
		while ($row=mysqli_fetch_assoc($query1))
		{
			$barbershopId = $row["barbershopId"];
		}
	}
	if (is_null($barbershopId) || $barbershopId <= 0)
		header("Location: ../index.php");
	$query2 = mysqli_query($conn, "select adminemail from barbershop where barbershopId = '" . mysqli_real_escape_string($conn, $barbershopId) . "'");
	if (mysqli_num_rows($query2) > 0)
	{
		while ($row=mysqli_fetch_assoc($query2))
		{
			if ($row["adminemail"] != mysqli_real_escape_string($conn, $_SESSION["email"]))
				header("Location: ../barbershopPage.php");
		}
	}
}
if ($_POST && $conn)
{
	$query2 = mysqli_query($conn, "select email, beingdeleted, count(*) upcomingAppointments from professional inner join scheduling on email = professionalEmail where md5(email) = '" . mysqli_real_escape_string($conn, $_POST["professional"]) . "' and accepted='1' and concat(scheduling.year,'-',scheduling.month,'-',scheduling.day) >= '" . $today->format("Y-m-d") . "' group by professionalEmail union select email, beingdeleted, 0 from professional inner join scheduling on email = professionalEmail where md5(email) = '" . mysqli_real_escape_string($conn, $_POST["professional"]) . "' and accepted='1' and concat(scheduling.year,'-',scheduling.month,'-',scheduling.day) < '" . $today->format("Y-m-d") . "' union select email, beingdeleted, 0 from professional where md5(email) = '" . mysqli_real_escape_string($conn, $_POST["professional"]) . "' and accepted='1' and email not in (select professionalEmail from scheduling);");
	$email = null;
	if (mysqli_num_rows($query2) > 0)
	{
		while ($row=mysqli_fetch_assoc($query2))
		{
			$email = $row["email"];
			$beingdeleted = $row["beingdeleted"];
			$numAppointments = $row["upcomingAppointments"];
		}
	}
	if (!is_null($email))
	{
		if ($numAppointments > 0 && $beingdeleted == 1) // Has some upcoming appointments but was accidentally deleted
		{
			$query3 = mysqli_query($conn, "update professional set beingdeleted='0' where email = '$email'");
		}
		elseif ($numAppointments == 0) // has no upcoming appointments.
		{
			$query3 = mysqli_query($conn, "update professional set barbershopId=null, accepted=0 where email = '$email'");
			$query4 = mysqli_query($conn, "delete from professionalhours where professionalEmail = '$email'");
			$query5 = mysqli_query($conn, "delete from professionaloff where professionalEmail = '$email'");
			$query6 = mysqli_query($conn, "delete from notificationBoard where professionalEmail = '$email'");
			$query7 = mysqli_query($conn, "delete from scheduling where professionalEmail = '$email'");
		}
		elseif ($numAppointments > 0 && $beingdeleted == 0)
		{
			$query3 = mysqli_query($conn, "update professional set beingdeleted='1' where email = '$email'");
		}
		mysqli_close($conn);
		header("Location: takeProfessionalsOff.php");
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Take Professionals Off</title>
<?php include 'header.php'; ?>
</head>
<body>
<?php include 'navbar.php'; ?>
<h2>Take Professionals Off</h2>
<?php
if (!$conn)
	die("<p>Sorry, unable to do this.</p>");
$withAppointments = mysqli_query($conn, "select firstName, lastName, email from professional inner join scheduling on email = professionalEmail where barbershopId = '" . mysqli_real_escape_string($conn, $barbershopId) . "' and accepted='1' and beingdeleted = '0' and email <> '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and concat(scheduling.year,'-',scheduling.month,'-',scheduling.day) >= '" . $today->format("Y-m-d") . "'");
$canBeRestored = mysqli_query($conn, "select firstName, lastName, email from professional inner join scheduling on email = professionalEmail where barbershopId = '" . mysqli_real_escape_string($conn, $barbershopId) . "' and accepted='1' and beingdeleted = '1' and email <> '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and concat(scheduling.year,'-',scheduling.month,'-',scheduling.day) >= '" . $today->format("Y-m-d") . "'");
$withoutAppointments = mysqli_query($conn, "select firstName, lastName, email from professional where barbershopId = '" . mysqli_real_escape_string($conn, $barbershopId) . "' and accepted='1' and email <> '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and email not in (select professionalEmail from scheduling) union select firstName, lastName, email from professional inner join scheduling on email = professionalEmail where barbershopId = '" . mysqli_real_escape_string($conn, $barbershopId) . "' and accepted='1' and email <> '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and scheduling.id not in (select scheduling.id from professional inner join scheduling on email = professionalEmail where barbershopId = '" . mysqli_real_escape_string($conn, $barbershopId) . "' and email <> '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and concat(scheduling.year,'-',scheduling.month,'-',scheduling.day) > '" . $today->format("Y-m-d") . "')");
if (mysqli_num_rows($withAppointments) <= 0 && mysqli_num_rows($canBeRestored) <= 0 && mysqli_num_rows($withoutAppointments) <= 0)
	die("<p>No other professionals work here. <a href=\"leaveBarbershop.php\">Now you can take this barbershop off.</a></p>");
if (mysqli_num_rows($canBeRestored) > 0)
{
	
	echo "<form method=\"post\"><p>Professionals With Upcoming Appointments Who Have Been Marked:</p><ul>";
	while($row=mysqli_fetch_assoc($canBeRestored))
	{
		$email2 = md5($row["email"]);
		echo "<li><input type=\"radio\" name=\"professional\" value=\"$email2\" id=\"$email2\" onclick=\"document.getElementById('restore').disabled = false;\" /><label for=\"$email2\">{$row["firstName"]} {$row["lastName"]}</label></li>\r\n";
	}
	echo "</ul><input type=\"submit\" value=\"Restore\"  id=\"restore\" disabled /></form><hr>";
}
echo "<form method=\"post\" onsubmit=\"return confirm('Are you sure?')\">";
if (mysqli_num_rows($withAppointments) > 0)
{
	echo "<p>Professionals With Upcoming Appointments:</p><ul>";
	while($row=mysqli_fetch_assoc($withAppointments))
	{
		$email2 = md5($row["email"]);
		echo "<li><input type=\"radio\" name=\"professional\" value=\"$email2\" id=\"$email2\" onclick=\"document.getElementById('takeoff').disabled = false;\" /><label for=\"$email2\">{$row["firstName"]} {$row["lastName"]}</label></li>\r\n";
	}
	echo "</ul>";
}

if (mysqli_num_rows($withoutAppointments) > 0)
{
	echo "<p>Professionals Available to Remove:</p><ul>";
	while($row=mysqli_fetch_assoc($withoutAppointments))
	{
		$email2 = md5($row["email"]);
		echo "<li><input type=\"radio\" name=\"professional\" value=\"$email2\" id=\"$email2\" onclick=\"document.getElementById('takeoff').disabled = false;\" /><label for=\"$email2\">{$row["firstName"]} {$row["lastName"]}</label></li>\r\n";
	}
	echo "</ul>";
}
mysqli_close($conn);
echo "<input type=\"submit\" value=\"Take Off Professional\" id=\"takeoff\" disabled /></form>";
?>
</body>
</html>