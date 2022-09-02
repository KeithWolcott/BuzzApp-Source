<div class="dropdown">
<?php
function isMobile()
{
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
}
if (isMobile())
{
	echo "<button onclick=\"showMenu();\" class=\"dropbtn\"></button>\n<div class=\"dropdown-content\" id=\"dropdown-content\">";
}
else
{
	echo "<div class=\"navbar\">";
}
?><a href="../index.php">Home</a>
<?php
	function fixprofile($image)
	{
		if (is_null($image) || $image == "")
			return "../images/defaultavatar.png";
		else
			return $image;
	}
	$image2 = "";
	$name2 = "";
	echo "<a href=\"searchforbarbershop.php\">Search for a Barbershop</a>";
	echo "<a href=\"searchforprofessional.php\">Search for a Professional</a>"; // this is where they can make appointments
	// And the home page has options for writing a review and visit barbershop pages where they have appointments.
	if ($conn)
	{
		$result = mysqli_query($conn, "select vip, profilePicture, firstName, lastName, count(*) from client where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
		$result2 = mysqli_query($conn, "select * from scheduling where clientEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and confirmed='1'");
		$result3 = mysqli_query($conn, "select * from scheduling inner join rating on scheduling.id = rating.schedulingId where clientEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and confirmed='1'");
		$vip = -1;
		if (mysqli_num_rows($result) > 0)
		{
			while($row = mysqli_fetch_assoc($result)) {
				$vip = $row["vip"];
				$name2 = "{$row["firstName"]} {$row["lastName"]}";
				$image2 = $row["profilePicture"];
			}
		}
		$numAppointments = mysqli_num_rows($result2);
		if (mysqli_num_rows($result3) > 0)
			echo "<a href=\"viewReviews.php\">View Reviews</a>";
		if ($numAppointments > 0)
			echo "<a href=\"changeAppointments.php\">Change Appointments</a>";
		if (is_null($vip) || $vip != "" || $vip == 0)
			echo "<a href=\"becomeVip.php\">Become a VIP</a>";
		else
			echo "<a href=\"manageVIP.php\">Manage VIP Status</a>";
	}
	echo "<a href=\"../contactus.php\">Contact Us</a>";
  ?>
</ul></div><?php
function fiximage2($im)
{
	// If the user's profile picture isn't there, show the default one.
	if (is_null($im) || $im == "")
		return "../images/defaultavatar.png";
	else
		return $im;
}
if (isset($_SESSION["accountType"]))
{
	if ($_SESSION["accountType"] == 1 && $conn)
	{
		echo "<div class=\"navbarimage\"><img class=\"navbarimage\" onclick=\"showMenu2();\" src=\"" . fixprofile($image2) . "\"></div>
		<div class=\"dropdown-content\" id=\"dropdown-content2\">
		<a href=\"profilepicture.php\">$name2</a>
		<a href=\"../logout.php\">Log-out</a>
		<a href=\"../changepassword.php\">Change Password</a>";
		if ($numAppointments > 0)
		{
		echo "<a href=\"myBarbershops.php\">My Barbershops</a>
		<a href=\"myProfessionals.php\">My Professionals</a>";
		}
	}
	echo "<a href=\"../configureNotifications.php\">Configure Notifications</a></div>";
}
?></div>