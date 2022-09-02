<?php
session_start();
if (!isset($_SESSION["email"]) || $_SESSION["accountType"] != 2)
	header("Location: ../login.php");
include '../config.php';
include '../functions.php';
?>
<html>
<head>
<?php include 'header.php'; ?>
<title>Manage Notification Board</title>
<script src="notificationBoard.js"></script>
<body onload="loadMessages();">
<?php include '../navbar.php';
if (!$conn)
	die("<br>Connection failed: " . mysqli_connect_error());
$result = mysqli_query($conn, "select name from barbershop, professional where professional.email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and professional.barbershopId = barbershop.barbershopId");
if (mysqli_num_rows($result) <= 0) {
	echo "<p>Invalid barbershop</p>";
}
else
{
	while($row = mysqli_fetch_assoc($result)) {
		echo "<h2>Notification Board for {$row["name"]}</h2><div id=\"messages\"></div>";
	}
}
?>
<script>
function deleteMessage(butn)
{
	if (confirm("Are you sure?"))
	{
		var div = $(butn).parent().parent().parent();
		var f = $(div).parent();
		$.ajax({
			type: 'post',
			url: 'deleteMessage.php',
			data: $(f).serialize(),
			success: function () {
				div.slideUp('fast', function() {
				  div.remove(); // After submitting the form on the side, remove the form.
				});
			}
		  });
	}
}
</script>
</body>
</html>