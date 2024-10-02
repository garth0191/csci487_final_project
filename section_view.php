<?php
session_start();
require '/home/gnmcclur/connections/connect.php';

if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
}

$user_id = $_SESSION['user_id'];

// Grab section ID that has been passed to this page.
if (isset($_GET["section_id"]) && $_GET["section_id"] !== "") {
    $section_id = $_GET["section_id"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section Page</title>
    <link rel="stylesheet" href="course_view.css">
</head>

<body>
    <!-- Nav bar at top of page. -->
    <nav class="navbar">
        <!-- Will appear on left side of nav bar. -->
        <div class="navbar-buttons">
            <div class="button" id="Button1">Button1</div>
            <div class="button" id="Button2">Button2</div>
            <div class="button" id="Button3">Button3</div>
            <div class="button logout" id="logout-button">Logout</div>
        </div>
        <!-- Will appear on right side of nav bar. -->
        <div class="navbar-logo">
            <img src="./images/logo.png" height="35%" alt="CourseCanvas logo">
        </div>
    </nav>

    <!-- Container to hold any items assigned to this section. -->
    <div class="container">
        <div class="main-section">
            <section class="upload-items-form">
                <h2>Upload Course Items</h2>
                <form action='section_view.php?id=<?php echo $section_id; ?>' enctype='multipart/form-data' method='post'>
                    <input type="file" id="course_item" name="course_item" accept=".pdf, .txt"></input>
                    <input type='hidden' id='user_id' name='user_id' value='<?php echo $user_id; ?>'></input>
                    Item Title: <input type='text' id='item_name' name='item_name'></input>
                    <button name="submit">Upload</button>
                </form>
            </section>
    
            <section class="uploaded-items">
                <!-- Pull any items for the section. -->
                 <table>
                    <tr>
                        <th>Course Item Name</th>
                        <th>Uploaded By</th>
                        <th>Date Uploaded</th>
                    </tr>

                    <?php
                    try {
                        $itemQuery = $conn->prepare("SELECT * FROM ITEM WHERE `section_id` = ?");
                        $itemQuery->execute([$section_id]);

                        if ($itemQuery->rowCount() < 1) {
                            echo "<tr><td colspan='3'><i><b>No course items have been uploaded for this section.</b></i></td></tr>";
                        } else {
                            while ($oneItem = $itemQuery->fetch(PDO::FETCH_ASSOC)) {
                                //CODE TO RETRIEVE COURSE ITEMS HERE.




                            }
                        }
                    } catch (PDOException $e) {
                        echo "ERROR: Could not retrieve course items. ".$e->getMessage();
                    }
                    ?>
                </table>
            </section>
        </div>

        <!-- Sidebar. -->
        <div class="sidebar">
            <!-- Course edit options, etc., will go here. -->
            <a href="course_edit.php?course_id=<?php echo $course_id; ?>">EDIT COURSE</a>
            <a href="course_edit.php?course_id=<?php echo $course_id; ?>">DELETE COURSE</a>
            <a href="assessment_create.php?course_id=<?php echo $course_id; ?>">CREATE ASSESSMENT</a>
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

<script src="course_view.js"></script>
</body>
</html>
