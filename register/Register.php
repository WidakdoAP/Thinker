<?php
include '../Database.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password)) {
        die("Password harus 8 karakter dan mengandung huruf.");
    }

    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        die("Username sudah digunakan. Silakan pilih username lain.");
    }
    $checkStmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $defaultProfileImg = '../asset/3.png';
    $role = 'user';
    $about = 'Hi :3';
    if (isset($_GET['as']) && $_GET['as'] === 'admin' && isset($_SESSION['user_id'])) {
        // Cek apakah yang sedang login adalah admin
        $stmtCheckAdmin = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmtCheckAdmin->bind_param("i", $_SESSION['user_id']);
        $stmtCheckAdmin->execute();
        $result = $stmtCheckAdmin->get_result();
        $adminData = $result->fetch_assoc();
        if ($adminData && $adminData['role'] === 'admin') {
            $role = 'admin';
        }
        $stmtCheckAdmin->close();
    }

    $stmt = $conn->prepare("INSERT INTO users (username, password, role, profile_img) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hashedPassword, $role, $defaultProfileImg);
    $stmt->execute();
    header("Location: /pjbl/login/Login.php");
    exit;

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
    <link rel="stylesheet" href="Register.css">
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
            <li><i class="fas fa-circle-user"></i><a href="../user/User.php">User</a></li>
            <li><i class="fas fa-question-circle"></i><a href="../question/Question.php">Question</a></li>
            <li><i class="fas fa-key"></i><a href="../keyword/Keyword.php">Keyword</a></li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <li><i class="fas fa-triangle-exclamation"></i><a href="../report/Report.php">Report</a></li>
              <li><i class="fas fa-user-secret"></i><a href="../register/Register.php?as=admin">Add Admin</a></li>
            <?php endif; ?>
          </ul>
        </nav>

        <main class="content">
            <form class="register-form" id="registerForm" onsubmit="return validateForm()" method="post">
                <img src="../asset/logo.png" alt="Logo Website" class="logo">
              
                <input type="text" id="username" placeholder="Username" name="username">
              
                <div class="password-container">
                  <input type="password" id="password" placeholder="Password" name="password">
                  <i class="fa-solid fa-eye toggle-password" onclick="togglePassword()"></i>
                </div>
              
                <div class="button">
                  <button type="submit">Register</button>
                </div>
              
                <p id="error-message"></p>
            </form>         
        </main>
    </div>
    <script src="Register.js"></script>
</body>
</html>