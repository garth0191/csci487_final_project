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
    (isset($_POST["instructor_name"]) && $_POST["instructor_name"] !== "") && (isset($_POST["course_code"]) && $_POST["course_code"] !== "") &&
    (isset($_POST["semester"]) && $_POST["semester"] !== "") && (isset($_POST["course_sec_num"]) && $_POST["course_sec_num"] !== "")) {

        try {
            //Check that course is not already taken.
            $courseCheck = $conn->prepare("SELECT * FROM COURSE WHERE `course_num` = ? AND `course_sec_num` = ? AND `semester` = ?");
            $courseCheck->execute([$_POST["course_code"], $_POST["course_sec_num"], $_POST["semester"]]);
            if ($courseCheck->rowCount() >= 1) {
                $empty = false;
                $message = "A course with the given name for this semester already exists with the indicated section number.";
            } else {
                $addCourse = $conn->prepare("INSERT INTO COURSE (course_num, course_name, instructor_id, assistant_id, course_description, professor_name, course_sec_num, semester) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $addCourse->execute([$_POST["course_code"], $_POST["course_name"], $user_id, NULL, $_POST["course_description"], $_POST["instructor_name"], $_POST["course_sec_num"], $_POST["semester"]]);

                //Grab new course and add initial empty weights to each assessment type for the new course.
                $retrieveCourse = $conn->prepare("SELECT * FROM COURSE WHERE `course_num` = ? AND `instructor_id` = ? AND `course_sec_num` = ? AND `semester` = ?");
                $retrieveCourse->execute([$_POST["course_code"], $user_id, $_POST["course_sec_num"], $_POST["semester"]]);
                while ($oneCourse = $retrieveCourse->fetch(PDO::FETCH_ASSOC)) {
                    $course_id = $oneCourse["course_id"];
                    $pullTypes = $conn->prepare("SELECT * FROM ASSESSMENT_TYPE");
                    $pullTypes->execute();
                    while ($oneType = $pullTypes->fetch(PDO::FETCH_ASSOC)) {
                        $newWeight = $conn->prepare("INSERT INTO COURSE_WEIGHT (course_id, type_id, weight) VALUES (?, ?, ?)");
                        $newWeight->execute([$course_id, $oneType["assessment_type_id"], NULL]);
                    }
                }
                header("Location: home.php");
            }
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
            <div class="button home" id="home-button">Home</div>
            <div class="button create" id="create-button">Create Course</div>
            <div class="button account" id="account-button">Profile</div>
            <div class="button logout" id="logout-button">Logout</div>
        </div>
        <!-- Will appear on right side of nav bar. -->
        <div class="navbar-logo">
            <img src="./images/logo.png" height="35%" alt="CourseCanvas logo">
        </div>
    </nav>

    <div class="container">
    <h2>New Course Details</h2>
        <div class="main-section">
            <section class="course-creation-form">
                <form action="course_create.php" method="post">
                    <div class="create-container">
                        Course Code: <input type="text" class="text element" placeholder="Input course department code, e.g., 'CSCI 487'." name="course_code"><br>
                        Course Name: <input type="text" class="text element" placeholder="Input course name." name="course_name"></input><br>
                        Section Number: <input type="number" class="number element" placeholder="Input course section number." min ="1" max="99" name="course_sec_num"><br></br>
                        Semester: <select name='semester'>
                            <option style="display: none;"></option>
                            <option name="semester" value="Fall 2024">Fall 2024</option>
                            <option name="semester" value="Spring 2025">Spring 2025</option>
                        </select><br>
                        Course Description: <input type="text" class="text element" placeholder='Input course description.' name='course_description'></input><br>
                        Instructor Name: <input type="text" class="text element" placeholder="Input desired instructor name." name="instructor_name"></input><br>
                        <?php if(!$empty) {echo "<div class='error'>".$message."</div>";} ?>
                        <button type="submit" name="submit" class="creation-submit">Submit</button>
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

