<?php
    session_start();
    require '/home/gnmcclur/connections/connect.php';

    if(!isset($_SESSION['user_id'])){
        header('Location: index.php');
    }

    $user_id = $_SESSION['user_id'];

    // Grab course ID that has been passed to this page.
    if (isset($_GET["course_id"]) && $_GET["course_id"] !== "") {
        $course_id = $_GET["course_id"];
    }

    // Pull number of course assessments for table header.
    try {
        $assessments = $conn -> prepare("SELECT * FROM ASSESSMENT WHERE `course_id` = ?");
        $assessments->execute([$course_id]);
        $numAssessments = $assessments -> rowCount();
    } catch (PDOException $e) {
        echo "ERROR: Could not pull total number of assessments for courses. ".$e->getMessage();
    }

    // Update student grade.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['new_grade'], $_POST['assessment_id'], $_POST['user_id'])) {
            $new_grade = $_POST['new_grade'];
            $assessment_id = $_POST['assessment_id'];
            $student_id = $_POST['user_id'];

            if (is_numeric($new_grade) && $new_grade >= 0 && $new_grade <= 100) {
                try {
                    $insertGrade = $conn->prepare("INSERT INTO USER_ASSESSMENT (user_id, assessment_id, course_id, assessment_score) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE assessment_score = VALUES(assessment_score)");
                    $insertGrade->execute([$student_id, $assessment_id, $course_id, $new_grade]);
                    header("Location: gradebook.php?course_id=".$course_id);
                    exit();
                } catch (PDOException $e) {
                    echo "ERROR: Could not update grade in database. " . $e->getMessage();
                }
            } else {
                echo "Invalid grade value.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gradebook Page</title>
    <link rel="stylesheet" href="gradebook.css">
</head>

<body>
<!-- Nav bar at top of page. -->
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
    <div class="main-section">
            <?php
            $pullCourseName = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
            $pullCourseName->execute([$course_id]);
            while ($oneCourse = $pullCourseName->fetch(PDO::FETCH_ASSOC)) {
                echo "<h1>".$oneCourse["course_num"]." ".$oneCourse["course_name"]."</h1>";
                echo "<h2>Section ".$oneCourse["course_sec_num"].", ".$oneCourse["semester"]."</h2>";
            }
            ?>
        <!-- Gradebook section. -->
        <section class="gradebook">
            <h3>Course Assessments</h3>
            <div class="gradebook-container">
                <table id="gradebook-table">
                    <?php
                        if ($numAssessments < 1) {
                            echo "<tr>";
                            echo "<td><b>No assessments created yet.</b></td>";
                            echo "</tr>";
                        } else {
                            // Table headers.
                            echo "<tr>";
                            echo "<th onclick='sortTable(0)'><b>Last Name</b></th>";
                            echo "<th onclick='sortTable(1)'><b>First Name</b></th>";
                            echo "<th colspan='".$numAssessments."'><b><em>Assessments</em></b></th>";
                            echo "</tr>";
                            echo "<tr>";
                            echo "<td colspan='2' bgcolor='gray'></td>"; // Blank for student first and last name.
                            $assessmentsList = array();
                            $assessmentNames = $conn->prepare("SELECT * FROM `ASSESSMENT` WHERE `course_id` = ?");
                            $assessmentNames->execute([$course_id]);
                            while ($oneAssessment = $assessmentNames->fetch(PDO::FETCH_ASSOC)) {
                                $assessmentsList[] = $oneAssessment;
                                echo "<td bgcolor='gray'>".$oneAssessment["assessment_description"]."</td>";
                            }
                            echo "</tr>";
                            // Rows: student last name, student first name, all assessments for that student, average.
                            // Check that course has students.
                            $pullStudents = $conn -> prepare("SELECT * FROM USER_COURSE WHERE `course_id` = ?");
                            $pullStudents->execute([$course_id]);
                            $numStudents = $pullStudents -> rowCount();
                            if ($numStudents < 1) {
                                echo "<tr><td colspan='".(2+$numAssessments)."'><em><strong>No students currently registered in course.</strong></em></td></tr>";
                            } else {
                                while ($oneStudent = $pullStudents->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    $pullName = $conn->prepare("SELECT * FROM USER WHERE `user_id` = ?");
                                    $pullName->execute([$oneStudent["user_id"]]);
                                    while ($oneName = $pullName->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<td>".$oneName["last_name"]."</td>";
                                        echo "<td>".$oneName["first_name"]."</td>";
                                        // Pull from USER_ASSESSMENT table all records for student for this course.
                                        $pullUserAssessments = $conn -> prepare("SELECT * FROM USER_ASSESSMENT WHERE `course_id` = ? AND user_id = ?");
                                        $pullUserAssessments->execute([$course_id, $oneStudent["user_id"]]);
                                        $userAssessments = array();
                                        while ($oneUserAssessment = $pullUserAssessments->fetch(PDO::FETCH_ASSOC)) {
                                            $userAssessments[$oneUserAssessment["assessment_id"]] = $oneUserAssessment;
                                        }
                                        // Display grades with EDIT button.
                                        foreach ($assessmentsList as $assessment) {
                                            $assessment_id = $assessment["assessment_id"];
                                            if (isset($userAssessments[$assessment_id])) {
                                                $score = $userAssessments[$assessment_id]["assessment_score"];
                                            } else {
                                                $score = "N/A";
                                            }
                                            echo "<td>";
                                            echo $score;
                                            echo "&nbsp;<button class='edit-grade-button' data-assessment-id='".$assessment_id."' data-user-id='".$oneStudent["user_id"]."' data-score='".$score."' style='background: transparent; display: inline; border: none; padding: 0; cursor: pointer;'><img src='./images/pencil-square.svg' alt='Edit'></button>";
                                            echo "</td>";
                                        }
                                    }
                                    echo "</tr>";
                                }
                            }
                        }
                    ?>
                </table>
            </div>
        </section>
    </div>

    <!-- Sidebar. -->
    <div class="sidebar">
        <!-- Course edit options, etc., will go here. -->
        <a href="course.php?course_id=<?php echo $course_id; ?>">COURSE HOME</a>
        <a href="course_edit.php?course_id=<?php echo $course_id; ?>">EDIT COURSE</a>
        <a href="assessment_create.php?course_id=<?php echo $course_id; ?>">CREATE ASSESSMENT</a>
        <a href="assessment_view.php?course_id=<?php echo $course_id; ?>">VIEW/EDIT ASSESSMENTS</a>
        <a href="section_edit.php?course_id=<?php echo $course_id; ?>">EDIT COURSE CONTENT</a>
        <a href="gradebook.php?course_id=<?php echo $course_id; ?>">GRADEBOOK</a>
        <?php
        // Pull all sections created by instructor.
        try {
            $sectionQuery = $conn->prepare("SELECT * FROM SECTION WHERE `course_id` = ?");
            $sectionQuery->execute([$course_id]);

            if ($sectionQuery->rowCount() >= 1) {
                echo "<br>";
                echo "<hr>";
                echo "<br>";
            }

            while ($sectionRow = $sectionQuery->fetch(PDO::FETCH_ASSOC)) {
                echo "<a href='section_view.php?section_id=".$sectionRow["section_id"]."'>".$sectionRow["section_name"]."</a>";
            }
        } catch (PDOException $e) {
            echo "ERROR: Could not retrieve sections from database. ".$e->getMessage();
        }
        ?>
    </div>
</div>

<footer class="footer">
    <p>© Garth McClure. All rights reserved.</p>
</footer>

<div id="edit-grade-modal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <form id="edit-grade-form" method="post" action="">
            <label for="new-grade">Input new grade:</label>
            <input type="number" id="new-grade" name="new_grade" min="0" max="100" required>
            <input type="hidden" id="modal-assessment-id" name="assessment_id">
            <input type="hidden" id="modal-user-id" name="user_id">
            <button type="submit">Submit</button>
        </form>
    </div>
</div>

<script src="gradebook.js"></script>
</body>
</html>

