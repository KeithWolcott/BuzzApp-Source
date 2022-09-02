<?php
session_start();
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 2)
	header("Location: ../login.php");
include '../config.php';
include '../functions.php';
  // Get barbershopid, name, image, address, city, state, zip, day, open, close. 0 rows if professional doesn't have a barbershop.
if ($conn)
{
  $result = mysqli_query($conn, "select barbershop.barbershopId idnum, name, image, address, city, state, zip, barbershophours.day, open, close, professional.barbershopId, adminemail, accepted from (barbershop natural left join barbershophours), professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershop.barbershopId=professional.barbershopId");
  $openhours = array();
  $closehours = array();
  $days = array();
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			$id = $row["idnum"];
			$title = $row["name"];
			$address = $row["address"];
			$city = $row["city"];
			$state = $row["state"];
			$zip = $row["zip"];
			$admin = $row["adminemail"];
			$image = fiximage($row["image"]);
			$accepted = $row["accepted"];
		  if (!is_null($row["day"]))
		  {
			  $openhours[convertday($row["day"])] = $row["open"];
			  $closehours[convertday($row["day"])] = $row["close"];
			  array_push($days, convertday($row["day"]));
		  }
		}
	}
	else
	{
		header("Location: setupBarbershop.php");
	}
}
?>
<html>
<head>
<?php include 'header.php'; ?>
<script src="../upload.js"></script>
<title><?php echo $title; ?></title>
</head>
<body>
<?php include 'navbar.php'; ?><br>
<div class="leftBarbershop">
<?php
if ($admin == $_SESSION["email"])
{
	if ($_POST && isset($_POST["uploadImage"]))
	{ // If they uploaded an image
		require('../aws/aws-autoloader.php');
		$s3 = new Aws\S3\S3Client([
			'version'  => '2006-03-01',
			'region'   => 'us-east-2',
		]);
		$msgs = array();
		$bucket = getenv('S3_BUCKET')?: array_push($msgs,'No "S3_BUCKET" config var in found in env!');
		if (!is_uploaded_file($_FILES['file_upload']['tmp_name']))
			array_push($msgs,"Didn't choose an image, or the image might be too large.");
		if (count($msgs) == 0)
		{
		$uploadImage = $_FILES['file_upload']['name'];
		if($uploadImage != "")
		{
			$uploadOk = 1;
			$imageFileType = strtolower(pathinfo(basename($_FILES["file_upload"]["name"]),PATHINFO_EXTENSION));
			// Check if image file is a actual image or fake image
			$check = getimagesize($_FILES["file_upload"]["tmp_name"]);          ///////////////////
			if($check === false) {
				array_push($msgs,"File is not an image.");
				$uploadOk = 0;
			}
			// Check file size
			if ($_FILES["file_upload"]["size"] > 5000000) {
				array_push($msgs,"Sorry, " .  basename( $_FILES["file_upload"]["name"]) ." is too large.");
				$uploadOk = 0;
			}
			// Allow certain file formats
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
			&& $imageFileType != "gif") {
				array_push($msgs,"Sorry, only JPG, JPEG, PNG & GIF , PDF files are allowed.");
				$uploadOk = 0;
			}
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 0) {
				array_push($msgs,"Sorry, your file was not uploaded.");
			// if everything is ok, try to upload file
			} else {
				$upload = $s3->upload($bucket, basename($_FILES["file_upload"]["tmp_name"]), fopen($_FILES["file_upload"]["tmp_name"], 'rb'), 'public-read');
				$imageName = $upload->get('ObjectURL');
				$image = $imageName;
				if ($conn && isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"]==2)
				{
					// $image is the Amazon S3 link to the image
					$query2 = mysqli_query($conn, "UPDATE barbershop set image = '" . mysqli_real_escape_string($conn, $imageName) . "' where barbershopId='$id'");
				}
			}
		}
		}
			if (count($msgs) > 0)
			{
				echo "<ul>";
				foreach ($msgs as $msg)
					echo "<li>$msg</li>";
				echo "</ul>";
			}
	}
	echo "<img onclick=\"toggleUpload();\" style=\"cursor:pointer;\" src=\"$image\" class=\"barbershopImage adminBarbershop\"><div id=\"uploadNewImage\" style=\"display:none;\">
	<form id=\"uploadform\" method=\"post\" enctype=\"multipart/form-data\" onsubmit=\"return checkFile(this,true);\">
                <input type=\"file\" id=\"file_upload\" name=\"file_upload\" accept=\".png,.gif,.jpg,.jpeg\" >
            <input type=\"submit\" name=\"uploadImage\" class=\"uploadButton\" value=\"Upload Image\" />
			<div id=\"progressbox\"><div id=\"progressbar\"></div><div id=\"statustxt\">0%</div></div>
