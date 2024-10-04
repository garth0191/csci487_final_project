<?php
session_start();
require '/home/gnmcclur/connections/connect.php';

if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
}

//Create a new course.

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Creation Page</title>
    <link rel="stylesheet" href="course_create.css">
</head>

<body>
    <nav class="navbar">
        <!-- Will appear on left side of nav bar. -->
        <div class="navbar-buttons">
            <div class="button" id="Button1">Home</div>
            <div class="button" id="Button2">Create Course</div>
            <div class="button" id="Button3">Account Options</div>
            <div class="button logout" id="logout-button">Logout</div>
        </div>
        <!-- Will appear on right side of nav bar. -->
        <div class="navbar-logo">
            <img src="./images/logo.png" height="35%" alt="CourseCanvas logo">
        </div>
    </nav>

    

    <footer class="footer">
        <p>© Garth McClure. All rights reserved.</p>
    </footer>

    <script src="course.js"></script>
</body>

