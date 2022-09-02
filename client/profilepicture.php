<?php
session_start();
if (!isset($_SESSION["email"]) || $_SESSION["accountType"]!=1)
	header("Location: ../login.php"); // redirect to home page if not logged in as client
include '../config.php';
include '../functions.php';
$msgs = array();
if ($_POST && isset($_POST["submit"]))
{
	require('../aws/aws-autoloader.php'); // Amazon S3 is where the images will be stored.
	$s3 = new Aws\S3\S3Client([
		'version'  => '2006-03-01',
		'region'   => 'us-east-2',
	]);
	$bucket = getenv('S3_BUCKET')?: array_push($msgs,'No "S3_BUCKET" config var in found in env!');
	if (!is_uploaded_file($_FILES['file_upload']['tmp_name']))
		array_push($msgs,"Didn't choose an image, or the image might be too large.");
	if (count($msgs) == 0)
	{
	$uploadImage = $_FILES['file_upload']['name'];
	if($uploadImage != "")
	{
		$uploadOk = 1;
		$imageFileType = strtolower(pathinfo(basename($_FILES["file_upload"]["name"]),PATHINFO_EXTENSION));
		// Check if image file is a actual image or fake image
		$check = getimagesize($_FILES["file_upload"]["tmp_name"]);
		if($check === false) {
			array_push($msgs,"File is not an image.");
			$uploadOk = 0;
		}
		// Check file size
		if ($_FILES["file_upload"]["size"] > 5000000) {
			array_push($msgs,"Sorry, " .  basename( $_FILES["file_upload"]["name"]) ." is too large.");
			$uploadOk = 0;
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif") {
			array_push($msgs,"Sorry, only JPG, JPEG, PNG & GIF , PDF files are allowed.");
			$uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			array_push($msgs,"Sorry, your file was not uploaded.");
		// if everything is ok, try to upload file
		} else {
			$upload = $s3->upload($bucket, basename($_FILES["file_upload"]["tmp_name"]), fopen($_FILES["file_upload"]["tmp_name"], 'rb'), 'public-read');
			$imageName = $upload->get('ObjectURL');
			if ($conn && isset($_SESSION["email"]) && isset($_SESSION["accountType"]) && $_SESSION["accountType"]==1)
			{
				$result = mysqli_query($conn, "update client set profilePicture='" . mysqli_real_escape_string($conn, $imageName) . "' where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'"); // set the profile picture to linked image in Amazon
			}
		}
	}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>BuzzApp - Profile Picture</title>
<?php include 'header.php'; ?>
<script src="../jqueryform.js"></script>
<script src="../upload.js"></script>
<link rel="stylesheet" href="../registersheet.css" />
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
<div id="contact">
<h3>Profile Image</h3><?php
if ($_POST && isset($_POST["submit"]) && count($msgs) > 0)
{
	echo "<ul>";
	foreach ($msgs as $msg)
		echo "<li>$msg</li>";
	echo "</ul>";
}
?><fieldset>
<?php
if ($conn)
{
	echo "<div class=\"center\" id=\"profilePicture\">";
	$result = mysqli_query($conn, "select profilePicture from client where email='" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'");
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			if (is_null($row["profilePicture"]) || $row["profilePicture"] == "")
				echo "<img src=\"../images/defaultavatar.png\" alt=\"No Profile Picture\" style=\"cursor:pointer;\" onclick=\"document.getElementById('file_upload').click();\">";
			else
				echo "<img src=\"{$row["profilePicture"]}\" class=\"profilePicture\" style=\"cursor:pointer;\" alt=\"Profile Picture\" onclick=\"document.getElementById('file_upload').click();\">";
		}
	}
	echo "</div>";
}
?>
<form id="uploadform" method="post" enctype="multipart/form-data" onsubmit="return checkFile(this,true);">
		<input type="file" id="file_upload" name="file_upload" style="margin:0;" accept=".png,.gif,.jpg,.jpeg">
	<input type="submit" name="submit" class="uploadButton" value="Upload Image" />
</form><div id="progressbox"><div id="progressbar"></div><div id="statustxt">0%</div></div>
</fieldset></div></div>
</body>
</html>