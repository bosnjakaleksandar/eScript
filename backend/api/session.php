<?php
session_start();
include 'header.php';

if (isset($_GET['action']) && $_GET['action'] == "getLoggedUser") {


    if (isset($_SESSION['user'])) {
        echo json_encode([
            "success" => true,
            "user" => $_SESSION['user']
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "User not logged in"
        ]);
    }
}
