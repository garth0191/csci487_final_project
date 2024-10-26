<?php
    session_start();
    require '/home/gnmcclur/connections/connect.php';

    if(!isset($_SESSION['user_id'])){
        header('Location: index.php');
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Reset password.
        if (isset($_POST["password_reset_email"]) && $_POST["password_reset_email"] !== "") {
            $user_email = $_POST["password_reset_email"];

            try {
                $stmt = $conn->prepare("SELECT * FROM USER WHERE user_email = :user");
                $stmt->bindParam(":user", $user_email);
                $stmt->execute();

                $isUser = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($isUser) {
                    //Generate password reset token.
                    $token = bin2hex(random_bytes(16));
                    $resetLink = "https://turing.cs.olemiss.edu/~gnmcclur/reset_password.php?token=" . $token;

                    //Store token in database.
                    $stmt = $conn->prepare("UPDATE USER SET reset_token = :token WHERE user_email = :email");
                    $stmt->bindParam(":token", $token);
                    $stmt->bindParam(":email", $user_email);
                    $stmt->execute();

                    //Prepare and send e-mail to the address in the database.
                    $subject = "Request for Password Reset";
                    $message = "Click the following link to reset your password: " . $resetLink;
                    $headers = "From: no-reply@coursecanvas.com\r\n";
                    mail($user_email, $subject, $message, $headers);

                    $result = 01;
                    header("Location: index.php?result=$result");
                } else {
                    $result = 02;
                    header("Location: index.php?result=$result");
                }
            } catch (PDOException $e) {
                echo "Error retrieving email from database: " . $e->getMessage();
            }
        } else {
            $result = 03;
            header("Location: index.php?result=$result");
        }
    }
?>
