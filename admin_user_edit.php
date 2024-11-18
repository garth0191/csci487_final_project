<?php
require '/home/gnmcclur/connections/connect.php';
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
}

$this_user_type = $_SESSION['user_type'];

// ONLY administrators have access to this page.
if($this_user_type != 0){
    header("Location: home.php");
}

// Grab user ID that has been passed to this page.
if (isset($_GET["user_id"]) && $_GET["user_id"] !== "") {
    $user_id = $_GET["user_id"];
}

$empty = true;
$message = "";

// Edit user details.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Change user last name.
    if ((isset($_POST["last_name"]) && $_POST["last_name"] !== "")) {
        try {
            $changeLastName = $conn->prepare("UPDATE USER SET `last_name` = ? WHERE user_id = ?");
            $changeLastName->execute([$_POST["last_name"], $user_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not change course name. " . $e->getMessage();
        }
    }

    // Change user first name.
    if ((isset($_POST["first_name"]) && $_POST["first_name"] !== "")) {
        try {
            $changeFirstName = $conn->prepare("UPDATE USER SET `first_name` = ? WHERE user_id = ?");
            $changeFirstName->execute([$_POST["first_name"], $user_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not change course description. " . $e->getMessage();
        }
    }

    // Change user e-mail.
    if ((isset($_POST["user_email"]) && $_POST["user_email"] !== "")) {
        try {
            $changeEmail = $conn->prepare("UPDATE USER SET `user_email` = ? WHERE user_id = ?");
            $changeEmail->execute([$_POST["user_email"], $user_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not add TA to course. " . $e->getMessage();
        }
    }

    // Change user type.
    if ((isset($_POST["new_type"]) && $_POST["new_type"] !== "")) {
        try {
            $changeUserType = $conn->prepare("UPDATE USER SET `user_type` = ? WHERE user_id = ?");
            $changeUserType->execute([$_POST["new_type"], $user_id]);
        } catch (PDOException $e) {
            echo "ERROR: Could not add student to course. " . $e->getMessage();
        }
    }
}


// Pull selected user's details.
$userInfo = $conn->prepare("SELECT * FROM USER WHERE `user_id` = ?");
$userInfo->execute([$user_id]);
while ($oneUser = $userInfo->fetch(PDO::FETCH_ASSOC)) {
    $user_email = $oneUser['user_email'];
    $user_type_id = $oneUser['user_type'];
    $first_name = $oneUser['first_name'];
    $last_name = $oneUser['last_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin User Edit Page</title>
    <link rel="stylesheet" href="admin_user_edit.css">
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
        <section class="user-details">
            <h2>User Details</h2>
            <table id="user-details-table">
                <tr><td><strong>Last Name</strong></td><td><?php echo $last_name; ?></td></tr>
                <tr><td><strong>First Name</strong></td><td><?php echo $first_name; ?></td></tr>
                <tr><td><strong>E-Mail</strong></td><td><?php echo $user_email; ?></td></tr>
                <?php
                    $userTypeQuery = $conn->prepare("SELECT * FROM USER_TYPE WHERE `type_id` = ?");
                    $userTypeQuery->execute([$user_type_id]);
                    while ($oneUserType = $userTypeQuery->fetch(PDO::FETCH_ASSOC)) {
                        $user_type_string = $oneUserType['type_description'];
                        echo "<tr><td><strong>User Type</strong></td><td>".$user_type_string."</td></tr>";
                    }
                ?>
            </table>
        </section>
        <br>

        <section class="edit-user-details">
            <h2>Edit User Details</h2>
            <div class="edit-user-details-container">
                <form action='admin_user_edit.php?user_id=<?php echo $user_id; ?>' method='post'>
                    Last Name: <input type="text" id="last_name" name="last_name" style="width: 20%;" placeholder="<?php echo $last_name; ?>"></input><br>
                    First Name: <input type='text' id='first_name' name='first_name' style='width: 20%;' placeholder="<?php echo $first_name; ?>"></input><br>
                    E-Mail: <input type='text' id='user_email' name='user_email' style='width: 20%;' placeholder="<?php echo $user_email; ?>"></input><br>

                    <!-- Pull all available user types. -->
                    User Type:
                    <?php
                    $pullUserTypes = $conn->prepare("SELECT * FROM USER_TYPE WHERE `type_id` <> ?");
                    $pullUserTypes->execute([$user_type_id]);
                    echo "<select name='new_assistant'>";
                    echo '<option style="display:none"></option>';
                    while ($aType = $pullUserTypes->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option name='new_type' value='".$aType["type_id"]."'>".$aType["type_description"]."</option>";
                    }
                    echo "</select>";
                    ?>
                    <br>
                    <input type="submit" name="submit" value="&nbsp;Confirm Changes&nbsp;"></input>
                </form>
            </div>
        </section>
    </div>
</div>

<footer class="footer">
    <p>© Garth McClure. All rights reserved.</p>
</footer>

<script src="admin_user_edit.js"></script>
</body>
</html>
