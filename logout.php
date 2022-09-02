<?php
session_start();
session_unset();
session_destroy(); // removes SESSION variables, which is how the system keeps track if the user is logged in
header("Location: index.php"); // redirect to home page
?>