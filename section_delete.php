<!-- Delete section and redirect user back to the section_edit.php. -->
<?php
require '/home/gnmcclur/connections/connect.php';
session_start();

$user_id = $_SESSION['user_id'];

// Grab item ID that has been passed to this page.
if (isset($_GET["section_id"]) && $_GET["section_id"] !== "") {
    $section_id = $_GET["section_id"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ((isset($_POST["course_id"]) && $_POST["course_id"] !== "")) {
        $course_id = $_POST["course_id"];
    }
}

try {
    $deleteQuery = $conn->prepare("DELETE FROM SECTION WHERE `section_id` = ?");
    $deleteQuery->execute([$section_id]);

} catch (PDOException $e) {
    echo "ERROR: Could not delete section.".$e->getMessage();
}

header("Location: section_edit.php?course_id=$course_id");