</form></div>";
}
else
{
	echo "<img src=\"$image\" class=\"barbershopImage\">";
}
if (isset($title))
{
	if ($admin == $_SESSION["email"])
	{
		echo "<form method=\"post\">
		<input name=\"newname\" class=\"newname\" id=\"newname\" onkeydown=\"if (event.keyCode == 13) changeName(this);\" value=\"" . str_replace('"',"&quot;",$title) . "\" style=\"display:none\" /><h1 id=\"curname\" onclick=\"displayName();\">$title</h1> &nbsp; <img src=\"../images/pen.png\" class=\"pen\" onclick=\"displayName();\"></form>";
	}
	else
	{
		echo "<h1>$title</h1>";
	}
}
else
	die("<h1>No Barbershop</h1>");
$addressstring = "$city, $state $zip";
if (!empty($address))
	$addressstring = "$address, $addressstring";
if ($admin == $_SESSION["email"])
{
	echo "<form method=\"post\">
		<input name=\"newaddress\" class=\"newaddress\" id=\"newaddress\" onkeydown=\"if (event.keyCode == 13) changeAddress(this);\" value=\"" . str_replace('"',"&quot;",$addressstring) . "\" style=\"display:none\" /><p id=\"curaddress\" onclick=\"displayAddress();\">$addressstring</p> &nbsp; <img src=\"../images/pen.png\" class=\"pen\" onclick=\"displayAddress();\"></form>";
}
else
	echo "<p>$addressstring</p>";
?>
<table><tr><td>Hours of Operations</td><?php
if ($admin == $_SESSION["email"])
	echo "<td><img src=\"../images/pen.png\" class=\"pen\" onclick=\"editHours(this);\" id=\"edithours\"></td>";
