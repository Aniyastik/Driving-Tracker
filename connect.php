<?php
$servername = "mysql-aniya.alwaysdata.net";   // your AlwaysData MySQL host
$username   = "aniya";                        // your AlwaysData MySQL username
$password   = "Aniya2006.";          // the password you set when creating the DB
$dbname     = "aniya_driving_experience";      // the full DB name shown in your AlwaysData panel

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
