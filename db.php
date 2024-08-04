<?php
$servername = "localhost";
$usernamee = "root";  
$password = ""; 
$dbname = "moodinterstellar";  
$port = 3307;
 
$conn = new mysqli($servername, $usernamee, $password, $dbname,$port);
 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
