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

$sql = "SELECT posts.*, users.username, users.profile_img 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE posts.user_id = ? 
        ORDER BY posts.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
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

      <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <form action="" method="get">
          <input type="text" class="search-form" placeholder="Search...">
        </form>
      </div>
    </header>
    
    <div class="container">
        <nav class="sidebar">
          <ul>
            <li><i class="fas fa-home"></i><a href="../dashboard/Dashboard.php">Home</a></li>
            <li><i class="fas fa-circle-user"></i><a href="../user/User.php">User</a></li>
            <li><i class="fas fa-question-circle"></i><a href="../question/Question.php">Question</a></li>
            <li><i class="fas fa-key"></i><a href="../keyword/Keyword.php">Keyword</a></li>
            <li><i class="fas fa-triangle-exclamation"></i><a href="../report/Report.php">Report</a></li>
          </ul>
        
          <form action="../logout.php" method="post">
            <button type="submit" class="btnLogout">Logout</button>
          </form>
        </nav>

        <main class="content">
          <div class="content-wrapper">
            <h1>Welcome, <?php echo htmlspecialchars($nama_user); ?>!</h1>
            <p>Find various answers to your questions, as well as answering other people's questions.</p><hr>
            <?php
                if ($result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                    echo '
                      <a href="../question/QuestionForm.php?id=' . $row["id"] . '" class="card-link" style="text-decoration: none; color: inherit;">
                      <div class="card">
                        <h2 class="card-title">' . htmlspecialchars($row["title"]) . '</h2>
                        <p class="card-preview">' . htmlspecialchars(substr($row["content"], 0, 150)) . '...</p>
                          <div class="card-footer">
                            <div class="user-info">
                              <img src="' . htmlspecialchars($row["profile_img"]) . '" class="user-avatar">
                              <span class="user-name">' . htmlspecialchars($row["username"]) . '</span>
                            </div>
                            <span class="card-time">' . date('d M Y', strtotime($row["created_at"])) . '</span>
                          </div>
                      </div>
                      </a>';
                    }
              } else {
                echo '<p>Tidak ada data.</p>';
              }
              $conn->close();
            ?>
            </div>
        </main>
    </div>
</body>
</html>