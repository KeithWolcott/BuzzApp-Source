<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="navbarjs.js"></script>
<link rel="stylesheet" href="jquery.timepicker.css" />
<script src="jquery.timepicker.js" type="text/javascript"></script>
<?php echo (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 1 ? "<script src=\"client.js\"></script>" : ""); ?>
<?php echo (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2 ? "<script src=\"professional/professional.js\"></script>\r\n<script src=\"professional.js\"></script>" : ""); ?>
<script>
if (isMobileDevice())
{
	document.write("<link rel=\"stylesheet\" href=\"mobile.css\" />");
}
else
{
	document.write("<link rel=\"stylesheet\" href=\"stylesheet.css\" />");
}
</script>