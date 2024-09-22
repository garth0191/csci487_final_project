<?php
    require '/home/gnmcclur/connections/connect.php';

    //Get username and password from login form.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        //Login.
        if ((isset($_POST["login_email"]) && $_POST["login_email"] !== "") && (isset($_POST["login_password"]) && $_POST["login_password"] !== "")) {
            $username = $_POST["login_email"];
            $password = $_POST["login_password"];

            try {
                //Prepare SQL query.
                $stmt = $conn->prepare("SELECT * FROM USER WHERE user_email = :user");
                $stmt->bindParam(":user", $username);
                $stmt->execute();

                $isUser = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($isUser && password_verify($password, $isUser["user_password"])) {
                    //Password is correct.
                    session_start();
                    $_SESSION["user_email"] = $isUser["user_email"];
                    $_SESSION["user_id"] = $isUser["user_id"];
                    $empty = true;
                    $message = "";

                    //Check if user is an administrator.
                    if ($isUser["user_type"] == 0) {
                        header("Location: admin.php");
                    } else {
                        header("Location: home.php");
                    }
                } else {
                    //Password is incorrect.
                    $empty = false;
                    $message = "Your password is incorrect. Please try again.";
                }
            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
        } elseif ((isset($_POST["signup_email"]) && $_POST["signup_email"] !== "") && (isset($_POST["signup_password"]) && $_POST["signup_password"] !== "")) {
            try {
                $stmt = $conn->prepare("SELECT * FROM USER WHERE user_email = ?");
                $stmt->execute([$_POST["signup_email"]]);

                //Check that e-mail is not already taken.
                if ($rowNum = $stmt->rowCount() >= 1) {
                    $error = true;
                    $message = "This e-mail is already taken. Please choose a different e-mail, or login.";
                    header("Location: index.php");
                }

                $desiredEmail = $_POST["signup_email"];
                $desiredPassword = $_POST["signup_password"];
                $desiredUserType = $_POST["type"];

                //Check that submitted passwords match.
                if ($_POST["signup_password"] !== $_POST["signup_password_confirm"]) {
                    //Passwords do not match.
                    $error = true;
                    $message = "Submitted passwords do not match.";
                    header("Location: index.php");
                } else {
                    $error = false;
                    $message = "";

                    try {
                        //Prepare to add user to the database.
                        $addUserStmt = "INSERT INTO USER (user_email, user_password, user_type) VALUES (?, ?, ?)";
                        $addUser = $conn->prepare($addUserStmt);

                        //Encrypt password.
                        $hash = password_hash($desiredPassword, PASSWORD_DEFAULT);
                        $addUser->execute([$desiredEmail, $hash, $desiredUserType]);

                        $error = false;
                        $empty = false;
                        $message = "Registration successful! Please log in.";
                    } catch (PDOException $e) {
                        $error = true;
                        $message = $e->getMessage();
                        echo "Could not add user to database: " . $e->getMessage();
                    }
                }
            } catch (PDOException $e) {
                echo "Error retrieving e-mail from database: " . $e->getMessage();
            }
        } else {
            $error = true;
            $message = "Fields cannot be blank.";
        }
    }
?>

<!DOCTYPE html>
<html lang = "en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CSCI 487 Final Project</title>
        <link rel="stylesheet" href="index.css">
    </head>
    <body>
        <header>
            <!-- Placeholder. Might use custom logo. -->
            <h1 class="heading">CanvasCourse</h1>
            <h3 class="title">Login or sign-up below!</h3>
        </header>

        <!-- Container div. -->
        <div class="container">
            <!-- Login/Sign-up buttons. -->
            <div class="slider"></div>
            <div class="button">
                <button class="login-slider-button">Login</button>
                <button class="signup-slider-button">Sign-up</button>
            </div>

            <!-- Form div. -->
            <div class="form-section">
                <!-- Login form. -->
                 <form action="index.php" method="post">
                    <div class="login-container">
                        E-mail: <input type="email" class="email element" placeholder="Input registered e-mail." name="login_email">
                        Password: <input type="password" class="password element" placeholder="Input password." name="login_password">
                        <?php if(!$empty) {echo "<div class='error'>".$message."</div>";} ?>
                        <button class="login-submit">Login</button>
                    </div>
                 </form>

                 <!-- Sign-up form. -->
                  <form action="index.php" method="post">
                    <div class="signup-container">
                        E-mail: <input type="email" class="email element" placeholder="Input desired e-mail address." name="signup_email">
                        Password: <input type="password" class="password element" placeholder="Input desired password." name="signup_password">
                        Confirm password: <input type="password" class="password element" placeholder="" name="signup_password_confirm">
                        <?php if($error) {echo "<div class='error'>".$message."</div>";} ?>
                        Account type: <select name="type">
                            <option value="" disabled selected>Select desired account type.</option>
                            <?php 
                                $user_types = $conn->prepare("SELECT * FROM USER_TYPE WHERE `type_id` IN (1, 3)");
                                $user_types->execute();
                                while ($row = $user_types->fetch(PDO::FETCH_ASSOC)){
                                    echo "<option value='".$row['type_id']."'>".$row["type_description"]."</option>";
                                }
                            ?>
                        </select>
                        <input type="submit" name="submit" class="signup-submit"></input>
                    </div>
                </form>
            </div>
        </div>
        <script src="index.js"></script>
    </body>
</html>