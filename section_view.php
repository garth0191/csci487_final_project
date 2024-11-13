<?php
    session_start();
    require '/home/gnmcclur/connections/connect.php';

    if(!isset($_SESSION['user_id'])){
        header('Location: index.php');
    }

    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];
    $error = false;

    // Grab section ID that has been passed to this page.
    if (isset($_GET["section_id"]) && $_GET["section_id"] !== "") {
        $section_id = $_GET["section_id"];
    }

    // Grab corresponding course ID.
    $courseQuery = $conn->prepare("SELECT * FROM SECTION WHERE `section_id` = ?");
    $courseQuery->execute([$section_id]);
    while ($course = $courseQuery->fetch(PDO::FETCH_ASSOC)) {
        $course_id = $course["course_id"];
        $section_name = $course["section_name"];
        $pullCourseNum = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
        $pullCourseNum->execute([$course_id]);
        $pullCourse = $pullCourseNum->fetch(PDO::FETCH_ASSOC);
        $course_num = $pullCourse["course_num"];
    }

    // Check whether an instructor has uploaded a new course item for the section.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ( (isset($_FILES["course_item"]) && $_FILES["course_item"]["error"] === UPLOAD_ERR_OK ) && (isset($_POST["user_id"]) && $_POST["user_id"] !== "") && (isset($_POST["item_name"]) && $_POST["item_name"] !== "") ) {
            $item_name = $_POST["item_name"];
            $instructor_id = $_POST["user_id"];

            try {
                $item_filepath = $_FILES["course_item"];

                if ($item_filepath['error'] == 0) {
                    try {

                        //Upload course item to server.
                        $directory = "course_items/".$course_num;
                        if (!is_dir($directory)) {
                            mkdir($directory, 0777, true);
                        }

                        $temp_filename = $item_filepath['tmp_name'];
                        $file_extension = pathinfo($item_filepath['name'], PATHINFO_EXTENSION);
                        $new_filename= "USERID_".$instructor_id."_SECTIONID_".$section_id."_".time().".".$file_extension;
                        $upload_path = $directory."/".$new_filename;
                        move_uploaded_file($temp_filename, $upload_path);

                        $date = date('Y-m-d');
                        //Add course item to database.
                        $itemAddQuery = $conn->prepare("INSERT INTO ITEM (section_id, user_id, item_name, file_path, upload_date) VALUES (?, ?, ?, ?, ?)");
                        $itemAddQuery->execute([$section_id, $instructor_id, $item_name, $upload_path, $date]);
                    } catch (PDOException $e) {
                        echo "ERROR: Could not add item to database. ".$e->getMessage();
                    }
                }
            } catch (PDOException $e) {
                echo "ERROR: Could not upload course item to server. ".$e->getMessage();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section Page</title>
    <link rel="stylesheet" href="section_view.css">
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

    <!-- Container to hold any items assigned to this section. -->
    <div class="container">
        <div class="main-section">
            <?php
            $pullCourseName = $conn->prepare("SELECT * FROM COURSE WHERE `course_id` = ?");
            $pullCourseName->execute([$course_id]);
            while ($oneCourse = $pullCourseName->fetch(PDO::FETCH_ASSOC)) {
                echo "<h1>".$oneCourse["course_num"]." ".$oneCourse["course_name"]."</h1>";
                echo "<h2>Section ".$oneCourse["course_sec_num"].", ".$oneCourse["semester"]."</h2>";
                echo "<h3>".$section_name."</h3><br>";
            }

            ?>
            <!-- INSTRUCTORS ONLY: upload new course materials. -->
            <section class="upload-items-form">
                <h3>Upload Course Items</h3>
                <?php
                if ($user_type < 2) {
                    echo "<form action='section_view.php?section_id=".$section_id."' enctype='multipart/form-data' method='post'>";
                    echo "<input type='file' id='course_item' name='course_item' accept='.pdf, .txt'></input>";
                    echo "<label for='item_name'>Input name for course item: </label>";
                    echo "<input type='text' id='item_name' name='item_name'>";
                    echo "<input type='hidden' id='user_id' name='user_id' value='".$user_id."'></input>";
                    echo "<input type='hidden' id='section_id' name='section_id' value='".$section_id."'></input>";
                    echo "&nbsp;<button type='submit' name='submit'>Upload</button>";
                    echo "</form>";
                }
                ?>


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
                                $item_path = $oneItem["file_path"];
                                $name = $oneItem["item_name"];
                                $upload_date = $oneItem["upload_date"];
                                $instructor = $oneItem["user_id"];

                                echo "<tr>";
                                echo "<td><a href='".$item_path."'>".$name."</a></td>";

                                //Pull uploader name.
                                $nameQuery = $conn->prepare("SELECT * FROM USER WHERE `user_id` = ?");
                                $nameQuery->execute([$instructor]);
                                while ($nameRow = $nameQuery->fetch(PDO::FETCH_ASSOC)) {
                                    $last_name = $nameRow["last_name"];
                                    $first_name = $nameRow["first_name"];
                                }
                                echo "<td>".$first_name." ".$last_name."</td>";
                                echo "<td>".$upload_date."&nbsp;";

                                //Delete option ONLY available for instructors.
                                if ($user_type < 2) {
                                    echo "<form action='item_delete.php?item_id=".$oneItem["item_id"]."' method='post' style='display: inline; padding: 5px;'>";
                                    echo "<input type='hidden' name='section_id' value='".$section_id."'></input>";
                                    echo "<input type='hidden' name='course_id' value='".$course_id."'></input>";
                                    echo "<button type='submit' name='submit' onclick='confirmDelete(event)' style='background: transparent; border: none; padding: 0; cursor: pointer;'><img src='./images/trash.svg' alt='Delete'></button>";
                                    echo "</form>";
                                }
                                echo "</td>";
                                echo "</tr>";
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

<script src="section_view.js"></script>
</body>
</html>
