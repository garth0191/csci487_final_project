<!-- Delete user's account and redirect them back to the login/signup page. -->
<?php
require '/home/gnmcclur/connections/connect.php';
session_start();

// Grab user ID that has been passed to this page.
if (isset($_GET["user_id"]) && $_GET["user_id"] !== "") {
    $user_id = $_GET["user_id"];
}

try {
    //Remove user from the database.
    $deleteQuery = $conn->prepare("DELETE FROM USER WHERE user_id = ?");
    $deleteQuery->execute([$user_id]);
    
} catch (PDOException $e) {
    echo "ERROR: Could not delete user account.".$e->getMessage();
}

header("Location: admin_dashboard.php");
?>