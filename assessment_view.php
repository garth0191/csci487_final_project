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
    
    $message = "";
    $error = false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment View Page</title>
    <link rel="stylesheet" href="assessment_view.css">
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
        <?php
        $pullCourseName = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
        $pullCourseName->execute([$course_id]);
        while ($oneCourse = $pullCourseName->fetch(PDO::FETCH_ASSOC)) {
            echo "<h1>".$oneCourse["course_num"]." ".$oneCourse["course_name"]."</h1>";
            echo "<h2>Section ".$oneCourse["course_sec_num"].", ".$oneCourse["semester"]."</h2>";
        }
        ?>
        <br>
        <section class="all-assessments">
            <h2>Assessment List</h2>
            <div class="all-assessments-container">
                <table id="assessment-table">
                    <tr>
                        <th onclick="sortTable(0, 'assessment-table')">Assessment Name
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                        <th onclick="sortTable(1, 'assessment-table')">Assessment Type
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                        <th onclick="sortTable(2, 'assessment-table')">Due Date
                            <span class="sort-indicator" style="display: none;">&#9650;</span>
                            <span class="sort-indicator" style="display: none;">&#9660;</span></th>
                    </tr>
                        <?php
                            try {
                                // Grab assessments from database.
                                $pullAssessments = $conn->prepare("SELECT * FROM ASSESSMENT WHERE `course_id` = ?");
                                $pullAssessments->execute([$course_id]);
                                while ($oneAssessment = $pullAssessments->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>";
                                    echo $oneAssessment["assessment_description"];

                                    // Only instructor should be able to edit/delete an assessment.
                                    if ($user_type < 2) {
                                        echo "&nbsp;<form action='assessment_edit.php?assessment_id=".$oneAssessment["assessment_id"]."' method='post' style='display: inline; padding: 5px;'>";
                                        echo "<input type='hidden' name='course_id' value='".$course_id."'></input>";
                                        echo "<button type='submit' name='submit' style='background: transparent; border: none; padding: 0; cursor: pointer;'><img src='./images/pencil-square.svg' alt='Edit'></button>";
                                        echo "</form>&nbsp;";
                                        echo "<form action='assessment_delete.php?assessment_id=".$oneAssessment["assessment_id"]."' method='post' style='display: inline; padding: 5px;'>";
                                        echo "<input type='hidden' name='course_id' value='".$course_id."'></input>";
                                        echo "<button type='submit' name='submit' onclick='confirmDelete(event)' style='background: transparent; border: none; padding: 0; cursor: pointer;'><img src='./images/trash.svg' alt='Delete'></button>";
                                        echo "</form>";
                                        echo "</td>";
                                    }

                                    // Grab assessment types from database.
                                    echo "<td>";
                                    echo $oneAssessment["assessment_type"];
                                    echo "</td>";
                                    echo "<td>";
                                    echo $oneAssessment["due_date"];
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                            echo "ERROR: Could not pull assessment items from database. ".$e->getMessage();
                            }

                        ?>
                </table>
            </div>
        </section>
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
            echo '<a href="assessment_view.php?course_id=' . $course_id . '">VIEW ASSESSMENTS</a>';
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

<script src="assessment_view.js"></script>
</body>
</html>
