<?php
    session_start();
    require '/home/gnmcclur/connections/connect.php';

    if(!isset($_SESSION['user_id'])){
        header('Location: index.php');
    }

    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];

    // Grab course ID that has been passed to this page.
    if (isset($_GET["course_id"]) && $_GET["course_id"] !== "") {
        $course_id = $_GET["course_id"];
    }

    // Pull assistant_id for the course, if there is one.
    $pullAssistant = $conn->prepare("SELECT `assistant_id` FROM COURSE WHERE `course_id` = ?");
    $pullAssistant->execute([$course_id]);
    $assistant_id = $pullAssistant->fetchColumn();

    // Pull number of course assessments for table header.
    try {
        $assessments = $conn -> prepare("SELECT * FROM ASSESSMENT WHERE `course_id` = ?");
        $assessments->execute([$course_id]);
        $numAssessments = $assessments -> rowCount();
    } catch (PDOException $e) {
        echo "ERROR: Could not pull total number of assessments for courses. ".$e->getMessage();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update student grade.
        if (isset($_POST['new_grade'], $_POST['assessment_id'], $_POST['user_id'])) {
            $new_grade = $_POST['new_grade'];
            $assessment_id = $_POST['assessment_id'];
            $student_id = $_POST['user_id'];

            if (is_numeric($new_grade) && $new_grade >= 0 && $new_grade <= 100) {
                try {
                    $updateGrade = $conn->prepare("UPDATE USER_ASSESSMENT SET assessment_score = ? WHERE user_id = ? AND assessment_id = ?");
                    $updateGrade->execute([$new_grade, $student_id, $assessment_id]);

                    header("Location: gradebook.php?course_id=".$course_id);
                    exit();
                } catch (PDOException $e) {
                    echo "ERROR: Could not update grade in database. " . $e->getMessage();
                }
            } else {
                echo "Invalid grade value.";
            }
        }

        // Delete student's grade.
        if ((isset($_POST["delete_assessment_id"]) && $_POST["delete_assessment_id"] !== "") && (isset($_POST["delete_student_id"]) && $_POST["delete_student_id"] !== "")) {
            try {
                $deleteGrade = $conn->prepare("UPDATE USER_ASSESSMENT SET `assessment_score` = null WHERE user_id = ? AND assessment_id = ?");
                $deleteGrade->execute([$_POST["delete_student_id"], $_POST["delete_assessment_id"]]);

                header("Location: gradebook.php?course_id=".$course_id);
                exit();
            } catch (PDOException $e) {
                echo "ERROR: Could not delete USER_ASSESSMENT record. ".$e->getMessage();
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
        <!-- Display 'Create Course' option ONLY for instructors. -->
        <?php
        if ($user_type < 2) {
            echo "<div class='button create' id='create-button'>Create Course</div>";
        }
        ?>
        <?php
        if ($user_type == 0) {
            echo "<div class='button admin' id='admin-button'>Admin Dashboard</div>";
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
    <div class="main-section">
        <?php
        $pullCourseName = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
        $pullCourseName->execute([$course_id]);
        while ($oneCourse = $pullCourseName->fetch(PDO::FETCH_ASSOC)) {
            echo "<h1>".$oneCourse["course_num"]." ".$oneCourse["course_name"]."</h1>";
            $pullSemester  = $conn->prepare("SELECT * FROM SEMESTER WHERE `semester_id` = ?");
            $pullSemester->execute([$oneCourse["semester"]]);
            while ($oneSemester = $pullSemester->fetch(PDO::FETCH_ASSOC)) {
                echo "<h3>Section ".$oneCourse["course_sec_num"].", ".$oneSemester["semester_name"]."</h3>";
            }
        }
        ?>
        <!-- Gradebook section. -->
        <section class="gradebook">
            <h3>Course Assessments</h3>
            <div class="gradebook-container">
                <table id="gradebook-table">
                    <?php
                        // INSTRUCTOR and ASSISTANT gradebook.
                        if ($user_type == 1 || $user_type == 0 || ($user_type == 2 && $assistant_id == $user_id)) {
                            echo "<tr>";
                            if ($numAssessments < 1) {
                                echo "<td><b>No assessments created yet.</b></td>";
                                echo "</tr>";
                            } else {
                                // Table headers.
                                echo "<th onclick='sortTable(0)'><b>Last Name</b></th>";
                                echo "<th onclick='sortTable(1)'><b>First Name</b></th>";
                                echo "<th colspan='".$numAssessments."'><b><em>Assessments</em></b></th>";
                                echo "</tr>";
                                echo "<tr>";
                                echo "<td colspan='2' bgcolor='gray'></td>"; // Blank for student first and last name.
                                $assessmentsList = array();
                                $assessmentNames = $conn->prepare("SELECT * FROM `ASSESSMENT` WHERE `course_id` = ? ORDER BY assessment_type, due_date");
                                $assessmentNames->execute([$course_id]);
                                while ($oneAssessment = $assessmentNames->fetch(PDO::FETCH_ASSOC)) {
                                    $assessmentsList[] = $oneAssessment;
                                    echo "<td bgcolor='gray'>".$oneAssessment["assessment_description"]."</td>";
                                }
                                echo "</tr>";
                                // Rows: student last name, student first name, all assessments for that student, average.
                                // Check that course has students.
                                $pullStudents = $conn->prepare("SELECT uc.user_id, u.first_name, u.last_name FROM USER_COURSE uc INNER JOIN USER u ON uc.user_id = u.user_id WHERE uc.course_id = ?");
                                $pullStudents->execute([$course_id]);
                                $numStudents = $pullStudents->rowCount();

                                if ($numStudents < 1) {
                                    echo "<tr><td colspan='".(2+$numAssessments)."'><em><strong>No students currently registered in course.</strong></em></td></tr>";
                                } else {
                                    while ($oneStudent = $pullStudents->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td>".htmlspecialchars($oneStudent["last_name"], ENT_QUOTES, 'UTF-8')."</td>";
                                        echo "<td>".htmlspecialchars($oneStudent["first_name"], ENT_QUOTES, 'UTF-8')."</td>";

                                        // Pull all USER_ASSESSMENT records for the student and course.
                                        $pullUserAssessments = $conn->prepare("SELECT ua.* FROM USER_ASSESSMENT ua INNER JOIN ASSESSMENT a ON ua.assessment_id = a.assessment_id WHERE a.course_id = ? AND ua.user_id = ?");
                                        $pullUserAssessments->execute([$course_id, $oneStudent["user_id"]]);
                                        $userAssessments = array();
                                        while ($oneUserAssessment = $pullUserAssessments->fetch(PDO::FETCH_ASSOC)) {
                                            $userAssessments[$oneUserAssessment["assessment_id"]] = $oneUserAssessment;
                                        }
                                        foreach ($assessmentsList as $assessment) {
                                            $assessment_id = $assessment["assessment_id"];
                                            $has_submissions = $assessment["has_submissions"];
                                            if (isset($userAssessments[$assessment_id])) {
                                                $score = $userAssessments[$assessment_id]["assessment_score"];
                                                $submission_filepath = $userAssessments[$assessment_id]["user_submission_filepath"];
                                            } else {
                                                $score = "N/A";
                                                $submission_filepath = null;
                                            }
                                            echo "<td>";

                                            echo htmlspecialchars($score, ENT_QUOTES, 'UTF-8');
                                            if ($score != null) {
                                                echo "<form action='gradebook.php?course_id=".$course_id."' method='post' style='display: inline; padding: 5px;'>";
                                                echo "<input type='hidden' name='delete_assessment_id' value='".$assessment_id."'></input>";
                                                echo "<input type='hidden' name='delete_student_id' value='".$oneStudent["user_id"]."'></input>";
                                                echo "<button type='submit' name='submit' onclick='confirmDelete(event)' style='background: transparent; border: none; padding: 0; cursor: pointer;'><img src='./images/trash.svg' alt='Delete'></button>";
                                                echo "</form>";
                                            }
                                            echo "&nbsp;<button class='edit-grade-button' data-assessment-id='" . $assessment_id . "' data-user-id='" . $oneStudent["user_id"] . "' data-score='" . htmlspecialchars($score, ENT_QUOTES, 'UTF-8') . "' style='background: transparent; border: none; padding: 0; cursor: pointer;'><img src='./images/pencil-square.svg' alt='Edit' style='width:20px; height:auto;'></button>";

                                            if ($has_submissions == 1 && !empty($submission_filepath)) {
                                                echo "&nbsp;<a href='" . htmlspecialchars($submission_filepath, ENT_QUOTES, 'UTF-8') . "' target='_blank'>Submission</a>";
                                            }

                                            echo "</td>";
                                        }
                                        echo "</tr>";
                                    }
                                }

                            }
                        } else {
                            // STUDENT gradebook.
                            echo "<tr>";
                            if ($numAssessments < 1) {
                                echo "<td><b>No assessment grades available.</b></td>";
                                echo "</tr>";
                            } else {
                                // Table headers.
                                echo "<th onclick='sortTable(0)'><b>Assessment Name</b></th>";
                                echo "<th onclick='sortTable(1)'><b>Assessment Type</b></th>";
                                echo "<th onclick='sortTable(2)'><b>Due Date</b></th>";
                                echo "<th onclick='sortTable(3)'><b>Grade</b></th>";
                                echo "</tr>";
                                // Pull student grade information for each assessment in the course.
                                $assessmentNames = $conn->prepare("SELECT * FROM `ASSESSMENT` WHERE `course_id` = ? ORDER BY assessment_type, due_date");
                                $assessmentNames->execute([$course_id]);
                                while ($oneAssessment = $assessmentNames->fetch(PDO::FETCH_ASSOC)) {
                                    $has_submissions = $oneAssessment["has_submissions"];
                                    echo "<tr>";
                                    echo "<td>".htmlspecialchars($oneAssessment["assessment_description"], ENT_QUOTES, 'UTF-8')."</td>";
                                    echo "<td>".htmlspecialchars($oneAssessment["assessment_type"], ENT_QUOTES, 'UTF-8')."</td>";
                                    echo "<td>".htmlspecialchars($oneAssessment["due_date"], ENT_QUOTES, 'UTF-8')."</td>";

                                    // Pull the grade for the student and assessment.
                                    $pullGradeQuery = $conn->prepare("SELECT * FROM `USER_ASSESSMENT` WHERE `user_id` = ? AND `assessment_id` = ?");
                                    $pullGradeQuery->execute([$user_id, $oneAssessment["assessment_id"]]);

                                    // Check if a grade record exists.
                                    if ($oneGrade = $pullGradeQuery->fetch(PDO::FETCH_ASSOC)) {
                                        if ($oneGrade["assessment_score"] !== NULL) {
                                            if ($has_submissions === 1) {
                                                echo "<td><a href='".$oneGrade["user_submission_filepath"]."'>Student Submission</a>&nbsp;".htmlspecialchars($oneGrade["assessment_score"], ENT_QUOTES, 'UTF-8')."</td>";
                                            } else {
                                                echo "<td>".htmlspecialchars($oneGrade["assessment_score"], ENT_QUOTES, 'UTF-8')."</td>";
                                            }
                                        } else {
                                            echo "<td><em>N/A</em></td>";
                                        }
                                    } else {
                                        // No grade record exists.
                                        echo "<td><em>N/A</em></td>";
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
        <?php
        if ($user_type < 2) {
            // User is an instructor.
            echo '<a href="course_edit.php?course_id=' . $course_id . '">EDIT COURSE</a>';
            echo '<a href="assessment_create.php?course_id=' . $course_id . '">CREATE ASSESSMENT</a>';
            echo '<a href="assessment_view.php?course_id=' . $course_id . '">VIEW/EDIT ASSESSMENTS</a>';
            echo '<a href="section_edit.php?course_id=' . $course_id . '">EDIT COURSE CONTENT</a>';
            echo '<a href="gradebook.php?course_id=' . $course_id . '">GRADEBOOK</a>';
        } else {
            // User is a student.
            echo '<a href="gradebook.php?course_id=' . $course_id . '">GRADEBOOK</a>';
        }
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

