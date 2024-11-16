<?php
require '/home/gnmcclur/connections/connect.php';
session_start();

if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
}

$user_id = $_SESSION["user_id"];
$user_type = $_SESSION["user_type"];

$current_courses = [];
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
            <div class="button home" id="home-button">Home</div>
            <!-- Display 'Create Course' option ONLY for instructors. -->
            <?php
                if ($user_type < 2) {
                    if ($user_type == 0) {
                        echo "<div class='button admin' id='admin-button'>Admin Dashboard</div>";
                    }
                    echo "<div class='button create' id='create-button'>Create Course</div>";
                }
            ?>
            <div class="button account" id="account-button">Profile</div>
            <div class="button logout" id="logout-button">Logout</div>
        </div>
        <!-- Will appear on right side of nav bar. -->
        <div class="navbar-logo">
            <img src="./images/logo.png" height="35%" alt="CourseCanvas logo">
        </div>
    </nav>

    <div class="container">
        <section class="main-section">
            <center><h2>Affiliated Courses</h2></center><br><br>
            <div class="course-container">
                <div class="tile-container">
                    <?php
                        // Retrieve all courses associated with current user.

                        // Check if user is an instructor or a student.
                        if ($user_type < 2) {
                            try {
                                // User is an instructor.
                                $courseQuery = $conn->prepare("SELECT * FROM COURSE WHERE `instructor_id` = ?");
                                $courseQuery->execute([$user_id]);
                                while ($oneCourse = $courseQuery->fetch(PDO::FETCH_ASSOC)) {
                                    $current_courses[] = array (
                                        "course_id" => $oneCourse["course_id"],
                                        "course_num" => $oneCourse["course_num"],
                                        "course_name" => $oneCourse["course_name"],
                                        "course_description" => $oneCourse["course_description"],
                                        "instructor_name" => $oneCourse["professor_name"],
                                        "semester" => $oneCourse["semester"],
                                        "course_sec_num" => $oneCourse["course_sec_num"]
                                    );
                                }
                            } catch (PDOException $e) {
                                echo "ERROR: Could not pull affiliated instructor courses. ".$e->getMessage();
                            }
                        } else {
                            try {
                                // User is a student.
                                $courseQuery = $conn->prepare("SELECT * FROM USER_COURSE WHERE `user_id` = ?");
                                $courseQuery->execute([$user_id]);
                                while ($oneCourse = $courseQuery->fetch(PDO::FETCH_ASSOC)) {
                                    // Pull the associated course information.
                                    $studentCourses = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
                                    $studentCourses->execute([$oneCourse["course_id"]]);
                                    while ($oneStudentCourse = $studentCourses->fetch(PDO::FETCH_ASSOC)) {
                                        $current_courses[] = array (
                                            "course_id" => $oneStudentCourse["course_id"],
                                            "course_num" => $oneStudentCourse["course_num"],
                                            "course_name" => $oneStudentCourse["course_name"],
                                            "course_description" => $oneStudentCourse["course_description"],
                                            "instructor_name" => $oneStudentCourse["professor_name"],
                                            "semester" => $oneStudentCourse["semester"],
                                            "course_sec_num" => $oneStudentCourse["course_sec_num"]
                                        );
                                    }
                                }
                            } catch (PDOException $e) {
                                echo "ERROR: Could not pull affiliated student courses. ".$e->getMessage();
                            }
                        }
                    ?>
                </div>
            </div>
        </section>
    </div>

    <script>let current_courses = <?php echo json_encode($current_courses); ?></script>

    <footer class="footer">
        <p>© Garth McClure. All rights reserved.</p>
    </footer>

<script src="home.js"></script>
</body>
</html>
