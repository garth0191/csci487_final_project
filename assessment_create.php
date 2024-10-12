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
    if ((isset($_POST["assessment_name"]) && $_POST["assessment_name"] !== "") && (isset($_POST["assessment_type"]) && $_POST["assessment_type"] !== "") && (isset($_POST["score_type"]) && $_POST["score_type"] !== "") && (isset($_POST["due_date"]) && $_POST["due_date"])){
        // Check if an assessment already exists with submitted name.
        $nameCheck = $conn->prepare("SELECT * FROM COURSE WHERE `assessment_description` = ?");
        $nameCheck->execute([$_POST["assessment_name"]]);
        if ($nameCheck->rowCount() >= 1) {
            $error = true;
            $message = "The submitted assessment name is already in use. Please choose another name for your assessment.";
        } else {
            try {
                if (isset($_POST["points_possible"]) && $_POST["points_possible"] !== "") {
                    $newAssignment = $conn->prepare("INSERT INTO ASSESSMENT (course_id, assessment_description, assessment_type, points_possible, due_date) VALUES (?, ?, ?, ?, ?)");
                    $newAssignment->execute([$course_id, $_POST["assessment_name"], $_POST["assessment_type"], $_POST["score_type"], $_POST["points_possible"], $_POST["due_date"]]);
                } else {
                    $newAssignment = $conn->prepare("INSERT INTO ASSESSMENT (course_id, assessment_description, assessment_type, due_date) VALUES (?, ?, ?, ?)");
                    $newAssignment->execute([$course_id, $_POST["assessment_name"], $_POST["assessment_type"], $_POST["score_type"], $_POST["due_date"]]);
                }
            } catch (PDOException $e) {
                echo "ERROR: Could not create new assignment. ".$e->getMessage();
            }

            // Grab ID for newly created assessment.
            $grabID = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `assessment_description` = ?");
            $grabID->execute([$_POST["assessment_name"]]);
            while ($oneID = $grabID->fetch(PDO::FETCH_ASSOC)) {
                $assessment_id = $oneID["assessment_id"];

                // Add submitted grades to database.
                if ($_POST["score_type"] === 0) {
                    // Score type will be PASS/FAIL.
                    try {
                        $newGrade = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, grade_description, result) VALUES (?, ?, ?, ?)");
                        $newGrade->execute([$_POST["score_type"], $assessment_id, "Pass", 1]);
                        $newGrade2 = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, grade_description, result) VALUES (?, ?, ?, ?)");
                        $newGrade2->execute([$_POST["score_type"], $assessment_id, "Fail", 0]);
                    } catch (PDOException $e) {
                        echo "ERROR: Could not add PASS/FAIL grades to database. ".$e->getMessage();
                    }
                } else {
                    // Score type will be PERCENTILE.
                    try {
                        // Check if +/- scores will be used.
                        if ($_POST["plus-minus"] === "false") {
                            $a = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $a->execute([$_POST["score_type"], $assessment_id, NULL, $_POST["a-threshold"], "A", 1]);
                            $b = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $b->execute([$_POST["score_type"], $assessment_id, ($_POST["a-threshold"]-1), $_POST["b-threshold"], "B", 1]);
                            $c = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $c->execute([$_POST["score_type"], $assessment_id, ($_POST["b-threshold"]-1), $_POST["c-threshold"], "C", 1]);
                            $d = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $d->execute([$_POST["score_type"], $assessment_id, ($_POST["c-threshold"]-1), $_POST["d-threshold"], "D", 1]);
                            $f = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $f->execute([$_POST["score_type"], $assessment_id, ($_POST["d-threshold"]-1), NULL, "F", 0]);
                        } else {
                            $a = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $a->execute([$_POST["score_type"], $assessment_id, NULL, $_POST["a-threshold"], "A", 1]);
                            $aMinus = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $aMinus->execute([$_POST["score_type"], $assessment_id, ($_POST["a-threshold"]-1), $_POST["a-minus-threshold"], "A-", 1]);

                            $bPlus = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $bPlus->execute([$_POST["score_type"], $assessment_id, ($_POST["a-minus-threshold"]-1), $_POST["b-plus-threshold"], "B+", 1]);
                            $b = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $b->execute([$_POST["score_type"], $assessment_id, ($_POST["b-plus-threshold"]-1), $_POST["b-threshold"], "B", 1]);
                            $bMinus = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $bMinus->execute([$_POST["score_type"], $assessment_id, ($_POST["b-threshold"]-1), $_POST["b-minus-threshold"], "B-", 1]);

                            $cPlus = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $cPlus->execute([$_POST["score_type"], $assessment_id, ($_POST["b-minus-threshold"]-1), $_POST["c-plus-threshold"], "C+", 1]);
                            $c = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $c->execute([$_POST["score_type"], $assessment_id, ($_POST["c-plus-threshold"]-1), $_POST["c-threshold"], "C", 1]);
                            $cMinus = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $cMinus->execute([$_POST["score_type"], $assessment_id, ($_POST["c-threshold"]-1), $_POST["c-minus-threshold"], "C-", 1]);

                            $d = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $d->execute([$_POST["score_type"], $assessment_id, ($_POST["c-minus-threshold"]-1), $_POST["d-threshold"], "D", 1]);

                            $f = $conn->prepare("INSERT INTO GRADE (score_type, assessment_id, upper_limit, lower_limit, grade_description, result) VALUES (?, ?, ?, ?, ?, ?)");
                            $f->execute([$_POST["score_type"], $assessment_id, ($_POST["d-threshold"]-1), NULL, "F", 0]);
                        }
                    } catch (PDOException $e) {
                        echo "ERROR: Could not add PERCENTILE grades to database. ".$e->getMessage();
                    }
                }
                // Create USER_ASSESSMENT bridge records for all students in the course.
                try {
                    $pullStudentRecords = $conn->preapre("SELECT * FROM USER_COURSE WHERE `course_id` = ?");
                    $pullStudentRecords->execute([$course_id]);
                    while ($oneStudent = $pullStudentRecords->fetch(PDO::FETCH_ASSOC)) {
                        $student_id = $oneStudent["user_id"];
                        $newRecord = $conn->prepare("INSERT INTO USER_ASSESSMENT (user_id, course_id, assessment_id) VALUES (?, ?, ?)");
                        $newRecord->execute([$student_id, $course_id, $assessment_id]);
                    }
                } catch (PDOException $e) {
                    echo "ERROR: Could not pull student records from USER_COURSE table. ".$e->getMessage();
                }
                // Redirect user to course page.
                try {
                    header("Location: course.php?course_id=$course_id");
                } catch (PDOException $e) {
                    echo "ERROR: Could not redirect user to course page after adding assessment. ".$e->getMessage();
                }
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
        <section class="assessment-create">
            <h2>New Course Details</h2>
            <center><?php if($error) {echo "<div class='error'>".$message."</div>";} ?></center>
            <div class="assessment-create-container">
                <form action='assessment_create.php?course_id=<?php echo $course_id; ?>' method='post'>
                    Assessment Name: <input type="text" id="assessment_name" name="assessment_name" style="width: 20%;" required><br>
                    Due Date: <input type="date" id="due_date" name="due_date" min="1900-01-01" max="2999-01-01" required><br>
                    Assessment Type:
                    <?php
                        try {
                            $typeQuery = $conn->prepare("SELECT * FROM ASSESSMENT_TYPE");
                            $typeQuery->execute();
                            echo "<select name='assessment_type' required>";
                            echo '<option style="display:none"></option>';
                            while ($allTypes = $typeQuery->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option name='assessment_type' value='".$allTypes["assessment_type_id"]."'>".$allTypes["type_description"]."</option>";
                            }
                            echo "</select>";
                        } catch (PDOException $e) {
                            echo "ERROR: Could not pull assessment types. ".$e->getMessage();
                        }
                    ?>
                    Score Type:
                    <?php
                    try {
                        $scoreQuery = $conn->prepare("SELECT * FROM SCORE_TYPE");
                        $scoreQuery->execute();
                        echo "<select id='score_type' name='score_type' required>";
                        echo '<option style="display:none"></option>';
                        while ($allScores = $scoreQuery->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option name='score_type' value='".$allScores["score_id"]."'>".$allScores["score_description"]."</option>";
                        }
                        echo "</select>";
                    } catch (PDOException $e) {
                        echo "ERROR: Could not pull score types. ".$e->getMessage();
                    }
                    ?>

                    <div class="invisible percentage-points-selection" name="points-possible-selection">

                        Points Possible: <input type="number" id="1" name="points-possible"></input>

                        <br><br>
                        <h2>Grade Assignments</h2><br>
                        <fieldset>
                            <legend>Will your assessment use +/- scoring?</legend>
                            <div>
                                <input type="radio" id="true" name="plus-minus" value="pm-true" />
                                <label for="true">Yes</label>&nbsp;&nbsp;&nbsp;
                                <input type="radio" id="false" name="plus-minus" value="pm-false" />
                                <label for="false">No</label>
                            </div>
                        </fieldset>

                        <!-- Without +/- scoring options. -->
                        <div class="pm-false" name="pm-false" id="pm-false">
                            <table>
                                <tr>
                                    <th>Letter Grade</th>
                                    <th>Lower Threshold</th>
                                </tr>
                                <tr>
                                    <td>A</td>
                                    <td><input type="number" id="a-threshold" name="a-threshold"></td>
                                </tr>
                                <tr>
                                    <td>B</td>
                                    <td><input type="number" id="b-threshold" name="b-threshold"></td>
                                </tr>
                                <tr>
                                    <td>C</td>
                                    <td><input type="number" id="c-threshold" name="c-threshold"></td>
                                </tr>
                                <tr>
                                    <td>D</td>
                                    <td><input type="number" id="d-threshold" name="d-threshold"></td>
                                </tr>
                            </table>
                        </div>

                        <!-- With +/- scoring options. -->
                        <div class="pm-true" name="pm-true" id="pm-true">
                            <table>
                                <tr>
                                    <th>Letter Grade</th>
                                    <th>Lower Threshold</th>
                                </tr>
                                <tr>
                                    <td>A</td>
                                    <td><input type="number" id="a-threshold" name="a-threshold"></td>
                                </tr>
                                <tr>
                                    <td>A-</td>
                                    <td><input type="number" id="a-minus-threshold" name="a-minus-threshold"></td>
                                </tr>
                                <tr>
                                    <td>B+</td>
                                    <td><input type="number" id="b-plus-threshold" name="b-plus-threshold"></td>
                                </tr>
                                <tr>
                                    <td>B</td>
                                    <td><input type="number" id="b-threshold" name="b-threshold"></td>
                                </tr>
                                <tr>
                                    <td>B-</td>
                                    <td><input type="number" id="b-minus-threshold" name="b-minus-threshold"></td>
                                </tr>
                                <tr>
                                    <td>C+</td>
                                    <td><input type="number" id="c-plus-threshold" name="c-plus-threshold"></td>
                                </tr>
                                <tr>
                                    <td>C</td>
                                    <td><input type="number" id="c-threshold" name="c-threshold"></td>
                                </tr>
                                <tr>
                                    <td>C-</td>
                                    <td><input type="number" id="c-minus-threshold" name="c-minus-threshold"></td>
                                </tr>
                                <tr>
                                    <td>D</td>
                                    <td><input type="number" id="d-threshold" name="d-threshold"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <input type="submit" name="submit" value="Submit">
                </form>
            </div>
        </section>
    </div>

    <!-- Sidebar. -->
    <div class="sidebar">
        <!-- Course edit options, etc., will go here. -->
        <a href="course_edit.php?course_id=<?php echo $course_id; ?>">EDIT COURSE</a>
        <a href="assessment_create.php?course_id=<?php echo $course_id; ?>">CREATE ASSESSMENT</a>
        <a href="assessment_edit.php?course_id=<?php echo $course_id; ?>">EDIT ASSESSMENTS</a>
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

<script src="assessment_create.js"></script>
</body>
</html>
