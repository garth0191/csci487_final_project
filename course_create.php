<?php
session_start();
require '/home/gnmcclur/connections/connect.php';

if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
}

$user_id = $_SESSION['user_id'];
$empty = true;
$message = "";

//Create a new course.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ((isset($_POST["course_name"]) && $_POST["course_name"] !== "") && (isset($_POST["course_description"]) && $_POST["course_description"] !== "") && 
    (isset($_POST["instructor_name"]) && $_POST["instructor_name"] !== "")) {

        try {
            $addCourse = $conn->prepare("INSERT INTO COURSE (course_name, instructor_id, assistant_id, course_description, professor_name) VALUES (?, ?, ?, ?, ?)");
            $addCourse->execute([$_POST["course_name"], $user_id, NULL, $_POST["course_description"], $_POST["instructor_name"]]);
            header("Location: home.php");
        } catch (PDOException $e) {
            echo "ERROR: Could not add course to database. ".$e->getMessage();
        }

    } else {
        $empty = false;
        $message = "Fields cannot be left blank.";
    }
}
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
            <div class="button create" id="create-button">Create Course</div>
            <div class="button" id="Button3">Account Options</div>
            <div class="button logout" id="logout-button">Logout</div>
        </div>
        <!-- Will appear on right side of nav bar. -->
        <div class="navbar-logo">
            <img src="./images/logo.png" height="35%" alt="CourseCanvas logo">
        </div>
    </nav>

    <div class="container">
        <div class="main-section">
            <section class="course-creation-form">
                <h2>New Course Details</h2>
                <form action="course_create.php" method="post">
                    <div class="create-container">
                        Course Name: <input type="text" class="text element" placeholder="Input course name." name="course_name"></input><br>
                        Course Description: <input type="text" class="text element" placeholder='Input course description.' name='course_description'></input><br>
                        Instructor Name: <input type="text" class="text element" placeholder="Input desired instructor name." name="instructor_name"></input><br>
                        <?php if(!$empty) {echo "<div class='error'>".$message."</div>";} ?>
                        <button type="submit" name="submit">Submit</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <footer class="footer">
        <p>© Garth McClure. All rights reserved.</p>
    </footer>

    <script src="course_create.js"></script>
</body>

