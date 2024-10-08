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
            <section class="current-course-details">
                    <!-- Display current course details. -->
                <table>
                    <?php
                        try {
                            $courseQuery = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
                            $courseQuery->execute([$course_id]);
                            while ($oneCourse = $courseQuery->fetch(PDO::FETCH_ASSOC)) {
                                $course_name = $oneCourse["course_name"];
                                $course_description = $oneCourse["course_description"];
                                $assistant_id = $oneCourse["assistant_id"];
                                $professor_name = $oneCourse["professor_name"];

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
                                            echo "<td><em>".$assistantDetails["user_email"]."</em>";
                                            echo "<form action='course_edit.php?course_id=".$course_id."' method='post' style='display: inline;'>";
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

                <!-- Options to edit course details. -->
            <section class="edit-course-details">
                <br><br><br><h2>Edit Course Details</h2>
                <div class="edit-course-details-container">
                    <form action='course_edit.php?course_id=<?php echo $course_id; ?>' method='post'>
                        Course Name: <input type="text" id="course_name" name="course_name" placeholder="<?php echo $course_name; ?>"></input><br>
                        Course Description: <input type='text' id='course_description' name='course_description' placeholder="<?php echo $course_description; ?>"></input><br>
                        Instructor Name: <input type='text' id='professor_name' name='professor_name' placeholder="<?php echo $professor_name; ?>"></input><br>

                        <!-- Pull all available users to assign a new assistant. -->
                        Course Teaching Assistant: 
                        <?php
                            $allUsers = $conn->prepare("SELECT * FROM USER WHERE `user_type` = 3");
                            $allUsers->execute();
                            echo "<select name='new_assistant'>";
                            echo '<option style="display:none"></option>';
                            while ($allUsersRow = $allUsers->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option name='new_assistant' value='".$allUsersRow["user_id"]."'>".$allUsersRow["user_email"]."</option>";
                            }
                            echo "</select>";
                        ?>
                        <br>
                        <input type="submit" name="submit" value="&nbsp;Confirm Changes&nbsp;"></input>
                    </form>
                </div>

                <!-- Delete course. -->
                <div class="delete-course" id="delete-course">
                    <button type="submit" onclick="if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) window.location.href='course_delete.php?course_id=<?php echo $course_id; ?>';">&nbsp;Delete Course&nbsp;</button>
                </div>
            </section>
        </div>

        <!-- Sidebar. -->
        <div class="sidebar">
            <!-- Course edit options, etc., will go here. -->
            <a href="course_edit.php?course_id=<?php echo $course_id; ?>">EDIT COURSE</a>
            <a href="assessment_create.php?course_id=<?php echo $course_id; ?>">CREATE ASSESSMENT</a>
            <a href="assessment_edit.php?course_id=<?php echo $course_id; ?>">EDIT ASSESSMENTS</a>
            <a href="section_edit.php?course_id=<?php echo $course_id; ?>">EDIT COURSE ITEMS</a>
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
