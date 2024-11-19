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
$user_type = $_SESSION['user_type'];

if ($user_type > 1) {
    header('Location: home.php');
}

// Edit course details.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Change course name.
    if ((isset($_POST["course_name"]) && $_POST["course_name"] !== "")) {
        try {
            $courseNameUpdate = $conn->prepare("UPDATE COURSE SET course_name = ? WHERE course_id = ?");
            $courseNameUpdate->execute([$_POST["course_name"], $course_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not change course name. ".$e->getMessage();
        }
    }

    // Change course description.
    if ((isset($_POST["course_description"]) && $_POST["course_description"] !== "")) {
        try {
            $courseDescUpdate = $conn->prepare("UPDATE COURSE SET course_description = ? WHERE course_id = ?");
            $courseDescUpdate->execute([$_POST["course_description"], $course_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not change course description. ".$e->getMessage();
        }
    }

    // Change instructor name.
    if ((isset($_POST["professor_name"]) && $_POST["professor_name"] !== "")) {
        try {
            $nameUpdate = $conn->prepare("UPDATE COURSE SET professor_name = ? WHERE course_id = ?");
            $nameUpdate->execute([$_POST["professor_name"], $course_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not change instructor name. ".$e->getMessage();
        }
    }

    // Assign or change teaching assistant.
    if ((isset($_POST["new_assistant"]) && $_POST["new_assistant"] !== "")) {
        try {
            //Remove the previous assistant, and change their user type to STUDENT.
            $grabTA = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
            $grabTA->execute([$course_id]);
            while ($row = $grabTA->fetch()) {
                $TA_ID = $row["assistant_id"];
                $changeTA_ID = $conn->prepare("UPDATE USER SET user_type = 3 WHERE `user_id` = ?");
                $changeTA_ID->execute([$TA_ID]);
            }
            $assistantUpdate = $conn->prepare("UPDATE COURSE SET assistant_id = ? WHERE course_id = ?");
            $assistantUpdate->execute([$_POST["new_assistant"], $course_id]);
            $updateUserType = $conn->prepare("UPDATE USER SET user_type = 2 WHERE user_id = ?");
            $updateUserType->execute([$_POST["new_assistant"]]);
        } catch (PDOException $e) {
            echo "ERROR: Could not add TA to course. ".$e->getMessage();
        }
    }

    // Remove teaching assistant from course.
    if ((isset($_POST["assistant_remove"]) && $_POST["assistant_remove"] !== "")) {
        try {
            $removeAssistant = $conn->prepare("UPDATE COURSE SET assistant_id = NULL WHERE course_id = ?");
            $removeAssistant->execute([$course_id]);
            $updateUserType2 = $conn->prepare("UPDATE USER SET user_type = 3 WHERE user_id = ?");
            $updateUserType2->execute([$_POST["assistant_remove"]]);
        } catch (PDOException $e) {
            echo "ERROR: Could not remove TA from course. ".$e->getMessage();
        }
    }

    // Add a student to the course.
    if ((isset($_POST["new_student"]) && $_POST["new_student"] !== "")) {
        try {
            // Create USER_COURSE bridge record.
            $UC_Records = $conn->prepare("INSERT INTO USER_COURSE (user_id, course_id) VALUES (?, ?)");
            $UC_Records->execute([$_POST["new_student"], $course_id]);
            // Create USER_ASSESSMENT bridge records for all course assessments.
            $pullAssessments = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `course_id` = ?");
            $pullAssessments->execute([$course_id]);
            while ($assessmentRow = $pullAssessments->fetch()) {
                $assessment_id = $assessmentRow["assessment_id"];
                $UA_Records = $conn->prepare("INSERT INTO USER_ASSESSMENT(user_id, assessment_id) VALUES (?, ?)");
                $UA_Records->execute([$_POST["new_student"], $assessment_id]);
            }
        } catch (PDOException $e) {
            echo "ERROR: Could not add student to course. ".$e->getMessage();
        }
    }

    // Remove a student from the course.
    if ((isset($_POST["remove_student"]) && $_POST["remove_student"] !== "")) {
        try {
            // Remove USER_COURSE bridge record.
            $removeBridge = $conn->prepare("DELETE FROM USER_COURSE WHERE `course_id` = ? AND `user_id` = ?");
            $removeBridge->execute([$course_id, $_POST["remove_student"]]);
        } catch (PDOException $e) {
            echo "ERROR: Could not add student to course. ".$e->getMessage();
        }
    }

    // Change assessment weights.
    if ((isset($_POST["weight_0"]) && $_POST["weight_0"] !== "")) {
        // Extra Credit
        try {
            $extraCreditWeight = $conn->prepare("UPDATE COURSE_WEIGHT SET weight = ? WHERE course_id = ? AND type_id = 0");
            $extraCreditWeight->execute([$_POST["weight_0"], $course_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not edit Extra Credit weight. ".$e->getMessage();
        }
    }

    if ((isset($_POST["weight_1"]) && $_POST["weight_1"] !== "")) {
        // Attendance
        try {
            $attWeight = $conn->prepare("UPDATE COURSE_WEIGHT SET weight = ? WHERE course_id = ? AND type_id = 1");
            $attWeight->execute([$_POST["weight_1"], $course_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not edit Attendance weight. ".$e->getMessage();
        }
    }

    if ((isset($_POST["weight_2"]) && $_POST["weight_2"] !== "")) {
        // Participation
        try {
            $partWeight = $conn->prepare("UPDATE COURSE_WEIGHT SET weight = ? WHERE course_id = ? AND type_id = 2");
            $partWeight->execute([$_POST["weight_2"], $course_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not edit Participation weight. ".$e->getMessage();
        }
    }

    if ((isset($_POST["weight_3"]) && $_POST["weight_3"] !== "")) {
        // Quiz
        try {
            $quizWeight = $conn->prepare("UPDATE COURSE_WEIGHT SET weight = ? WHERE course_id = ? AND type_id = 3");
            $quizWeight->execute([$_POST["weight_3"], $course_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not edit Quiz weight. ".$e->getMessage();
        }
    }

    if ((isset($_POST["weight_4"]) && $_POST["weight_4"] !== "")) {
        // Exam
        try {
            $examWeight = $conn->prepare("UPDATE COURSE_WEIGHT SET weight = ? WHERE course_id = ? AND type_id = 4");
            $examWeight->execute([$_POST["weight_4"], $course_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not edit Exam weight. ".$e->getMessage();
        }
    }

    if ((isset($_POST["weight_5"]) && $_POST["weight_5"] !== "")) {
        // Lab
        try {
            $labWeight = $conn->prepare("UPDATE COURSE_WEIGHT SET weight = ? WHERE course_id = ? AND type_id = 5");
            $labWeight->execute([$_POST["weight_5"], $course_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not edit Lab weight. ".$e->getMessage();
        }
    }

    if ((isset($_POST["weight_6"]) && $_POST["weight_6"] !== "")) {
        // Project
        try {
            $projectWeight = $conn->prepare("UPDATE COURSE_WEIGHT SET weight = ? WHERE course_id = ? AND type_id = 6");
            $projectWeight->execute([$_POST["weight_6"], $course_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not edit Project weight. ".$e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Edit Page</title>
    <link rel="stylesheet" href="course_edit.css">
</head>

<body>
    <!-- Nav bar at top of page. -->
    <nav class="navbar">
        <!-- Will appear on left side of nav bar. -->
        <div class="navbar-buttons">
            <div class="button home" id="home-button">Home</div>
            <?php
            if ($user_type == 0) {
                echo "<div class='button admin' id='admin-button'>Admin Dashboard</div>";
            }
            ?>
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
            <section class="current-course-details">
                <h2>Course Summary</h2>
                    <!-- Display current course details. -->
                <table>
                    <?php
                        try {
                            $courseQuery = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
                            $courseQuery->execute([$course_id]);
                            while ($oneCourse = $courseQuery->fetch(PDO::FETCH_ASSOC)) {
                                $course_num = $oneCourse["course_num"];
                                $course_name = $oneCourse["course_name"];
                                $course_description = $oneCourse["course_description"];
                                $assistant_id = $oneCourse["assistant_id"];
                                $professor_name = $oneCourse["professor_name"];

                                echo "<tr>";
                                echo "<td><strong>Department Course ID</strong></td><td>".$course_num."</td>";
                                echo "</tr>";
                                echo "<tr>";
                                echo "<td><strong>Course Name</strong></td><td>".$course_name."</td>";
                                echo "</tr>";
                                echo "<tr>";
                                echo "<td><strong>Course Description</strong></td><td>".$course_description."</td>";
                                echo "</tr>";
                                echo "<tr>";
                                echo "<td><strong>Teaching Assistant</strong></td>";
                                // Pull assistant information from database.
                                try {
                                    if ($assistant_id != NULL) {
                                        $assistantQuery = $conn->prepare("SELECT * FROM USER WHERE `user_id` = ?");
                                        $assistantQuery->execute([$assistant_id]);
                                        while ($assistantDetails = $assistantQuery->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<td><em>".$assistantDetails["last_name"].", ".$assistantDetails["first_name"]."</em>";
                                            echo "<form action='course_edit.php?course_id=".$course_id."' method='post' style='display: inline; padding: 5px;'>";
                                            echo "<input type='hidden' name='assistant_remove' value='".$assistantDetails["user_id"]."'></input>";
                                            echo "<input type='submit' name='submit' value=' X '></input>";
                                            echo "</form></td>";
                                        }
                                    } else {
                                        echo "<td><em>No assigned assistant for this course.</em></td>";
                                    }
                                } catch (PDOException $e) {
                                    echo "ERROR: Could not retrieve assistant details from database. ".$e->getMessage();
                                }
                                echo "</tr>";
                                echo "<tr>";
                                echo "<td><strong>Instructor Name</strong></td><td>".$professor_name."</td>";
                                echo "</tr>";
                            }
                        } catch (PDOException $e) {
                            echo "ERROR: Could not retrieve course items. ".$e->getMessage();
                        }
                    ?>
                </table>
            </section>

            <section class="course-weights">
                <h2>Course Weights</h2>
                <table>
                    <?php
                        try {
                            $weightsQuery = $conn->prepare("SELECT * FROM COURSE_WEIGHT WHERE `course_id` = ?");
                            $weightsQuery->execute([$course_id]);
                            while ($oneWeight = $weightsQuery->fetch(PDO::FETCH_ASSOC)) {
                                $pullTypes = $conn->prepare("SELECT * FROM ASSESSMENT_TYPE WHERE `assessment_type_id` = ?");
                                $pullTypes->execute([$oneWeight["type_id"]]);
                                while ($oneType = $pullTypes->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td><strong>".$oneType["type_description"]."</strong></td>";
                                    if ($oneWeight["weight"] !== NULL) {
                                        $weightPercentage = $oneWeight["weight"];
                                        echo "<td>".$weightPercentage."%"."</td>";
                                    } else {
                                        echo "<td><em>Weight not yet assigned.</em></td>";
                                    }
                                    echo "</tr>";
                                }
                            }
                        } catch (PDOException $e) {
                            echo "ERROR: Could not retrieve assessment weights. ".$e->getMessage();
                        }
                    ?>
                </table>
            </section>

                <!-- Options to edit course details. -->
            <section class="edit-course-details">
                <br><br><br><h2>Edit Course Details</h2>
                <div class="edit-course-details-container">
                    <form action='course_edit.php?course_id=<?php echo $course_id; ?>' method='post'>
                        Course Name: <input type="text" id="course_name" name="course_name" style="width: 20%;" placeholder="<?php echo $course_name; ?>"></input><br>
                        Course Description: <input type='text' id='course_description' name='course_description' style='width: 20%;' placeholder="<?php echo $course_description; ?>"></input><br>
                        Instructor Name: <input type='text' id='professor_name' name='professor_name' placeholder="<?php echo $professor_name; ?>"></input><br>

                        <!-- Pull all available users to assign a new assistant. -->
                        Course Teaching Assistant: 
                        <?php
                            $allUsers = $conn->prepare("SELECT * FROM USER WHERE `user_type` = 3 AND user_id IN (SELECT `user_id` FROM USER_COURSE WHERE `course_id` = ?)");
                            $allUsers->execute([$course_id]);
                            echo "<select name='new_assistant'>";
                            echo '<option style="display:none"></option>';
                            while ($allUsersRow = $allUsers->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option name='new_assistant' value='".$allUsersRow["user_id"]."'>".$allUsersRow["last_name"].", ".$allUsersRow["first_name"]."</option>";
                            }
                            echo "</select>";
                        ?>
                        <br>
                        <input type="submit" name="submit" value="&nbsp;Confirm Changes&nbsp;"></input>
                    </form>
                </div>
            </section>

            <section class="edit-course-weights">
                <h2>Edit Course Weights</h2>
                <div class="edit-course-weights-container">
                    <form action='course_edit.php?course_id=<?php echo $course_id; ?>' method='post'>
                        <?php
                            try {
                                $weightsQuery2 = $conn->prepare("SELECT * FROM ASSESSMENT_TYPE");
                                $weightsQuery2->execute();
                                while ($oneWeight = $weightsQuery2->fetch(PDO::FETCH_ASSOC)) {
                                    $courseWeights = $conn->prepare("SELECT * FROM COURSE_WEIGHT WHERE `course_id` = ? AND `type_id` = ?");
                                    $courseWeights->execute([$course_id, $oneWeight["assessment_type_id"]]);
                                    while ($oneWeight2 = $courseWeights->fetch(PDO::FETCH_ASSOC)) {
                                        echo $oneWeight["type_description"]."&nbsp;";
                                        $weightPercentage2 = $oneWeight2["weight"];
                                        echo "<input type='number' min='1' max='100' id='weight_".$oneWeight["assessment_type_id"]."' name='weight_".$oneWeight["assessment_type_id"]."' placeholder ='".$weightPercentage2."'><br>";
                                    }
                                }
                            } catch (PDOException $e) {
                                echo "ERROR: Could not retrieve weights. ".$e->getMessage();
                            }
                        ?>
                        <input type="submit" name="submit" value="&nbsp;Confirm Weights&nbsp;">
                    </form>
                </div>
                <br>
            </section>

            <section class="course-add-students">
                <h2>Add Students to Course</h2>
                <div class="course-add-students-container">
                    <form action='course_edit.php?course_id=<?php echo $course_id; ?>' method='post'>
                        <?php
                        try {
                            $allStudents = $conn->prepare("SELECT * FROM USER WHERE `user_type` > 1 AND user_id NOT IN (SELECT `user_id` FROM USER_COURSE WHERE `course_id` = ?)");
                            $allStudents->execute([$course_id]);
                            echo "<select name='new_student'>";
                            echo '<option style="display:none"></option>';
                            while ($allStudentsRow = $allStudents->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option name='new_student' value='".$allStudentsRow["user_id"]."'>".$allStudentsRow["last_name"].", ".$allStudentsRow["first_name"]."</option>";
                            }
                            echo "</select>";
                        } catch (PDOException $e) {
                            echo "ERROR: Could not retrieve weights. ".$e->getMessage();
                        }
                        ?>
                        <input type="submit" name="submit" value="&nbsp;Add Student&nbsp;">
                    </form>
                </div>
            </section>

            <section class="course-delete-students">
                <h2>Remove Student from Course</h2>
                <div class="course-delete-students-container">
                    <form action='course_edit.php?course_id=<?php echo $course_id; ?>' method='post'>
                        <?php
                        try {
                            $allStudentsDelete = $conn->prepare("SELECT * FROM USER WHERE `user_type` > 1 AND user_id IN (SELECT `user_id` FROM USER_COURSE WHERE `course_id` = ?)");
                            $allStudentsDelete->execute([$course_id]);
                            echo "<select name='remove_student'>";
                            echo '<option style="display:none"></option>';
                            while ($deleteRow = $allStudentsDelete->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option name='remove_student' value='".$deleteRow["user_id"]."'>".$deleteRow["last_name"].", ".$deleteRow["first_name"]."</option>";
                            }
                            echo "</select>";
                        } catch (PDOException $e) {
                            echo "ERROR: Could not retrieve weights. ".$e->getMessage();
                        }
                        ?>
                        <input type="submit" name="submit" value="&nbsp;Remove Student&nbsp; onclick='confirmDelete(event)'">
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

<script src="course_edit.js"></script>
</body>
</html>
