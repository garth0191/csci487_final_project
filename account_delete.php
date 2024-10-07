<?php
require '/home/gnmcclur/connections/connect.php';
session_start();

$user_id = $_SESSION['user_id'];

try {
    //Remove user from the database.
    $deleteQuery = $conn->prepare("DELETE FROM USER WHERE user_id = ?");
    $deleteQuery->execute([$user_id]);
    
} catch (PDOException $e) {
    echo "ERROR: Could not delete user account.".$e->getMessage();
}

//Destroy session.
unset($_SESSION['user_email']);
unset($_SESSION['user_id']);
session_destroy();
header("Location: index.php");
?>