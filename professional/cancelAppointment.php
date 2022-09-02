<?php
session_start();
include '../config.php';
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2 && $_POST && isset($_POST["id"]) && isset($_POST["reason"]) && strlen($_POST["reason"]) < 500 && $conn)
{
	$query2 = mysqli_query($conn, "select professionalEmail from scheduling where md5(id) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'");
	if (mysqli_num_rows($query2) > 0)
	{
		while($row=mysqli_fetch_assoc($query2))
		{
			$email = $row["professionalEmail"];
			if ($email == mysqli_real_escape_string($conn, $_SESSION["email"]))
			{
				$query = mysqli_query($conn, "update scheduling set cancelled='1', reasonforcancellation='" . htmlentities($_POST["reason"],ENT_QUOTES) . "', remindaboutcancellation='1', cancelledbyprofessional='1' where md5(id) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'");
				break;
			}
		}
	}
}
?>