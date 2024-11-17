<?php
    require '/home/gnmcclur/connections/connect.php';
    session_start();

    if(!isset($_SESSION['user_id'])){
        header("Location: index.php");
    }

    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];

    // Grab course ID that has been passed to this page.
    if (isset($_GET["course_id"]) && $_GET["course_id"] !== "") {
        $course_id = $_GET["course_id"];
    }

    // Pull course details.
    $courseDetails = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
    $courseDetails->execute([$course_id]);
    while ($courseDetail = $courseDetails->fetch(PDO::FETCH_ASSOC)) {
        $course_name = $courseDetail['course_name'];
        $course_num = $courseDetail['course_num'];
        $course_sec_num = $courseDetail['course_sec_num'];
        $course_semester_id = $courseDetail['semester'];
        $course_instructor_id = $courseDetail['instructor_id'];
        $course_assistant_id = $courseDetail['assistant_id'];
        $course_description = $courseDetail['course_description'];
    }

    // ONLY administrators have access to this page.
    if($user_type != 0){
        header("Location: home.php");
    }

    $empty = true;
    $message = "";

// Edit course details.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Change course name.
    if ((isset($_POST["course_name"]) && $_POST["course_name"] !== "")) {
        try {
            $courseNameUpdate = $conn->prepare("UPDATE COURSE SET course_name = ? WHERE course_id = ?");
            $courseNameUpdate->execute([$_POST["course_name"], $course_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not change course name. " . $e->getMessage();
        }
    }

    // Change course description.
    if ((isset($_POST["course_description"]) && $_POST["course_description"] !== "")) {
        try {
            $courseDescUpdate = $conn->prepare("UPDATE COURSE SET course_description = ? WHERE course_id = ?");
            $courseDescUpdate->execute([$_POST["course_description"], $course_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not change course description. " . $e->getMessage();
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
            echo "ERROR: Could not add TA to course. " . $e->getMessage();
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
            echo "ERROR: Could not remove TA from course. " . $e->getMessage();
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
            echo "ERROR: Could not add student to course. " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Course Edit Page</title>
    <link rel="stylesheet" href="admin_course_edit.css">
</head>

<body>
<!-- Nav bar at top of page. -->
<nav class="navbar">
    <!-- Will appear on left side of nav bar. -->
    <div class="navbar-buttons">
        <div class="button home" id="home-button">Home</div>
        <!-- Display 'Create Course' option ONLY for instructors. -->
        <?php
        if ($user_type < 2) {
            if ($user_type == 0) {
                echo "<div class='button admin' id='admin-button'>Admin Dashboard</div>";
            }
            echo "<div class='button create' id='create-button'>Create Course</div>";
        }
        ?>
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
        <?php if(!$empty) {echo "<div class='error'>".$message."</div>";} ?>
        <section class="course-details">
            <h3>Course Details</h3>
            <table id="course-details-table">
                <?php
                    $courseQuery = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
                    $courseQuery->execute([$course_id]);
                    while ($courseRow = $courseQuery->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr><td><b>Department Code</b></td><td>".$courseRow["course_num"]."</td></tr>";
                        echo "<tr><td><b>Course Name</b></td><td>".$courseRow["course_name"]."</td></tr>";
                        echo "<tr><td><b>Section</b></td><td>".$courseRow["course_sec_num"]."</td></tr>";

                        $pullSemester = $conn->prepare("SELECT * FROM SEMESTER WHERE `semester_id` = ?");
                        $pullSemester->execute([$courseRow["semester"]]);
                        while ($semesterRow = $pullSemester->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr><td><b>Semester</b></td><td>".$semesterRow["semester_name"]."</td></tr>";
                        }

                        // Check whether the course has an assigned assistant.
                        $assistantQuery = $conn->prepare("SELECT * FROM USER WHERE `user_id` = ?");
                        $assistantQuery->execute([$courseRow["assistant_id"]]);
                        if ($assistantQuery->rowCount() < 1) {
                            echo "<tr><td><b>Assistant</b></td><td><em>No assigned assistant.</em></td></tr>";
                        } else {
                            while ($assistantRow = $assistantQuery->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr><td><b>Assistant</b></td><td>".$assistantRow["last_name"].", ".$assistantRow["first_name"];
                                echo "<form action='admin_course_edit.php?course_id=".$course_id."' method='post' style='display: inline; padding: 5px;'>";
                                echo "<input type='hidden' name='assistant_remove' value='".$assistantRow["user_id"]."'></input>";
                                echo "<input type='submit' name='submit' value=' X '></input>";
                                echo "</form></td>";
                                echo "</tr>";
                            }
                        }

                        // Pull instructor information.
                        $instructorQuery = $conn->prepare("SELECT * FROM USER WHERE `user_id` = ?");
                        $instructorQuery->execute([$courseRow["instructor_id"]]);
                        while ($instructorRow = $instructorQuery->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr><td><b>Instructor</b></td><td>".$instructorRow["last_name"].", ".$instructorRow["first_name"]."</td></tr>";
                        }

                        echo "<tr><td><b>Course Description</b></td><td>".$courseRow["course_description"]."</td></tr>";
                    }
                ?>
            </table>
        </section>
        <br>

        <section class="edit-course-details">
            <h2>Edit Course Details</h2>
            <div class="edit-course-details-container">

                <form action='admin_course_edit.php?course_id=<?php echo $course_id; ?>' method='post'>
                    Course Name: <input type="text" id="course_name" name="course_name" style="width: 20%;" placeholder="<?php echo $course_name; ?>"></input><br>
                    Course Description: <input type='text' id='course_description' name='course_description' style='width: 20%;' placeholder="<?php echo $course_description; ?>"></input><br>

                    <!-- Pull all available users to assign a new assistant. -->
                    Course Teaching Assistant:
                    <?php
                        $allUsers = $conn->prepare("SELECT * FROM USER WHERE `user_type` = 3");
                        $allUsers->execute();
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
        <br>

        <section class="course-add-students">
            <h2>Add Students to Course</h2>
            <div class="course-add-students-container">
                <form action='admin_course_edit.php?course_id=<?php echo $course_id; ?>' method='post'>
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
        <br>

    </div>
</div>

<footer class="footer">
    <p>© Garth McClure. All rights reserved.</p>
</footer>

<script src="admin_course_edit.js"></script>
</body>
</html>
