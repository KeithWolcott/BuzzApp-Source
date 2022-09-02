<?php
session_start();
if (!isset($_SESSION["email"]))
	header("Location: ../login.php");
include '../config.php';
include '../functions.php';
if ($conn)
{
  $result = mysqli_query($conn, "select barbershop.barbershopId idnum, name, image, address, city, state, zip, day, open, close from barbershop natural join barbershophours where barbershop.barbershopId='" . mysqli_real_escape_string($conn, $_GET["id"]) . "'");
  $result2 = mysqli_query($conn, "SELECT scheduling.id idnum, firstName, lastName, scheduling.year, scheduling.month, scheduling.day, timestart, duration, services.name, madereview, remindaboutreview, confirmed FROM barbershop, professional, (scheduling inner join services on scheduling.serviceId = services.id) WHERE scheduling.clientEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and professional.barbershopId = barbershop.barbershopId and professional.email = professionalEmail and barbershop.adminemail = professional.email and accepted = '1' and cancelled='0' order by scheduling.id desc");
  $title = "Invalid Barbershop";
  $openhours = array();
  $closehours = array();
  $days = array();
  $appointments = array();
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			$id = $row["idnum"];
			$title = $row["name"];
			$address = $row["address"];
			$city = $row["city"];
			$state = $row["state"];
			$zip = $row["zip"];
			$image	 = fiximage($row["image"]);
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
		header("Location: searchforbarbershop.php");
	}
	if (mysqli_num_rows($result2) > 0) {
		while($row = mysqli_fetch_assoc($result2)) {
			array_push($appointments,array($row["firstName"],$row["lastName"],$row["timestart"],findend($row["timestart"],$row["duration"]),$row["year"],$row["month"],$row["day"],$row["name"],$row["madereview"],$row["remindaboutreview"],$row["idnum"],$row["confirmed"]));
		}
	}
}
?>
<html>
<head>
<?php include 'header.php'; ?>
<script>
 $(function() {
            $('.date-picker').datepicker( {
            changeMonth: true,
            changeYear: true,
			minDate: 0,
            showButtonPanel: true,
            dateFormat: 'MM dd, yy',
            onClose: function(dateText, inst) { 
                $(this).datepicker('setDate', $(this).datepicker("getDate"));
            }
            });
 });
</script>
<title><?php echo $title; ?></title>
</head>
<body>
<?php include 'navbar.php'; ?><br>
<div class="leftBarbershop">
<?php
if (!$conn)
	die("Connection failed: " . mysqli_connect_error());
?>
<img src="<?php echo $image; ?>" class="barbershopImage">
<h1><?php
if (isset($title))
	echo $title;
else
	die("Invalid Barbershop");
?></h1>
<p><?php
$addressstring = "$city, $state $zip";
if (!empty($address))
	$addressstring = "$address, $addressstring";
echo $addressstring; ?></p>
<table><tr><td>Hours of Operations</td></tr>
<?php
if (count($days) > 0)
{
	foreach ($days as $day)
	{
		echo "<tr><td>$day</td><td>{$openhours[$day]} - {$closehours[$day]}</td></tr>\r\n";
	}
}
else
	echo "<tr><td>No hours of operations available</td></tr>";
