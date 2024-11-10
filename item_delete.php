<?php
require '/home/gnmcclur/connections/connect.php';
session_start();

$user_id = $_SESSION['user_id'];

// Grab item ID that has been passed to this page.
if (isset($_GET["item_id"]) && $_GET["item_id"] !== "") {
    $item_id = $_GET["item_id"];
}

// Retrieve section_id.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ((isset($_POST["section_id"]) && $_POST["section_id"] !== "")) {
        $section_id = $_POST["section_id"];
    }
}

try {
    // Retrieve file path and delete item.
    $filePathQuery = $conn->prepare("SELECT `file_path` FROM ITEM WHERE `item_id` = ?");
    $filePathQuery->execute([$item_id]);
    $row = $filePathQuery->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $filePath = $row['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Delete item from database.
    $deleteQuery = $conn->prepare("DELETE FROM ITEM WHERE `item_id` = ?");
    $deleteQuery->execute([$item_id]);

} catch (PDOException $e) {
    echo "ERROR: Could not delete item.".$e->getMessage();
}

header("Location: section_view.php?section_id=$section_id");
?>
