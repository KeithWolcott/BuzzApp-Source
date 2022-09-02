<?php
session_start();
if (!isset($_SESSION["email"]) || $_SESSION["accountType"] != 2)
	header("Location: ../login.php");
include '../config.php';
include '../functions.php';
$connectionfailed = false;
$errors = array();
if ($conn)
{
	$query = mysqli_query($conn, "select barbershopId, accepted from professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
	if (mysqli_num_rows($query) > 0)
	{
		while($row = mysqli_fetch_assoc($query)) {
			if (!is_null($row["barbershopId"]) && $row["barbershopId"] != "")
			{
				if ($row["accepted"] == 1)
					header("Location: barbershopPage.php");
				else
					header("Location: ../index.php");
			}
		}
	}
}
else
{
	$connectionfailed = true;
	array_push($errors,"Unable to connect to the database: " . mysqli_connect_error);
}
if ($_POST)
{
	$stateCodes = array("AL","AK","AZ","AR","CA","CO","CT","DE","DC","FL","GA","HI","ID","IL","IN","IA","KS","KY","LA","ME","MD","MA","MI","MN","MS","MO","MT","NE","NV","NH","NJ","NM","NY","NC","ND","OH","OK","OR","PA","PR","RI","SC","SD","TN","TX","UT","VT","VA","VI","WA","WV","WI","WY");
	$barbershopName = $_POST["barbershopName"];
	$barbershopAddress = $_POST["address"];
	if (!isset($_POST["mobile"]))
	{
		if ($barbershopAddress == "")
			array_push($errors,"You didn't provide an address.");
	}
	if ($barbershopName == "")
		array_push($errors,"You need to provide a name for the barbershop.");
	if ($_POST["city"] == "")
		array_push($errors,"You didn't provide a city.");
	if (!in_array($_POST["state"],$stateCodes))
		array_push($errors,"Invalid state.");
	if (strlen($_POST["zip"]) < 5 || !preg_match('/^[0-9\-]+$/',$_POST["zip"]))
		array_push($errors,"Invalid zip.");
	$dates = array();
	$days = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
	$dates = array();
	for ($i = 0; $i < count($days); $i++)
	{
		$day = $days[$i];
		if (!isset($_POST[$day . "closed"]))
		{
			$date1 = false;
			$date2 = false;
			$opensplit = explode(" ",$_POST[$day . "openhour"]);
			$closesplit = explode(" ",$_POST[$day . "closehour"]);
			$opentime = $opensplit[0];
			$openampm = $opensplit[1];
			$closetime = $closesplit[0];
			$closeampm = $closesplit[1];
			if (preg_match('/^[0-9:]+$/',$opentime) && is_numeric(str_replace(":","",$opentime)))
				$date1 = checktime($opentime,$openampm);
			else
			{
				array_push($errors,"Invalid opening time for " . ucfirst($day) . ".");
			}
			if (preg_match('/^[0-9:]+$/',$closetime) && is_numeric(str_replace(":","",$closetime)))
				$date2 = checktime($closetime,$closeampm);
			else
			{
				array_push($errors,"Invalid closing time for " . ucfirst($day) . ".");
			}
			if ($date1 != false && $date2 != false)
			{
				if ($date1 > $date2)
					array_push($errors,"The closing time for " . ucfirst($day) . " is before that day's opening time...");
				else
				{
					$hour1 = $date1->format("h:i");
					$hour2 = $date2->format("h:i");
					if (substr($hour1,0,1) == "0")
						$hour1 = substr($hour1,1);
					if (substr($hour2,0,1) == "0")
						$hour2 = substr($hour2,1);
					$dates[$i] = array($hour1,$date1->format("A"),$hour2,$date2->format("A"));
				}
			}
		}
	}
	$latlng = array();
	$url = "https://maps.google.com/maps/api/geocode/json?key=AIzaSyDmYM6WeabzumGIfAYrHQIM_nEnyKjXUyQ&address=" . urlencode($_POST["address"] . ", " . $_POST["city"] . ", " . $_POST["state"] . " " . $_POST["zip"]);
	$resp = json_decode(file_get_contents($url), true);
	if($resp['status']==='OK')
	{
		foreach($resp['results'] as $res){
			$loc['lng'] = $res['geometry']['location']['lng'];
			$loc['lat'] = $res['geometry']['location']['lat'];
			$latlng[] = $loc;
		}
	}
	else
		array_push($errors,"Invalid address.");
	// First, check if the barbershop already exists.
	$query1 = mysqli_query($conn,"select barbershopId from barbershop where binary name='" . mysqli_real_escape_string($conn, $barbershopName) . "' and binary address='" . mysqli_real_escape_string($conn, $_POST["address"]) . "' and binary city='" . mysqli_real_escape_string($conn, $_POST["city"]) . "' and binary state='{$_POST["state"]}'");
	if (mysqli_num_rows($query1) > 0)
	{
		array_push($errors,"We already have a barbershop with that name and address.");
	}
	// Now, get image.
	$image = null;
	if (is_uploaded_file($_FILES['file_upload']['tmp_name']))
	{
		require('../aws/aws-autoloader.php');
		$s3 = new Aws\S3\S3Client([
			'version'  => '2006-03-01',
			'region'   => 'us-east-2',
		]);
		$bucket = getenv('S3_BUCKET');
		if (!$bucket)
			array_push($errors,"Unable to upload image.");
		else
		{
			$uploadImage = $_FILES['file_upload']['name'];
			if($uploadImage != "")
			{
				$uploadOk = 1;
				$imageFileType = strtolower(pathinfo(basename($_FILES["file_upload"]["name"]),PATHINFO_EXTENSION));
				// Check if image file is a actual image or fake image
				$check = getimagesize($_FILES["file_upload"]["tmp_name"]);          ///////////////////
				if($check === false) {
					array_push($errors,"File is not an image.");
					$uploadOk = 0;
				}
				// Check file size
				if ($_FILES["file_upload"]["size"] > 5000000) {
					array_push($errors,"Sorry, " .  basename( $_FILES["file_upload"]["name"]) ." is too large.");
					$uploadOk = 0;
				}
				// Allow certain file formats
				if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
				&& $imageFileType != "gif") {
					array_push($errors,"Sorry, only JPG, JPEG, PNG & GIF , PDF files are allowed.");
					$uploadOk = 0;
				}
				// Check if $uploadOk is set to 0 by an error
				if ($uploadOk == 0) {
					array_push($errors,"Sorry, your file was not uploaded.");
				// if everything is ok, try to upload file
				} else {
					$upload = $s3->upload($bucket, basename($_FILES["file_upload"]["tmp_name"]), fopen($_FILES["file_upload"]["tmp_name"], 'rb'), 'public-read');
					$imageName = $upload->get('ObjectURL');
					$image = $imageName;
				}
			}
		}
	}
	if (count($errors) == 0 && !$connectionfailed)
	{
		// Now add the barbershop.
		$strquery = "insert into barbershop (adminemail, name, image, address, city, state, zip, latitude, longitude) values ('" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "', '" . mysqli_real_escape_string($conn, $barbershopName) . "', ";
		if ($image == null)
			$strquery .= "null";
		else
			$strquery .= "'$image'";
		$strquery .= ", '" . mysqli_real_escape_string($conn, $_POST["address"]) . "', '" . mysqli_real_escape_string($conn, $_POST["city"]) . "', '{$_POST["state"]}', '{$_POST["zip"]}', '{$latlng[0]["lat"]}', '{$latlng[0]["lng"]}')";
		$query2 = mysqli_query($conn, $strquery);
		// Now find the barbershopId.
		$id = mysqli_insert_id($conn);
		// Hours of operation
		for($i=0;$i<7;$i++)
		{
			if (array_key_exists($i,$dates))
			{
				$datear = $dates[$i];
				$query3 = mysqli_query($conn, "insert into barbershophours(barbershopId, day, open, close) values('$id', '$i', '{$datear[0]} {$datear[1]}', '{$datear[2]} {$datear[3]}')");
			}
		}
		// Finally, update professional.
		$query4 = mysqli_query($conn,"update professional set barbershopId='$id', accepted='1' where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
		header("Location: barbershopPage.php");
	}
}
?>
<!DOCTYPE html>
<head>
<title>Manually set up Independent Barbershop</title>
<?php include 'header.php'; ?>
<script src="../upload.js"></script>
<script src="setupbarbershop.js"></script>
</head>
<body onload="loadTimePickers();">
<?php include 'navbar.php'; ?>
<br><h1>Manually set up Independent or Mobile Barbershop</h1><form method="post" onsubmit="return verify();" id="submitForm" enctype="multipart/form-data"><?php
if ($_POST && count($errors) > 0)
{
	echo "<p>Unable to add your barbershop because of these reasons:</p><ol>";
	foreach ($errors as $reason)
		echo "<li>$reason</li>";
	echo "</ol>";
	if (isset($_FILES["file_upload"]))
		echo "<p>You'll have to re-select your image.</p>";
}
?>
<input type="checkbox" id="mobile" value="mobile" name="mobile" onclick="allowFor(this);" /><label for="mobile">Mobile Barber</label><br>
Name of Barbershop: <input name="barbershopName" id="barbershopName" type="text" required size="50" <?php
if ($_POST && isset($_POST["barbershopName"]))
	echo "value=\"{$_POST["barbershopName"]}\" ";
?>/><div id="mobileinfo">
Address: <input name="address" id="address" type="text" size="62" <?php
if ($_POST && isset($_POST["address"]))
	echo "value=\"{$_POST["address"]}\" ";
?>/></div>
City: <input name="city" id="city" type="text" required size="30" <?php
if ($_POST && isset($_POST["city"]))
	echo "value=\"{$_POST["city"]}\" ";
?>/> State: <select name="state" id="state" />
<option value="AL"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "AL" ? " selected" : ""); ?>>Alabama</option>
<option value="AK"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "AK" ? " selected" : ""); ?>>Alaska</option>
<option value="AZ"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "AZ" ? " selected" : ""); ?>>Arizona</option>
<option value="AR"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "AR" ? " selected" : ""); ?>>Arkansas</option>
<option value="CA"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "CA" ? " selected" : ""); ?>>California</option>
<option value="CO"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "CO" ? " selected" : ""); ?>>Colorado</option>
<option value="CT"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "CT" ? " selected" : ""); ?>>Connecticut</option>
<option value="DE"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "DE" ? " selected" : ""); ?>>Delaware</option>
<option value="DC"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "DC" ? " selected" : ""); ?>>District of Columbia</option>
<option value="FL"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "FL" ? " selected" : ""); ?>>Florida</option>
<option value="GA"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "GA" ? " selected" : ""); ?>>Georgia</option>
<option value="HI"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "HI" ? " selected" : ""); ?>>Hawaii</option>
<option value="ID"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "ID" ? " selected" : ""); ?>>Idaho</option>
<option value="IL"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "IL" ? " selected" : ""); ?>>Illinois</option>
<option value="IN"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "IN" ? " selected" : ""); ?>>Indiana</option>
<option value="IA"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "IA" ? " selected" : ""); ?>>Iowa</option>
<option value="KS"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "KS" ? " selected" : ""); ?>>Kansas</option>
<option value="KY"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "KY" ? " selected" : ""); ?>>Kentucky</option>
<option value="LA"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "LA" ? " selected" : ""); ?>>Louisiana</option>
<option value="ME"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "ME" ? " selected" : ""); ?>>Maine</option>
<option value="MD"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "MD" ? " selected" : ""); ?>>Maryland</option>
<option value="MA"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "MA" ? " selected" : ""); ?>>Massachusetts</option>
<option value="MI"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "MI" ? " selected" : ""); ?>>Michigan</option>
<option value="MN"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "MN" ? " selected" : ""); ?>>Minnesota</option>
<option value="MS"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "MS" ? " selected" : ""); ?>>Mississippi</option>
<option value="MO"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "MO" ? " selected" : ""); ?>>Missouri</option>
<option value="MT"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "MT" ? " selected" : ""); ?>>Montana</option>
<option value="NE"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "NE" ? " selected" : ""); ?>>Nebraska</option>
<option value="NV"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "NV" ? " selected" : ""); ?>>Nevada</option>
<option value="NH"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "NH" ? " selected" : ""); ?>>New Hampshire</option>
<option value="NJ"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "NJ" ? " selected" : ""); ?>>New Jersey</option>
<option value="NM"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "NM" ? " selected" : ""); ?>>New Mexico</option>
<option value="NY"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "NY" ? " selected" : ""); ?>>New York</option>
<option value="NC"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "NC" ? " selected" : ""); ?>>North Carolina</option>
<option value="ND"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "ND" ? " selected" : ""); ?>>North Dakota</option>
<option value="OH"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "OH" ? " selected" : ""); ?>>Ohio</option>
<option value="OK"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "OK" ? " selected" : ""); ?>>Oklahoma</option>
<option value="OR"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "OR" ? " selected" : ""); ?>>Oregon</option>
<option value="PA"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "PA" ? " selected" : ""); ?>>Pennsylvania</option>
<option value="PR"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "PR" ? " selected" : ""); ?>>Puerto Rico</option>
<option value="RI"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "RI" ? " selected" : ""); ?>>Rhode Island</option>
<option value="SC"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "SC" ? " selected" : ""); ?>>South Carolina</option>
<option value="SD"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "SD" ? " selected" : ""); ?>>South Dakota</option>
<option value="TN"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "TN" ? " selected" : ""); ?>>Tennessee</option>
<option value="TX"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "TX" ? " selected" : ""); ?>>Texas</option>
<option value="UT"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "UT" ? " selected" : ""); ?>>Utah</option>
<option value="VT"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "VT" ? " selected" : ""); ?>>Vermont</option>
<option value="VA"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "VA" ? " selected" : ""); ?>>Virginia</option>
<option value="VI"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "VI" ? " selected" : ""); ?>>Virgin Islands</option>
<option value="WA"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "WA" ? " selected" : ""); ?>>Washington</option>
<option value="WV"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "WV" ? " selected" : ""); ?>>West Virginia</option>
<option value="WI"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "WI" ? " selected" : ""); ?>>Wisconsin</option>
<option value="WY"<?php
echo ($_POST && isset($_POST["state"]) && $_POST["state"] == "WY" ? " selected" : ""); ?>>Wyoming</option>
</select> Zip Code: <input size="11" name="zip" type="text" required id="zip" <?php
if ($_POST && isset($_POST["zip"]))
	echo "value=\"{$_POST["zip"]}\" ";
