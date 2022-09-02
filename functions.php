<?php
function extra_s($num)
{
	// add a s to the end if the num is not 1.
	if ($num != 1)
		return "s";
	return "";
}
function datedifference($d)
{
	// Pass it a DateTime, and it will find how many years, month, days, hours, and minutes had passed from then to the present.
	$date1 = fixtime(new DateTime("now"));
	$interval = $d->diff($date1);
	$years = $interval->y;
	$months = $interval->m;
	$days = $interval->d;
	$hours = $interval->h;
	$minutes = $interval->i;
	$timeago = array();
	if ($years > 0) // at least a year has passed.
	{
		array_push($timeago, "$years year" . extra_s($years));
	}
	if ($months > 0) // if it's at least a month old.
	{
		array_push($timeago, "$months month" . extra_s($months));
	}
	if ($days > 0) // if it's at least a day old
	{
		array_push($timeago, "$days day" . extra_s($days));
	}
	if ($hours > 0) // if it's at least an hour old
	{
		array_push($timeago, "$hours hour" . extra_s($hours));
	}
	if ($minutes > 0) // if it's at least a minute old
	{
		array_push($timeago, "$minutes minute" . extra_s($minutes));
	}
	if (count($timeago) == 0) // if less than a minute has passed
		$timeago[0] = "now";
	else
	{
		if (isPast($d)) // if the given date is before "now"
			$timeago[count($timeago)-1] .= " ago"; // add " ago" to the end.
	}
	return implode(", ",$timeago); // returns everything with a comma between each time unit
}
function fixtime($d)
{
	// for some reason heroku is like 5 hours ahead. I don't know how we're going to do time.
	$d->sub(new DateInterval('PT5H'));
	return $d;
}
function isPast($d)
{
	// Just figure out if the given DateTime is before "now"
	$date1 = fixtime(new DateTime());
	return $d < $date1;
}
function findend($timestart,$duration)
{
	$date1 = new DateTime($timestart);
	return $date1->add(new DateInterval("PT$duration" . "M"))->format("h:i A");
}
function findend2($timestart,$duration)
{
	$date1 = new DateTime($timestart);
	return $date1->add(new DateInterval("PT$duration" . "M"));
}
function difference_in_minutes($time1,$time2)
{
	return $time1->diff($time2)->h*60 + $time1->diff($time2)->i; // cause otherwise, if h > 0, m will not account for those extra hours;
}
function fiximage($im)
{
	// If the barbershop image isn't there, show the default one.
	if (is_null($im) || $im == "")
		return "/images/clipartbarbershop.jpg";
	else
		return $im;
}
function convertday($day)
{
	// Change day of week from 0 - 7 to text form.
	if ($day == 0)
		return "Sunday";
	elseif ($day == 1)
		return "Monday";
	elseif ($day == 2)
		return "Tuesday";
	elseif ($day == 3)
		return "Wednesday";
	elseif ($day == 4)
		return "Thursday";
	elseif ($day == 5)
		return "Friday";
	elseif ($day == 6)
		return "Saturday";
	else
		return $day;
}
function get_redirect_target($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $headers = curl_exec($ch);
    curl_close($ch);
    // Check if there's a Location: header (redirect)
    if (preg_match('/^Location: (.+)$/im', $headers, $matches))
        return trim($matches[1]);
    return $url;
} // Source: https://gist.github.com/davejamesmiller/dbefa0ff167cc5c08d6d
function formattime($time)
{
	$hour = substr($time,0,2);
	$minute = substr($time,2,2);
	if ($minute == "")
		$minute = "00";
	if (substr($hour,0,1) == "0")
		$hour = substr($hour,1);
	if ($hour < 12)
		return "$hour:$minute AM";
	else if ($hour == 12)
		return "$hour:$minute PM";
	else
	{
		$hour -= 12;
		return "$hour:$minute PM";
	}
}
function checktime($elem,$ampm)
{
	$sp = explode(":",$elem);
	$hour = 0;
	$min = 0;
	$valid = true;
	if (count($sp) == 1) // if they didn't have a colon
	{
		$str = $sp[0];
		if (strlen($str) < 3) { // then it's like 10:00
			if ($str > 0 && $str < 13)
				$hour = $str;
			else
				$valid = false;
		}
		elseif (strlen($str) == 3) { // if they put it in like 100
			$str2 = substr($str,0,1); // then the first number is the hour
			$str3 = substr($str,1);
			if ($str2 > 0)
				$hour = $str2;
			else
				$valid = false;
			if ($str3 < 60)
				$min = $str3;
			else
				$valid = false;
		}
		elseif (strlen($str) == 4) { // if they put it in like 1100
			$str2 = substr($str,0,2);
			$str3 = substr($str,2);
			if (substr($str2,0,1)=="0") // so it's like 0100
				$str2 = substr($str2,1);
			if ($str2 > 0 && $str2 < 13)
				$hour = $str2;
			else
				$valid = false;
			if ($str3 < 60)
				$min = $str3;
			else
				$valid = false;
		}
		else
		{
			$valid = false;
		}
	}
	elseif (count($sp) == 2) // so it's like 11:00
	{
		$str2 = $sp[0];
		$str3 = $sp[1];
		if (substr($str2,0,1)=="0") // so the time is like 1:00
			$str2 = substr($str2,1);
		if ($str2 > 0 && $str2 < 13)
			$hour = $str2;
		else
			$valid = false;
		if ($str3 < 60)
			$min = $str3;
		else
			$valid = false;
	}
	else // if there's more than one colon
	{
		$valid = false;
	}
	if ($valid)
	{
		if (strtoupper($ampm) == 'AM' && $hour == 12)
			$hour = 0; // 0:00 is the same as 12:00 AM
		elseif (strtoupper($ampm) == 'PM' && $hour != 12)
			$hour += 12; // as long as it's not 12:00 PM, then this puts it in 24 hour format
		return new DateTime("$hour:$min");
	}
	else
	{
		return false;
	}
}
function is_leap_year($year) {
			return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)));
} // Source: https://davidwalsh.name/checking-for-leap-year-using-php
function distance($lat1, $lon1, $lat2, $lon2) {
  if (($lat1 == $lat2) && ($lon1 == $lon2)) {
    return 0;
  }
  else {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    return $dist * 60 * 1.1515;
    }
}
function sort_by_distance($a, $b)
{
	return ($a[6] - $b[6]);
}
function sort_by_price($a, $b)
{
	return ($a[9] - $b[9]);
}
function sort_by_rating($a, $b)
{
	return ($b[9] - $a[9]);
}
function sort_by_times_visited($a, $b)
{
	return ($b[7] - $a[7]);
}
function in_arrayi($needle, $haystack) {
    return in_array(strtolower($needle), array_map('strtolower', $haystack));
}
function fixpm($time)
{
	$sp = explode(" ",$time);
	$sp2 = explode(":",$sp[0]);
	$hour = $sp2[0];
	if ($sp[1] == "PM" || $sp[1] == "pm")
	{
		if ($hour < 12)
			$hour += 12;
	}
	if ($sp[1] == "AM" || $sp[1] == "am")
	{
		if ($hour == 12)
			$hour = 0;
	}
	return "'$hour', '{$sp2[1]}'";
}
function send_notification($subject, $sender, $receiver, $message)
{
		$from = new SendGrid\Email(null, $sender);
		$to = new SendGrid\Email(null, $receiver);
		// Create Sendgrid content
		$content = new SendGrid\Content("text/html",nl2br($message));
		// Create a mail object
		$mail = new SendGrid\Mail($from, $subject, $to, $content);
		$sg = new \SendGrid('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');
		$response = $sg->client->mail()->send()->post($mail);
}
function fixCheckBox($attribute)
{
	if (isset($_POST[$attribute]))
		return 1;
	else
		return 0;
}
function fix_leading_zero($num)
{
	if (substr($num,0,1) == "0")
		return substr($num,1);
	else
		return $num;
}
?>