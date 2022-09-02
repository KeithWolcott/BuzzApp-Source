<?php
session_start();
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
	<form method="get" action="demo.php" onsubmit="return validate();">
	<br>
		<h1>Demo for Maintenance</h1>
         <input type="text" name="address" id="addressInput" placeholder="Address" size="50" <?php
		 if ($_GET)
			 echo "value=\"" . str_replace('"',"&quot;",$_GET["address"]) . "\" "; ?>required /> &nbsp; <input type="submit" id="searchButton" value="Search"/>
	</form><?php
if ($_GET)
{
	echo "<hr><div class=\"results\">";
	if (count($return) > 0)
		echo "<p>Latitude and Longitude: {$return[0]["lat"]}, {$return[0]["lng"]}</p><div id=\"map\"></div>";
	else
		echo "<p>Unknown Latitude and Longitude</p>";
	echo "</div>";
}
mysqli_close($conn);
	?>
	</body>
</html>