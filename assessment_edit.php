<?php
    session_start();
    require '/home/gnmcclur/connections/connect.php';
    
    if(!isset($_SESSION['user_id'])){
        header('Location: index.php');
    }

    if (isset($_GET["assessment_id"]) && $_GET["assessment_id"] !== "") {
        $assessment_id = $_GET["assessment_id"];
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ((isset($_POST["course_id"]) && $_POST["course_id"] !== "")) {
            $course_id = $_POST["course_id"];
        }
    }
    
    $user_id = $_SESSION['user_id'];
    
    $message = "";
    $error = false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Edit Page</title>
    <link rel="stylesheet" href="assessment_edit.css">
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
        <section class="current-assessment-details">
            <h2>Assessment Details</h2>
            <table>
                <?php
                    try {
                        $assessmentDetails = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `assessment_id` = ?");
                        $assessmentDetails->execute([$assessment_id]);
                        while ($oneAssessment = $assessmentDetails->fetch(PDO::FETCH_ASSOC)) {
                            $assessment_name = $oneAssessment["assessment_description"];
                            $assessment_type = $oneAssessment["assessment_type"];
                            $points_possible = $oneAssessment["points_possible"];

                        }
                    } catch (PDOException $e) {
                        echo "ERROR: Could not pull assessment data from database. ".$e->getMessage();
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
        <a href="section_edit.php?course_id=<?php echo $course_id; ?>">EDIT COURSE SECTIONS</a>
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

<script src="assessment_edit.js"></script>
</body>
</html>
