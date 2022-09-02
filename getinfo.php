<?php
$f = file_get_contents("kartparamcode.txt");
$weights = array(); // index 1, goes with weight
$speeds = array(); // index 2, goes with speed
$speedinturns = array(); // index 3, goes with speed
$accelerationnotdriftings0s = array(); // index 4, goes with acceleration
$accelerationnotdriftings1s = array(); // index 5, goes with acceleration
$accelerationnotdriftings2s = array(); // index 6, goes with acceleration
$accelerationnotdriftings3s = array(); // index 7, goes with acceleration
$accelerationdriftings0s = array(); // index 9, goes with acceleration
$accelerationdriftings1s = array(); // index 10, goes with acceleration
$accelerationdriftings2s = array(); // index 11, goes with acceleration
$accelerationdriftings3s = array(); // index 12, goes with acceleration
$handlingmanuals = array(); // index 14, goes with handling
$handlingautos = array(); // index 15, goes with handling
$handlingrange = array(); // index 16, goes with handling
$driftingmanuals = array(); // index 17, goes with drift
$driftingautos = array(); // index 18, goes with drift
$driftingrange = array(); // index 19, goes with drift
$miniturbos = array(); // index 20, goes with miniturbos
$offroadspeed02s = array(); // index 22, goes with offroad
$offroadspeed03s = array(); // index 23, goes with offroad
$offroadspeed04s = array(); // index 24, goes with offroad
$offroadspeed05s = array(); // index 25, goes with offroad
$offroadhandling01s = array(); // index 26, goes with offroad
$offroadhandling02s = array(); // index 27, goes with offroad
$offroadhandling03s = array(); // index 28, goes with offroad
$offroadhandling04s = array(); // index 29, goes with offroad
$offroadhandling05s = array(); // index 30, goes with offroad
$originalspeeds = array();
$originalweights = array();
$originalaccelerations = array();
$originalhandlings = array();
$originaldrifts = array();
$originaloffroads = array();
$originalminiturbos = array();
function linear_regression( $x, $y ) {
 
    $n     = count($x);     // number of items in the array
    $x_sum = array_sum($x); // sum of all X values
    $y_sum = array_sum($y); // sum of all Y values
 
    $xx_sum = 0;
    $xy_sum = 0;
 
    for($i = 0; $i < $n; $i++) {
        $xy_sum += ( $x[$i]*$y[$i] );
        $xx_sum += ( $x[$i]*$x[$i] );
    }
 
    // Slope
    $slope = ( ( $n * $xy_sum ) - ( $x_sum * $y_sum ) ) / ( ( $n * $xx_sum ) - ( $x_sum * $x_sum ) );
 
    // calculate intercept
    $intercept = ( $y_sum - ( $slope * $x_sum ) ) / $n;
 
    return array( 
        'slope'     => $slope,
        'intercept' => $intercept,
    );
}
function getnum($line)
{
	$i0 = strpos($line,"</td>");
	return trim(substr($line,0,$i0));
}
$ex = explode('<th bgcolor="#CCFFFF"><a href="',$f);
for($i=1;$i<count($ex);$i++)
{
	$i0 = strpos($ex[$i],'title="');
	$i1 = strpos($ex[$i],'">');
	$name = substr($ex[$i],$i0+7,$i1-7-$i0);
	$ex2 = explode('<td bgcolor="#CCFFFF"> ',$ex[$i]);
	$weights[$name] = getnum($ex2[1]);
	$speeds[$name] = getnum($ex2[2]);
	$speedinturns[$name] = getnum($ex2[3]);
	$accelerationnotdriftings0s[$name] = getnum($ex2[4]);
	$accelerationnotdriftings1s[$name] = getnum($ex2[5]);
	$accelerationnotdriftings2s[$name] = getnum($ex2[6]);
	$accelerationnotdriftings3s[$name] = getnum($ex2[7]);
	$accelerationdriftings0s[$name] = getnum($ex2[9]);
	$accelerationdriftings1s[$name] = getnum($ex2[10]);
	$accelerationdriftings2s[$name] = getnum($ex2[11]);
	$accelerationdriftings3s[$name] = getnum($ex2[12]);
	$handlingmanuals[$name] = getnum($ex2[14]);
	$handlingautos[$name] = getnum($ex2[15]);
	$handlingrange[$name] = getnum($ex2[16]);
	$driftingmanuals[$name] = getnum($ex2[17]);
	$driftingautos[$name] = getnum($ex2[18]);
	$driftingrange[$name] = getnum($ex2[19]);
	$miniturbos[$name] = getnum($ex2[20]);
	$offroadspeed02s[$name] = getnum($ex2[22]);
	$offroadspeed03s[$name] = getnum($ex2[23]);
	$offroadspeed04s[$name] = getnum($ex2[24]);
	$offroadspeed05s[$name] = getnum($ex2[25]);
	$offroadhandling01s[$name] = getnum($ex2[26]);
	$offroadhandling02s[$name] = getnum($ex2[27]);
	$offroadhandling03s[$name] = getnum($ex2[28]);
	$offroadhandling04s[$name] = getnum($ex2[29]);
	$offroadhandling05s[$name] = getnum($ex2[30]);
}
$ex = explode('<th bgcolor="honeydew"><a href="',$f);
for($i=1;$i<count($ex);$i++)
{
	$i0 = strpos($ex[$i],'title="');
	$i1 = strpos($ex[$i],'">');
	$name = substr($ex[$i],$i0+7,$i1-7-$i0);
	$ex2 = explode('<td bgcolor="honeydew"> ',$ex[$i]);
	$weights[$name] = getnum($ex2[1]);
	$speeds[$name] = getnum($ex2[2]);
	$speedinturns[$name] = getnum($ex2[3]);
	$accelerationnotdriftings0s[$name] = getnum($ex2[4]);
	$accelerationnotdriftings1s[$name] = getnum($ex2[5]);
	$accelerationnotdriftings2s[$name] = getnum($ex2[6]);
	$accelerationnotdriftings3s[$name] = getnum($ex2[7]);
	$accelerationdriftings0s[$name] = getnum($ex2[9]);
	$accelerationdriftings1s[$name] = getnum($ex2[10]);
	$accelerationdriftings2s[$name] = getnum($ex2[11]);
	$accelerationdriftings3s[$name] = getnum($ex2[12]);
	$handlingmanuals[$name] = getnum($ex2[14]);
	$handlingautos[$name] = getnum($ex2[15]);
	$handlingrange[$name] = getnum($ex2[16]);
	$driftingmanuals[$name] = getnum($ex2[17]);
	$driftingautos[$name] = getnum($ex2[18]);
	$driftingrange[$name] = getnum($ex2[19]);
	$miniturbos[$name] = getnum($ex2[20]);
	$offroadspeed02s[$name] = getnum($ex2[22]);
	$offroadspeed03s[$name] = getnum($ex2[23]);
	$offroadspeed04s[$name] = getnum($ex2[24]);
	$offroadspeed05s[$name] = getnum($ex2[25]);
	$offroadhandling01s[$name] = getnum($ex2[26]);
	$offroadhandling02s[$name] = getnum($ex2[27]);
	$offroadhandling03s[$name] = getnum($ex2[28]);
	$offroadhandling04s[$name] = getnum($ex2[29]);
	$offroadhandling05s[$name] = getnum($ex2[30]);
}
$ex = explode('<th bgcolor="MistyRose"><a href="',$f);
for($i=1;$i<count($ex);$i++)
{
	$i0 = strpos($ex[$i],'title="');
	$i1 = strpos($ex[$i],'">');
	$name = substr($ex[$i],$i0+7,$i1-7-$i0);
	$ex2 = explode('<td bgcolor="MistyRose"> ',$ex[$i]);
	$weights[$name] = getnum($ex2[1]);
	$speeds[$name] = getnum($ex2[2]);
	$speedinturns[$name] = getnum($ex2[3]);
	$accelerationnotdriftings0s[$name] = getnum($ex2[4]);
	$accelerationnotdriftings1s[$name] = getnum($ex2[5]);
	$accelerationnotdriftings2s[$name] = getnum($ex2[6]);
	$accelerationnotdriftings3s[$name] = getnum($ex2[7]);
	$accelerationdriftings0s[$name] = getnum($ex2[9]);
	$accelerationdriftings1s[$name] = getnum($ex2[10]);
	$accelerationdriftings2s[$name] = getnum($ex2[11]);
	$accelerationdriftings3s[$name] = getnum($ex2[12]);
	$handlingmanuals[$name] = getnum($ex2[14]);
	$handlingautos[$name] = getnum($ex2[15]);
	$handlingrange[$name] = getnum($ex2[16]);
	$driftingmanuals[$name] = getnum($ex2[17]);
	$driftingautos[$name] = getnum($ex2[18]);
	$driftingrange[$name] = getnum($ex2[19]);
	$miniturbos[$name] = getnum($ex2[20]);
	$offroadspeed02s[$name] = getnum($ex2[22]);
	$offroadspeed03s[$name] = getnum($ex2[23]);
	$offroadspeed04s[$name] = getnum($ex2[24]);
	$offroadspeed05s[$name] = getnum($ex2[25]);
	$offroadhandling01s[$name] = getnum($ex2[26]);
	$offroadhandling02s[$name] = getnum($ex2[27]);
	$offroadhandling03s[$name] = getnum($ex2[28]);
	$offroadhandling04s[$name] = getnum($ex2[29]);
	$offroadhandling05s[$name] = getnum($ex2[30]);
}
$f = file_get_contents("statcode.txt");
$ex = explode('<td><a href="',$f);
$i = 5;
while (strpos($ex[$i],"<td>Drift</td>") !== false || strpos($ex[$i],"<td>Hang-on</td>")!==false)
{
	$i0 = strpos($ex[$i],'title="');
	$i1 = strpos($ex[$i],'">',$i0);
	$i2 = strpos($ex[$i],"</b>",$i1);
	$name = strip_tags(substr($ex[$i],$i1+2,$i2-2-$i1));
	$name = str_replace(" (","   ",$name);
	$name = str_replace(")","",$name);
	$ex2 = explode("<td>",$ex[$i]);
	$originalspeeds[$name] = getnum($ex2[1]);
	$originalweights[$name] = getnum($ex2[2]);
	$originalaccelerations[$name] = getnum($ex2[3]);
	$originalhandlings[$name] = getnum($ex2[4]);
	$originaldrifts[$name] = getnum($ex2[5]);
	$originaloffroads[$name] = getnum($ex2[6]);
	$originalminiturbos[$name] = getnum($ex2[7]);
	$i++;
}
echo "<ul>";
$answer1 = linear_regression(array_values($originalspeeds),array_values($speeds));
echo "<li>Speed: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li>";
$answer1 = linear_regression(array_values($originalspeeds),array_values($speedinturns));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Speed In turn: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originalaccelerations),array_values($accelerationnotdriftings0s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Acceleration not drifting 0: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originalaccelerations),array_values($accelerationnotdriftings1s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Acceleration not drifting 1: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originalaccelerations),array_values($accelerationnotdriftings2s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Acceleration not drifting 2: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originalaccelerations),array_values($accelerationnotdriftings3s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Acceleration not drifting 3: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originalaccelerations),array_values($accelerationdriftings0s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Acceleration drifting 0: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originalaccelerations),array_values($accelerationdriftings1s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Acceleration drifting 1: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originalaccelerations),array_values($accelerationdriftings2s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Acceleration drifting 2: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originalaccelerations),array_values($accelerationdriftings3s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Acceleration drifting 3: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originalhandlings),array_values($handlingmanuals));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Manual Handling: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originalhandlings),array_values($handlingautos));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Auto Handling: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originalhandlings),array_values($handlingrange));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Handling Range: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originaldrifts),array_values($driftingmanuals));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Manual Drifting: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originaldrifts),array_values($driftingautos));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Auto Drifting: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originaldrifts),array_values($driftingrange));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Drifting Range: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originalminiturbos),array_values($miniturbos));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Miniturbo Duration: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originaloffroads),array_values($offroadspeed02s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Offroad speed 02: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originaloffroads),array_values($offroadspeed03s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Offroad speed 03: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originaloffroads),array_values($offroadspeed04s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Offroad speed 04: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originaloffroads),array_values($offroadspeed05s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Offroad speed 05: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originaloffroads),array_values($offroadhandling01s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Offroad handling 01: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originaloffroads),array_values($offroadhandling02s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Offroad handling 02: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originaloffroads),array_values($offroadhandling03s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Offroad handling 03: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originaloffroads),array_values($offroadhandling04s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Offroad handling 04: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li><li>$num</li><br>";
$answer1 = linear_regression(array_values($originaloffroads),array_values($offroadhandling05s));
$num = $answer1["slope"]*80 + $answer1["intercept"];
echo "<li>Offroad handling 05: <input onclick=\"this.select();\" size=\"40\" value=\"{$answer1["slope"]}x + {$answer1["intercept"]}\" /></li></ul>";

?>