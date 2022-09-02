<?php
session_start();
if (isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2)
{
	include '../config.php';
	$result = mysqli_query($conn, "select id, name, price, description, duration, free, discountedlimit from services where barbershopId in (select barbershopId from professional where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "') order by id desc");
	if (mysqli_num_rows($result) <= 0)
		echo "<p>No services yet.</p>";
	else
	{
		while($row = mysqli_fetch_assoc($result)) {
			$id2 = md5($row["id"]);
		echo "<form onsubmit=\"return updateService(this)\"><input type=\"hidden\" name=\"id\" value=\"$id2\" /><div class=\"messagePost\">
		<p>Name: <input name=\"serviceName\" size=\"";
		echo (strlen($row["name"]) >= 30 ? strlen($row["name"]) : 30);
		echo "\" required value=\"" . str_replace('"',"&quot;",$row["name"]) . "\"/></p>
		<p>Price: <input name=\"price\" type=\"number\" step=\"0.01\" min=\"0.01\" value=\"" . $row["price"] . "\" required /></p>
		Description:<br>
		<textarea name=\"description\" id=\"description\" rows=\"3\">" . $row["description"] . "</textarea><br>
		Duration: <select name=\"duration\">";
		$durations = array("5 minutes"=>5,"10 minutes"=>10,"15 minutes"=>15,"20 minutes"=>20,"25 minutes"=>25,"30 minutes"=>30,"35 minutes"=>35,"40 minutes"=>40,"45 minutes"=>45,"50 minutes"=>50,"55 minutes"=>55,"1 hour"=>60,"1 hour 5 minutes"=>65,"1 hour 10 minutes"=>70,"1 hour 15 minutes"=>75,"1 hour 20 minutes"=>80,"1 hour 25 minutes"=>85,"1 hour 30 minutes"=>90,"1 hour 35 minutes"=>95,"1 hour 40 minutes"=>100,"1 hour 45 minutes"=>105,"1 hour 50 minutes"=>110,"1 hour 55 minutes"=>115,"2 hours"=>120);
		$discounts = array("None"=>0,"10%"=>0.1,"25%"=>0.25,"33%"=>0.33,"50%"=>0.5,"66%"=>0.66,"75%"=>0.75);
		foreach ($durations as $str=>$tim)
		{
			echo "<option value=\"$tim\"";
			if ($row["duration"] == $tim)
				echo " selected";
			echo ">$str</option>\r\n";
		}
		echo "</select><br>
		<input type=\"checkbox\" id=\"free$id2\" value=\"free\" name=\"free\" ";
		if ($row["free"] == 1)
			echo "checked ";
		echo "/><label for=\"free$id2\">Can be free</label><br>
Maximum Discount: <select name=\"discount\">";
foreach ($discounts as $str=>$d)
{
	echo "<option value=\"$d\"";
	if (round($row["discountedlimit"],2) == $d)
		echo " selected";
	echo ">$str</option>\r\n";
}
echo "</select><br><input type=\"submit\" value=\"Update\" /></div></form>";
		}
	}
}
?>