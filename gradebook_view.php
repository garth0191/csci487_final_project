<?php
    session_start();
    require '/home/gnmcclur/connections/connect.php';

    if(!isset($_SESSION['user_id'])){
        header('Location: index.php');
    }

    $user_id = $_SESSION['user_id'];
    $currentTime = new DateTime();

    // Grab assessment ID that has been passed to this page.
    if (isset($_GET["assessment_id"]) && $_GET["assessment_id"] !== "") {
        $assessment_id = $_GET["assessment_id"];
    }

    // Grab assessment due date.
    try {
        $dueDateQuery = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `assessment_id` = ?");
        $dueDateQuery->execute([$assessment_id]);
        while ($oneDate = $dueDateQuery->fetch(PDO::FETCH_ASSOC)) {
            $assessment_due_date = new DateTime($oneDate["due_date"]);
        }
    } catch (PDOException $e) {
        echo "ERROR: Could not pull assessment due date. ".$e->getMessage();
    }

    // Grab course ID that has been passed to this page.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ((isset($_POST["course_id"]) && $_POST["course_id"] !== "")) {
            $course_id = $_POST["course_id"];
        }
    }

    // Check for new grade submission.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["new_grade"]) && $_POST["new_grade"] !== "") {
            try {
                $gradeUpdate = $conn->prepare("UPDATE USER_ASSESSMENT SET `assessment_score` = ? WHERE `user_assessment_id` = ?");
                $gradeUpdate->execute([$_POST["new_grade"], $_POST["user_assessment_id"]]);
            } catch (PDOException $e) {
                echo "ERROR: Could not update student assessment grade. ".$e->getMessage();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gradebook View Page</title>
    <link rel="stylesheet" href="gradebook_view.css">
</head>

<body>
<!-- Nav bar at top of page. -->
<nav class="navbar">
    <!-- Will appear on left side of nav bar. -->
    <div class="navbar-buttons">
        <div class="button home" id="home-button">Home</div>
        <div class="button create" id="create-button">Create Course</div>
        <div class="button account" id="account-button">Account Options</div>
        <div class="button logout" id="logout-button">Logout</div>
    </div>
    <!-- Will appear on right side of nav bar. -->
    <div class="navbar-logo">
        <img src="./images/logo.png" height="35%" alt="CourseCanvas logo">
    </div>
</nav>

<div class="container">
    <div class="main-section">
        <h1>
            <?php
                $pullAssessmentName = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `assessment_id` = ?");
                $pullAssessmentName->execute([$assessment_id]);
                while ($oneAssessment = $pullAssessmentName->fetch(PDO::FETCH_ASSOC)) {
                    echo $oneAssessment["assessment_description"]."<br>";
                }
            ?>
        </h1>
        <section class="user-assessment-list">
            <h2>Assessment Student Records</h2>
            <div class="assessment-list-container">
                <table id="user-assessment-table">
                    <tr>
                        <th onclick="sortTable(0)">Last Name</th>
                        <th onclick="sortTable(1)">First Name</th>
                        <th onclick="sortTable(2)">File Submission</th>
                        <th onclick="sortTable(3)">Score</th>
                        <th></th>
                    </tr>
                    <?php
                        // Pull USER_ASSESSMENT records for each student.
                        try {
                            $studentRecords = $conn->prepare("SELECT * FROM USER_ASSESSMENT WHERE `assessment_id` = ?");
                            $studentRecords->execute([$assessment_id]);
                            while ($oneRecord = $studentRecords->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                // Pull USER records for each student.
                                $studentName = $conn->prepare("SELECT * FROM USER WHERE `user_id` = ?");
                                $studentName->execute([$oneRecord["user_id"]]);
                                while ($oneStudent = $studentName->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<td>".$oneStudent["last_name"]."</td>";
                                    echo "<td>".$oneStudent["first_name"]."</td>";
                                }
                                if ($oneRecord["user_submission_filepath"] !== NULL) {
                                    echo "<td><a href='".$oneRecord["user_submission_filepath"]."'>View Submission</a></td>";
                                } else {
                                    echo "<td><em>No submission available.</em></td>";
                                }
                                if ($oneRecord["assessment_score"] !== NULL) {
                                    echo "<td>".$oneRecord["assessment_score"]."</td>";
                                } else {
                                    echo "<td><em>Not yet graded.</em></td>";
                                }
                                echo "<td>";
                                echo "<form action='gradebook_view.php?assessment_id=".$assessment_id."' method='post' id='grade-edit' name='grade-edit'>";
                                echo "<input type='number' name='new_grade' id='new_grade'>";
                                echo "<input type='hidden' name='course_id' value='".$course_id."'>";
                                echo "<input type='hidden' name='user_assessment_id' value='".$oneRecord["user_assessment_id"]."'>";
                                // If due date has NOT passed, make sure that alert is presented.
                                if ($assessment_due_date > $currentTime) {
                                    echo "<input type='submit' name='submit' value='Submit Grade' onclick='confirmGrade(event)'>";
                                } else {
                                    echo "<input type='submit' name='submit' value='Submit Grade'>";
                                }
                                echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } catch (PDOException $e) {
                            echo "ERROR: Could not pull student assessment records. ".$e->getMessage();
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
        <a href="section_edit.php?course_id=<?php echo $course_id; ?>">EDIT COURSE SECTIONS</a>
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

<script src="gradebook_view.js"></script>
</body>
</html>

