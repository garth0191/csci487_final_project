<!-- Ends a user's session and redirects them to the login page (index). -->

<?php
    session_start();
    unset($_SESSION['user_email']);
    session_destroy();

    header("Location: index.php");
?>