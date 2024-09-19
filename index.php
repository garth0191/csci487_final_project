<?php
    require '/home/gnmcclur/connections/connect.php';

    //Get username and password from login form.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        //Login.
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
                        <?php if($empty) {echo "<div class='error'>".$message."</div>";} ?>
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