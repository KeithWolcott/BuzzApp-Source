<?php
session_start();
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
if ($_GET)
{
	$return = array();
	$results = array();
	$hours = array();
	$url = "https://maps.google.com/maps/api/geocode/json?key=AIzaSyDmYM6WeabzumGIfAYrHQIM_nEnyKjXUyQ&address=" . urlencode($_GET["address"]);
	$resp = json_decode(file_get_contents($url), true);
	if($resp['status']==='OK')
	{
		foreach($resp['results'] as $res){
			$loc['lng'] = $res['geometry']['location']['lng'];
			$loc['lat'] = $res['geometry']['location']['lat'];
			$return[] = $loc;
		}
		$radius = $_GET["radius"];
		$query = mysqli_query($conn, "SELECT barbershop.barbershopId, barbershophours.barbershopId bh, name, address, city, state, zip, latitude, longitude, image, day, open, close, ( 3959 * acos( cos( radians('{$return[0]["lat"]}') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('{$return[0]["lng"]}') ) + sin( radians('{$return[0]["lat"]}') ) * sin( radians( latitude ) ) ) ) AS distance FROM barbershop natural left join barbershophours where name like '%" . mysqli_real_escape_string($conn, $_GET["barbershopName"]) . "%' HAVING distance < '$radius' ORDER BY distance LIMIT 0 , 10");
		$radius *= 1609; // needs to be in meters for Google
		while($row = mysqli_fetch_assoc($query)) {
		  if (!array_key_exists($row["barbershopId"],$results))
		  {
			  $image = fiximage($row["image"]);
			  $results[$row["barbershopId"]] = array($row["name"],$row["address"],$row["city"],$row["state"],$row["zip"],fiximage($image),$row["distance"],$row["latitude"],$row["longitude"]);
		  }
		  if (!is_null($row["bh"]))
		  {
			  if (!array_key_exists($row["bh"],$hours))
					  $hours[$row["barbershopId"]] = array(array(convertday($row["day"]),$row["open"],$row["close"]));
				  else
					  array_push($hours[$row["bh"]],array(convertday($row["day"]),$row["open"],$row["close"]));
			  }
		  }
		$url2 = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location={$return[0]["lat"]},{$return[0]["lng"]}&radius=" . urlencode($radius) . "&type=beauty_salon&name=" . urlencode($_GET["barbershopName"]) . "&key=AIzaSyDmYM6WeabzumGIfAYrHQIM_nEnyKjXUyQ";
		$resp = json_decode(file_get_contents($url2), true);
		if($resp['status']==='OK')
		{
				foreach($resp['results'] as $res)
				{
					$lat = $res["geometry"]["location"]["lat"];
					$lng = $res["geometry"]["location"]["lng"];
					$id = $res["place_id"];
					$url3 = "https://maps.googleapis.com/maps/api/place/details/json?placeid=$id&fields=name,formatted_address,address_components,photos,opening_hours&key=AIzaSyDmYM6WeabzumGIfAYrHQIM_nEnyKjXUyQ";
					$resp2 = json_decode(file_get_contents($url3), true);
					if($resp2['status']==='OK')
					{
						$name = $resp2["result"]["name"];
						$streetno = "";
						$route = "";
						$route2 = "";
						$city = "";
						$state = "";
						$zip = "";
						foreach ($resp2["result"]["address_components"] as $address)
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
						if ($streetno == "" || $route == "")
						{
							$formatted_address = $resp2["result"]["formatted_address"];
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
							$address2 = "$streetno $route2";
						}
						$exists = false;
						foreach ($results as $r)
						{
							if ($r[0] == $name && ($r[1] == $address || $r[1] == $address2) && $r[2] == $city && $r[3] == $state)
							{
								$exists = true;
								break;
							}
						}
						if (!$exists)
						{
							$photo = (isset($resp2["result"]["photos"][0]["photo_reference"]) ? get_redirect_target("https://maps.googleapis.com/maps/api/place/photo?photoreference={$resp2["result"]["photos"][0]["photo_reference"]}&sensor=false&maxheight=360&maxwidth=360&key=AIzaSyDmYM6WeabzumGIfAYrHQIM_nEnyKjXUyQ") : "../images/clipartbarbershop.jpg");
							$hourar = array();
							if (isset($resp2["result"]["opening_hours"]["periods"]))
							{
								foreach ($resp2["result"]["opening_hours"]["periods"] as $hour)
								{
									$hourtoset = array();
									array_push($hourtoset,convertday($hour["open"]["day"]));
									if (isset($hour["open"]["time"]))
										array_push($hourtoset,formattime($hour["open"]["time"]));
									if (isset($hour["close"]["time"]))
										array_push($hourtoset,formattime($hour["close"]["time"]));
									array_push($hourar,$hourtoset);
								}
							}
							$hours[$id] = $hourar;
							$distance = distance($return[0]["lat"],$return[0]["lng"],$lat,$lng);
							$results[$id] = array($name,$address,$city,$state,$zip,$photo,$distance,$lat,$lng);
						}
					}
				}
		}
	}
	uasort($results, "sort_by_distance");
}
?>
<!DOCTYPE html>
<head>
<title>Set up Barbershop</title>
<?php include 'header.php'; ?>
<?php
echo "<script async defer
    src=\"https://maps.googleapis.com/maps/api/js?key=AIzaSyDmYM6WeabzumGIfAYrHQIM_nEnyKjXUyQ&libraries=places&callback=donothing\">
    </script>";
