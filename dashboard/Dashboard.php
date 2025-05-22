<?php
session_start();
include '../Database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../landing/landing.php");
    exit;
}

$id = $_SESSION['user_id'];
$query = $conn->query("SELECT username FROM users WHERE id = $id");
$user = $query->fetch_assoc();
$nama_user = $user['username'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="Dashboard.css">
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

          <form action="../logout.php" method="post">
            <button type="submit" class="btnLogout">Logout</button>
          </form>
        </nav>

        <main class="content">
          <h1>Welcome, <?php echo htmlspecialchars($nama_user); ?>!</h1>
        </main>
    </div>
    <script src="DashboardForm.js"></script>
</body>
</html>