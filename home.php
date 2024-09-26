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
    <link rel="stylesheet" href="mainstyle.css">
</head>

<body>
<header>
    <h1>
        <center><img src="./images/logo.png" width="50%" height="50%" alt="CourseCanvas logo"></center>
    </h1>
</header>

<section class="main-section">
    <div>
        <center>
            You did it! You successfully signed it! That means the login functionality is working!
        </center>
    </div>
    <center><div class="button logout" id="logout-button">Now try to use the logout button.</div></center>
</section>

<footer class="footer">
    <p>© Garth McClure. All rights reserved.</p>
</footer>
</body>
</html>
