<?php
session_start();
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 1)
	header("Location: ../login.php");
include '../config.php';
include '../functions.php';
$barbershops = array();
$barbershophours = array();
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
		$query = mysqli_query($conn, "SELECT barbershop.barbershopId, barbershophours.barbershopId bh, barbershop.name, address, city, state, zip, latitude, longitude, image, barbershophours.day, barbershophours.open, barbershophours.close, ( 3959 * acos( cos( radians('{$return[0]["lat"]}') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('{$return[0]["lng"]}') ) + sin( radians('{$return[0]["lat"]}') ) * sin( radians( latitude ) ) ) ) AS distance FROM barbershop natural join barbershophours inner join professional on barbershop.adminemail = professional.email where name like '%" . mysqli_real_escape_string($conn, $_GET["keyword"]) . "%' and accepted = '1' HAVING distance < '$radius' ORDER BY distance LIMIT 0 , 20");
		while($row = mysqli_fetch_assoc($query)) {
			  if (!array_key_exists($row["barbershopId"],$barbershops)) //there are duplicates for each barbershop due to a new row for each different day for hours. Don't add same twice.
			  {
				  // find average price
				  $query2 = mysqli_query($conn, "select avg(price) price from services where barbershopId = '{$row["barbershopId"]}'");
				  $row2 = mysqli_fetch_assoc($query2);
				  $barbershops[$row["barbershopId"]] = array($row["name"],$row["address"],$row["city"],$row["state"],$row["zip"],fiximage($row["image"]),round($row["distance"],2),$row["latitude"],$row["longitude"],$row2["price"]);
			  }
			  if (!is_null($row["bh"])) // if the barbershop has hours, although every barbershop found should have hours
			  {
				  if (!array_key_exists($row["bh"],$barbershophours)) // if this barbershop already has an entry for hours
					  $barbershophours[$row["barbershopId"]] = array(array(convertday($row["day"]),$row["open"],$row["close"]));
				  else
					  array_push($barbershophours[$row["bh"]],array(convertday($row["day"]),$row["open"],$row["close"]));
			  }
		  }
	}
	if ($_GET["sortby"]=="price")
	{
		uasort($barbershops, "sort_by_price");
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Barbershops Near You</title>
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
		foreach ($barbershops as $id=>$ar)
		{
			$id2 = md5($id);
		echo "createMarker({$ar[7]},{$ar[8]},\"" . str_replace('"',"&quot;",$ar[0]) . "\",\"" . str_replace('"',"&quot;","{$ar[1]}, {$ar[2]}, {$ar[3]} {$ar[4]}") . "\", \"$id2\",\"$id\");\r\n";
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
	<form method="get" action="searchforbarbershop.php" onsubmit="return validate();" id="barbershop"><div class="center">
	<br>
		<h1>Find a Barbershop</h1>
		<p>To search for a professional, <a href="searchforprofessional.php">search here</a>.</p>
         <input type="text" name="address" id="addressInput" placeholder="Address" size="50" <?php
		 if ($_GET)
			 echo "value=\"" . str_replace('"',"&quot;",$_GET["address"]) . "\" "; ?>/> <input type="button" onclick="getcurrentlocation(false);" value="Current Location" /> 
        <label for="radiusSelect">Radius:</label>
        <select name="radius" id="radiusSelect" label="Radius">
          <option value="30"<?php
		  echo ($_GET && isset($_GET["radius"]) && $_GET["radius"] == 30 ? " selected" : ""); ?>>30 miles</option>
          <option value="20"<?php
		  echo ($_GET && isset($_GET["radius"]) && $_GET["radius"] == 20 ? " selected" : ""); ?>>20 miles</option>
          <option value="10"<?php
		  echo ($_GET && isset($_GET["radius"]) && $_GET["radius"] == 10 ? " selected" : ""); ?>>10 miles</option>
        </select> <input type="submit" id="searchButton" value="Search"/><br><fieldset style="display:inline-block;"><legend>Filters</legend>
		Name contains: <input name="keyword" placeholder="Optional" value="<?php echo ($_GET && isset($_GET["keyword"]) ? str_replace('"',"&quot;",$_GET["keyword"]) : ""); ?>" /><br>
		Sort by: <select name="sortby">
		<option value="distance">Distance</option>
		<option value="price"<?php echo ($_GET && isset($_GET["sortby"]) && $_GET["sortby"]=="price" ? " selected" : ""); ?>>Price</option></select>
    </fieldset>
	</div></form><?php
if ($_GET)
{
	echo "<hr>";
	$resultnumber = 1;
	echo "<div class=\"results\"><p>" . count($barbershops) . " result" . extra_s(count($barbershops)) . "</p>";
	foreach ($barbershops as $id=>$ar)
	{
		$addressstring = "{$ar[2]}, {$ar[3]} {$ar[4]}";
		if (!empty($ar[1]))
			$addressstring = "{$ar[1]} $addressstring";
		echo "<div style=\"clear:both;\"><a href=\"viewBarbershop.php?id=$id\"><img src=\"{$ar[5]}\" width=\"204\" height=\"114\" class=\"resultImg\"></a><p>$resultnumber. <a href=\"viewBarbershop.php?id=$id\">{$ar[0]}</a><br>$addressstring<br>{$ar[6]} miles away.<ul>";
		if (array_key_exists($id, $barbershophours))
		{
			foreach ($barbershophours[$id] as $ar2)
			{
				echo "<li>{$ar2[0]}: {$ar2[1]} - {$ar2[2]}</li>";
			}
		}
		else
		{
			echo "<li>No hours available</li>";
		}
		echo "</ul><p>";
		if (is_null($ar[9]))
			echo "No average price.";
		else
			echo "Average price: $" . number_format($ar[9],2);
		echo "</p></div><br>";
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