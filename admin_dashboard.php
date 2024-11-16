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
        <!-- Complete roster of all created courses. -->
        <h3>Course Listing</h3>
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
                            echo "<tr><td colspan='5'><i><b>No created courses exist.</b></i></td></tr>";
                        } else {
                            while ($oneCourse = $coursesQuery->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>".$oneCourse['course_num']."</td>";
                                echo "<td>".$oneCourse['course_name']."</td>";
                                echo "<td>".$oneCourse['course_sec_num']."</td>";
                                echo "<td>".$oneCourse['semester']."</td>";
                                echo "<td>".$oneCourse['professor_name']."</td>";
                                echo "<td>";
                                    echo "&nbsp;<form action='admin_course_edit?course_id=".$oneCourse["course_id"]."' method='post' style='display: inline; padding: 5px;'>";
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
        <h3>User Roster</h3>
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
                        $usersQuery = $conn->prepare("SELECT * FROM USER WHERE `user_type` <> 0");
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
                                    echo "&nbsp;<form action='admin_user_edit?user_id=".$oneUser["user_id"]."' method='post' style='display: inline; padding: 5px;'>";
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
        </section>
    </div>
</div>


<footer class="footer">
    <p>© Garth McClure. All rights reserved.</p>
</footer>

<script src="admin_dashboard.js"></script>
</body>
</html>
