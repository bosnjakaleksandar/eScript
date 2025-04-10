<?php
$host = "db";             // Ime servisa baze iz docker-compose.yml
$user = "user";           // Korisničko ime baze
$password = "secret";     // Šifra baze
$database = "notes_app";  // Ime baze

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
