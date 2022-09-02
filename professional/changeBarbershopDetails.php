<?php
session_start();
include '../config.php';
include '../functions.php';
if ($_SESSION && isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"] == 2 && $_POST)
{
	$query = mysqli_query($conn, "select barbershop.barbershopId, name, adminemail from professional inner join barbershop on professional.barbershopId = barbershop.barbershopId where email = '" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
	if ($query && mysqli_num_rows($query) > 0)
	{
		$row = mysqli_fetch_assoc($query);
		$id = $row["barbershopId"];
		$barbershopName = $row["name"];
		$adminemail = $row["adminemail"];
		if ($adminemail == mysqli_real_escape_string($conn, $_SESSION["email"]))
		{
			if (isset($_POST["newname"]) && strlen($_POST["newname"]) > 0)
			{
				$query2 = mysqli_query($conn, "update barbershop set name='" . mysqli_real_escape_string($conn, $_POST["newname"]) . "' where barbershopId='$id'");
				echo "changed";
			}
			if (isset($_POST["newaddress"]) && strlen($_POST["newaddress"]) > 0)
			{
				$ex = explode(",",$_POST["newaddress"]);
				if (count($ex) >= 2)
				{
					$valid = true;
					if (count($ex) == 2)
					{
						$newaddress = null;
						$newcity = trim($ex[0]);
						$lastspace = strrpos($ex[1]," ");
						$newstate = trim(substr($ex[1],0,$lastspace));
						$newzip = trim(substr($ex[1],$lastspace+1));
					}
					elseif (count($ex)==3)
					{
						$valid = true;
						$newaddress = trim($ex[0]);
						$newcity = trim($ex[1]);
						$lastspace = strrpos($ex[2]," ");
						$newstate = trim(substr($ex[2],0,$lastspace));
						$newzip = trim(substr($ex[2],$lastspace+1));
					}
					// data validation necessary
					$stateabbrs = array('Alabama'=>'AL','Alaska'=>'AK','Arizona'=>'AZ','Arkansas'=>'AR','California'=>'CA','Colorado'=>'CO','Connecticut'=>'CT','Delaware'=>'DE','District of Columbia'=>'DC','Florida'=>'FL','Georgia'=>'GA','Hawaii'=>'HI','Idaho'=>'ID','Illinois'=>'IL','Indiana'=>'IN','Iowa'=>'IA','Kansas'=>'KS','Kentucky'=>'KY','Louisiana'=>'LA','Maine'=>'ME','Maryland'=>'MD','Massachusetts'=>'MA','Michigan'=>'MI','Minnesota'=>'MN','Mississippi'=>'MS','Missouri'=>'MO','Montana'=>'MT','Nebraska'=>'NE','Nevada'=>'NV','New Hampshire'=>'NH','New Jersey'=>'NJ','New Mexico'=>'NM','New York'=>'NY','North Carolina'=>'NC','North Dakota'=>'ND','Ohio'=>'OH','Oklahoma'=>'OK','Oregon'=>'OR','Pennsylvania'=>'PA','Puerto Rico'=>'PR','Rhode Island'=>'RI','South Carolina'=>'SC','South Dakota'=>'SD','Tennessee'=>'TN','Texas'=>'TX','Utah'=>'UT','Vermont'=>'VT','Virginia'=>'VA','Virgin Islands'=>'VI','Washington'=>'WA','West Virginia'=>'WV','Wisconsin'=>'WI','Wyoming'=>'WY');
					$statenames = array_keys($stateabbrs);
					$stateabbrs2 = array_values($stateabbrs);
					if (in_arrayi($newstate,$statenames))
					{
						for($i=0;$i<count($statenames);$i++)
						{
							if (strtolower($statenames[$i])==strtolower($newstate))
							{
								$newstate = $stateabbrs[$statenames[$i]];
								break;
							}
						}
					}
					elseif (in_arrayi($newstate,$stateabbrs2))
					{
						for($i=0;$i<count($stateabbrs2);$i++)
						{
							if (strtolower($stateabbrs2[$i])==strtolower($newstate))
							{
								$newstate = $stateabbrs2[$i];
								break;
							}
						}
					}
					else
					{
						$valid = false;
					}
					if (strlen($newzip) < 5 || !preg_match('/^[0-9\-]+$/',$newzip))
						$valid = false;
					if ($valid)
					{
						$addressstring = "$newcity, $newstate $newzip";
						if (!is_null($newaddress))
							$addressstring = "$newaddress, $addressstring";
						$query3 = mysqli_query($conn, "select name from barbershop where address='" . mysqli_real_escape_string($conn, $newaddress) . "' and city='" . mysqli_real_escape_string($conn, $newcity) . "' and state='" . mysqli_real_escape_string($conn, $newstate) . "' and zip='" . mysqli_real_escape_string($conn, $newzip) . "'");
						if (mysqli_num_rows($query3) > 0)
						{
							$row = mysqli_fetch_assoc($query3);
							if ($row["name"] != $barbershopName)
								echo "exists";
							else
								echo "didntchange";
						}
						else
						{
							$url = "https://maps.google.com/maps/api/geocode/json?key=AIzaSyDmYM6WeabzumGIfAYrHQIM_nEnyKjXUyQ&address=" . urlencode("$addressstring");
							$resp = json_decode(file_get_contents($url), true);
							if($resp['status']==='OK')
							{
								foreach($resp['results'] as $res){
									$loc['lng'] = $res['geometry']['location']['lng'];
									$loc['lat'] = $res['geometry']['location']['lat'];
									$return[] = $loc;
								}
								$lat = $res["geometry"]["location"]["lat"];
								$lng = $res["geometry"]["location"]["lng"];
								$query2 = mysqli_query($conn, "update barbershop set address='" . mysqli_real_escape_string($conn, $newaddress) . "', city='" . mysqli_real_escape_string($conn, $newcity) . "', state='" . mysqli_real_escape_string($conn, $newstate) . "', zip='" . mysqli_real_escape_string($conn, $newzip) . "', latitude='$lat', longitude='$lng' where barbershopId='$id'");
								echo "changed$addressstring";
							}
							else
							{
								echo "invalid";
							}
						}
					}
				}
			}
		}
	}
}
?>