?></tr></table><div id="hourtable"><table>
<?php
if (count($days) > 0)
{
	foreach ($days as $day)
	{
		echo "<tr><td>$day</td><td>{$openhours[$day]} - {$closehours[$day]}</td></tr>\r\n";
	}
}
else
{
	echo "<tr><td>No hours of operations available</td></tr>";
	if ($admin == $_SESSION["email"])
		echo "<tr><td>Customers will not be able to find this barbershop until the hours is posted.</td></tr>";
}
?>
</table></div><?php
$februraydays = (is_leap_year(date("Y")) ? 29 : 28);
$maxyears = Date("Y", strtotime("+3 years"));
$months = array("January"=>31,"February"=>$februraydays,"March"=>31,"April"=>30,"May"=>31,"June"=>30,"July"=>31,"August"=>31,"September"=>30,"October"=>31,"November"=>30,"December"=>31);
$months2 = array_keys($months); // $months2 is just the string version of the months
$daysoftheweek = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
if ($admin == $_SESSION["email"])
{
	echo "<div id=\"hours\" style=\"display:none;\"><form id=\"hoursform\" onsubmit=\"return changeHours(this)\"><ul>";
	foreach ($daysoftheweek as $day)
	{
		$day2 = strtolower($day);
		echo "<li>$day: <input type=\"checkbox\" id=\"$day2\" value=\"closed\" onclick=\"allowFor(this)\" name=\"{$day2}closed\" ";
		if (!in_array($day,$days))
			echo "checked ";
		echo "/><label for=\"$day2\" />Closed</label><span id=\"{$day2}info\"";
		if (!in_array($day,$days))
			echo " class=\"greyout\" ";
		echo "> &nbsp; Open: <input type=\"text\" name=\"{$day2}openhour\" id=\"{$day2}openhour\" value=\"";
		if (array_key_exists($day,$openhours))
		{
			echo $openhours[$day];
		}
		echo "\" maxlength=\"8\" size=\"8\" /> &nbsp; Close: <input type=\"text\" name=\"{$day2}closehour\" value=\"";
		if (array_key_exists($day,$closehours))
		{
			echo $closehours[$day];
		}
		echo "\" maxlength=\"8\" size=\"8\" id=\"{$day2}closehour\" /></span></li>\r\n
		<script>
			$('#{$day2}openhour').timepicker({
			'step': 15,
			'timeFormat': 'h:i A'});
			$('#{$day2}closehour').timepicker({
			'step': 15,
			'timeFormat': 'h:i A'});</script>";
	}
	echo "</ul><input type=\"submit\" value=\"Submit\" /></form></div>";
	if (count($days) > 0)
	{
		echo "<p>Are your hours going to be different on an upcoming date? <A href=\"barbershopCalendar.php\">Indicate it.</a></p>";
	}
	$numEmployees = mysqli_query($conn, "select email from professional where barbershopId='$id'");
	if (mysqli_num_rows($numEmployees) > 1)
		echo "<p>Does a professional no longer work here? <a href=\"takeProfessionalsOff.php\">Indicate it.</a></p><p>If your barbershop is closing permanently, remove all the professionals first.</p>";
	else
		echo "<p>Is your barbershop permanently closing? <a href=\"leaveBarbershop.php\">Remove it here.</a></p>";
}
if (!$accepted)
{
	$adminsearch = mysqli_query($conn, "select firstName, lastName from professional where email = '" . mysqli_real_escape_string($conn, $admin) . "'");
	if (mysqli_num_rows($adminsearch) == 1)
	{
		$row = mysqli_fetch_assoc($adminsearch);
		die("<p>{$row["firstName"]} {$row["lastName"]} will need to verify you are an employee here.</p>");
	}
}
?>
</div><div class="rightBarbershop"><h2>Notification Board</h2>
<input type="button" id="showForm" value="Make Post" /><br><div id="messageForm" class="float" style="display:none;"><form method="post" id="postMessage" action="postMessage.php"><input type="button" id="x" value="X" /><textarea name="post" maxlength="500" id="post" rows="6" oninput="updateCharactersLeft();"></textarea><br><span id="charactersLeft">500 characters left.</span><br><input type="checkbox" id="appointmentsonly" value="appointmentsonly" name="appointmentsonly"><label for="appointmentsonly">Show only to clients with upcoming appointments here</label><br><input type="submit" value="Post" /></form></div><div class="contentBelow"><?php
$result2 = mysqli_query($conn, "SELECT postid, message, notificationBoard.year, notificationBoard.month, notificationBoard.day, notificationBoard.time, firstName, lastName, professionalEmail FROM notificationBoard, professional where professionalEmail = email and email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' union SELECT postid, message, notificationBoard.year, notificationBoard.month, notificationBoard.day, notificationBoard.time, firstName, lastName, professionalEmail FROM notificationBoard, professional where professionalEmail = email and email <> '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershopId = $id and (paid = '1' or (paid = '0' and DATEDIFF(NOW(), STR_TO_DATE(concat(professional.day,'/',professional.month,'/',professional.year),'%d/%m/%Y')) < 30)) order by postid desc limit 1");
if (mysqli_num_rows($result2) <= 0) {
	echo "<p>No posts on notification board yet.</p>";
}
else
{
	echo "<p>Most recent post on Notification Board:</p><div class=\"mostRecent\" id=\"mostRecent\">";
	while($row = mysqli_fetch_assoc($result2)) {
		$date2 = new DateTime("{$row["year"]}-{$row["month"]}-{$row["day"]} {$row["time"]}");
		$datestr = datedifference($date2);
		echo "<div class=\"messagePost\"><form><input type=\"hidden\" name=\"post\" value=\"{$row["postid"]}\" /><p>{$row["firstName"]} {$row["lastName"]}</p><div class=\"center\"><p>{$row["message"]}</p></div><p><span class=\"timeago\" title=\"{$row["month"]}-{$row["day"]}-{$row["year"]} {$row["time"]}\">$datestr</span>";
		if ($row["professionalEmail"] == $_SESSION["email"] || $admin == $_SESSION["email"]) // Admins can delete every message from the barbershop.
			echo "<span class=\"deleteButton\"><input type=\"button\" onclick=\"deleteMessage(this)\" value=\"Delete Message\" /></span>";
		echo "</form></div>";
	}
	echo "<p><a href=\"manageNotificationBoard.php\">Manage this barbershop's notification board</a></p></div>";
}
?></div><?php
if ($admin == $_SESSION["email"])
	echo "<p><a href=\"manageServices.php\">Manage your services here</a></p>";