?>/>
<fieldset><legend>Hours</legend>
<ul>
<?php
$daysoftheweek = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
foreach ($daysoftheweek as $day)
{
	$day2 = strtolower($day);
	echo "<li>$day: <input type=\"checkbox\" id=\"$day2\" value=\"closed\" onclick=\"allowFor(this)\" name=\"{$day2}closed\" ";
	if ($_POST && isset($_POST[$day2 . "closed"]))
		echo "checked ";
	echo "/><label for=\"$day2\" />Closed</label><span id=\"{$day2}info\"";
	if ($_POST && isset($_POST[$day2 . "closed"]))
		echo " class=\"greyout\" ";
	
	echo "> &nbsp; Open: <input type=\"text\" name=\"{$day2}openhour\" id=\"{$day2}openhour\" value=\"";
	if ($_POST && isset($_POST[$day2 . "openhour"]))
	{
		echo $_POST[$day2 . "openhour"];
	}
	echo "\" maxlength=\"8\" size=\"8\" /> &nbsp; Close: <input type=\"text\" name=\"{$day2}closehour\" value=\"";
	if ($_POST && isset($_POST[$day2 . "closehour"]))
	{
		echo $_POST[$day2 . "closehour"];
	}
	echo "\" maxlength=\"8\" size=\"8\" id=\"{$day2}closehour\" /></span></li>\r\n";
}
?>
</ul></fieldset>
Image (optional): <input type="file" id="file_upload" name="file_upload" accept=".png,.gif,.jpg,.jpeg" /><br>
<input type="submit" value="Add Barbershop" /><div id="progressbox"><div id="progressbar"></div><div id="statustxt">0%</div></div></form>
<script>
var days = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
function verifyHours()
{
	var valid = true;
	for (var i = 0; i < days.length; i++)
	{
		var day = days[i];
		var openhour = $("#" + day + "openhour");
		var closehour = $("#" + day + "closehour");
		var opensplit = openhour.val().split(" ");
		var opentime = opensplit[0];
		var openampm = opensplit[1];
		var closesplit = closehour.val().split(" ");
		var closetime = closesplit[0];
		var closeampm = closesplit[1];
		openhour.removeClass("invalid");
		closehour.removeClass("invalid");
		if (!document.getElementById(day).checked) // if not closed
		{
			var date1 = false;
			var date2 = false;
			if (opentime.match(new RegExp('^[0-9:]+$')) && !isNaN(parseInt(opentime)))
			{
				date1 = checktime(opentime,openampm);
			}
			else
			{
				openhour.addClass("invalid");
				valid = false;
			}
			if (closetime.match(new RegExp('^[0-9:]+$')) && !isNaN(parseInt(closetime)))
			{
				date2 = checktime(closetime,closeampm);
			}
			else
			{
				closehour.addClass("invalid");
				valid = false;
			}
			if (date1 != false && date2 != false)
			{
				var diff = date2 - date1;
				if (diff <= 0)
				{
					openhour.addClass("invalid");
					closehour.addClass("invalid");
					valid = false;
				}
			}
		}
	}
	return valid;
}
function allowFor(checkBox)
{
	var day = checkBox.id;
	if (checkBox.checked)
	{
		$("#" + day + "info").css("pointer-events", "none");
		document.getElementById(day + "info").className = "greyout";
	}
	else
	{
		$("#" + day + "info").css("pointer-events", "auto");
		document.getElementById(day + "info").className = "";
	}
}
function verifyField(elem)
{
	if (elem.value == "")
	{
		elem.className = "invalid";
		return false;
	}
	return true;
}
function verifyZip()
{
	var numPattern = /^[0-9\-]+$/; //Source: https://stackoverflow.com/questions/17004790/checking-for-numbers-and-dashes
	if (document.getElementById('zip').value == "" || !numPattern.test(document.getElementById('zip').value) || document.getElementById('zip').value.length < 5)
	{
		document.getElementById('zip').className = "invalid";
		return false;
	}
	return true;
}
function verifyState()
{
	var stateCodes = ["AL","AK","AZ","AR","CA","CO","CT","DE","DC","FL","GA","HI","ID","IL","IN","IA","KS","KY","LA","ME","MD","MA","MI","MN","MS","MO","MT","NE","NV","NH","NJ","NM","NY","NC","ND","OH","OK","OR","PA","PR","RI","SC","SD","TN","TX","UT","VT","VA","VI","WA","WV","WI","WY"];
	if (stateCodes.indexOf(document.getElementById('state').options[document.getElementById('state').selectedIndex].value) == -1)
	{
		document.getElementById('state').className = "invalid";
		return false;
	}
	return true;
}
function verifyFields()
{
	document.getElementById('barbershopName').className = "";
	document.getElementById('address').className = "";
	document.getElementById('city').className = "";
	document.getElementById('state').className = "";
	document.getElementById('zip').className = "";
	if (document.getElementById('mobile').checked)
	{
		return (verifyField(document.getElementById('barbershopName')) && verifyField(document.getElementById('city')) && verifyZip() && verifyState());
	}
	else
	{
		return (verifyField(document.getElementById('barbershopName')) && verifyField(document.getElementById('address')) && verifyField(document.getElementById('city')) && verifyZip() && verifyState());
	}
}
function verify()
{	
	if (verifyFields() && verifyHours() && checkFile(document.getElementById('submitForm'),false))
	{
		return true;
	}
	return false;
}
function loadTimePickers()
{
	for (var i = 0; i < days.length; i++)
	{
		var day = days[i];
		$('#' + day + 'openhour').timepicker({
		'step': 15,
		'timeFormat': 'h:i A',
		'scrollDefault': 'now'});
		$('#' + day + 'closehour').timepicker({
		'step': 15,
		'timeFormat': 'h:i A',
		'scrollDefault': 'now'});
	}
}
</script>
</body>
</html>