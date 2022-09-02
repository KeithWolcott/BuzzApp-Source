<?php
session_start();
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 2)
	header("Location: ../login.php");
include '../config.php';
include '../functions.php';
$barbershopId = 0;
$email = mysqli_real_escape_string($conn, $_SESSION["email"]);
if ($conn)
{
	$query1 = mysqli_query($conn, "select barbershopId from professional where email = '$email'");
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
			if ($row["adminemail"] != $email)
				header("Location: ../barbershopPage.php");
		}
	}
}
if ($_POST && $conn)
{
	$cancontinue = true;
	$msg = "";
	if ($_POST["password"] == "" && $_POST["confirmpassword"] == "")
	{
		$cancontinue = false;
		$msg = "Need to fill in both fields.";
	}
	if ($_POST["password"] != $_POST["confirmpassword"])
	{
		$cancontinue = false;
		$msg = "Passwords don't match.";
	}
	if ($cancontinue)
	{
		$query3 = mysqli_query($conn, "select email from professional where barbershopId = '$barbershopId'");
		if (mysqli_num_rows($query3) == 1) // only the administrator is hired.
		{
			
			$query4 = mysqli_query($conn, "update professional set barbershopId=null, accepted=0 where email = '$email'");
			$query5 = mysqli_query($conn, "delete from professionalhours where professionalEmail = '$email'");
			$query6 = mysqli_query($conn, "delete from professionaloff where professionalEmail = '$email'");
			$query7 = mysqli_query($conn, "delete from notificationBoard where professionalEmail = '$email'");
			$query8 = mysqli_query($conn, "delete from scheduling where professionalEmail = '$email'");
			$query9 = mysqli_query($conn, "update barbershop set adminemail=null where barbershopId='$barbershopId'");
			mysqli_close($conn);
			header("Location: ../index.php");
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Leave Barbershop</title>
<?php include 'header.php'; ?>
</head>
<body>
<?php include 'navbar.php'; ?><div class="center">
<h2>Leave Barbershop</h2>
<?php
if (!$conn)
	die("<p>Sorry, unable to do this.</p>");
$professionalsWorking = mysqli_query($conn, "select email from professional where barbershopId = '" . mysqli_real_escape_string($conn, $barbershopId) . "' and accepted = '1' and email <> '$email'");
if (mysqli_num_rows($professionalsWorking) > 0)
	die("<p>Before you can remove your barbershop, no other professionals must work here. <a href=\"takeProfessionalsOff.php\">Remove all the professionals first.</a></p>");
echo "<form method=\"post\"><p>Type in your password to confirm the deletion of this barbershop.</p>
<p style=\"line-height:1.5em;\">Password: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type=\"password\" name=\"password\" required oninput=\"checkSubmit();\" id=\"password\" /><br />
Confirm password: <input type=\"password\" name=\"confirmpassword\" id=\"confirmpassword\" required oninput=\"checkSubmit();\" /></p>
<input type=\"submit\" value=\"Delete Barbershop\" id=\"deletebutton\" disabled /></form>";
mysqli_close($conn);
?></div>
</body>
</html>