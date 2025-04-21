<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/LoginForm.html");
    exit;
}
?>

<h1>Selamat datang di Dashboard!</h1>
<a href="logout.php">Logout</a>
