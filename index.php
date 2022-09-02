<?php
  session_start(); // Needs to be at the top of the page for any page where the user needs to be logged in.
  include 'config.php';
?>
<html>
<head>
<?php include 'header.php'; ?>
<title>BuzzApp</title>
</head>
<body>
	<?php
	include 'navbar.php';
	include 'functions.php';
			if (!isset($_SESSION["email"])) // if not logged in
			{
				echo "<div class=\"home-background\"><div class=\"home-text\"> <h1 style=\"font-size:50px\">BuzzApp<span class=\"blinking-cursor\">|</span></h1><button class=\"home-button\" onclick=\"window.location.href = 'login.php'\">Login</button><br>
    	  <button class=\"home-button\" onclick=\"window.location.href = 'register.php'\">Create an account</button></div>
	</div>";
			}
			elseif ($_SESSION["accountType"] == 1)
			{
				$msgs = array();
				$profilePicture = null;
				$firstName = "";
				$lastName = "";
				$id = 0;
				if (!$conn)
					  die("Connection failed: " . mysqli_connect_error());
				if ($_POST && isset($_POST["submit"]))
				{
					require('aws/aws-autoloader.php'); // Amazon S3 is where the images will be stored.
					$s3 = new Aws\S3\S3Client([
						'version'  => '2006-03-01',
						'region'   => 'us-east-2',
					]);
					$bucket = getenv('S3_BUCKET');
					if (!$bucket)
						array_push($msgs,"Unable to upload image.");
					else
					{
						$uploadImage = $_FILES['file_upload']['name'];
						if($uploadImage != "")
						{
							$uploadOk = 1;
							$imageFileType = strtolower(pathinfo(basename($_FILES["file_upload"]["name"]),PATHINFO_EXTENSION));
							// Check if image file is a actual image or fake image
							$check = getimagesize($_FILES["file_upload"]["tmp_name"]);
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
								$profilePicture = $upload->get('ObjectURL');
								if ($conn && isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"]==1)
								{
									$result = mysqli_query($conn, "update client set profilePicture='" . mysqli_real_escape_string($conn, $profilePicture) . "' where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'"); // set the profile picture to linked image in Amazon
								}
							}
						}
					}
				}
					echo "<div class=\"user\"><a href=\"client/profilepicture.php\" border='0'>";
					$result = mysqli_query($conn, "select * from client where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
					if (mysqli_num_rows($result) > 0) { // get the client's info, including profile picture
						while($row = mysqli_fetch_assoc($result)) {
							$firstName = $row["firstName"];
							$lastName = $row["lastName"];
							$id = $row["id"];
							if (!is_null($row["profilePicture"]))
								$profilePicture = $row["profilePicture"];
						}
					}
					if (is_null($profilePicture))
						echo "<img src=\"images/defaultavatar.png\" alt=\"No Profile Picture\">";
					else
						echo "<img src=\"$profilePicture\" class=\"profilePicture\" alt=\"Profile Picture\">";
					echo "</a><br><br><span class=\"name\">$firstName $lastName</span></div><div class=\"additionalfront\">";
					if (count($msgs) > 0)
					{
						echo "<ul>";
						foreach ($msgs as $msg)
							echo "<li>$msg</li>";
						echo "</ul>";
					}
					if (is_null($profilePicture)) // Show page to set up profile picture
					{
						echo "<form method=\"post\" enctype=\"multipart/form-data\"><p>First up - give us your profile picture to familiarize barbers:</p><input type=\"file\" id=\"file_upload\" name=\"file_upload\" accept=\".png,.gif,.jpg,.jpeg\"><input type=\"submit\" name=\"submit\" class=\"uploadButton\" value=\"Upload Image\" />.</form>";
					}//https://buzzapp-profile-pictures.s3.us-east-2.amazonaws.com/phprYAUNu
					$result2 = mysqli_query($conn, "SELECT scheduling.id idnum, firstName, lastName, barbershop.name barbershopname, address, city, state, zip, image, professional.barbershopId bid, scheduling.year, scheduling.month, scheduling.day, timestart, duration, confirmed, cancelled, reason, remind, byprofessional, services.name, madereview, remindaboutreview FROM barbershop, professional, (scheduling inner join services on scheduling.serviceId = services.id) WHERE scheduling.clientEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and professional.barbershopId = barbershop.barbershopId and professional.email = professionalEmail order by scheduling.id desc"); // find client's appointments.
					$pastAppointments = array(); // keep track of previous, confirmed appointments
					$upcomingAppointments = array(); // keep track of the upcoming, confirmed appointments
					$unconfirmedAppointments = array(); // keep track of upcoming, unconfirmed appointments
					$rescheduledAppointments = array(); // keep track of rescheduled appointments (by professional)
					$cancelledAppointments = array(); // keep track of declined appointments
					if (mysqli_num_rows($result2) > 0)
					{
						while($row = mysqli_fetch_assoc($result2)) {
							$barberfirstName = $row["firstName"];
							$barberlastName = $row["lastName"];
							$address = $row["address"];
							$city = $row["city"];
							$state = $row["state"];
							$zip = $row["zip"];
							$addressstring = "$city, $state $zip";
							if (!empty($address))
								$addressstring = "$address, $addressstring";
							$timestart = $row["timestart"];
							$timeend = findend($row["timestart"],$row["duration"]);
							$barbershopName = $row["barbershopname"];
							$barbershopId = $row["bid"];
							$image = $row["image"];
							$year = $row["year"];
							$month = $row["month"];
							$day = $row["day"];
							$serviceName = $row["name"];
							$madereview = $row["madereview"];
							$remindaboutreview = $row["remindaboutreview"];
							$date2 = new DateTime("$year-$month-$day $timeend");
							if (isPast($date2)) // if the appointment is in the past
							{
								if ($row["confirmed"] == 1 && $madereview == 0 && $remindaboutreview == 1) // and they haven't made a review yet.
								{
									if (count($pastAppointments) < 20)
										array_push($pastAppointments,array($barberfirstName,$barberlastName,$barbershopName,$barbershopId,$year,$month,$day,$serviceName,$row["idnum"])); // Inside pastAppointments, keep track of the barbershop (name and id), barber's name, the date of the appointment, and the service requested.
								}
							}
							else
							{
								if ($row["confirmed"] == "0")
								{
									if ($row["cancelled"] == "1")
										array_push($cancelledAppointments,array($barberfirstName,$barberlastName,$addressstring,fiximage($image),$timestart,$barbershopId,$barbershopName,$year,$month,$day,$row["reason"],$row["idnum"]));
									else
										array_push($unconfirmedAppointments, array($barberfirstName,$barberlastName,$addressstring,fiximage($image),$timestart,$timeend,$barbershopName,$barbershopId,$year,$month,$day,$serviceName));
								}
								else
								{
									if ($row["byprofessional"] == 1 && $row["remind"] == 1) // means it was rescheduled by professional
										array_push($rescheduledAppointments,array($barberfirstName,$barberlastName,$addressstring,fiximage($image),$timestart,$timeend,$barbershopName,$year,$month,$day,$row["reason"],$row["idnum"],$barbershopId));
									else
										array_push($upcomingAppointments,array($barberfirstName,$barberlastName,$addressstring,fiximage($image),$timestart,$timeend,$barbershopName,$barbershopId,$year,$month,$day,$serviceName)); // Inside upcomingAppointments, keep track of the barbershop, the date of the appointment, how long it will take, and the barber's name.
								}
							}
						}
					}
					else
						echo "<button class=\"home-button\" onclick=\"window.location.href = 'client/searchforbarbershop.php'\">Make your First Appointment</button>";
							echo "</div><br><div class=\"appointments\"><div class=\"float col\"><div class=\"appointmentStatus center\"><h2>Declined Appointments</h2>";
						if (count($cancelledAppointments) > 0)
						{
							foreach ($cancelledAppointments as $a)
							{
								$date2 = new DateTime("{$a[7]}-{$a[8]}-{$a[9]} {$a[4]}");
								$id = md5($a[11]);
								echo "<div class=\"appointment\" name=\"cancelledAppointment\"><form onsubmit=\"return remindSchedule(this)\" method=\"post\"><input type=\"submit\" id=\"x\" value=\"X\" /><a href=\"client/viewBarbershop.php?id={$a[5]}\"><img src=\"{$a[3]}\" style=\"max-height:120px;\"></a><div style=\"margin-left:60px;text-align:left;\"><p><a href=\"client/viewBarbershop.php?id={$a[5]}\">{$a[6]}</a> with {$a[0]} {$a[1]} on " .  $date2->format("F j, Y") . "</p><p>Reason: {$a[10]}</p><input type=\"hidden\" name=\"id\" value=\"$id\" /></form></div></div>";
							}
						}
						else
						{
							echo "<p>If a professional declines an appointment, you'll be notified here.</p>";
						}
						echo "<h2>Rescheduled Appointments</h2>";
						if (count($rescheduledAppointments) > 0)
						{
							foreach ($rescheduledAppointments as $a)
							{
								$date2 = new DateTime("{$a[7]}-{$a[8]}-{$a[9]} {$a[4]}");
								$id = md5($a[11]);
								echo "<div class=\"appointment\" name=\"rescheduledAppointment\"><form onsubmit=\"return remindSchedule(this)\" method=\"post\"><input type=\"submit\" id=\"x\" value=\"X\" /><a href=\"client/viewBarbershop.php?id={$a[12]}\"><img src=\"{$a[3]}\" style=\"max-height:120px;\"><div style=\"margin-left:60px;text-align:left;\"><p><a href=\"client/viewBarbershop.php?id={$a[12]}\">{$a[6]}</a> with {$a[0]} {$a[1]}.</p><p>It is now on " . $date2->format("F j, Y") . ", from {$a[4]} until {$a[5]}.</p>
								<p>That's in " . datedifference($date2) . "</p>
								<p>Reason: {$a[10]}</p><input type=\"hidden\" name=\"id\" value=\"$id\" /></div></form></div>";
							}
							echo "</div>";
						}
						else
						{
							echo "<p>If a professional reschedules an appointment, you'll be notified here.</p>";
						}
						echo "</div></div><div class=\"float col\">";
							echo "<div class=\"appointmentStatus center\"><h2>Upcoming Appointments</h2>";
							if (count($upcomingAppointments) > 0)
							{
								foreach ($upcomingAppointments as $a)
								{
									$barberfirstName = $a[0];
									$barberlastName = $a[1];
									$address = $a[2];
									$image = $a[3];
									$timestart = $a[4];
									$timeend = $a[5];
									$barbershopName = $a[6];
									$barbershopId = $a[7];
									$year = $a[8];
									$month = $a[9];
									$day = $a[10];
									$date2 = new DateTime("$year-$month-$day $timestart");
									echo "<div class=\"appointment\"><a href=\"client/changeAppointments.php\"><img src=\"$image\" style=\"max-height:120px;\"></a><div style=\"margin-left:60px;text-align:left;\"><p><a href=\"client/viewBarbershop.php?id=$barbershopId\">$barbershopName</a> with $barberfirstName $barberlastName on " . $date2->format("F j, Y") . ".</p><p>Address: $address</p>
									<p>Time: $timestart until $timeend. It's in " . datedifference($date2) . ".</p><p>Style: {$a[11]}.</p></div></div>";
								}
							}
							else
							{
								echo "<p>Confirmed appointments will be here.</p>";
							}
							echo "<h2>Unconfirmed Appointments</h2>";
							if (count($unconfirmedAppointments) > 0)
							{ // If there are some unconfirmed appointments
								foreach ($unconfirmedAppointments as $a)
								{
									$barberfirstName = $a[0];
									$barberlastName = $a[1];
									$address = $a[2];
									$image = $a[3];
									$timestart = $a[4];
									$timeend = $a[5];
									$barbershopName = $a[6];
									$barbershopId = $a[7];
									$year = $a[8];
									$month = $a[9];
									$day = $a[10];
									$date2 = new DateTime("$year-$month-$day $timestart");
									echo "<div class=\"appointment\"><a href=\"client/viewBarbershop.php?id=$barbershopId\"><img src=\"$image\" style=\"max-height:120px;\"></a><div style=\"margin-left:60px;text-align:left;\"><p><a href=\"client/viewBarbershop.php?id=$barbershopId\">$barbershopName</a> with $barberfirstName $barberlastName on " . $date2->format("F j, Y") . ".</p><p>Address: $address</p>
									<p>Time: $timestart until $timeend.</p><p>Style: {$a[11]}.</p></div></div>";
								}
							}
							else
							{
								echo "<p>Unconfirmed appointments will be here.</p>";
							}
							echo "</div></div><div class=\"float col\"><div class=\"appointmentStatus center\"><h2>Previous Appointments with No Reviews</h2>";
							if (count($pastAppointments) > 0)
							{ // If there are some previous appointments
								foreach ($pastAppointments as $a)
								{
									$barberfirstName = $a[0];
									$barberlastName = $a[1];
									$barbershopName = $a[2];
									$barbershopId = $a[3];
									$year = $a[4];
									$month = $a[5];
									$day = $a[6];
									$stylewanted = $a[7];
									$date2 = new DateTime("$year-$month-$day $timeend");
									echo "<div><p>Write a review for your appointment with $firstName $lastName on " . $date2->format("F j, Y") . " at $barbershopName, where you got $stylewanted?<br><input type=\"button\" onclick=\"writeReview(this);\" value=\"Yes\" /> &nbsp; <input type=\"button\" onclick=\"dontWriteReview(this);\" value=\"No\" /></p><div id=\"reviewForm\" style=\"display:none;margin:0 auto;width:initial;\"><form method=\"post\" action=\"client/postReview.php\" onsubmit=\"return postreview(this)\"><img src=\"images/star.png\" class=\"star\" name=\"star\" onclick=\"ranking(1,this)\"><img src=\"images/star.png\" class=\"star\" name=\"star\" onclick=\"ranking(2,this)\"><img src=\"images/star.png\" class=\"star\" name=\"star\" onclick=\"ranking(3,this)\"><img src=\"images/blankstar.png\" class=\"star\" name=\"star\" onclick=\"ranking(4,this)\"><img src=\"images/blankstar.png\" class=\"star\" name=\"star\" onclick=\"ranking(5,this)\"><input type=\"hidden\" name=\"rating\" id=\"rating\" value=\"3\" /><input type=\"hidden\" name=\"id\" value=\"" . md5($a[8]) . "\" /><input type=\"button\" id=\"x\" value=\"X\" onclick=\"closeReview(this);\" /><textarea name=\"post\" maxlength=\"1000\" id=\"post\" style=\"width:initial;\" rows=\"6\" oninput=\"updateCharactersLeft(this);\"></textarea><br><span id=\"charactersLeft\">1000 characters left.</span> <input type=\"submit\" value=\"Post\" /></form></div></div>";
								}
							}
							else
							{
								echo "<p>After an appointment, you'll be able to write a review for it here.</p>";
							}
							echo "</div>";
						echo "</div>";
					}
			elseif ($_SESSION["accountType"] == 2)
			{
				if (!$conn)
					 die("Connection failed: " . mysqli_connect_error());
				$result = mysqli_query($conn, "select firstName, lastName, barbershopId, accepted, paid, DATEDIFF(DATE_ADD(STR_TO_DATE(concat(professional.day,'/',professional.month,'/',professional.year),'%d/%m/%Y'), INTERVAL 31 DAY), NOW()) trialleft from professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'"); // get the info for the barbershop.
				if (mysqli_num_rows($result) > 0) {
					while($row = mysqli_fetch_assoc($result)) {
						$firstname = $row["firstName"];
						$lastname = $row["lastName"];
						$barbershopId = $row["barbershopId"];
						$accepted = $row["accepted"];
						$paid = $row["paid"];
						$trialleft = $row["trialleft"];
					}
				}
				
				if (is_null($barbershopId) || $barbershopId == "") // no barbershop set up
				{
					echo "<div class=\"home-background\"><div class=\"home-text\"> <h1 style=\"font-size:50px\">BuzzApp<span class=\"blinking-cursor\">|</span></h1><span class=\"name\">$firstname $lastname</span><button class=\"home-button\" onclick=\"window.location.href = 'professional/setupBarbershop.php'\">Find your Business</button>";
				}
				else
				{
					if ($paid == 0)
					{
						if ($trialleft > 0 && $trialleft <= 31)
						{
							echo "<div class=\"trialmsg center\" onclick=\"window.location.href='professional/pay.php';\"><strong class=\"trialmsg\">Notice: You have ";
							if ($trialleft == 1)
								echo "until tomorrow";
							elseif ($trialleft == 0)
								echo "until today";
							else
								echo "$trialleft day" . extra_s($trialleft) . " left";
							echo " to pay.</strong></div>";
						}
						else
						{
							$expired = -$trialleft;
							echo "<div class=\"trialmsg center\" onclick=\"window.location.href='professional/pay.php';\"><strong class=\"trialmsg\">Notice: Your trial ended ";
							if ($expired == 1)
								echo "yesterday";
							else
								echo "$expired day" . extra_s($expired) . " ago";
							echo ". You must pay to accept more clients.</strong></div>";
						}
					}
					$result2 = mysqli_query($conn, "select adminemail, name, image, firstName, lastName, barbershophours.day, barbershophours.open, barbershophours.close from (barbershop natural left join barbershophours), professional where barbershop.barbershopId = '$barbershopId' and professional.email = adminemail");
					$days = array();
					echo "<div class=\"user\">";
					$openhours = array();
					$closehours = array();
					if (mysqli_num_rows($result) > 0) {
						while($row = mysqli_fetch_assoc($result2)) {
							$adminemail = $row["adminemail"];
							$adminfirstname = $row["firstName"];
							$adminlastname = $row["lastName"];
							$barbershopName = $row["name"];
							$image = fiximage($row["image"]);
							if (!is_null($row["day"]))
							{
								$openhours[convertday($row["day"])] = $row["open"];
								$closehours[convertday($row["day"])] = $row["close"];
								array_push($days, convertday($row["day"]));
							}
						}
						echo "<a href=\"professional/barbershopPage.php\"><img src=\"$image\" alt=\"$barbershopName\" class=\"barbershopImage\"></a><br><span class=\"name\">$firstname $lastname</span></div><div class=\"additionalfront\">";
						if ($accepted == 0)
						{
							echo "<p>$adminfirstname $adminlastname will need to verify you are an employee for $barbershopName.</p>";
						}
						else
						{
							if ($adminemail == $_SESSION["email"])
							{
								$result3 = mysqli_query($conn, "select id, firstName, lastName, email from barbershop, professional where professional.barbershopId = barbershop.barbershopId and barbershop.barbershopId = '$barbershopId' and accepted='0'");
								if (mysqli_num_rows($result3) > 0)
								{
									echo "<h2>Requests</h2>";
									while($row = mysqli_fetch_assoc($result3))
									{ // Create request form.
										echo "<div style=\"border:1px solid black;padding:5px 10px;\"><form method=\"post\"><input type=\"hidden\" name=\"user\" value=\"" . md5($row["email"]) . "\" /><p><strong>{$row["firstName"]} {$row["lastName"]}</strong> has asked to join $barbershopName on BuzzApp.</p>
										<input type=\"button\" onclick=\"acceptRequest(this)\" value=\"Accept\" class=\"acceptButton\" /> &nbsp; <input type=\"button\" class=\"declinebutton\" onclick=\"decline(this)\" value=\"Decline\"  /></form></div><br>";
									}
								}
							}
							$result4 = mysqli_query($conn, "select * from professionalhours where professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
							if (mysqli_num_rows($result4) <= 0) {
								if (count($openhours) > 0)
								{
								echo "<br><br><div style=\"border:1px solid black;padding:5px 10px;display: inline-block;\" id=\"professionalhours\"><p>";
								if ($adminemail != $_SESSION["email"])
									echo "You've been accepted to $barbershopName; now";
								else
									echo "First";
								echo ", put in your hours:</p><form id=\"professionalhoursform\" onsubmit=\"return changeHours2();\" method=\"post\"><ul>";
								// That complicated-ness of the schedule form.
								$daysoftheweek = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
								foreach ($daysoftheweek as $day)
								{
									$day2 = strtolower($day);
									if (in_array($day,$days))
									{
										echo "<li>$day: <input type=\"checkbox\" id=\"$day2\" value=\"off\" onclick=\"allowFor(this)\" name=\"{$day2}off\" ";
									if (!in_array($day,$days))
										echo "checked ";
									echo "/><label for=\"$day2\" />Off</label><span id=\"{$day2}info\"";
									if (!in_array($day,$days))
										echo " class=\"greyout\" ";
									echo "> &nbsp; Open: <input type=\"text\" name=\"{$day2}open\" id=\"{$day2}openhour\" value=\"";
										if (array_key_exists($day,$openhours))
										{
											echo $openhours[$day];
										}
										echo "\" maxlength=\"8\" size=\"8\" /> &nbsp; Close: <input type=\"text\" name=\"{$day2}close\" value=\"";
										if (array_key_exists($day,$closehours))
										{
											echo $closehours[$day];
										}
										echo "\" maxlength=\"8\" size=\"8\" id=\"{$day2}closehour\" /></span></li>\r\n<script>
										$('#{$day2}openhour').timepicker({
										'step': 15,
										'timeFormat': 'h:i A',
										'minTime': '{$openhours[$day]}',
										'maxTime': '{$closehours[$day]}',
										'scrollDefault': 'now'});
										$('#{$day2}closehour').timepicker({
										'step': 15,
										'timeFormat': 'h:i A',
										'minTime': '{$openhours[$day]}',
										'maxTime': '{$closehours[$day]}',
										'scrollDefault': 'now'});</script>";
									}
								}
								echo "</ul><input type=\"submit\" value=\"Submit\" /></form></div>";
								}
								else
								{
									if ($adminemail == mysqli_real_escape_string($conn, $_SESSION["email"]))
									{
										echo "<p><a href=\"professional/barbershopPage.php\">Put in the hours of the barbershop first!</a></p>";
									}
									else
										echo "<p>The hours of the barbershop need to be posted.</p>";
								}
							}
							$result5 = mysqli_query($conn, "SELECT scheduling.id idnum, firstName, lastName, profilePicture, scheduling.year, scheduling.month, scheduling.day, timestart, duration, confirmed, cancelled, reason, byprofessional, remind, newclient, services.name FROM client, (scheduling inner join services on scheduling.serviceId = services.id) WHERE scheduling.professionalEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and client.email = scheduling.clientEmail order by scheduling.id desc");
							$unconfirmed = 0;
							$upcomingAppointments = array();
							$cancelledAppointments = array();
							$rescheduledAppointments = array();
							while($row = mysqli_fetch_assoc($result5)) {
								$clientFirstName = $row["firstName"];
								$clientlastName = $row["lastName"];
								$timestart = $row["timestart"];
								$timeend = findend($row["timestart"],$row["duration"]);
								$profilePicture = $row["profilePicture"];
								if (is_null($profilePicture))
									$profilePicture = "images/defaultavatar.png";
								$year = $row["year"];
								$month = $row["month"];
								$day = $row["day"];
								$serviceName = $row["name"];
								$remind = $row["remind"];
								$date2 = new DateTime("$year-$month-$day $timeend");
					
								if ($row["cancelled"] == 0)
								{
									if (!isPast($date2)) // if the appointment is in the past
									{
										if ($row["confirmed"] == "0")
										{
											$unconfirmed++;
										}
										else
										{
											if ($remind == 1 && $row["byprofessional"] == "0") // meaning it was rescheduled
											{
												array_push($rescheduledAppointments,array($clientFirstName,$clientlastName,$timestart,$timeend,$year,$month,$day,$profilePicture,$row["reason"],$row["idnum"],$row["newclient"]));
											}
											else
											{
												array_push($upcomingAppointments,array($clientFirstName,$clientlastName,$timestart,$timeend,$year,$month,$day,$profilePicture)); // Inside upcomingAppointments, keep track of the barbershop, the date of the appointment, how long it will take, and the barber's name.
											}
										}
									}
								}
								else
								{
									if ($row["byprofessional"] == "0" && $remind == "1")
									{
										array_push($cancelledAppointments,array($clientFirstName,$clientlastName,$timestart,$year,$month,$day,$row["reason"],$profilePicture,$row["idnum"]));
									}
								}
						}
						if ($unconfirmed > 0)
						{
							echo "<button class=\"home-button\" onclick=\"window.location.href = 'professional/confirmAppointments.php'\">Confirm Appointments ($unconfirmed)</button>";
						}
						else
							echo "<p>You have no appointments to confirm.</p>";
						if (isset($unreadreviews) && $unreadreviews > 0)
							echo "<button class=\"home-button\" onclick=\"window.location.href = 'professional/checkReviews.php'\">Read Reviews ($unreadreviews)</button>";
						else
							echo "<p>You have no unread reviews.</p>";
						echo "</div></div><br><div class=\"appointments\"><div class=\"float col2\"><div class=\"appointmentStatus center\"><h2>Cancelled Appointments</h2>";
						if (count($cancelledAppointments) > 0)
						{
							foreach ($cancelledAppointments as $a)
							{
								$date2 = new DateTime("{$a[5]}-{$a[4]}-{$a[3]} {$a[2]}");
								$id = md5($a[8]);
								echo "<div class=\"appointment\" name=\"cancelledAppointment\"><form onsubmit=\"return remindCancellation(this)\" method=\"post\"><input type=\"submit\" id=\"x\" value=\"X\" /><img src=\"{$a[7]}\" class=\"profilePicture\"><div style=\"margin-left:60px;text-align:left;\"><p>{$a[0]} {$a[1]} on " . $date2->format("F j, Y") . ".</p><p>Reason: {$a[6]}</p><input type=\"hidden\" name=\"id\" value=\"$id\" /></div></form></div>";
							}
						}
						else
						{
							echo "<p>Appointments cancelled by clients will be shown here.</p>";
						}
						echo "<h2>Rescheduled Appointments</h2>";
						if (count($rescheduledAppointments) > 0)
						{
							foreach ($rescheduledAppointments as $a)
							{
								$date2 = new DateTime("{$a[6]}-{$a[5]}-{$a[4]} {$a[2]}");
								$id = md5($a[9]);
								echo "<div style=\"clear:both;\"><form onsubmit=\"return remindCancellation(this)\" method=\"post\"><input type=\"submit\" id=\"x\" value=\"X\" /><img src=\"{$a[7]}\" class=\"profilePicture\"><div style=\"margin-left:60px;text-align:left;\">{$a[0]} {$a[1]}";
								if ($a[10] == 1)
									echo " is now scheduled with you.";
								echo "<p>It is ";
								if ($a[10] == 0)
									echo "now ";
								echo "on " . $date2->format("F j, Y") . ", from {$a[2]} until {$a[3]}.</p>
								<p>That's in " . datedifference($date2) . "</p>
								<p>Reason: {$a[8]}</p><input type=\"hidden\" name=\"id\" value=\"$id\" /></div></form></div>";
							}
						}
						else
						{
							echo "<p>Appointments rescheduled by clients will be shown here.</p>";
						}
						echo "</div></div><div class=\"float col2\"><div class=\"appointmentStatus center\"><h2>Upcoming Appointments</h2>";
						if (count($upcomingAppointments) > 0)
						{ // If there are some upcoming appointments
							foreach ($upcomingAppointments as $a)
							{
								$date2 = new DateTime("{$a[6]}-{$a[5]}-{$a[4]} {$a[2]}");
								echo "<div style=\"clear:both;\"><img src=\"{$a[7]}\" class=\"profilePicture\"><div style=\"margin-left:60px;text-align:left;\"><p>{$a[0]} {$a[1]} on " . $date2->format("F j, Y") . ".</p><p>Time: {$a[2]} until {$a[3]}. It's in " . datedifference($date2) . ".</p></div></div>";
							}
							
						}
						else
						{
							echo "<p>Confirmed appointments will be here.<?p>";
						}
						echo "</div>";
						}
					}
				}
				echo "</div>";
			}
			mysqli_close($conn);
			?></div>
</body>
</html>