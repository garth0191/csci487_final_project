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
$error = false;

// Edit course details.

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
                                            echo "<td>".$assistantDetails["user_email"]."</td>";
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
                <h2>Edit Course Details</h2>
                <div class="edit-course-details-container">
                    <form action='course_edit.php?course_id=<?php echo $course_id; ?>' method='post'>
                        Course Name: <input type="text" id="course_name" name="course_name" placeholder="<?php echo $course_name; ?>"></input>
                        Course Description: <input type='text' id='course_description' name='course_description' placeholder="<?php echo $course_description; ?>"></input>
                        Instructor Name: <input type='text' id='professor_name' name='professor_name' placeholder="<?php echo $professor_name; ?>"></input>

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
                        ?>

                        <input type="submit" name="submit"></input>
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
