<?php
// Include this in your code to connect to database: include 'includes/db_conn.php';

$servername = "localhost";
$username = "root";        // Default WAMP username
$password = "";            // Default WAMP password 
$dbname = "sustainaquest_db"; // Database file name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected Succesfully";
?>