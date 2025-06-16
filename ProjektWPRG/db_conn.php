<?php
$servername = "szuflandia.pjwstk.edu.pl";
$username = "s32708";
$password = "Pio.De";
$dbname = "s32708";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