if ($_GET && count($return) > 0)
{
	echo "<script>
	var markers = {};
	var map;
	var infoWindow;
	function initMap() {
		var lat = parseFloat({$return[0]["lat"]});
		var lng = parseFloat({$return[0]["lng"]});
		var center = {lat: lat, lng: lng};
		map = new google.maps.Map(
		  document.getElementById('map'), {zoom: 10, center: center, mapTypeId: 'roadmap'});
		infoWindow = new google.maps.InfoWindow();\r\n";
		foreach ($results as $id=>$ar)
		{
			$id2 = md5($id);
		echo "createMarker($ar[7],$ar[8],\"" . str_replace('"',"&quot;",$ar[0]) . "\",\"" . str_replace('"',"&quot;","{$ar[1]}, {$ar[2]}, {$ar[3]} {$ar[4]}") . "\", \"$id2\");\r\n";
		}
		echo "}
	function createMarker(lat, lng, name, address, markerNum) {
	  var latlng = new google.maps.LatLng(
			  parseFloat(lat),
			  parseFloat(lng));
	  var html = \"<span class='shopName'>\" + name + \"</span> <br>\" + address;
	  var marker = new google.maps.Marker({
		map: map,
		position: latlng
	  });
	  google.maps.event.addListener(marker, 'click', function() {
		infoWindow.setContent(html);
		infoWindow.open(map, marker);
	  });
	  markers[markerNum] = marker;
	}
	</script>";
}
?>
</head>
<body<?php
if ($_GET)
	echo " onload=\"initMap();\"";
?>>
<?php include 'navbar.php'; ?>
<br><h1>Set up Barbershop</h1>
<form method="get" action="setupBarbershop.php">
<input type="text" name="barbershopName" id="barbershopName" placeholder="Name of barbershop" size="30" required <?php
 if ($_GET)
	 echo "value=\"" . $_GET["barbershopName"] . "\" "; ?>/>
<input type="text" name="address" id="addressInput" placeholder="Address to search by" size="50" required <?php
 if ($_GET)
	 echo "value=\"" . $_GET["address"] . "\" "; ?>/> <input type="button" onclick="getcurrentlocation();" value="Current Location" /> 
        <label for="radiusSelect">Radius:</label>
        <select name="radius" id="radiusSelect" label="Radius">
          <option value="30"<?php
		  echo ($_GET && isset($_GET["radius"]) && $_GET["radius"] == 30 ? " selected" : ""); ?>>30 miles</option>
          <option value="20"<?php
		  echo ($_GET && isset($_GET["radius"]) && $_GET["radius"] == 20 ? " selected" : ""); ?>>20 miles</option>
          <option value="10"<?php
		  echo ($_GET && isset($_GET["radius"]) && $_GET["radius"] == 10 ? " selected" : ""); ?>>10 miles</option>
        </select>
		<input type="submit" id="searchButton" value="Search"/>
	</form><?php
	if ($_GET)
	{
		echo "<hr><div id=\"results\" class=\"results\"><p>If you're an independent barber, you can <a href=\"setupIndependentBarbershop.php\">enter your information manually.</a></p><p>" . count($results) . " result";
		if (count($results) != 1)
			echo "s";
		echo ".</p>";
		echo "<div class=\"results2\">";
		foreach ($results as $id=>$ar)
		{
			$id2 = md5($id);
			echo "<form method=\"post\" id=\"form\" action=\"addBarbershop.php\" /><div style=\"clear:both;\"><input type=\"hidden\" name=\"id\" value=\"";
			if (is_numeric($id))
				echo $id2;
			else
				echo $id;
			$addressstring = "{$ar[2]}, {$ar[3]} {$ar[4]}";
			if (!empty($ar[1]))
				$addressstring = "{$ar[1]} $addressstring";
			echo "\" /><img onclick=\"myBarbershop(this)\" src=\"{$ar[5]}\" class=\"resultImg\"><br>{$ar[0]}<br>Approximately " . round($ar[6],2) . " miles.<br>$addressstring<br><ul>";
			if (array_key_exists($id, $hours))
			{
				foreach ($hours[$id] as $ar2)
				{
					echo "<li>{$ar2[0]}";
					if (count($ar2) > 1)
					{
						echo ": {$ar2[1]}";
						if (count($ar2) > 2)
							echo " - {$ar2[2]}";
					}
					echo "</li>";
				}
			}
			else
			{
				echo "<li>No hours available</li>";
			}
			echo "</ul><input type=\"submit\" value=\"This is my barbershop\" /></div></form>\r\n";
		}
		echo "</div>";
		if (count($return) > 0)
			echo "<div id=\"map\"></div>";
		echo "</div>";
	}
	mysqli_close($conn);
	?>
	</body>
</html>
