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

//Create a new assessment.

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
            <div class="assessment-create-container">
                <form action='assessment_create.php?course_id=<?php echo $course_id; ?>' method='post'>
                    Assessment Name: <input type="text" id="assessment_name" name="assessment_name" style="width: 20%;"></input><br>
                    Assessment Type:
                    <?php
                        try {
                            $typeQuery = $conn->prepare("SELECT * FROM ASSESSMENT_TYPE");
                            $typeQuery->execute();
                            echo "<select name='assessment_type'>";
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
                        echo "<select name='score_type'>";
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

                        Points Possible: <input type="number" id="points-possible" name="points-possible"></input>

                        <br><br>
                        <h2>Grade Assignments</h2><br>
                        <fieldset>
                            <legend>Will your assessment use +/- scoring?</legend>
                            <div>
                                <input type="radio" id="true" name="plus-minus" value="true" />
                                <label for="true">Yes</label>&nbsp;&nbsp;&nbsp;
                                <input type="radio" id="false" name="plus-minus" value="false" />
                                <label for="false">No</label>
                            </div>
                        </fieldset>

                        <!-- Without +/- scoring options. -->
                        <div class="invisible-grades grade-assignments" name="grade-assignments">
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
                        <div class="invisible-grades grade-assignments-plus-minus" name="grade-assignments-plus-minus">
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
