<?php
session_start();
include '../config.php';
include '../functions.php';
if (!isset($_SESSION["email"]) || !isset($_SESSION["accountType"]) || $_SESSION["accountType"] != 1)
	header("Location: ../index.php");
?>
<!DOCTYPE html>
<html>
<head>
<title>My Barbershops</title>
<?php include 'header.php'; ?>
</head>
<body>
<?php include 'navbar.php'; ?>
<h1>My Barbershops</h1>
<p>These are barbershops you have previously made an appointment with.</p>
<?php
$query = mysqli_query($conn, "select distinct barbershop.barbershopId, barbershophours.barbershopId bh, barbershop.name, image, address, city, state, zip, barbershophours.day, barbershophours.open, barbershophours.close from scheduling inner join professional on professional.email = scheduling.professionalEmail inner join barbershop on barbershop.barbershopId = professional.barbershopId left join barbershophours on barbershophours.barbershopId = barbershop.barbershopId where clientEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and accepted = '1'");
if (mysqli_num_rows($query) <= 0)
	die("<p>You haven't made an appointment yet. <a href=\"searchForBarbershop.php\">Make one.</a></p>");
echo "<hr>";
$barbershops = array();
$barbershophours = array();
while ($row = mysqli_fetch_assoc($query))
{
  if (!array_key_exists($row["barbershopId"],$barbershops)) //there are duplicates for each barbershop due to a new row for each different day for hours. Don't add same twice.
  {
	  // find average price
	  $query2 = mysqli_query($conn, "select avg(price) price from services where barbershopId = '{$row["barbershopId"]}'");
	  $row2 = mysqli_fetch_assoc($query2);
	  $query3 = mysqli_query($conn, "select count(*) c from scheduling inner join professional on scheduling.professionalEmail = professional.email where clientEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and barbershopId='{$row["barbershopId"]}'");
	  $row3 = mysqli_fetch_assoc($query3);
	  $barbershops[$row["barbershopId"]] = array($row["name"],$row["address"],$row["city"],$row["state"],$row["zip"],fiximage($row["image"]),$row2["price"],$row3["c"]);
  }
  if (!is_null($row["bh"])) // if the barbershop has hours, although every barbershop found should have hours
  {
	  if (!array_key_exists($row["bh"],$barbershophours)) // if this barbershop already has an entry for hours
		  $barbershophours[$row["bh"]] = array(array(convertday($row["day"]),$row["open"],$row["close"]));
	  else
		  array_push($barbershophours[$row["bh"]],array(convertday($row["day"]),$row["open"],$row["close"]));
  }
}
uasort($barbershops, "sort_by_times_visited");
$numResults = count($barbershops);
echo "<p>$numResults result" . extra_s($numResults) . "</p><div class=\"results2\">";
$resultnumber = 1;
foreach ($barbershops as $id=>$ar)
{
	$addressstring = "{$ar[2]}, {$ar[3]} {$ar[4]}";
	if (!empty($ar[1]))
		$addressstring = "{$ar[1]} $addressstring";
	echo "<div style=\"clear:both;\"><a href=\"viewBarbershop.php?id=$id\"><img src=\"{$ar[5]}\" width=\"204\" height=\"114\" class=\"resultImg\"></a><p>$resultnumber. <a href=\"viewBarbershop.php?id=$id\">{$ar[0]}</a> - {$ar[7]} time" . extra_s($ar[7]) . "<br>$addressstring<ul>";
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
	if (is_null($ar[6]))
		echo "No average price.";
	else
		echo "Average price: $" . number_format($ar[6],2);
	echo "</p></div><br>";
	$resultnumber++;
}
?>