?>
</table>
<h2>Employees</h2>
<?php
$ratingquery = mysqli_query($conn, "select firstName, lastName, avg(rating) avgRating, count(*) numReviews from professional left join scheduling on professional.email = scheduling.professionalEmail inner join rating on scheduling.id = rating.schedulingId where barbershopId = '$id' and accepted = '1' group by professionalEmail union select firstName, lastName, 0, 0 from professional inner join scheduling on professional.email = scheduling.professionalEmail where scheduling.id not in (select schedulingId from rating) and email not in (select professionalEmail from scheduling where id in (select schedulingId from rating)) and barbershopId = '$id' and accepted = '1' union select firstName, lastName, 0, 0 from professional where email not in (select professionalEmail from scheduling) and barbershopId = '$id' and accepted = '1' order by avgRating desc;");
$hoursquery = mysqli_query($conn, "select firstName, lastName, professionalhours.day, professionalhours.start, professionalhours.end from professional left join professionalhours on professional.email = professionalhours.professionalEmail where barbershopId = '$id' and accepted = '1'");
if (mysqli_num_rows($ratingquery) <= 0 || mysqli_num_rows($hoursquery) <= 0)
{
	echo "<p>No employee works here at the moment.</p>";
}
else
{
	echo "<ul>";
	$professional_hours = array();
	while ($row = mysqli_fetch_assoc($hoursquery))
	{
		if (!is_null($row["day"]))
		{
			if (array_key_exists("{$row["firstName"]} {$row["lastName"]}",$professional_hours))
				array_push($professional_hours["{$row["firstName"]} {$row["lastName"]}"], array($row["day"],$row["start"],$row["end"]));
			else
				$professional_hours["{$row["firstName"]} {$row["lastName"]}"] = array(array($row["day"],$row["start"],$row["end"]));
		}
	}
	while ($row = mysqli_fetch_assoc($ratingquery))
	{
		$professional = "{$row["firstName"]} {$row["lastName"]}";
		echo "<li>$professional. ";;
		if ($row["numReviews"] == 0)
			echo "No average rating.";
		else
			echo number_format($row["avgRating"],2) . " out of {$row["numReviews"]} review" . extra_s($row["numReviews"]) . ".";
		if (array_key_exists($professional,$professional_hours))
		{
			echo "<table>";
			foreach ($professional_hours[$professional] as $d)
			{
				echo "<tr><td>" . convertday($d[0]) . "</td><td>{$d[1]} - {$d[2]}</td></tr>";
			}
		}
		else
			echo "<br><table><tr><td>No hours.</td></tr>";
		echo "</table></li><br>\r\n";
	}
	echo "</ul>";
}
?></div><div class="rightBarbershop"><ul><?php
if (count($appointments) > 0)
{
	foreach ($appointments as $a)
	{
		$firstName = $a[0];
		$lastName = $a[1];
		$timestart = $a[2];
		$timeend = $a[3];
		$year = $a[4];
		$month = $a[5];
		$day = $a[6];	
		$stylewanted = $a[7];
		$madereview = $a[8];
		$remindaboutreview = $a[9];
		$confirmed = $a[11];
		$date2 = new DateTime("$year-$month-$day $timestart");
		if (!isPast($date2) && $confirmed == '1')
		{
			echo "<li>You have an appointment here with $firstName $lastName on " . $date2->format("F j, Y") . " from $timestart to $timeend. It's in " . datedifference($date2) . ". You ordered $stylewanted.</li>";
		}
	}
}
echo "</ul><p>Most recent post on Notification Board:</p>";
$result3 = mysqli_query($conn, "SELECT postid, message, notificationBoard.year, notificationBoard.month, notificationBoard.day, notificationBoard.time, upcomingonly, firstName, lastName, barbershopId FROM notificationBoard, professional where professionalEmail = email and barbershopId = $id and (paid = '1' or (paid = '0' and DATEDIFF(NOW(), STR_TO_DATE(concat(professional.day,'/',professional.month,'/',professional.year),'%d/%m/%Y')) < 30)) order by postid desc");
if (mysqli_num_rows($result3) <= 0)
	echo "<p>No posts on notification board yet.</p>";
