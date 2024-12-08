<?php
session_start();
require '/home/gnmcclur/connections/connect.php';

if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$error = false;
$empty = true;
$message = "";

//Change request.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ((isset($_POST["current_password"]) && $_POST["current_password"] !== "") && (isset($_POST["new_password"]) && $_POST["new_password"] !== "") && (isset($_POST["confirm_password"]) && $_POST["confirm_password"] !== "")) {
        $current_password = $_POST["current_password"];
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];

        try {
            //Pull user's current information from database.
            $userInfoQuery = $conn->prepare("SELECT * FROM USER WHERE `user_id` = ?");
            $userInfoQuery->execute([$user_id]);
            while ($userInfo = $userInfoQuery->fetch(PDO::FETCH_ASSOC)) {
                //Check that entered password matches database password.
                if ($userInfo && password_verify($current_password, $userInfo["user_password"])) {
                    if ($new_password !== $confirm_password) {
                        $error = true;
                        $message = "Desired passwords do not match.";
                    } else {
                        //Passwords match -- prepare new password.
                        $hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $passwordQuery = $conn->prepare("UPDATE USER SET user_password = ? WHERE `user_id` = ?");
                        $passwordQuery->execute([$hash, $user_id]);
                        $empty = false;
                        $message = "Password changed successfully.";
                    }
                } else {
                    $error = true;
                    $message = "Given password does not match password in database.";
                }
            }
        } catch (PDOException $e) {
            echo "ERROR: Could not change user password. ".$e->getMessage();
        }
    } else {
        $error = true;
        $message = "Fields must not be blank.";
    }

    if ((isset($_POST["first_name"]) && $_POST["first_name"] !== "") && (isset($_POST["last_name"]) && $_POST["last_name"] !== "")) {
        try {
            $changeName = $conn->prepare("UPDATE USER SET first_name = ?, last_name = ? WHERE `user_id` = ?");
            $changeName->execute([$_POST["first_name"], $_POST["last_name"], $user_id]);
            $error = false;
        } catch (PDOException $e) {
            echo "ERROR: Could not update first name. ".$e->getMessage();
        }
    }
}

//Pull user's usertype from database.
try {
    $userTypeQuery = $conn->prepare("SELECT * FROM USER WHERE `user_id` = ?");
    $userTypeQuery->execute([$user_id]);
    while ($oneUser = $userTypeQuery->fetch(PDO::FETCH_ASSOC)) {
        $user_type = $oneUser["user_type"];
        $first_name = $oneUser["first_name"];
        $last_name = $oneUser["last_name"];
        $userDescQuery = $conn->prepare("SELECT * FROM USER_TYPE WHERE `type_id` = ?");
        $userDescQuery->execute([$user_type]);
        while ($oneDesc = $userDescQuery->fetch(PDO::FETCH_ASSOC)) {
            $type_description = $oneDesc["type_description"];
        }
    }
} catch (PDOException $e) {
    echo "ERROR: Could not pull user data from database. ".$e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Account Options Page</title>
        <link rel="stylesheet" href="account.css">
    </head>

    <body>
        <!-- Nav bar at top of page. -->
        <nav class="navbar">
            <!-- Will appear on left side of nav bar. -->
            <div class="navbar-buttons">
                <div class="button home" id="home-button">Home</div>
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
                <!-- Display current user details. -->
                <section class="account-details">
                    <h2>User Details</h2>
                    <table>
                        <tr>
                            <?php
                            echo "<td>First Name</td>";
                            if ($first_name !== NULL) {
                                echo "<td>".$first_name."</td>";
                            } else {
                                echo "<td><em>Unspecified</em></td>";
                            }
                            ?>
                        </tr>
                        <tr>
                            <?php
                            echo "<td>Last Name</td>";
                            if ($last_name !== NULL) {
                                echo "<td>".$last_name."</td>";
                            } else {
                                echo "<td><em>Unspecified</em></td>";
                            }
                            ?>
                        </tr>
                        <tr>
                            <?php echo "<td>E-mail Address</td><td>".$user_email."</td>"; ?>
                        </tr>
                        <tr>
                            <?php echo "<td>Password</td><td>**********</td>"; ?>
                        </tr>
                        <tr>
                            <?php echo "<td>User Type</td><td>".$type_description."</td>"; ?>
                        </tr>
                    </table>
                </section>

                <!-- Change name. -->
                <section class="change-name">
                    <h2>Change Profile Name</h2>
                    <div class="change-name-container">
                        <form action="account.php" method="post">
                            <input type="text" name="first_name" placeholder="Enter new first name."></input>
                            <input type="text" name="last_name" placeholder="Enter new last name."></input>
                            <button type ="submit" id="name-confirm">Confirm</button>
                        </form>
                    </div>
                </section>

                <!-- Change user password. -->
                <section class="change-password">
                    <h2>Change Password</h2>
                    <div class="change-password-container">
                        <form action="account.php" method="post">
                            <input type="password" name="current_password" placeholder="Enter current password."></input>
                            <input type="password" name="new_password" placeholder="Enter new password."></input>
                            <input type="password" name="confirm_password" placeholder="Confirm new password."></input>
                            <button type ="submit" id="password-confirm">Confirm</button>
                        </form>
                    </div>
                </section>

                <?php if($error) {echo "<center><div class='error'>".$message."</div></center>";} ?>
                <?php if(!$empty) {echo "<center><div class='error'>".$message."</div></center>";} ?>
            </div>
        </div>
        <footer class="footer">
        <p>© Garth McClure. All rights reserved.</p>
        </footer>
    
    <script src="account.js"></script>
    </body>
</html>