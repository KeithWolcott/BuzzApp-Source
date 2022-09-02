<?php
session_start();
require('../sendgrid-php/sendgrid-php.php');
if (!isset($_SESSION["email"]) || $_SESSION["accountType"] != 2)
	header("Location: ../login.php");
include '../config.php';
include '../functions.php';
if ($conn)
{
	$query = mysqli_query($conn, "select barbershopId, accepted from professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
	if (mysqli_num_rows($query) > 0)
	{
		while($row = mysqli_fetch_assoc($query)) {
			if (!is_null($row["barbershopId"]) && $row["barbershopId"] != "") // Then they already have a barbershop
			{
				if ($row["accepted"] == 1) 
					header("Location: barbershopPage.php");
				else // but if they are not accepted.
				{
					header("Location: ../index.php");
				}
			}
		}
	}
}
else
	header("Location: ../index.php");
$reasons = array();
if ($_POST)
{
	if (isset($_POST["id"]))
	{
		$id = $_POST["id"];
		// First, find out if this ID exists in the database.
		$query = mysqli_query($conn, "select barbershop.barbershopId, adminemail, notificationJoinRequest, name from barbershop left join professional on barbershop.barbershopId = professional.barbershopId where md5(barbershop.barbershopId) = '" . mysqli_real_escape_string($conn, $id) . "'");
		if (mysqli_num_rows($query) > 0)
		{
			while($row = mysqli_fetch_assoc($query)) {
				$id = $row["barbershopId"];
				$adminemail = $row["adminemail"]; // If an administrator leaves a barbershop, that barbershop stays in the database but there is no adminemail set.
				$barbershopName = $row["name"];
				$notifyAdmin = $row["notificationJoinRequest"];
			}
			if (is_null($adminemail))
			{
				$query2 = mysqli_query($conn,"update professional set barbershopId='$id', accepted='1' where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
				$query3 = mysqli_query($conn,"update barbershop set adminemail='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' where barbershopId='$id'");
				mysqli_close($conn);
				header("Location: barbershopPage.php");
			}
			else
			{
				$namequery = mysqli_query($conn, "select firstName, lastName from professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
				$row = mysqli_fetch_assoc($namequery);
				$professionalName = "{$row["firstName"]} {$row["lastName"]}";
				$query2 = mysqli_query($conn,"update professional set barbershopId='$id', accepted='0' where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");// But they aren't accepted yet, so they have to be approved.
				if ($notifyAdmin == 1)
				{
					$curl = curl_init();
					curl_setopt_array($curl, array(
					  CURLOPT_URL => "https://api.pushengage.com/apiv1/notifications",
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "POST",
					  CURLOPT_POSTFIELDS => "notification_title=Barbershop Request for $barbershopName&notification_message=$professionalName wants to join.&notification_url=http://buzapp.herokuapp.com&profile_id[0]=3",
					  CURLOPT_HTTPHEADER => array(
						"api_key: 1Si5m2Ks4Doj1B5OIM68kdRJ75ZuTPbv",
						"cache-control: no-cache",
						"content-type: application/x-www-form-urlencoded"
					  ),
					));

					$response = curl_exec($curl);
					$err = curl_error($curl);

					curl_close($curl);

					if ($err) {
					  //echo "cURL Error #:" . $err;
					} else {
					  //echo $response;
					}
					send_notification("Barbershop Request for $barbershopName","noreply@buzzapp.net",$adminemail,"$professionalName wants to join.\n<a href=\"http://buzapp.herokuapp.com\">Go here to accept it.</a>");
				}
				mysqli_close($conn);
				header("Location: ../index.php");
			}
		}
		else
		{
			// It's from Google.
			$url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=$id&fields=name,formatted_address,address_components,geometry,photos,opening_hours&key=AIzaSyDmYM6WeabzumGIfAYrHQIM_nEnyKjXUyQ";
			$resp = json_decode(file_get_contents($url), true); // find name, address, latitude, longitude, picture, and hours
			if($resp['status']==='OK')
			{
				$lat = $resp["result"]["geometry"]["location"]["lat"];
				$lng = $resp["result"]["geometry"]["location"]["lng"];
				$name = $resp["result"]["name"];
				$streetno = "";
				$route = "";
				$route2 = "";
				$city = "";
				$state = "";
				$zip = "";
				foreach ($resp["result"]["address_components"] as $address)
				{
					if (in_array("street_number",$address["types"]))
						$streetno = $address["long_name"];
					if (in_array("route",$address["types"]))
					{
						$route = $address["long_name"];
						$route2 = $address["short_name"];
					}
					if (in_array("locality",$address["types"]))
						$city = $address["long_name"];
					if (in_array("administrative_area_level_1",$address["types"]))
						$state = $address["short_name"];
					if (in_array("postal_code",$address["types"]))
						$zip = $address["long_name"];
				}
				if ($streetno == "" || $route == "") // because some places don't have a street no or a route
				{
					$formatted_address = $resp["result"]["formatted_address"]; 
					$coms = explode(", ",$formatted_address);
					$address = $coms[0];
					$address2 = $coms[0];
					if ($city == "")
						$city = $coms[1];
					$coms2 = explode(" ",$coms[2]);
					if ($state == "")
						$state = $coms2[0];
					if ($zip == "")
						$zip = $coms2[1];
				}
				else
				{
					$address = "$streetno $route";
					$address2 = "$streetno $route2"; // address2 is supposed to have a shorter street name
				}
				$query2 = mysqli_query($conn, "select barbershopId from barbershop where name = '" . mysqli_real_escape_string($conn, $name) . "' and (address = '" . mysqli_real_escape_string($conn, $address) . "' or address = '" . mysqli_real_escape_string($conn, $address2) . "') and city = '" . mysqli_real_escape_string($conn, $city) . "' and state = '" . mysqli_real_escape_string($conn, $state) . "'");
				// If that name, address, city, and state are in the database, come on, that's it.
				if (mysqli_num_rows($query2) > 0)
				{
					while($row = mysqli_fetch_assoc($query2)) {
						$id = $row["barbershopId"];
					}
					$query3 = mysqli_query($conn,"update professional set barbershopId='$id', accepted='0' where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
					mysqli_close($conn);
					header("Location: barbershopPage.php");
				}
				else
				{
					$photo = (isset($resp["result"]["photos"][0]["photo_reference"]) ? get_redirect_target("https://maps.googleapis.com/maps/api/place/photo?photoreference={$resp["result"]["photos"][0]["photo_reference"]}&sensor=false&maxheight=400&maxwidth=400&key=AIzaSyDmYM6WeabzumGIfAYrHQIM_nEnyKjXUyQ") : "../images/clipartbarbershop.jpg"); // the picture is redirected. If there is a picture, that is. Otherwise, use clipartbarbershop.
					$hourar = array();
					if (isset($resp["result"]["opening_hours"]["periods"]))
					{
						foreach ($resp["result"]["opening_hours"]["periods"] as $hour)
						{
							$hourtoset = array();
							array_push($hourtoset,$hour["open"]["day"]);
							if (isset($hour["open"]["time"]))
								array_push($hourtoset,formattime($hour["open"]["time"]));
							if (isset($hour["close"]["time"]))
								array_push($hourtoset,formattime($hour["close"]["time"]));
							array_push($hourar,$hourtoset);
						}
					}
					$query3 = mysqli_query($conn, "insert into barbershop (adminemail, name, image, address, city, state, zip, latitude, longitude) values('" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "', '" . mysqli_real_escape_string($conn, $name) . "', '" . mysqli_real_escape_string($conn, $photo) . "', '" . mysqli_real_escape_string($conn, $address) . "', '" . mysqli_real_escape_string($conn, $city) . "', '" . mysqli_real_escape_string($conn, $state) . "', '" . mysqli_real_escape_string($conn, $zip) . "', '" . mysqli_real_escape_string($conn, $lat) . "', '" . mysqli_real_escape_string($conn, $lng) . "')");
					$id = mysqli_insert_id($conn);
					foreach ($hourar as $h)
					{
						if (count($h) > 2)
						{
							$query5 = mysqli_query($conn,"insert into barbershophours (barbershopId, day, open, close) values ('$id', '" . mysqli_real_escape_string($conn, $h[0]) . "', '" . mysqli_real_escape_string($conn, $h[1]) . "', '" . mysqli_real_escape_string($conn, $h[2]) . "')");
						}
					}
					$query6 = mysqli_query($conn,"update professional set barbershopId='$id', accepted='1' where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
					mysqli_close($conn);
					header("Location: barbershopPage.php");
				}
			}
			else
				array_push($reasons,"Unable to get info from Google.");
		}
	}
	else
		array_push($reasons,"You shouldn't be here.");
}
else
	array_push($reasons,"You shouldn't be here.");
?>
<!DOCTYPE html>
<html>
<head>
<title>Add Barbershop</title>
<?php include 'header.php'; ?>
</head>
<body><?php include 'navbar.php'; ?>
<h1>Add Barbershop</h1>
<p>Unable to add the barbershop. Reasons:<ul><?php
foreach ($reasons as $reason)
echo "<li>$reason</li>";
?></ul>
<a href="javascript:history.go(-1)">Go back to results</a></p>
</body>
</html>