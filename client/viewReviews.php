<?php
session_start();
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 1)
	header("Location: ../login.php");
include '../config.php';
include '../functions.php';
?>
<!DOCTYPE html>
<html>
<head>
<title>View Your Reviews</title>
<?php
include 'header.php';
?>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="center"><h1>View Your Reviews</h1>
<?php
if (!$conn)
	die("<p>Unable to find reviews.</p>");
$query = mysqli_query($conn, "select rating, text, rating.year, rating.month, rating.day, rating.time, professional.firstName, professional.lastName, name, barbershop.barbershopId, image from rating inner join scheduling on rating.schedulingId = scheduling.id inner join professional on scheduling.professionalEmail = professional.email inner join barbershop on professional.barbershopId = barbershop.barbershopId where clientEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' order by ratingId desc");
if (mysqli_num_rows($query) <= 0)
{
	echo "<p>You haven't written any reviews yet.</p>";
}
else
{
	while($row = mysqli_fetch_assoc($query))
	{
		echo "<div class=\"reviews\">";
		$date = new DateTime("{$row["year"]}-{$row["month"]}-{$row["day"]} {$row["time"]}");
		$datestr = datedifference($date);
		echo "<div><span class=\"reviewLink\" onclick=\"revealReview(this)\"><span name=\"review1\">{$row["firstName"]} {$row["lastName"]}</span> ";
		for ($i=1;$i<=$row["rating"];$i++)
			echo "<img src=\"../images/star.png\" width=\"36\" height=\"36\" />";
		for ($i=$row["rating"];$i<5;$i++)
			echo "<img src=\"../images/blankstar.png\" width=\"36\" height=\"36\" />";
		echo " <span style=\"float:right;\" name=\"review2\">$datestr</span></span>
		<div class=\"reviewContents\" name=\"contents\" style=\"display:none;\"><div style=\"float:left;\"><a href=\"viewBarbershop.php?id={$row["barbershopId"]}\">";
		if (is_null($row["image"]))
			echo "<img src=\"images/clipartbarbershop.png\" alt=\"No Profile Picture\">";
		else
			echo "<img src=\"{$row["image"]}\" class=\"profilePicture\" alt=\"Profile Picture\">";
		echo "</a><p>For: {$row["firstName"]} {$row["lastName"]}<br>
		<a href=\"viewBarbershop.php?id={$row["barbershopId"]}\">{$row["name"]}</a></p></div>";
		for ($i=1;$i<=$row["rating"];$i++)
			echo "<img src=\"../images/star.png\" width=\"36\" height=\"36\" /> &nbsp; ";
		for ($i=$row["rating"];$i<5;$i++)
			echo "<img src=\"../images/blankstar.png\" width=\"36\" height=\"36\" /> &nbsp; ";
		echo "<br><p>{$row["text"]}</p></div></div><br>";
	}
}
?></div>
</body>
</html>