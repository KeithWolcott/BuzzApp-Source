<?php
session_start();
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 1)
	header("Location: ../login.php");
include '../config.php';
include '../functions.php';
$professionals = array();
$return = array();
if ($_GET && $conn)
{
	$url = "https://maps.google.com/maps/api/geocode/json?key=AIzaSyDmYM6WeabzumGIfAYrHQIM_nEnyKjXUyQ&address=" . urlencode($_GET["address"]); // This url can figure out if the address is real and its latitude and longitude. That latitude and longitude is how to sort by distance.
	$resp = json_decode(file_get_contents($url), true);
	if($resp['status']==='OK')
	{
		foreach($resp['results'] as $res){
			$loc['lng'] = $res['geometry']['location']['lng'];
			$loc['lat'] = $res['geometry']['location']['lat'];
			$return[] = $loc;
		}
		$radius = $_GET["radius"];
		$query = mysqli_query($conn, "SELECT firstName, lastName, email, barbershop.barbershopId, barbershop.name, address, city, state, zip, latitude, longitude, image, ( 3959 * acos( cos( radians('{$return[0]["lat"]}') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('{$return[0]["lng"]}') ) + sin( radians('{$return[0]["lat"]}') ) * sin( radians( latitude ) ) ) ) AS distance FROM professional inner join barbershop on professional.barbershopId = barbershop.barbershopId where accepted='1' and firstName like '%" . mysqli_real_escape_string($conn, $_GET["firstName"]) ."%' and lastName like '%" . mysqli_real_escape_string($conn, $_GET["lastName"]) ."%' and name like '%" . mysqli_real_escape_string($conn, $_GET["keyword"]) . "%' and (paid = '1' or (paid = '0' and DATEDIFF(NOW(), STR_TO_DATE(concat(professional.day,'/',professional.month,'/',professional.year),'%d/%m/%Y')) < 30)) HAVING distance < '$radius' order by distance LIMIT 0 , 20");
		while($row = mysqli_fetch_assoc($query)) {
				  // find average rating
			  $query2 = mysqli_query($conn, "select avg(rating) r, count(*) c from scheduling inner join rating on scheduling.id = rating.schedulingId where professionalEmail = '{$row["email"]}' group by rating union select 0, 0 from scheduling inner join rating on scheduling.professionalEmail where scheduling.id not in (select schedulingId from rating) and professionalEmail = '{$row["email"]}' union select 0, 0 from professional where email = '{$row["email"]}' and email not in (select professionalEmail from scheduling);");
			  $row2 = mysqli_fetch_assoc($query2);
			  $professionals[$row["email"]] = array($row["firstName"],$row["lastName"],$row["name"],$row["address"],$row["city"],$row["state"],$row["zip"],fiximage($row["image"]),$row["barbershopId"],$row2["r"],$row2["c"],round($row["distance"],2),$row["latitude"],$row["longitude"],);
			  }
	if ($_GET["sortby"] == "rating")
		uasort($professionals, "sort_by_rating");
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Find a Professional</title>
<?php
include 'header.php';
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
		$visited = array();
		foreach ($professionals as $email=>$ar)
		{
			$id = $ar[8];
			if (!in_array($id,$visited))
			{
				$id2 = md5($ar[8]);
				echo "createMarker({$ar[12]},{$ar[13]},\"" . str_replace('"',"&quot;",$ar[2]) . "\",\"" . str_replace('"',"&quot;","{$ar[3]}, {$ar[4]}, {$ar[5]} {$ar[6]}") . "\", \"$id2\",\"$id\");\r\n";
				array_push($visited,$id);
			}
		}
		echo "}
		
	function go_to_location(markerNum)
	{
		 google.maps.event.trigger(markers[markerNum], 'click');
	}
	function createMarker(lat, lng, name, address, markerNum, idNum) {
	  var latlng = new google.maps.LatLng(
			  parseFloat(lat),
			  parseFloat(lng));
	  var html = \"<span class='shopName'><a href='viewBarbershop.php?id=\" + idNum + \"'>\" + name + \"</a></span> <br>\" + address;
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
	<form method="get" action="searchforprofessional.php" onsubmit="return validate();" id="barbershop"><div class="center">
	<br>
		<h1>Find a Professional</h1>
		 <input type="text" name="firstName" id="firstname" placeholder="First Name" size="17" <?php echo ($_GET ? "value=\"" . str_replace('"',"&quot;",$_GET["firstName"]) . "\" " : ""); ?>/> &nbsp; <input type="text" name="lastName" id="lastname" placeholder="Last Name" size="20" <?php echo ($_GET ? "value=\"" . str_replace('"',"&quot;",$_GET["lastName"]) . "\" " : ""); ?>/><br>
         <input type="text" name="address" id="addressInput" placeholder="Address" size="44" <?php echo ($_GET ? "value=\"" . str_replace('"',"&quot;",$_GET["address"]) . "\" " : ""); ?>/> <input type="button" onclick="getcurrentlocation(false);" value="Current Location" /><br>
        <label for="radiusSelect">Radius:</label>
        <select name="radius" id="radiusSelect" label="Radius">
          <option value="30"<?php
		  echo ($_GET && isset($_GET["radius"]) && $_GET["radius"] == 30 ? " selected" : ""); ?>>30 miles</option>
          <option value="20"<?php
		  echo ($_GET && isset($_GET["radius"]) && $_GET["radius"] == 20 ? " selected" : ""); ?>>20 miles</option>
          <option value="10"<?php
		  echo ($_GET && isset($_GET["radius"]) && $_GET["radius"] == 10 ? " selected" : ""); ?>>10 miles</option>
        </select><br>
		Barbershop name contains: <input name="keyword" placeholder="Optional" value="<?php echo ($_GET && isset($_GET["keyword"]) ? str_replace('"',"&quot;",$_GET["keyword"]) : ""); ?>" /><br>
		Sort by: <select name="sortby">
		<option value="rating">Rating</option>
		<option value="distance"<?php echo ($_GET && isset($_GET["sortby"]) && $_GET["sortby"]=="distance" ? " selected" : ""); ?>>Distance</option></select><br><input type="submit" id="searchButton" value="Search" />
	</div></form><?php
if ($_GET)
{
	echo "<hr>";
	$resultnumber = 1;
	echo "<div class=\"results\"><p>" . count($professionals) . " result" . extra_s(count($professionals)) . "</p>";
	foreach ($professionals as $email=>$ar)
	{
		$addressstring = "{$ar[4]}, {$ar[5]} {$ar[6]}";
		if (!empty($ar[3]))
			$addressstring = "{$ar[3]} $addressstring";
		echo "<div style=\"clear:both;\"><a href=\"viewBarbershop.php?id={$ar[8]}\"><img src=\"{$ar[7]}\" width=\"204\" height=\"114\" class=\"resultImg\"></a><p>$resultnumber. <a href=\"viewBarbershop.php?id={$ar[8]}\">{$ar[0]} {$ar[1]}</a><br>";
		if ($ar[9]==0)
			echo "No average rating";
		else
			echo number_format($ar[9],2) . " out of {$ar[10]} review" . extra_s($ar[10]) . ".";
		echo "<br>{$ar[2]}<br>$addressstring<br>{$ar[11]} miles away.</div><br>";
		$resultnumber++;
	}
	if (count($return) > 0)
		echo "<div id=\"map\"></div>";
	echo "</div>";
}
mysqli_close($conn);
	?>
	</body>
</html>