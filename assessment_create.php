<?php
session_start();
require '/home/gnmcclur/connections/connect.php';

if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
}

// Grab course ID that has been passed to this page.
if (isset($_GET["course_id"]) && $_GET["course_id"] !== "") {
    $course_id = $_GET["course_id"];
}

$user_id = $_SESSION['user_id'];

$message = "";
$error = false;

// Create a new assessment.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new assessment to database.
    if ((isset($_POST["assessment_name"]) && $_POST["assessment_name"] !== "") && (isset($_POST["assessment_type"]) && $_POST["assessment_type"] !== "") && (isset($_POST["due_date"]) && $_POST["due_date"] !== "") &&
        (isset($_POST["submissions"]) && $_POST["submissions"] !== "")){
        // Check if an assessment already exists with submitted name.
        $nameCheck = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `assessment_description` = ? AND `course_id` = ?");
        $nameCheck->execute([$_POST["assessment_name"], $course_id]);
        if ($nameCheck->rowCount() >= 1) {
            $error = true;
            $message = "The submitted assessment name is already in use. Please choose another name for your assessment.";
        } else {
            try {
                $due_date = date('Y-m-d', strtotime($_POST["due_date"]));
                if (isset($_POST["points-possible"]) && $_POST["points-possible"] !== "") {
                    $newAssignment = $conn->prepare("INSERT INTO ASSESSMENT (course_id, assessment_description, assessment_type, points_possible, due_date, has_submissions) VALUES (?, ?, ?, ?, ?, ?)");
                    $newAssignment->execute([$course_id, $_POST["assessment_name"], $_POST["assessment_type"], $_POST["points-possible"], $due_date, $_POST["submissions"]]);
                } else {
                    $newAssignment = $conn->prepare("INSERT INTO ASSESSMENT (course_id, assessment_description, assessment_type, due_date, has_submissions) VALUES (?, ?, ?, ?, ?)");
                    $newAssignment->execute([$course_id, $_POST["assessment_name"], $_POST["assessment_type"], $due_date, $_POST["submissions"]]);
                }
            } catch (PDOException $e) {
                echo "ERROR: Could not create new assignment. ".$e->getMessage();
            }

            // Grab ID for newly created assessment.
            $grabID = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `assessment_description` = ?");
            $grabID->execute([$_POST["assessment_name"]]);
            while ($oneID = $grabID->fetch(PDO::FETCH_ASSOC)) {
                $assessment_id = $oneID["assessment_id"];

                // Create USER_ASSESSMENT bridge records for all students in the course.
                try {
                    $pullStudentRecords = $conn->prepare("SELECT * FROM USER_COURSE WHERE `course_id` = ?");
                    $pullStudentRecords->execute([$course_id]);
                    while ($oneStudent = $pullStudentRecords->fetch(PDO::FETCH_ASSOC)) {
                        $student_id = $oneStudent["user_id"];
                        $newRecord = $conn->prepare("INSERT INTO USER_ASSESSMENT (user_id, course_id, assessment_id) VALUES (?, ?, ?)");
                        $newRecord->execute([$student_id, $course_id, $assessment_id]);
                    }
                } catch (PDOException $e) {
                    echo "ERROR: Could not pull student records from USER_COURSE table. ".$e->getMessage();
                }
            }
            // Redirect user to course page.
            try {
                header("Location: course.php?course_id=$course_id");
            } catch (PDOException $e) {
                echo "ERROR: Could not redirect user to course page after adding assessment. ".$e->getMessage();
            }
        }
    } else {
        $error = true;
        $message = "Required fields cannot be blank.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Creation Page</title>
    <link rel="stylesheet" href="assessment_create.css">
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
        <section class="assessment-create">
            <h2>New Assessment Details</h2><br>
            <center><?php if($error) {echo "<div class='error'>".$message."</div>";} ?></center>
            <div class="assessment-create-container">
                <form action='assessment_create.php?course_id=<?php echo $course_id; ?>' method='post'>
                    Assessment Name: <input type="text" id="assessment_name" name="assessment_name" style="width: 20%;" required><br>
                    Due Date: <input type="date" id="due_date" name="due_date" min="<?php echo date('Y-m-d'); ?>" max="2999-01-01" required><br>
                    Assessment Type:
                    <?php
                        try {
                            $typeQuery = $conn->prepare("SELECT * FROM ASSESSMENT_TYPE");
                            $typeQuery->execute();
                            echo "<select name='assessment_type' required>";
                            echo '<option style="display:none"></option>';
                            while ($allTypes = $typeQuery->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option name='assessment_type' value='".$allTypes["type_description"]."'>".$allTypes["type_description"]."</option>";
                            }
                            echo "</select>";
                        } catch (PDOException $e) {
                            echo "ERROR: Could not pull assessment types. ".$e->getMessage();
                        }
                    ?>
                    Points Possible: <input type="number" name="points-possible" placeholder="Leave blank if PASS/FAIL." style="width: 200px;"></input><br>
                    Will this assessment require user submissions?&nbsp;
                    <select name="submissions">
                        <option style="display:none"></option>
                        <option name="submissions" value="1">Yes</option>
                        <option name="submissions" value="0">No</option>
                    </select>
                    <input type="submit" name="submit" value="Submit">
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

<script src="assessment_create.js"></script>
</body>
</html>
