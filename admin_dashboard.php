<?php
    require '/home/gnmcclur/connections/connect.php';
    session_start();

    if(!isset($_SESSION['user_id'])){
        header("Location: index.php");
    }

    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];

    // ONLY administrators have access to this page.
    if($user_type != 0){
        header("Location: home.php");
    }

    $empty = true;
    $message = "";

    // Add new semester to database.
    if ((isset($_POST["semester-kind"]) && $_POST["semester-kind"] !== "") && (isset($_POST["semester-year"]) && $_POST["semester-year"] !== "")) {
        try {
            // Check that semester is not already in the database.
            $semester_string = $_POST["semester-kind"]." ".$_POST["semester-year"];
            $semesterCheck = $conn->prepare("SELECT * FROM SEMESTER WHERE `semester_name` = ?");
            $semesterCheck->execute([$semester_string]);
            if ($semesterCheck->rowCount() > 0) {
                $empty = false;
                $message = "The semester already exists.";
            } else {
                $semesterAdd = $conn->prepare("INSERT INTO `SEMESTER` (semester_name) VALUES (?)");
                $semesterAdd->execute([$semester_string]);
                $empty = false;
                $message = "The semester has been successfully created.";
            }
        } catch (PDOException $e) {
            echo "ERROR: Could not create new semester. ".$e->getMessage();
        }
    }

    // Create a new Instructor user.
    if (isset($_POST['create_instructor'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $user_email = trim($_POST['user_email']);
        $user_password = $_POST['user_password'];

        // Check that none of the required fields are empty and that the e-mail address is valid.
        if (empty($first_name) || empty($last_name) || empty($user_email) || empty($user_password)) {
            $error_message = "All fields are required. Please try again.";
            $show_modal = true;
        } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid e-mail format. Please try again.";
            $show_modal = true;
        } else {
            // Hash password.
            $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

            // Check if user e-mail already exists.
            $emailCheck = $conn->prepare("SELECT * FROM USER WHERE `user_email` = ?");
            $emailCheck->execute([$user_email]);
            if ($emailCheck->rowCount() > 0) {
                $error_message = "A user with this email already exists. Please choose a different e-mail.";
                $show_modal = true;
            } else {
                // Add new instructor to database.
                try {
                    $insertUser = $conn->prepare("INSERT INTO USER (user_email, user_password, user_type, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
                    $insertUser->execute([$user_email, $hashed_password, 1, $first_name, $last_name]);

                    header("Location: admin_dashboard.php");
                } catch (PDOException $e) {
                    $error_message = "ERROR: Could not create new instructor. " . $e->getMessage();
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard Page</title>
    <link rel="stylesheet" href="admin_dashboard.css">
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
        <!-- Complete roster of all created courses. -->
        <h3>All Courses</h3>
        <section class="course-list">
            <table id="course-list-table">
                <tr>
                    <th onclick="sortTable(0, 'course-list-table')">Dept ID</th>
                    <th onclick="sortTable(1, 'course-list-table')">Course Name</th>
                    <th onclick="sortTable(2, 'course-list-table')">Section</th>
                    <th onclick="sortTable(3, 'course-list-table')">Semester</th>
                    <th onclick="sortTable(4, 'course-list-table')">Instructor</th>
                    <th></th>
                </tr>
                <?php
                    try {
                        $coursesQuery = $conn->prepare("SELECT * FROM COURSE");
                        $coursesQuery->execute();
                        if ($coursesQuery->rowCount() < 1) {
                            echo "<tr><td colspan='6'><i><b>No created courses exist.</b></i></td></tr>";
                        } else {
                            while ($oneCourse = $coursesQuery->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>".$oneCourse['course_num']."</td>";
                                echo "<td>".$oneCourse['course_name']."</td>";
                                echo "<td>".$oneCourse['course_sec_num']."</td>";
                                $pullSemester = $conn->prepare("SELECT * FROM SEMESTER WHERE `semester_id` = ?");
                                $pullSemester -> execute([$oneCourse['semester']]);
                                while ($oneSemester = $pullSemester->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<td>".$oneSemester['semester_name']."</td>";
                                }
                                echo "<td>".$oneCourse['professor_name']."</td>";
                                echo "<td>";
                                    echo "&nbsp;<form action='admin_course_edit.php?course_id=".$oneCourse["course_id"]."' method='post' style='display: inline; padding: 5px;'>";
                                    echo "<button type='submit' name='submit' style='background: transparent; border: none; padding: 0; cursor: pointer;'><img src='./images/pencil-square.svg' alt='Edit'></button>";
                                    echo "</form>&nbsp;";
                                    echo "<form action='course_delete.php?course_id=".$oneCourse["course_id"]."' method='post' style='display: inline; padding: 5px;'>";
                                    echo "<button type='submit' name='submit' onclick='confirmDelete(event)' style='background: transparent; border: none; padding: 0; cursor: pointer;'><img src='./images/trash.svg' alt='Delete'></button>";
                                    echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        }
                    } catch (PDOException $e) {
                        echo "ERROR: Could not pull course list from database. ".$e->getMessage();
                    }
                ?>
            </table>
        </section>
        <br>
        <!-- Roster of all users. -->
        <h3>All Users</h3>
        <section class="user-list">
            <table id="user-list-table">
                <tr>
                    <th onclick="sortTable(0, 'user-list-table')">Last Name</th>
                    <th onclick="sortTable(1, 'user-list-table')">First Name</th>
                    <th onclick="sortTable(2, 'user-list-table')">E-Mail Address</th>
                    <th onclick="sortTable(3, 'user-list-table')">User Type</th>
                    <th></th>
                </tr>
                <?php
                    try {
                        // Pull all users who are not administrators.
                        $usersQuery = $conn->prepare("SELECT * FROM USER WHERE `user_type` <> 0 ORDER BY user_type ASC");
                        $usersQuery->execute();
                        if ($usersQuery->rowCount() < 1) {
                            echo "<tr><td colspan='5'><i><b>No users exist.</b></i></td></tr>";
                        } else {
                            while ($oneUser = $usersQuery->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>".$oneUser['last_name']."</td>";
                                echo "<td>".$oneUser['first_name']."</td>";
                                echo "<td>".$oneUser['user_email']."</td>";
                                // Pull user types to display instead of the type_id.
                                $userTypes = $conn->prepare("SELECT * FROM USER_TYPE WHERE `type_id` = ?");
                                $userTypes->execute([$oneUser['user_type']]);
                                while ($oneUserType = $userTypes->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<td>".$oneUserType['type_description']."</td>";
                                }
                                echo "<td>";
                                    echo "&nbsp;<form action='admin_user_edit.php?user_id=".$oneUser["user_id"]."' method='post' style='display: inline; padding: 5px;'>";
                                    echo "<button type='submit' name='submit' style='background: transparent; border: none; padding: 0; cursor: pointer;'><img src='./images/pencil-square.svg' alt='Edit'></button>";
                                    echo "</form>&nbsp;";
                                    echo "<form action='account_delete.php?user_id=".$oneUser["user_id"]."' method='post' style='display: inline; padding: 5px;'>";
                                    echo "<button type='submit' name='submit' onclick='confirmDelete(event)' style='background: transparent; border: none; padding: 0; cursor: pointer;'><img src='./images/trash.svg' alt='Delete'></button>";
                                    echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        }
                    } catch (PDOException $e) {
                        echo "ERROR: Could not pull user list from database. ".$e->getMessage();
                    }
                ?>
            </table>
            <!-- Create a new instructor. -->
            <button id="add-instructor-button">Add Instructor</button>
        </section>
        <br>
        <!-- Add a new semester to database. -->
        <h3>Create New Semester</h3>
        <section class="add-semester">
            <form action="admin_dashboard.php" method="post">
                <label for="semester-kind">Semester:</label>
                <select name="semester-kind" required>
                    <option style="display:none"></option>
                    <option name="semester-kind" value="Fall">Fall</option>
                    <option name="semester-kind" value="First Fall">First Fall</option>
                    <option name="semester-kind" value="Second Fall">Second Fall</option>
                    <option name="semester-kind" value="Winter Intersession">Winter Intersession</option>
                    <option name="semester-kind" value="Spring">Spring</option>
                    <option name="semester-kind" value="First Spring">First Spring</option>
                    <option name="semester-kind" value="Second Spring">Second Spring</option>
                    <option name="semester-kind" value="May Intersession">May Intersession</option>
                    <option name="semester-kind" value="Full Summer">Full Summer</option>
                    <option name="semester-kind" value="First Summer">First Summer</option>
                    <option name="semester-kind" value="Second Summer">Second Summer</option>
                    <option name="semester-kind" value="August Intersession">August Intersession</option>
                </select>
                <label for="semester-year">Year ('YYYY'):</label>
                <input type="text" pattern="\d{4}" name="semester-year" required>
                <input type="submit" name="submit" value="Add Semester">
            </form>
        </section>
    </div>
</div>

<?php
if (isset($error_message)) {
    echo "<p style='color:red;'>" . htmlspecialchars($error_message) . "</p>";
}
?>

<footer class="footer">
    <p>© Garth McClure. All rights reserved.</p>
</footer>

<div id="add-instructor-modal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Add New Instructor</h2>
        <form id="add-instructor-form" method="post" action="">
            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" required><br>

            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" required><br>

            <label for="user_email">Email:</label>
            <input type="email" name="user_email" required><br>

            <label for="user_password">Password:</label>
            <input type="password" name="user_password" required><br>

            <button type="submit" name="create_instructor">Create New Instructor</button>
        </form>
    </div>
</div>

<script src="admin_dashboard.js"></script>
<script>var showModal = <?php echo (isset($show_modal) && $show_modal) ? 'true' : 'false'; ?>;</script>
</body>
</html>

