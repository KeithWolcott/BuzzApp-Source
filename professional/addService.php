<?php
session_start();
include '../config.php';
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2)
{
	$result = mysqli_query($conn, "select adminemail, barbershopId from barbershop where barbershopId in (select barbershopId from professional where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "')");
	if (mysqli_num_rows($result) > 0)
	{
		while($row = mysqli_fetch_assoc($result))
		{
			$adminemail = $row["adminemail"];
			$barbershopId = $row["barbershopId"];
		}
	}
	if ($adminemail == $_SESSION["email"] && $_POST && $conn)
	{
		if ($_POST["serviceName"] != "" && is_numeric($_POST["price"]))
		{
			if (isset($_POST["free"]))
				$free = 1;
			else
				$free = 0;
			if (isset($_POST["discount"]) && strtolower(trim($_POST["discount"])) != "none" && is_numeric($_POST["discount"]))
				$discount = $_POST["discount"];
			else
				$discount = 0;
			if (is_numeric($_POST["duration"]) && $_POST["duration"] >= 5 && $_POST["duration"] <= 120)
				$query = mysqli_query($conn, "insert into services(barbershopId, name, price, description, duration, free, discountedlimit) values('$barbershopId', '" . mysqli_real_escape_string($conn, $_POST["serviceName"]) . "', '" . mysqli_real_escape_string($conn, $_POST["price"]) . "', '" . htmlspecialchars($_POST["description"],ENT_QUOTES) . "', '" . mysqli_real_escape_string($conn, $_POST["duration"]) . "', '$free', '" . mysqli_real_escape_string($conn, $discount) . "') on duplicate key update name='" . mysqli_real_escape_string($conn, $_POST["serviceName"]) . "', price='" . mysqli_real_escape_string($conn, $_POST["price"]) . "', description='" . htmlspecialchars($_POST["description"],ENT_QUOTES) . "', duration='" . mysqli_real_escape_string($conn, $_POST["duration"]) . "', free='$free', discountedlimit='" . mysqli_real_escape_string($conn, $discount) . "'");
		}
	}
}
?>