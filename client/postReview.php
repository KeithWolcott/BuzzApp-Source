<?php
session_start();
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 1)
	header("Location: ../login.php");
if ($_POST && isset($_POST["post"])) // if the user is logged in as a client and submitted a post
{
	  include '../config.php';
	  include '../functions.php';
	  if ($conn)
	  {
		  if (strlen($_POST["post"]) > 0 && strlen($_POST["post"]) < 1000) // if the post is the right length
		  {
			  $rating = 3;
			  if (isset($_POST["rating"]) && $_POST["rating"] > 0 && $_POST["rating"] < 6) // the default rating is 3, in case the rating isn't between 1 and 5
				  $rating = $_POST["rating"];
			  $result2 = mysqli_query($conn, "select id from scheduling where md5(id) = '" . mysqli_real_escape_string($conn, $_POST["id"]) . "'");
			  if (mysqli_num_rows($result2) > 0) {
				  while($row = mysqli_fetch_assoc($result2)) {
					  $id = mysqli_real_escape_string($conn, $row["id"]);
				  }
				  $now = fixtime(new DateTime("now"));
				  $result = mysqli_query($conn, "insert into rating (rating, schedulingId, text, year, month, day, time) values ('$rating', '$id', '" . htmlspecialchars($_POST["post"]) . "', '" . $now->format("Y") . "', '" . $now->format("m") . "', '" . $now->format("d") . "', '" . $now->format("h:i A") . "')");
				  $result3 = mysqli_query($conn, "update scheduling set madereview='1', remindaboutreview='0' where id = '$id'"); // add the rating to the database
			  }
		  }
		  mysqli_close($conn);
	  }
}	
  ?>