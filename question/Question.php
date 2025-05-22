<?php 
include '../Database.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="Question.css">
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
            <div class="content-wrapper">
                <h1 class="heading">Question</h1>
                <?php
                $sql = "SELECT posts.*, users.username, users.profile_img 
                        FROM posts 
                        JOIN users ON posts.user_id = users.id 
                        ORDER BY posts.created_at DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '
                        <a href="QuestionForm.php?id=' . $row["id"] . '" class="card-link" style="text-decoration: none; color: inherit;">
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
    <script src="QuestionForm.js"></script>
</body>
</html>