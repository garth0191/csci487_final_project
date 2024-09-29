<?php
require '/home/gnmcclur/connections/connect.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="home.css">
</head>

<body>
<!-- Nav bar at top of page. -->
<nav class="navbar">
    <!-- Will appear on left side of nav bar. -->
    <div class="navbar-buttons">
        <div class="button" id="Button1">Button1</div>
        <div class="button" id="Button2">Button2</div>
        <div class="button" id="Button3">Button3</div>
        <div class="button logout" id="logout-button">Logout</div>
    </div>
    <!-- Will appear on right side of nav bar. -->
    <div class="navbar-logo">
        <img src="./images/logo.png" width="30%" height="30%" alt="CourseCanvas logo">
    </div>
</nav>

<section class="main-section">
    <!-- Temporary link to main course page. -->
    <div class="button course" id="course-button">To temporary course page.</div>
</section>

<footer class="footer">
    <p>© Garth McClure. All rights reserved.</p>
</footer>

<script src="home.js"></script>
</body>
</html>
