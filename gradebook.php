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
            $pullCourseName = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
            $pullCourseName->execute([$course_id]);
            while ($oneCourse = $pullCourseName->fetch(PDO::FETCH_ASSOC)) {
                echo "<center>".$oneCourse["course_num"]." ".$oneCourse["course_name"]."</center>";
            }
            ?>
        </h1>
        <!-- Gradebook section. -->
        <section class="gradebook">
            <h2>Course Assessments</h2>
            <div class="gradebook-container">
                <table id="gradebook-table">
                    <tr>
                        <th onclick="sortTable(0)">Assessment Name</th>
                        <th onclick="sortTable(1)">Assessment Type</th>
                        <th onclick="sortTable(2)">Due Date</th>
                        <th onclick="sortTable(3)">Student Records</th>
                    </tr>
                    <?php
                        try {
                            // Pull all assessments associated with the course ID.
                            $assessmentQuery = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `course_id` = ?");
                            $assessmentQuery->execute([$course_id]);
                            if ($assessmentQuery->rowCount() < 1) {
                                echo "<tr><td colspan='4'><i><b>No upcoming assessments at this time.</b></i></td></tr>";
                            } else {
                                while ($oneAssessment = $assessmentQuery->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>".$oneAssessment["assessment_description"]."</td>";
                                    // Pull assessment type description to match each respective assessment.
                                    $assessmentTypeQuery = $conn->prepare("SELECT * FROM `ASSESSMENT_TYPE` WHERE `assessment_type_id` = ?");
                                    $assessmentTypeQuery->execute([$oneAssessment["assessment_type"]]);
                                    while ($oneAssessmentType = $assessmentTypeQuery->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<td>".$oneAssessmentType["type_description"]."</td>";
                                    }
                                    // Pull respective assessment due date.
                                    echo "<td>".$oneAssessment["due_date"]."</td>";
                                    // Pull total number of records from USER_ASSESSMENT for each assessment ID.
                                    $userAssessmentQuery = $conn->prepare("SELECT * FROM `USER_ASSESSMENT` WHERE `assessment_id` = ?");
                                    $userAssessmentQuery->execute([$oneAssessment["assessment_id"]]);
                                    $numRows = $userAssessmentQuery->rowCount();
                                    if ($numRows >= 1) {
                                        echo "<td>";
                                        echo $numRows;
                                        echo "&nbsp;<form action='gradebook_view.php?assessment_id=".$oneAssessment["assessment_id"]."' method='post' style='display: inline; padding: 5px;'>";
                                        echo "<input type='hidden' name='course_id' value='".$course_id."'></input>";
                                        echo "<input type='submit' name='submit' value=' View Records '></input>";
                                        echo "</form>";
                                        echo "</td>";
                                    } else {
                                        echo "<td><em>No student records available.</em></td>";
                                    }
                                    echo "</tr>";
                                }
                            }
                        } catch (PDOException $e) {
                            echo "ERROR: Could not pull records from USER_ASSESSMENT for gradebook table. ".$e->getMessage();
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

<script src="gradebook.js"></script>
</body>
</html>