?>
<div id="allchangeHours"><?php
$result3 = mysqli_query($conn, "select day, start, end from professionalhours where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' order by day");
if (mysqli_num_rows($result3) <= 0) {
	if (count($openhours) > 0)
	{
		// This part is for a professional putting in his hours for the first time.
		echo "<div style=\"border:1px solid black;padding:5px 10px;\" id=\"professionalhours\"><p>Put in your hours:</p><form id=\"professionalhoursform\" onsubmit=\"return changeHours(this);\" method=\"post\" ><ul>";
		foreach ($daysoftheweek as $day)
		{
			// Yep this code again
			$day2 = strtolower($day);
			if (in_array($day,$days))
			{
				echo "<li>$day: <input type=\"checkbox\" id=\"{$day2}professional\" value=\"off\" onclick=\"allowFor(this)\" name=\"{$day2}off\" ";
			if (!in_array($day,$days))
				echo "checked ";
			echo "/><label for=\"{$day2}professional\" />Off</label><span id=\"{$day2}professionalinfo\"";
			if (!in_array($day,$days))
				echo " class=\"greyout\" ";
			echo "> &nbsp; Open: <input type=\"text\" name=\"{$day2}open\" id=\"{$day2}professionalopenhour\" value=\"";
				if (array_key_exists($day,$openhours))
				{
					echo $openhours[$day];
				}
				echo "\" maxlength=\"8\" size=\"8\" /> &nbsp; Close: <input type=\"text\" name=\"{$day2}close\" value=\"";
				if (array_key_exists($day,$closehours))
				{
					echo $closehours[$day];
				}
				echo "\" maxlength=\"8\" size=\"8\" id=\"{$day2}professionalclosehour\" /></span></li>\r\n
				<script>
				$('#{$day2}professionalopenhour').timepicker({
				'step': 15,
				'timeFormat': 'h:i A',
				'minTime': '{$openhours[$day]}',
				'maxTime': '{$closehours[$day]}',
				'scrollDefault': 'now'});
				$('#{$day2}professionalclosehour').timepicker({
				'step': 15,
				'timeFormat': 'h:i A',
				'minTime': '{$openhours[$day]}',
				'maxTime': '{$closehours[$day]}',
				'scrollDefault': 'now'});</script>";
			}
		}
		echo "</ul><input type=\"submit\" value=\"Submit\" /></form></div>";
	}
}
else
{
	// This part is for editing the hours currently in the database.
	echo "<p>Do you need to change your lunch break schedule? <a href=\"addLunchBreak.php\">Go here</a>.</p><p>Has the hours you work changed? &nbsp; <input type=\"button\" onclick=\"showChangeHours();\" value=\"Fix it\" /></p><div id=\"changeHours\" style=\"display:none;\"><form id=\"changeHoursForm\" method=\"post\" onsubmit=\"return changeHours(this)\"><ul>";
	$currentdays = array();
	while($row = mysqli_fetch_assoc($result3)) {
		$currentdays[convertday($row["day"])] = array($row["start"],$row["end"]);
	}
	foreach ($daysoftheweek as $day)
	{
		$day2 = strtolower($day);
		if (in_array($day,$days))
		{
			echo "<li>$day: <input type=\"checkbox\" id=\"{$day2}professional\" value=\"off\" onclick=\"allowFor(this)\" name=\"{$day2}off\" ";
		if (!array_key_exists($day,$currentdays))
			echo "checked ";
		echo "/><label for=\"{$day2}professional\" />Off</label><span id=\"{$day2}professionalinfo\"";
		if (!array_key_exists($day,$currentdays))
			echo " class=\"greyout\" ";
		echo "> &nbsp; Open: <input type=\"text\" name=\"{$day2}open\" id=\"{$day2}professionalopenhour\" value=\"";
			if (array_key_exists($day,$currentdays))
			{
				echo $currentdays[$day][0];
			}
			echo "\" maxlength=\"8\" size=\"8\" /> &nbsp; Close: <input type=\"text\" name=\"{$day2}close\" value=\"";
			if (array_key_exists($day,$currentdays))
			{
				echo $currentdays[$day][1];
			}
			echo "\" maxlength=\"8\" size=\"8\" id=\"{$day2}professionalclosehour\" /></span></li>\r\n
			<script>
			$('#{$day2}professionalopenhour').timepicker({
			'step': 15,
			'timeFormat': 'h:i A',
			'minTime': '{$openhours[$day]}',
			'maxTime': '{$closehours[$day]}',
			'scrollDefault': 'now'});
			$('#{$day2}professionalclosehour').timepicker({
			'step': 15,
			'timeFormat': 'h:i A',
			'minTime': '{$openhours[$day]}',
			'maxTime': '{$closehours[$day]}',
			'scrollDefault': 'now'});</script>";
		}
	}
	echo "</ul><input type=\"submit\" value=\"Fix\" /><p>You'll need to add your lunch break hours again after changing your schedule.</p></form></div><p>Is your schedule going to be different on an upcoming date? <A href=\"professionalCalendar.php\">Indicate it.</a></p>";
}
?>
</div></div>
<script>
function deleteMessage(butn)
{
	if (confirm("Are you sure?"))
	{
		var f = $(butn).parent().parent().parent();
		$.ajax({
			type: 'post',
			url: 'deleteMessage.php',
			data: $(f).serialize(),
			success: function () {
				generateMostRecent();
			}
		  });
	}
}
_pe.addProfileId(3);
</script>

<?php
mysqli_close($conn);
?>
</body>
</html>