<?php
session_start();
require '/home/gnmcclur/connections/connect.php';

if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
}

$currentTime = new DateTime();
$assessmentCounter = 0;
$assessmentsReadyToGrade = 0;
$hasAssessments = false;
$user_id = $_SESSION['user_id'];

// Grab course ID that has been passed to this page.
if (isset($_GET["course_id"]) && $_GET["course_id"] !== "") {
    $course_id = $_GET["course_id"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Page</title>
    <link rel="stylesheet" href="course.css">
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
            <!-- Upcoming assessments section. -->
            <section class="upcoming">
            <h3>Upcoming Assessments</h3>
                <table id="upcoming-assessments-table">
                    <tr>
                        <th onclick="sortTable(0, 'upcoming-assessments-table')">Assessment Name
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                        <th onclick="sortTable(1, 'upcoming-assessments-table')">Assessment Type
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                        <th onclick="sortTable(2, 'upcoming-assessments-table')">Due Date
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                    </tr>
                        <!-- Grab all pending assessment items for course. 
                         NOTE: They should only be listed if the current date is not past the assessment's due date. -->
                        <?php
                            try {
                                $assessmentGrab = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `course_id` = ?");
                                $assessmentGrab->execute([$course_id]);

                                if ($assessmentGrab->rowCount() < 1) {
                                    echo "<tr><td colspan='3'><i><b>No upcoming assessments at this time.</b></i></td></tr>";
                                } else {
                                    $hasAssessments = true;
                                    while ($oneAssessment = $assessmentGrab->fetch(PDO::FETCH_ASSOC)) {
                                        $assessmentDueDate = new DateTime($oneAssessment["due_date"]);
                                        if ($assessmentDueDate > $currentTime) {
                                            $assessmentCounter++;
                                            echo "<tr>";
                                            echo "<td>".$oneAssessment["assessment_description"]."</td>";
                                            echo "<td>".$oneAssessment["assessment_type"]."</td>";
                                            echo "<td>".$oneAssessment["due_date"]."</td>";
                                            echo "</tr>";
                                        }
                                    }
                                }

                                if (($assessmentCounter < 1) && $hasAssessments) {
                                    echo "<tr><td colspan='3'><i><b>No upcoming assessments at this time.</b></i></td></tr>";
                                }
                                $assessmentCounter = 0;
                            } catch (PDOException $e) {
                                echo "ERROR: Could not grab assessment data.\n".$e->getMessage();
                            }
                        ?>
                </table>
            </section>

            <!-- Section for assessments ready to be graded. -->
            <section class="ready-to-grade">
            <h3>Items Ready to Grade</h3>
                <table id="ready-to-grade-table">
                    <tr>
                        <th onclick="sortTable(0, 'ready-to-grade-table')">Assessment Name
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                        <th onclick="sortTable(1, 'ready-to-grade-table')">Assessment Type
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                        <th onclick="sortTable(2, 'ready-to-grade-table')">Total Items
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                    </tr>
                    <?php
                        try {
                            $readyToGrade = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `course_id` = ?");
                            $readyToGrade->execute([$course_id]);
                            if ($readyToGrade->rowCount() < 1) {
                                echo "<tr><td colspan='3'><i><b>No assessments require grading at this time.</b></i></td></tr>";
                            } else {
                                while ($oneItem = $readyToGrade->fetch(PDO::FETCH_ASSOC)) {
                                    $dueDate = new DateTime($oneItem["due_date"]);
                                    if ($dueDate <= $currentTime) {
                                        // Check to see if there are any USER_ASSESSMENT items to grade for
                                        // each ASSESSMENT item.
                                        $pullAssessments = $conn->prepare("SELECT * FROM USER_ASSESSMENT WHERE `course_id` = ? AND `assessment_id` = ?");
                                        $pullAssessments->execute([$course_id, $oneItem["assessment_id"]]);
                                        if ($pullAssessments->rowCount() >= 1) {
                                            $assessmentCounter++;
                                            $assessmentsReadyToGrade = $pullAssessments->rowCount();
                                            echo "<tr>";
                                            echo "<td>".$oneItem["assessment_description"]."</td>";

                                            echo "<td>".$oneItem["assessment_type"]."</td>";

                                            echo "<td>".$assessmentsReadyToGrade."</td>";
                                            echo "</tr>";
                                        }
                                    }
                                }

                                if ($assessmentCounter < 1) {
                                    echo "<tr><td colspan='3'><i><b>No assessments require grading at this time.</b></i></td></tr>";
                                }
                            }
                            $assessmentCounter = 0;
                        } catch (PDOException $e) {
                            echo "ERROR: Could not grab data for items ready to be graded.\n".$e->getMessage();
                        }
                    ?>
                </table>
            </section>

            <!-- Course roster of all registered students. -->
            <section class="student-roster">
                <h3>Course Roster</h3>
                <table id="student-roster-table">
                    <tr>
                        <th onclick="sortTable(0, 'student-roster-table')">Last Name
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                        <th onclick="sortTable(1, 'student-roster-table')">First Name
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                        <th onclick="sortTable(2, 'student-roster-table')">Contact E-Mail
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                    </tr>
                        <?php
                            $rosterQuery = $conn->prepare("SELECT * FROM USER_COURSE WHERE `course_id` = ?");
                            $rosterQuery->execute([$course_id]);
                            if ($rosterQuery->rowCount() < 1) {
                                echo "<tr><td colspan='3'><i><b>No students currently registered in course.</b></i></td></tr>";
                            } else {
                                while ($oneStudent = $rosterQuery->fetch(PDO::FETCH_ASSOC)) {
                                    try {
                                        echo "<tr>";
                                        $userQuery = $conn->prepare("SELECT * FROM USER WHERE `user_id` = ?");
                                        $userQuery->execute([$oneStudent["user_id"]]);
                                        while ($userRow = $userQuery->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<td>".$userRow["last_name"]."</td>";
                                            echo "<td>".$userRow["first_name"]."</td>";
                                            echo "<td>".$userRow["user_email"]."</td>";
                                        }
                                        echo "</tr>";
                                    } catch (PDOException $e) {
                                        echo "ERROR: Could not pull student data from database. ".$e->getMessage();
                                    }
                                }
                            }
                        ?>
                </table>
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

<script src="course.js"></script>
</body>
</html>
