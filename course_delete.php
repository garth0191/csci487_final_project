<!-- Delete a course and redirect user back to the homepage -->
<?php
require '/home/gnmcclur/connections/connect.php';
session_start();

$user_id = $_SESSION['user_id'];

// Grab course ID that has been passed to this page.
if (isset($_GET["course_id"]) && $_GET["course_id"] !== "") {
    $course_id = $_GET["course_id"];
}

try {
    //Remove user from the database.
    $deleteQuery = $conn->prepare("DELETE FROM COURSE WHERE `course_id` = ?");
    $deleteQuery->execute([$course_id]);
    
} catch (PDOException $e) {
    echo "ERROR: Could not delete user account.".$e->getMessage();
}

header("Location: admin_dashboard.php");
?>