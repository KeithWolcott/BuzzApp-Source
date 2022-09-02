<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.7/themes/base/jquery-ui.css">  
<script src="../navbarjs.js"></script>
<script src="professional.js"></script>
<script src="calendar.js"></script>
<link rel="stylesheet" href="../jquery.timepicker.css" />
<script src="../jquery.timepicker.js" type="text/javascript"></script>
<?php
if ($conn)
{
	$findadmin = mysqli_query($conn, "select adminemail from barbershop inner join professional on barbershop.barbershopId = professional.barbershopId where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
	if ($findadmin && mysqli_num_rows($findadmin) > 0)
	{
		$row = mysqli_fetch_assoc($findadmin);
		if ($row["adminemail"] == $_SESSION["email"])
			echo "<script src=\"admin.js\"></script>";
	}
}
?>
<script>
if (isMobileDevice())
{
	document.write("<link rel=\"stylesheet\" href=\"../mobile.css\" />");
}
else
{
	document.write("<link rel=\"stylesheet\" href=\"../stylesheet.css\" />");
}
</script>