else
{
	$countmsgs = 0;
	while($countmsgs < 1 && $row = mysqli_fetch_assoc($result3)) {
		$date2 = new DateTime("{$row["year"]}-{$row["month"]}-{$row["day"]} {$row["time"]}");
		$datestr = datedifference($date2);
		if ($row["upcomingonly"] == 0 || ($row["upcomingonly"] == 1 && count($appointments) > 0))
		{
			$countmsgs++; // Because what if the most recent message is for clients with an appointment only? Stop once a message is found.
			echo "<div class=\"messagePost\"><p>{$row["firstName"]} {$row["lastName"]}</p><div class=\"center\"><p>{$row["message"]}</p></div><p><span class=\"timeago\" title=\"{$row["month"]}-{$row["day"]}-{$row["year"]}, {$row["time"]}\">$datestr</span></div>";
		}
	}
	echo "<p><a href=\"viewNotificationBoard.php?id=$id\">View previous messages</a></p>";
}
$services = mysqli_query($conn, "select id, name, price, description from services where barbershopId = '$id' order by price");
if (mysqli_num_rows($services) <= 0)
{
	echo "<p>Oh. There's no services at this barbershop yet...</p>";
}
else
{
	$tomorrow = fixtime(new DateTime("now"));
	$tomorrow->add(new DateInterval("P1D"));
	$defaultday = $tomorrow->format("F d, Y");
	echo "<div class=\"services\"><form method=\"post\" action=\"makeAppointment.php\" onsubmit=\"return continuetoMakeAppointment(this);\"><h2>Make an Appointment</h2><p>Day: <input name=\"date\" id=\"date\" class=\"date-picker\" value=\"$defaultday\" readonly /><span id=\"datedetail\"></span></p><p>Choose Service:</p>";
	while($row = mysqli_fetch_assoc($services)) {
		$id2 = md5($row["id"]);
		echo "<div class=\"messagePost\">
		<input type=\"radio\" name=\"service\" value=\"$id2\" id=\"service$id2\" onclick=\"document.getElementById('continueappointment').disabled = false;\"><label for=\"service$id2\">{$row["name"]} - $" . $row["price"] . "
		<p>{$row["description"]}</p></label></div>";
	}
	$id2 = md5($id);
	echo "<br><input type=\"hidden\" name=\"id\" value=\"$id2\" /><input type=\"submit\" value=\"Continue\" id=\"continueappointment\" disabled /> <span id=\"appointmentstatus\"></span></form></div>";
}
if (count($appointments) > 0)
{
	foreach ($appointments as $a)
	{
		$firstName = $a[0];
		$lastName = $a[1];
		$timestart = $a[2];
		$timeend = $a[3];
		$year = $a[4];
		$month = $a[5];
		$day = $a[6];	
		$stylewanted = $a[7];
		$madereview = $a[8];
		$remindaboutreview = $a[9];
		$date2 = new DateTime("$year-$month-$day $timeend");
		$confirmed = $a[11];
		if (isPast($date2))
		{
			if ($confirmed == 1 && $madereview == 0 && $remindaboutreview == 1)
			{
				echo "<div><p>Write a review for $firstName $lastName on " . $date2->format("F j, Y") . ", where you got $stylewanted?<br><input type=\"button\" onclick=\"writeReview(this);\" value=\"Yes\" /> &nbsp; <input type=\"button\" onclick=\"dontWriteReview(this);\" value=\"No\" /></p><div id=\"reviewForm\" class=\"float\" style=\"display:none;\"><form><img src=\"../images/star.png\" class=\"star\" name=\"star\" onclick=\"ranking(1,this)\"><img src=\"../images/star.png\" class=\"star\" name=\"star\" onclick=\"ranking(2,this)\"><img src=\"../images/star.png\" class=\"star\" name=\"star\" onclick=\"ranking(3,this)\"><img src=\"../images/blankstar.png\" class=\"star\" name=\"star\" onclick=\"ranking(4,this)\"><img src=\"../images/blankstar.png\" class=\"star\" name=\"star\" onclick=\"ranking(5,this)\"><input type=\"hidden\" name=\"rating\" id=\"rating\" value=\"3\" /><input type=\"hidden\" name=\"id\" value=\"" . md5($a[10]) . "\" /><input type=\"button\" id=\"x\" value=\"X\" onclick=\"closeReview(this);\" /><textarea name=\"post\" maxlength=\"1000\" id=\"post\" rows=\"6\" oninput=\"updateCharactersLeft(this);\"></textarea><br><span id=\"charactersLeft\">1000 characters left.</span> <input type=\"button\" onclick=\"postreview(this)\" value=\"Post\" /></form></div></div>";
			}
		}
	}
}
?></div>

<?php
mysqli_close($conn);
?>
<script>
function writeReview(button)
{
	var f = $(button).parents("div:first");
	var div = f.find("#reviewForm");
	div.slideDown("slow");
}
function dontWriteReview(button)
{
	if (confirm("Are you sure? You won't be asked again."))
	{
		var div = $(button).parents("div:first");
		var f = div.find("form");
		$.ajax({
				type: 'post',
				url: 'dontPostReview.php',
				data: f.serialize(),
				success: function () {
					div.slideUp('fast', function() {
					  div.remove();
					});
				}
			  });
	}
}
function closeReview(button)
{
	var f = $(button).parent().parent().parent();
	var div = f.find("#reviewForm");
	div.slideUp("slow");
}
function ranking(num,star)
{
	var f = $(star).parent();
	var stars = f.children("[name='star']");
	var x = num - 1;
	for(var i = 0; i < stars.length;i++)
	{
		if (i > x)
		{
			stars[i].src = "../images/blankstar.png";
		}
		else
		{
			stars[i].src = "../images/star.png";
		}
	}
}
function updateCharactersLeft(textarea)
{
	var f = $(textarea).parent();
	var disp = f.find("#charactersLeft");
	var difference = 1000 - textarea.value.length;
	if (difference == 1)
		disp.text("1 character left.");
	else if (difference < 0)
	{
		difference = -difference
		disp.text(difference + " characters too long.");
	}
	else
		disp.text(difference + " characters left.");
}
function postreview(button)
{
	var f = $(button).parent();
	var div = f.parent().parent();
	var stars = f.children("[name='star']");
	var ranking = 0;
	for(var i = 0; i < stars.length;i++)
	{
		var sp = stars[i].src.split("/");
		if (sp[sp.length - 1] == "star.png")
		{
			ranking += 1;
		}
	}
	if (ranking < 1)
	{
		ranking = 3;
	}
	f.find("#rating").val(ranking);
	$.ajax({
				type: 'post',
				url: 'postReview.php',
				data: f.serialize(),
				success: function () {
					div.slideUp('fast', function() {
					  div.remove();
					});
				}
			  });
}
</script>
</body>
</html>