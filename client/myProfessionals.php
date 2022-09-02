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
<title>My Professionals</title>
<?php include 'header.php'; ?>
</head>
<body>
<?php include 'navbar.php'; ?>
<h1>My Professionals</h1>
<p>These are professionals you have previously made an appointment with.</p>
<?php
$query = mysqli_query($conn, "select firstName, lastName, email, barbershop.barbershopId, barbershop.name, image, address, city, state, zip from scheduling inner join professional on professional.email = scheduling.professionalEmail inner join barbershop on barbershop.barbershopId = professional.barbershopId inner join rating on scheduling.id = rating.schedulingId where clientEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and (paid = '1' or (paid = '0' and DATEDIFF(NOW(), STR_TO_DATE(concat(professional.day,'/',professional.month,'/',professional.year),'%d/%m/%Y')) < 30))");
if (mysqli_num_rows($query) <= 0)
	die("<p>There isn't any professionals found. <a href=\"searchForProfessional.php\">Find one.</a></p>");
echo "<hr>";
$professionals = array();
while ($row = mysqli_fetch_assoc($query))
{
	  // find average price
	  $query2 = mysqli_query($conn, "select avg(rating) r, count(*) c from scheduling inner join rating on scheduling.id = rating.schedulingId where professionalEmail = '{$row["email"]}' group by professionalEmail union select 0, 0 from professional inner join scheduling on professional.email = scheduling.professionalEmail where scheduling.id not in (select schedulingId from rating) and email not in (select professionalEmail from scheduling where id in (select schedulingId from rating)) and professionalEmail = '{$row["email"]}' and accepted = '1' union select 0, 0 from professional where email = '{$row["email"]}' and email not in (select professionalEmail from scheduling);");
	  $query3 = mysqli_query($conn, "select count(*) c from scheduling where clientEmail = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "' and professionalEmail='{$row["email"]}'");
	  $row2 = mysqli_fetch_assoc($query2);
	  $row3 = mysqli_fetch_assoc($query3);
	  $professionals[$row["email"]] = array($row["firstName"],$row["lastName"],$row["name"],$row["address"],$row["city"],$row["state"],$row["zip"],fiximage($row["image"]),$row["barbershopId"],$row3["c"],$row2["r"],$row2["c"]);
}
uasort($professionals, "sort_by_rating"); // sorts by number of times made appointment
$numResults = count($professionals);
echo "<p>$numResults result" . extra_s($numResults) . "</p><div class=\"results2\">";
$resultnumber = 1;
foreach ($professionals as $email=>$ar)
{
	$addressstring = "{$ar[4]}, {$ar[5]} {$ar[6]}";
	if (!empty($ar[3]))
		$addressstring = "{$ar[3]} $addressstring";
	echo "<div style=\"clear:both;\"><a href=\"viewBarbershop.php?id={$ar[8]}\"><img src=\"{$ar[7]}\" width=\"204\" height=\"114\" class=\"resultImg\"></a><p>$resultnumber. <a href=\"viewBarbershop.php?id={$ar[8]}\">{$ar[0]} {$ar[1]}</a> - {$ar[9]} time" . extra_s($ar[9]) . "<br>";
	if ($ar[10]==0)
		echo "No average rating";
	else
		echo number_format($ar[10],2) . " out of {$ar[11]} review" . extra_s($ar[11]) . ".";
	echo "<br>{$ar[2]}<br>$addressstring<br>{$ar[8]} miles away.</div><br>";
	$resultnumber++;
}
?>