<?php
session_start();
require '/home/gnmcclur/connections/connect.php';

if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
}

$currentTime = new DateTime();
$assessmentCounter = 0;
$assessmentsReadyToGrade = 0;
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
                        <!-- Grab all pending assessment items for course. 
                         NOTE: They should only be listed if the current date is not past the assessment's due date. -->
                        <?php
                            try {
                                $assessmentGrab = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `course_id` = ?");
                                $assessmentGrab->execute([$course_id]);

                                if ($assessmentGrab->rowCount() < 1) {
                                    echo "<tr><td colspan='3'><i><b>No upcoming assessments at this time.</b></i></td></tr>";
                                } else {
                                    while ($oneAssessment = $assessmentGrab->fetch(PDO::FETCH_ASSOC)) {
                                        $assessmentDueDate = new DateTime($oneAssessment["due_date"]);
                                        if ($assessmentDueDate > $currentTime) {
                                            echo "<tr>";
                                            echo "<td>".$oneAssessment["assessment_description"]."</td>";

                                            //Grab assessment types.
                                            $typeStmt = $conn->prepare("SELECT * FROM ASSESSMENT_TYPE WHERE `assessment_type_id` = ?");
                                            $typeStmt->execute([$oneAssessment["assessment_type"]]);
                                            $assessmentType = $typeStmt->fetch(PDO::FETCH_ASSOC);
                                            echo "<td>".$assessmentType["type_description"]."</td>";

                                            echo "<td>".$oneAssessment["due_date"]."</td>";
                                            echo "</tr>";
                                        }
                                    }
                                }
                            } catch (PDOException $e) {
                                echo "ERROR: Could not grab assessment data.\n".$e->getMessage();
                            }
                        ?>
                </table>
            </section>

            <!-- Section for assessments ready to be graded. -->
            <section>
                <table>
                    <tr>
                        <th>Assessment Name</th>
                        <th>Assessment Type</th>
                        <th>Total Items</th>
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

                                            //Grab assessment types.
                                            $typeStmt2 = $conn->prepare("SELECT * FROM ASSESSMENT_TYPE WHERE `assessment_type_id` = ?");
                                            $typeStmt2->execute([$oneItem["assessment_type"]]);
                                            $assessmentType2 = $typeStmt2->fetch(PDO::FETCH_ASSOC);
                                            echo "<td>".$assessmentType2["type_description"]."</td>";

                                            echo "<td>".$assessmentsReadyToGrade."</td>";
                                            echo "</tr>";
                                        }
                                    }
                                }

                                if ($assessmentCounter < 1) {
                                    echo "<tr><td colspan='3'><i><b>No assessments require grading at this time.</b></i></td></tr>";
                                }
                            }
                        } catch (PDOException $e) {
                            echo "ERROR: Could not grab data for items ready to be graded.\n".$e->getMessage();
                        }
                    ?>
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
