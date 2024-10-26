<?php
    session_start();
    require '/home/gnmcclur/connections/connect.php';

    if(!isset($_SESSION['user_id'])){
        header('Location: index.php');
    }

    if (isset($_GET["course_id"]) && $_GET["course_id"] !== "") {
        $course_id = $_GET["course_id"];
    }

    $user_id = $_SESSION['user_id'];

    $message = "";
    $error = false;

    // Add new course item section.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST["section_name"]) && $_POST["section_name"] !== "") {
            try {
                $addSection = $conn->prepare("INSERT INTO SECTION (course_id, section_name) VALUES (?, ?)");
                $addSection->execute([$course_id, $_POST["section_name"]]);
            } catch (PDOException $e) {
                echo "ERROR: Could not add new section. ".$e->getMessage();
            }
        } else {
            $error = true;
            $message = "Fields cannot be blank.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section Edit Page</title>
    <link rel="stylesheet" href="section_edit.css">
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
        <section class="section-list">
            <center>
            <h2>Current Course Item Sections</h2>
            <table>
                <th>Section Name</th>
                <?php
                    $courseSections = $conn->prepare("SELECT * FROM SECTION WHERE `course_id` = ?");
                    $courseSections->execute([$course_id]);
                    while ($oneSection = $courseSections->fetch(PDO::FETCH_ASSOC)) {
                        $sectionName = $oneSection["section_name"];
                        echo "<tr>";
                        echo "<td>".$sectionName."&nbsp;";
                        echo "<form action='section_delete.php?section_id=".$oneSection["section_id"]."' method='post' style='display: inline; padding: 5px;'>";
                        echo "<input type='hidden' name='course_id' value='".$course_id."'></input>";
                        echo "<button type='submit' name='submit' onclick='confirmDelete(event)' style='background: transparent; border: none; padding: 0; cursor: pointer;'><img src='./images/trash.svg' alt='Delete'></button>";
                        echo "</form></td>";
                        echo "</tr>";
                    }
                ?>
            </table>
            </center>
        </section>

        <section class="add-section">
            <h2>Add New Item Section to Course</h2>
            <div class="add-section-container">
                <form action="section_edit.php?course_id=<?php echo $course_id; ?>" method="post">
                    <input type="text" name="section_name" placeholder="Input new section name." style="width: 150px;">
                    <input type="submit" name="submit" value="Add New Section">
                </form>
            </div>
        </section>
        <center><?php if($error) {echo "<div class='error'>".$message."</div>";} ?></center>
    </div>


    <!-- Sidebar. -->
    <div class="sidebar">
        <!-- Course edit options, etc., will go here. -->
        <a href="course.php?course_id=<?php echo $course_id; ?>">COURSE HOME</a>
        <a href="course_edit.php?course_id=<?php echo $course_id; ?>">EDIT COURSE</a>
        <a href="assessment_create.php?course_id=<?php echo $course_id; ?>">CREATE ASSESSMENT</a>
        <a href="assessment_view.php?course_id=<?php echo $course_id; ?>">VIEW/EDIT ASSESSMENTS</a>
        <a href="section_edit.php?course_id=<?php echo $course_id; ?>">EDIT COURSE SECTIONS</a>
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

<script src="section_edit.js"></script>
</body>
</html>
