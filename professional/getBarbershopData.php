<?php
include '../config.php';
include '../functions.php';
if ($conn && $_POST && isset($_POST["city"]) && isset($_POST["address"]) && isset($_POST["name"]) && isset($_POST["zip"]))
{
	$result = mysqli_query($conn, "select image from barbershop where name='" . mysqli_real_escape_string($conn, $_POST["name"]) . "' and address='" . mysqli_real_escape_string($conn, $_POST["address"]) . "' an city='" . mysqli_real_escape_string($conn, $_POST["city"]) . "' and zip = '" . mysqli_real_escape_string($conn, $_POST["zip"]) . "'");
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_assoc($result))
		{
			echo fiximage($row["image"]);
		}
	}
	else
	{
		echo "";
	}
}
?>