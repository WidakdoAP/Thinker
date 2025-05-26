<?php 
include '../Database.php'; 

$postsPerPage = 10;
$postPage = isset($_GET['post_page']) ? max(1, intval($_GET['post_page'])) : 1;
$postOffset = ($postPage - 1) * $postsPerPage;

$postStmt = $conn->prepare("SELECT posts.*, users.username, users.profile_img 
                            FROM posts 
                            JOIN users ON posts.user_id = users.id 
                            ORDER BY posts.created_at DESC
                            LIMIT ? OFFSET ?");
$postStmt->bind_param("ii", $postsPerPage, $postOffset);
$postStmt->execute();
$posts = $postStmt->get_result();

$totalPostStmt = $conn->prepare("SELECT COUNT(*) as total FROM posts");
$totalPostStmt->execute();
$totalPostResult = $totalPostStmt->get_result()->fetch_assoc();
$totalPosts = $totalPostResult['total'];
$totalPostPages = ceil($totalPosts / $postsPerPage);
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
          <li><i class="fas fa-house"></i><a href="../dashboard/Dashboard.php">Home</a></li>
          <li><i class="fas fa-circle-user"></i><a href="../user/User.php">User</a></li>
          <li><i class="fas fa-question-circle"></i><a href="../question/Question.php">Question</a></li>
          <li><i class="fas fa-key"></i><a href="../keyword/Keyword.php">Keyword</a></li>
          <li><i class="fas fa-triangle-exclamation"></i><a href="../report/Report.php">Report</a><li>
        </ul>
        </nav>

        <main class="content">
            <div class="content-wrapper">
                <div class="content-header">
                    <h1 class="heading">Question</h1>
                    <button type="button" class="btnAsk" onclick="window.location.href='../ask/Ask.php'">Ask</button>
                </div><hr>
                <?php
                if ($posts->num_rows > 0) {
                    while ($row = $posts->fetch_assoc()) {
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
                echo '<div class="pagination">';
                    for ($i = 1; $i <= $totalPostPages; $i++) {
                        $active = ($i == $postPage) ? 'style="font-weight: bold;"' : '';
                        echo "<a href='?post_page=$i' $active>$i</a> ";
                    }
                echo '</div>';
                ?>
            </div>
        </main>
    </div>
</body>
</html>