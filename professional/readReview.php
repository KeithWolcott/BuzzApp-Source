<?php
session_start();
include '../config.php';
if ($_SESSION["email"] && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2 && $_POST && isset($_POST["id"]) && $conn)
{
	$result = mysqli_query($conn, "update rating set readReview=1 where md5(ratingId) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'");
}
?>