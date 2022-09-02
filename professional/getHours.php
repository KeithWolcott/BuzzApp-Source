<?php
session_start();
include '../config.php';
$convertdays = array(0=>"sunday",1=>"monday",2=>"tuesday",3=>"wednesday",4=>"thursday",5=>"friday",6=>"saturday");
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"]==2 && $conn)
{
	header("Content-type: text/xml");
	$query = mysqli_query($conn, "select barbershophours.day, open, close from barbershophours where barbershopId in (select barbershopId from professional where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "')");
	$dom = new DOMDocument("1.0");
	$node = $dom->createElement("days");
	$parnode = $dom->appendChild($node);
	while ($row = mysqli_fetch_assoc($query)){
	  $node = $dom->createElement("day");
	  $newnode = $parnode->appendChild($node);
	  $newnode->setAttribute("daynum", $row['day']);
	  $newnode->setAttribute("dayname", $convertdays[$row['day']]);
	  $sp = explode(" ",$row["open"]);
	  $sp2 = explode(":",$sp[0]);
	  $hour = $sp2[0];
	  $min = $sp2[1];
	  if ($sp[1] == "PM")
	  {
		  if ($hour < 12)
			  $hour += 12;
	  }
	  elseif ($sp[1] == "AM")
	  {
		  if ($hour == 12)
			  $hour = 0;
	  }
	  if (substr($min,0,1)=="0")
		  $min = substr($min,1);
	  $sp3 = explode(" ",$row["close"]);
	  $sp4 = explode(":",$sp3[0]);
	  $hour2 = $sp4[0];
	  $min2 = $sp4[1];
	  if ($sp3[1] == "PM")
	  {
		  if ($hour2 < 12)
			  $hour2 += 12;
	  }
	  elseif ($sp3[1] == "AM")
	  {
		  if ($hour2 == 12)
			  $hour2 = 0;
	  }
	  if (substr($min2,0,1)=="0")
		  $min2 = substr($min2,1);
	  $newnode->setAttribute("openhour",$hour);
	  $newnode->setAttribute("openmin",$min);
	  $newnode->setAttribute("closehour", $hour2);
	  $newnode->setAttribute("closemin",$min2);
	}
	echo $dom->saveXML();
}
?>