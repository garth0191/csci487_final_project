<?php
session_start();
require '/home/gnmcclur/connections/connect.php';

if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
}

$user_id = $_SESSION['user_id'];

// Grab course ID that has been passed.
if (isset($_GET["course_id"]) && $_GET["course_id"] !== "") {
    $course_id = $_GET["course_id"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="course.css">
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
            <img src="./images/logo.png" height="35%" alt="CourseCanvas logo">
        </div>
    </nav>

    <div class="container">
        <section class="main-section">
            <!-- Upcoming assessments section. -->
            <section class="upcoming">
                <table>
                    <tr>
                        <th>Assessment Name</th>
                        <th>Assessment Type</th>
                        <th>Due Date</th>
                    </tr>
                        <!-- PHP code to grab assessment items. -->
                </table>
            </section>
        </section>

        <!-- Sidebar. -->
        <section class="sidebar">
            <!-- Course edit options, etc., will go here. -->
            <a href="course_edit.php?course_id=<?php echo $course_id; ?>">EDIT COURSE</a>
            <a href="course_edit.php?course_id=<?php echo $course_id; ?>">DELETE COURSE</a>
            <a href="assessment_create.php?course_id=<?php echo $course_id; ?>">CREATE ASSESSMENT</a>
        </section>
    </div>

    <footer class="footer">
        <p>© Garth McClure. All rights reserved.</p>
    </footer>

<script src="course.js"></script>
</body>
</html>
