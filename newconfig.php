<?php
  // This is the server information for the database, you can also connect to it
  // using this information in something like mysql workbench, or phpmyadmin.
  $servername = "104.154.40.155"; // ip address for database
  $username = "root";
  $password = "SDWHKWJF4395";
  $port = "3306";
  $db = "buzzapp"; // database name
  $conn = mysqli_connect($servername, $username, $password, $db, $port); // create connection
?>