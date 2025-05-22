<?php
session_start();
include '../Database.php';

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/Admin.php");
            } else {
                header("Location: ../dashboard/Dashboard.php");
            }
            exit;
        }
    } else {
        echo "Username tidak ditemukan.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="Login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Thinker</title>
</head>
<body>
    <header class="navbar">
        <div class="navbar-left">
          <img src="../asset/3.png" class="navbar-logo">
          <img src="../asset/2.png" class="navbar-logo">
        </div>
    </header>
    
    <div class="container">
        <nav class="sidebar">
        <ul>
            <li><i class="fas fa-home"></i><a href="../dashboard/Dashboard.php">Home</a></li>
            <li><i class="fas fa-user"></i><a href="../user/User.php">User</a></li>
            <li><i class="fas fa-question-circle"></i><a href="../question/Question.php">Question</a></li>
            <li><i class="fas fa-key"></i><a href="../keyword/Keyword.php">Keyword</a></li>
            <li><i class="fas fa-triangle-exclamation"></i><a href="../report/Report.php">Report</a><li>
        </ul>
        </nav>

        <main class="content">
            <form class="login-form" id="loginForm" onsubmit="return validateForm()" method="post">
                <img src="../asset/logo.png" alt="Logo Website" class="logo">
              
                <input type="text" id="username" placeholder="Username" name="username">
              
                <div class="password-container">
                  <input type="password" id="password" placeholder="Password" name="password">
                  <i class="fa-solid fa-eye toggle-password" onclick="togglePassword()"></i>
                </div>
              
                <div class="button-group">
                  <button type="submit">Login</button>
                  <button type="button" onclick="window.location.href='../register/Register.php'">Register</button>
                </div>
              
                <p id="error-message"></p>
            </form>         
        </main>
    </div>
    <script src="Login.js"></script>      
</body>
</html>