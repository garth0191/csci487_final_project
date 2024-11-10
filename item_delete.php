<!-- Delete item and redirect user back to the section_view.php. -->
<?php
require '/home/gnmcclur/connections/connect.php';
session_start();

$user_id = $_SESSION['user_id'];

// Grab item ID that has been passed to this page.
if (isset($_GET["item_id"]) && $_GET["item_id"] !== "") {
    $item_id = $_GET["item_id"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ((isset($_POST["section_id"]) && $_POST["section_id"] !== "")) {
        $section_id = $_POST["section_id"];
    }
}

try {

    $deleteQuery = $conn->prepare("DELETE FROM ITEM WHERE `item_id` = ?");
    $deleteQuery->execute([$item_id]);

    // Grab filepath for item and unlink it.
    $filePathQuery = $conn->prepare("SELECT `file_path` FROM ITEM WHERE `item_id` = ?");
    $filePathQuery->execute([$item_id]);
    while ($row = $filePathQuery->fetch()) {
        $filePath = $row['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

} catch (PDOException $e) {
    echo "ERROR: Could not delete item.".$e->getMessage();
}

header("Location: section_view.php?course_id=$section_id");
