<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/LoginForm.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="DashboardForm.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Thinker</title>
</head>
<body>
    <header class="navbar">
      <div class="navbar-left">
        <img src="../asset/3.png" class="navbar-logo">
        <img src="../asset/2.png" class="navbar-logo">
      </div>

      <div>
        <form action="" method="get">
          <input type="text" placeholder="Search...">
        </form>
      </div>

      <div class="button-group">
            <?php
                if (!isset($_SESSION['user_id'])) {
                echo '
                    <button type="button" onclick="window.location.href=\'../login/LoginForm.html\'">Login</button>
                    <button type="button" onclick="window.location.href=\'../register/RegisterForm.html\'">Register</button>
                ';
                } else {
                echo '
                    <form action="../logout.php" method="post" style="display:inline;">
                    <button type="submit">Logout</button>
                    </form>
                ';
                }
            ?>
      </div>
    </header>
    
    <div class="container">
        <nav class="sidebar">
        <ul>
          <li><i class="fas fa-home"></i><a href="../dashboard/Dashboard.php">Home</a></li>
          <li><i class="fas fa-user"></i><a href="../user/UserForm.html">User</a></li>
          <li><i class="fas fa-question-circle"></i><a href="../question/Question.php">Question</a></li>
          <li><i class="fas fa-key"></i><a href="../keyword/KeywordForm.html">Keyword</a></li>
        </ul>
        </nav>

        <main class="content">
          <h1>Hello World!</h1>   
        </main>
    </div>
    <script src="DashboardForm.js"></script>
</body>
</html>