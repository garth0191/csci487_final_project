<?php
session_start()
require '/home/gnmcclur/connections/connect.php';

if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$error = false;
$empty = true;
$message = "";

//Pull user's usertype from database.
try {
    $userTypeQuery = $conn->prepare("SELECT * FROM USER WHERE `user_id` = ?");
    $userTypeQuery->execute([$user_id]);
    while ($oneUser = $userTypeQuery->fetch(PDO::FETCH_ASSOC)) {
        $user_type = $oneItem["user_type"];
        $userDescQuery = $conn->prepare("SELECT * FROM USER_TYPE WHERE `type_id` = ?");
        $userDescQuery->execute([$user_type]);
        while ($oneDesc = $userDescQuery->fetch(PDO::FETCH_ASSOC)) {
            $type_description = $oneDesc["type_description"];
        }
    }
} catch (PDOException $e) {
    echo "ERROR: Could not pull user data from database. ".$e->getMessage();
}

//Change password request.
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
                <div class="button create" id="create-button">Create Course</div>
                <div class="button" id="Button3">Account Options</div>
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
                            <?php echo "<td>User ID</td><td>".$user_id."</td>"; ?>
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

                <!-- Change user password. -->
                <section class="change-password">
                    <h2>Change Password</h2>
                    <form action="account.php" method="post">
                        <input type="password" name="current_password" placeholder="Enter current password."></input>
                        <input type="password" name="new_password" placeholder="Enter new password."></input>
                        <input type="password" name="confirm_password" placeholder="Confirm new password."></input>
                        <button type ="submit" id="password-confirm">Confirm</button>
                    </form>
                </section>

                <!-- Delete account. -->
                <div class="delete-account" id="delete-account">Delete Account</div>
                <?php if($error) {echo "<center><div class='error'>".$message."</div></center>";} ?>
                <?php if(!$empty) {echo "<center><div class='error'>".$message."</div></center>";} ?>
            </main>
        </div>
        <footer class="footer">
        <p>© 2024 Garth McClure. All rights reserved.</p>
        </footer>
    
    <script src="account.js"></script>
    </body>
</html>