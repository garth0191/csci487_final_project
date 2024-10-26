<?php
session_start();
require '/home/gnmcclur/connections/connect.php';

if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
}

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    //Confirm token value.
    try {
        $stmt = $conn->prepare("SELECT * FROM USER WHERE reset_token = :token");
        $stmt->bindParam(":token", $token);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (isset($_POST["new_password"]) && !empty($_POST["new_password"]) && isset($_POST["confirm_password"]) && !empty($_POST["confirm_password"])) {
                    if ($_POST["new_password"] !== $_POST["confirm_password"]) {
                        $message = "Passwords do not match.";
                    } else {
                        $hashedPassword = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

                        $updateStmt = $conn->prepare("UPDATE USER SET user_password = :password, reset_token = NULL WHERE user_id = :id");
                        $updateStmt->bindParam(":password", $hashedPassword);
                        $updateStmt->bindParam(":id", $user["user_id"]);
                        $updateStmt->execute();

                        $message = "Password reset successfully. You can now <a href='index.php'>log in</a>.";
                    }
                } else {
                    $message = "Required fields may not be blank.";
                }
            }
        } else {
            $message = "Invalid or expired token.";
        }
    } catch (PDOException $e) {
        echo "Error retrieving user: " . $e->getMessage();
    }
} else {
    $message = "No token provided.";
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$empty = true;
$message = "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Page</title>
    <link rel="stylesheet" href="reset_password.css">
</head>

<body>
<nav class="navbar">
    <!-- Will appear on left side of nav bar. -->
    <div class="navbar-buttons">
        <div class="button home" id="home-button">Home</div>
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
    <h2>Reset Password</h2>
    <div class="main-section">
        <section class="password-reset-form">
            <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
                <label for="new_password">New Password:</label>
                <input type="password" class="password element" name="new_password" required>
                <br>
                <label for="confirm_password" class="password element">Confirm Password:</label>
                <input type="password" class="password element" name="confirm_password" required>
                <br>
                <button type="submit" name="submit" class="password-reset-submit">Submit</button>
                <?php if (isset($message)) { echo "<div class='message'>$message</div>"; } ?>
            </form>
        </section>
    </div>
</div>

<footer class="footer">
    <p>© Garth McClure. All rights reserved.</p>
</footer>

<script src="reset_password.js"></script>
</body>

