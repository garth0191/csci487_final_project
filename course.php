<?php
session_start();
require '/home/gnmcclur/connections/connect.php';

if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
}

$currentTime = new DateTime();
$assessmentCounter = 0;
$assessmentsReadyToGrade = 0;
$hasAssessments = false;
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Grab course ID that has been passed to this page.
if (isset($_GET["course_id"]) && $_GET["course_id"] !== "") {
    $course_id = $_GET["course_id"];
}

// Update student submission.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ((isset($_FILES["student-file"]) && $_FILES["student-file"]["error"] === UPLOAD_ERR_OK)) {
        $new_student_filepath = $_FILES["student-file"];
        $user_assess_id = $_POST["user_assessment_id"];
        $student_id = $_POST["modal_user_id"];

        try {
            $temp_filename = $new_student_filepath["tmp_name"];
            $file_extension = pathinfo($new_student_filepath["name"], PATHINFO_EXTENSION);
            $new_filename = "USERID_".$student_id."_USERASSESSMENTID_".$user_assess_id."_".time().".".$file_extension;
            // Grab course number.
            $courseNum = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
            $courseNum->execute([$course_id]);
            while ($courseRow = $courseNum->fetch(PDO::FETCH_ASSOC)) {
                $upload_path = "student_submissions/".$new_filename;
                move_uploaded_file($temp_filename, $upload_path);
                $date = date("Y-m-d");
                $submissionAddQuery = $conn->prepare("UPDATE USER_ASSESSMENT SET user_submission_filepath = ? WHERE `user_assessment_id` = ?");
                $submissionAddQuery->execute([$upload_path, $user_assess_id]);
            }
        } catch (PDOException $e) {
            echo "ERROR: Could not update student submission after modal confirmation." . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Page</title>
    <link rel="stylesheet" href="course.css">
</head>

<body>
    <!-- Nav bar at top of page. -->
    <nav class="navbar">
        <!-- Will appear on left side of nav bar. -->
        <div class="navbar-buttons">
            <div class="button home" id="home-button">Home</div>
            <?php
            if ($user_type < 2) {
                echo "<div class='button create' id='create-button'>Create Course</div>";
            }
            ?>
            <?php
            if ($user_type == 0) {
                echo "<div class='button admin' id='admin-button'>Admin Dashboard</div>";
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
            <?php
                $pullCourseName = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
                $pullCourseName->execute([$course_id]);
                while ($oneCourse = $pullCourseName->fetch(PDO::FETCH_ASSOC)) {
                    echo "<h1>".$oneCourse["course_num"]." ".$oneCourse["course_name"]."</h1>";
                    echo "<h2>Section ".$oneCourse["course_sec_num"].", ".$oneCourse["semester"]."</h2>";
                }
            ?>
            <!-- Upcoming assessments section. -->
            <section class="upcoming">
            <h3>Upcoming Assessments</h3>
                <table id="upcoming-assessments-table">
                    <tr>
                        <th onclick="sortTable(0, 'upcoming-assessments-table')">Assessment Name
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                        <th onclick="sortTable(1, 'upcoming-assessments-table')">Assessment Type
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                        <th onclick="sortTable(2, 'upcoming-assessments-table')">Due Date
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                        <?php
                            if ($user_type > 1) {
                                echo "<th>Submissions</th>";
                            }
                        ?>
                    </tr>
                        <!-- Grab all pending assessment items for course. 
                         NOTE: They should only be listed if the current date is not past the assessment's due date. -->
                        <?php
                            try {
                                $assessmentGrab = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `course_id` = ?");
                                $assessmentGrab->execute([$course_id]);

                                if ($assessmentGrab->rowCount() < 1) {
                                    echo "<tr><td colspan='3'><i><b>No upcoming assessments at this time.</b></i></td></tr>";
                                } else {
                                    $hasAssessments = true;
                                    while ($oneAssessment = $assessmentGrab->fetch(PDO::FETCH_ASSOC)) {
                                        $assessment_id = $oneAssessment["assessment_id"];
                                        $assessmentDueDate = new DateTime($oneAssessment["due_date"]);
                                        if ($assessmentDueDate > $currentTime) {
                                            $assessmentCounter++;
                                            echo "<tr>";
                                            echo "<td>".$oneAssessment["assessment_description"]."</td>";
                                            echo "<td>".$oneAssessment["assessment_type"]."</td>";
                                            echo "<td>".$oneAssessment["due_date"]."</td>";

                                            // Extra submission column if user is a student.
                                            if ($user_type > 1) {
                                                // Check that assessment in question requires submissions.
                                                if ($oneAssessment["has_submissions"] === 0) {
                                                    echo "<td><em>No submission required.</em></td>";
                                                } else {
                                                    // Fetch the USER_ASSESSMENT record for the current user and assessment.
                                                    $userAssessment = $conn->prepare("SELECT * FROM USER_ASSESSMENT WHERE `assessment_id` = ? AND `user_id` = ?");
                                                    $userAssessment->execute([$assessment_id, $user_id]);
                                                    $oneUserAssessment = $userAssessment->fetch(PDO::FETCH_ASSOC);

                                                    if ($oneUserAssessment) {
                                                        $user_assessment_id = $oneUserAssessment["user_assessment_id"];
                                                        if ($oneUserAssessment["user_submission_filepath"] !== NULL) {
                                                            // Output cell with previous submission link and upload button.
                                                            echo "<td><a href='".$oneUserAssessment["user_submission_filepath"]."'>Previous Submission</a>&nbsp;<button class='student-submit-button' data-user-assessment-id='".$user_assessment_id."' data-user-id='".$user_id."' style='background: transparent; display: inline; border: none; padding: 0; cursor: pointer;'><img src='./images/pencil-square.svg' alt='Upload New Submission'></button></td>";
                                                        } else {
                                                            // Output cell with upload button only.
                                                            echo "<td><button class='student-submit-button' data-user-assessment-id='".$user_assessment_id."' data-user-id='".$user_id."' style='background: transparent; display: inline; border: none; padding: 0; cursor: pointer;'><img src='./images/pencil-square.svg' alt='Upload Submission'></button></td>";
                                                        }
                                                    } else {
                                                        // No USER_ASSESSMENT record exists; create one.
                                                        try {
                                                            $insertUserAssessment = $conn->prepare("INSERT INTO USER_ASSESSMENT (user_id, assessment_id) VALUES (?, ?)");
                                                            $insertUserAssessment->execute([$user_id, $assessment_id]);
                                                            $user_assessment_id = $conn->lastInsertId();

                                                            // Output cell with upload button.
                                                            echo "<td><button class='student-submit-button' data-user-assessment-id='".$user_assessment_id."' data-user-id='".$user_id."' style='background: transparent; display: inline; border: none; padding: 0; cursor: pointer;'><img src='./images/pencil-square.svg' alt='Upload Submission'></button></td>";
                                                        } catch (PDOException $e) {
                                                            echo "<td><em>Error creating submission record.</em></td>";
                                                        }
                                                    }
                                                }
                                            }
                                            echo "</tr>";
                                        }
                                    }
                                }
                                if (($assessmentCounter < 1) && $hasAssessments) {
                                    if ($user_type < 2) {
                                        echo "<tr><td colspan='3'><i><b>No upcoming assessments at this time.</b></i></td></tr>";
                                    } else {
                                        echo "<tr><td colspan='4'><i><b>No upcoming assessments at this time.</b></i></td></tr>";
                                    }
                                }
                                $assessmentCounter = 0;
                            } catch (PDOException $e) {
                                echo "ERROR: Could not grab assessment data.\n".$e->getMessage();
                            }
                        ?>
                </table>
            </section>

            <!-- Section for assessments ready to be graded. Should ONLY appear for instructors.-->
            <?php
                if ($user_type < 2) {
                    echo '<section class="ready-to-grade">';
                    echo '<h3>Items Ready to Grade</h3>';
                    echo '<table id="ready-to-grade-table">';
                    echo '<tr>';
                    echo '<th onclick="sortTable(0, \'ready-to-grade-table\')">Assessment Name';
                    echo '<span class="sort-indicator" style="display: none;">&#9650;</span>';
                    echo '<span class="sort-indicator" style="display: none;">&#9660;</span></th>';
                    echo '<th onclick="sortTable(1, \'ready-to-grade-table\')">Assessment Type';
                    echo '<span class="sort-indicator" style="display: none;">&#9650;</span>';
                    echo '<span class="sort-indicator" style="display: none;">&#9660;</span></th>';
                    echo '<th onclick="sortTable(2, \'ready-to-grade-table\')">Total Items';
                    echo '<span class="sort-indicator" style="display: none;">&#9650;</span>';
                    echo '<span class="sort-indicator" style="display: none;">&#9660;</span></th>';
                    echo '</tr>';
                    try {
                        $readyToGrade = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `course_id` = ?");
                        $readyToGrade->execute([$course_id]);
                        if ($readyToGrade->rowCount() < 1) {
                            echo "<tr><td colspan='3'><i><b>No assessments available at this time.</b></i></td></tr>";
                        } else {
                            while ($oneItem = $readyToGrade->fetch(PDO::FETCH_ASSOC)) {
                                $dueDate = new DateTime($oneItem["due_date"]);
                                if ($dueDate <= $currentTime) {
                                    // Check to see if there are any USER_ASSESSMENT items to grade for each ASSESSMENT item.
                                    $pullAssessments = $conn->prepare("SELECT * FROM USER_ASSESSMENT WHERE `assessment_id` = ?");
                                    $pullAssessments->execute([$oneItem["assessment_id"]]);
                                    if ($pullAssessments->rowCount() >= 1) {
                                        $assessmentCounter++;
                                        $assessmentsReadyToGrade = $pullAssessments->rowCount();
                                        echo "<tr>";
                                        echo "<td>" . $oneItem["assessment_description"] . "</td>";
                                        echo "<td>" . $oneItem["assessment_type"] . "</td>";
                                        echo "<td>";
                                        echo '<a href="gradebook.php?course_id=' . $course_id . '">'.$assessmentsReadyToGrade.'</a></td>';
                                        echo "</tr>";
                                    }
                                }
                            }
                            if ($assessmentCounter < 1) {
                                echo "<tr><td colspan='3'><i><b>No assessments require grading at this time.</b></i></td></tr>";
                            }
                        }
                        $assessmentCounter = 0;
                    } catch (PDOException $e) {
                        echo "ERROR: Could not grab data for items ready to be graded.\n" . $e->getMessage();
                    }
                    echo '</table>';
                    echo '</section>';
                }

                // Course roster of all registered students. Should ONLY appear for instructors.
                echo '<section class="student-roster">';
                echo '<h3>Course Roster</h3>';
                echo '<table id="student-roster-table">';
                echo '<tr>';
                echo '<th onclick="sortTable(0, \'student-roster-table\')">Last Name';
                echo '<span class="sort-indicator" style="display: none;">&#9650;</span>';
                echo '<span class="sort-indicator" style="display: none;">&#9660;</span>';
                echo '</th>';
                echo '<th onclick="sortTable(1, \'student-roster-table\')">First Name';
                echo '<span class="sort-indicator" style="display: none;">&#9650;</span>';
                echo '<span class="sort-indicator" style="display: none;">&#9660;</span>';
                echo '</th>';
                echo '<th onclick="sortTable(2, \'student-roster-table\')">Contact E-Mail';
                echo '<span class="sort-indicator" style="display: none;">&#9650;</span>';
                echo '<span class="sort-indicator" style="display: none;">&#9660;</span>';
                echo '</th>';
                echo '</tr>';

                $rosterQuery = $conn->prepare("SELECT * FROM USER_COURSE WHERE `course_id` = ?");
                $rosterQuery->execute([$course_id]);
                if ($rosterQuery->rowCount() < 1) {
                    echo "<tr><td colspan='3'><i><b>No students currently registered in course.</b></i></td></tr>";
                } else {
                    while ($oneStudent = $rosterQuery->fetch(PDO::FETCH_ASSOC)) {
                        try {
                            echo '<tr>';
                            $userQuery = $conn->prepare("SELECT * FROM USER WHERE `user_id` = ?");
                            $userQuery->execute([$oneStudent["user_id"]]);
                            while ($userRow = $userQuery->fetch(PDO::FETCH_ASSOC)) {
                                echo '<td>' . htmlspecialchars($userRow["last_name"]) . '</td>';
                                echo '<td>' . htmlspecialchars($userRow["first_name"]) . '</td>';
                                echo '<td>' . htmlspecialchars($userRow["user_email"]) . '</td>';
                            }
                            echo '</tr>';
                        } catch (PDOException $e) {
                            echo "ERROR: Could not pull student data from database. " . $e->getMessage();
                        }
                    }
                }
                echo '</table>';
                echo '</section>';
            ?>
        </div>

        <!-- Sidebar. -->
        <div class="sidebar">
            <!-- Course edit options, etc., will go here. -->
            <a href="course.php?course_id=<?php echo $course_id; ?>">COURSE HOME</a>

            <?php
                if ($user_type < 2) {
                    // User is an instructor.
                    echo '<a href="course_edit.php?course_id=' . $course_id . '">EDIT COURSE</a>';
                    echo '<a href="assessment_create.php?course_id=' . $course_id . '">CREATE ASSESSMENT</a>';
                    echo '<a href="assessment_view.php?course_id=' . $course_id . '">VIEW/EDIT ASSESSMENTS</a>';
                    echo '<a href="section_edit.php?course_id=' . $course_id . '">EDIT COURSE CONTENT</a>';
                    echo '<a href="gradebook.php?course_id=' . $course_id . '">GRADEBOOK</a>';
                } else {
                    // User is a student.
                    echo '<a href="gradebook.php?course_id=' . $course_id . '">GRADEBOOK</a>';
                }

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

    <div id="student-submit-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <form id="student-submit-form" method="post" enctype='multipart/form-data' action="">
                <h3>Upload your submission below.</h3><br>
                <input type="file" id="student-file" name="student-file" accept=".pdf, .txt"></input>
                <input type='hidden' id='modal-user-id' name='modal_user_id' value='<?php echo $user_id; ?>'></input>
                <input type='hidden' id='user-assessment-id' name='user_assessment_id' value='<?php echo $user_assessment_id; ?>'></input>
                <button type="submit" name="submit">Upload</button>
            </form>
        </div>
    </div>


    <script src="course.js"></script>
</body>
</html>
