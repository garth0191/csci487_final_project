<!-- Delete assessment and redirect user back to the assessment_view.php. -->
<?php
require '/home/gnmcclur/connections/connect.php';
session_start();

$user_id = $_SESSION['user_id'];

// Grab assessment ID that has been passed to this page.
if (isset($_GET["assessment_id"]) && $_GET["assessment_id"] !== "") {
    $assessment_id = $_GET["assessment_id"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ((isset($_POST["course_id"]) && $_POST["course_id"] !== "")) {
        $course_id = $_POST["course_id"];
    }
}

try {
    $deleteQuery = $conn->prepare("DELETE FROM ASSESSMENT WHERE `assessment_id` = ?");
    $deleteQuery->execute([$assessment_id]);
    
} catch (PDOException $e) {
    echo "ERROR: Could not delete assessment.".$e->getMessage();
}

header("Location: assessment_view.php?course_id=$course_id");