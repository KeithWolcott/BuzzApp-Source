<?php
session_start();
include '../config.php';
include '../functions.php';
if (!$_SESSION["email"] || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 2)
	header("Location: ../index.php");
?>
<!DOCTYPE html>
<html>
<head>
<?php include 'header.php'; ?>
<title>Check Reviews</title>
</head>
<body>
<?php include 'navbar.php'; ?>
<h1>Check Reviews</h1>
<?php
if (!$conn)
	die("<p>Unable to connect to database</p>");
$result = mysqli_query($conn, "select ratingId, rating, text, rating.year, rating.month, rating.day, rating.time, firstName, lastName, profilePicture, readReview, (select count(readReview) from rating where schedulingId in (select id from scheduling where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "') and readReview = 0) unread from rating inner join scheduling on rating.schedulingId = scheduling.id inner join client on scheduling.clientEmail = client.email where schedulingId in (select id from scheduling where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "')");
$unread = -1;
if (mysqli_num_rows($result) <= 0)
	echo "<p>Nobody has made a review about you yet.</p>";
else
{
	while ($row=mysqli_fetch_assoc($result))
	{
		if ($unread == -1)
		{
			$unread = $row["unread"];
			echo "<p id=\"unreadMsg\">($unread unread.)</p>";
		}
		echo "<div class=\"reviews\">";
		$date = new DateTime("{$row["year"]}-{$row["month"]}-{$row["day"]} {$row["time"]}");
		$datestr = datedifference($date);
		echo "<div><span class=\"reviewLink\" onclick=\"revealReview(this)\"><span name=\"review1\" class=\"review";
		if ($row["readReview"] == 0)
			echo " unread";
		echo "\">{$row["firstName"]} {$row["lastName"]}</span> ";
		for ($i=1;$i<=$row["rating"];$i++)
			echo "<img src=\"../images/star.png\" width=\"36\" height=\"36\" />";
		for ($i=$row["rating"];$i<5;$i++)
			echo "<img src=\"../images/blankstar.png\" width=\"36\" height=\"36\" />";
		echo " <span style=\"float:right;\" name=\"review2\" class=\"review";
		if ($row["readReview"] == 0)
			echo " unread";
		echo "\">$datestr</span></span>
		<div class=\"reviewContents\" name=\"contents\" style=\"display:none;\"><div class=\"review\">";
		if (is_null($row["profilePicture"]))
			echo "<img src=\"images/defaultavatar.png\" alt=\"No Profile Picture\">";
		else
			echo "<img src=\"{$row["profilePicture"]}\" class=\"profilePicture\" alt=\"Profile Picture\">";
		echo "<br>{$row["firstName"]} {$row["lastName"]}</div>";
		for ($i=1;$i<=$row["rating"];$i++)
			echo "<img src=\"../images/star.png\" width=\"36\" height=\"36\" /> &nbsp; ";
		for ($i=$row["rating"];$i<5;$i++)
			echo "<img src=\"../images/blankstar.png\" width=\"36\" height=\"36\" /> &nbsp; ";
		echo "<br><p>{$row["text"]}</p><form method=\"post\"><input type=\"hidden\" name=\"id\" value=\"";
		echo md5($row["ratingId"]);
		echo "\" /></form></div></div><br>";
	}
	echo "</div>";
}
?>
<script>
var unread = <?php echo $unread; ?>;
function revealReview(sp)
{
	var review = $(sp).parent().find("div[name='contents']");
	var link1 = $(sp).parent().find("span[name='review1']");
	var link2 = $(sp).parent().find("span[name='review2']");
	var form = $(review).find("form:first");
	$(review).slideToggle("slow");
	if ($(link1).hasClass("unread"))
	{
		$.ajax({
			type: 'post',
			url: 'readReview.php',
			data: $(form).serialize(),
			success: function () {
				unread = unread - 1;
				if (unread >= 0)
				{
					$("#unreadMsg").html("(" + unread + " unread)");
				}
				link1.removeClass("unread");
				link2.removeClass("unread");
			}
		  });
	}
}
</script>
</body>
</html>