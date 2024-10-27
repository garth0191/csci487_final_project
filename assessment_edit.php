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

    // Edit assessment details.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Change assessment name.
        if ((isset($_POST["assessment_name"]) && $_POST["assessment_name"] !== "")) {
            try {
                $changeName = $conn->prepare("UPDATE ASSESSMENT SET `assessment_description` = ? WHERE `assessment_id` = ?");
                $changeName->execute([$_POST["assessment_name"], $assessment_id]);
            } catch (PDOException $e) {
                echo "ERROR: Could not change assessment name. ".$e->getMessage();
            }
        }
        // Change assessment type.
        if ((isset($_POST["assessment_type"]) && $_POST["assessment_type"] !== "")) {
            try {
                $changeType = $conn->prepare("UPDATE ASSESSMENT SET `assessment_type` = ? WHERE `assessment_id` = ?");
                $changeType->execute([$_POST["assessment_type"], $assessment_id]);
            } catch (PDOException $e) {
                echo "ERROR: Could not change assessment type. ".$e->getMessage();
            }
        }
        // Change points possible.
        if ((isset($_POST["points_possible"]) && $_POST["points_possible"] !== "")) {
            try {
                $changePoints = $conn->prepare("UPDATE ASSESSMENT SET `points_possible` = ? WHERE `assessment_id` = ?");
                $changePoints->execute([$_POST["points_possible"], $assessment_id]);
            } catch (PDOException $e) {
                echo "ERROR: Could not change points possible. ".$e->getMessage();
            }
        }
        // Change due date.
        if (isset($_POST["due_date"]) && $_POST["due_date"] !== "") {
            try {
                $due_date = date('Y-m-d', strtotime($_POST["due_date"]));
                $changeDate = $conn->prepare("UPDATE ASSESSMENT SET `due_date` = ? WHERE `assessment_id` = ?");
                $changeDate->execute([$due_date, $assessment_id]);
            } catch (PDOException $e) {
                echo "ERROR: Could not change due date. ".$e->getMessage();
            }
        }
    }
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
                            $due_date = $oneAssessment["due_date"];

                            echo "<tr>";
                            echo "<td><strong>Assessment Name</strong></td><td>".$assessment_name."</td>";
                            echo "</tr>";
                            echo "<tr>";
                            $pullAssessmentType = $conn->prepare("SELECT * FROM ASSESSMENT_TYPE WHERE `assessment_type_id` = ?");
                            $pullAssessmentType->execute([$assessment_type]);
                            while ($oneType = $pullAssessmentType->fetch(PDO::FETCH_ASSOC)) {
                                echo "<td><strong>Assessment Type</strong></td><td>".$oneType["type_description"]."</td>";
                            }
                            echo "</tr>";
                            echo "<tr>";
                            echo "<td><strong>Points Possible</strong></td><td>".$points_possible."</td>";
                            echo "</tr>";
                            echo "<tr>";
                            echo "<td><strong>Due Date</strong></td><td>".$due_date."</td>";
                            echo "</tr>";
                        }
                    } catch (PDOException $e) {
                        echo "ERROR: Could not pull assessment data from database. ".$e->getMessage();
                    }
                ?>
            </table>
        </section>

        <section class="edit-assessment-details">
            <br><br><br><h2>Edit Assessment Details</h2>
            <div class="edit-assessment-details-container">
                <form action='assessment_edit.php?assessment_id=<?php echo $assessment_id; ?>' method='post'>
                    Assessment Name: <input type="text" id="assessment_name" name="assessment_name" style="width: 20%;" placeholder="<?php echo $assessment_name; ?>"></input><br>
                    Assessment Type:
                    <?php
                    $allTypes = $conn->prepare("SELECT * FROM ASSESSMENT_TYPE");
                    $allTypes->execute();
                    echo "<select name='assessment_type'>";
                    echo '<option style="display:none"></option>';
                    while ($oneRow = $allTypes->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option name='assessment_type' value='".$oneRow["assessment_type_id"]."'>".$oneRow["type_description"]."</option>";
                    }
                    echo "</select>";
                    ?>
                    <br>
                    Points Possible: <input type="number" id="points_possible" name="points_possible"><br>
                    Due Date: <input type="date" id="due_date" name="due_date" min="<?php echo date('Y-m-d'); ?>" max="2999-01-01"> <br>
                    <input type="submit" name="submit" value="Confirm Changes">
                </form>
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

<script src="assessment_edit.js"></script>
</body>
</html>
