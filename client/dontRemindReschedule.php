<?php
session_start();
include '../config.php';
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 1 && $_POST && isset($_POST["id"]) && $conn)
{
	$query1 = mysqli_query($conn, "select * from scheduling where md5(id) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'");
	if (mysqli_num_rows($query1) > 0)
	{
		while($row=mysqli_fetch_assoc($query1))
		{
			if ($row["clientEmail"] == mysqli_real_escape_string($conn, $_SESSION["email"]))
			{
				$query2 = mysqli_query($conn, "update scheduling set remind='0' where md5(id) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'");
				break;
			}
		}
	}
}
?>