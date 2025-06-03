<?php 
include '../Database.php'; 
session_start();

$postsPerPage = 10;

$keyword = $_GET['keyword'] ?? '';
$userId = $_GET['user_id'] ?? null;
$postPage = isset($_GET['post_page']) ? max(1, intval($_GET['post_page'])) : 1;
$postOffset = ($postPage - 1) * $postsPerPage;

$searchParam = '%' . $keyword . '%';
if (!empty($userId)) {
    $postStmt = $conn->prepare("
        SELECT posts.*, users.username, users.profile_img, 
            GROUP_CONCAT(DISTINCT keywords.name SEPARATOR ', ') AS keyword_names
        FROM posts
        JOIN users ON posts.user_id = users.id
        LEFT JOIN post_keywords ON posts.id = post_keywords.post_id
        LEFT JOIN keywords ON post_keywords.keyword_id = keywords.id
        WHERE posts.user_id = ?
          AND (
            posts.title LIKE ? 
            OR users.username LIKE ? 
            OR keywords.name LIKE ?
          )
        GROUP BY posts.id
        ORDER BY posts.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $postStmt->bind_param("isssii", $userId, $searchParam, $searchParam, $searchParam, $postsPerPage, $postOffset);
} else {
    $postStmt = $conn->prepare("
        SELECT posts.*, users.username, users.profile_img, 
            GROUP_CONCAT(DISTINCT keywords.name SEPARATOR ', ') AS keyword_names
        FROM posts
        JOIN users ON posts.user_id = users.id
        LEFT JOIN post_keywords ON posts.id = post_keywords.post_id
        LEFT JOIN keywords ON post_keywords.keyword_id = keywords.id
        WHERE posts.title LIKE ? 
           OR users.username LIKE ? 
           OR keywords.name LIKE ?
        GROUP BY posts.id
        ORDER BY posts.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $postStmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $postsPerPage, $postOffset);
}

$postStmt->execute();
$posts = $postStmt->get_result();

if (!empty($keyword)) {
    $searchParam = '%' . $keyword . '%';
    if (!empty($userId)) {
        $totalPostStmt = $conn->prepare("
            SELECT COUNT(DISTINCT posts.id) AS total
            FROM posts
            JOIN users ON posts.user_id = users.id
            LEFT JOIN post_keywords ON posts.id = post_keywords.post_id
            LEFT JOIN keywords ON post_keywords.keyword_id = keywords.id
            WHERE posts.user_id = ?
              AND (
                posts.title LIKE ? 
                OR users.username LIKE ? 
                OR keywords.name LIKE ?
              )
        ");
        $totalPostStmt->bind_param("isss", $userId, $searchParam, $searchParam, $searchParam);
    } else {
        $totalPostStmt = $conn->prepare("
            SELECT COUNT(DISTINCT posts.id) AS total
            FROM posts
            JOIN users ON posts.user_id = users.id
            LEFT JOIN post_keywords ON posts.id = post_keywords.post_id
            LEFT JOIN keywords ON post_keywords.keyword_id = keywords.id
            WHERE posts.title LIKE ? 
               OR users.username LIKE ? 
               OR keywords.name LIKE ?
        ");
        $totalPostStmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
    }
} else {
    $totalPostStmt = $conn->prepare("SELECT COUNT(*) as total FROM posts");
}


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

        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <form method="get">
                <input type="text" name="keyword" class="search-form" placeholder="Search...">
            </form>
        </div>
    </header>
    
    <div class="container">
        <nav class="sidebar">
        <ul>
          <li><i class="fas fa-house"></i><a href="../dashboard/Dashboard.php">Home</a></li>
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
            <div class="content-wrapper">
                <div class="content-header">
                    <h1 class="heading">Question</h1>
                    <button type="button" class="btnAsk" onclick="window.location.href='../ask/Ask.php'">Ask</button>
                </div><hr>
                <?php
                if ($posts->num_rows > 0) {
                    while ($row = $posts->fetch_assoc()) {
                        $verifiedText = '';
                        if ($row['verified']) {
                            $verifiedText = '<p class="verified">Verified |</p>';
                        }
                        echo '
                        <div class="card-link-wrapper" onclick="window.location.href=\'QuestionForm.php?id=' . $row["id"] . '\'" style="text-decoration: none; color: inherit; cursor: pointer;">
                            <div class="card">
                                <h2 class="card-title">' . htmlspecialchars($row["title"]) . '</h2>
                                <p class="card-preview">' . htmlspecialchars(substr($row["content"], 0, 225)) . '...</p>
                                <div class="card-footer">
                                    <div class="user-info">
                                        <a href="../user/User.php?username=' . urlencode($row["username"]) . '" onclick="event.stopPropagation();">
                                            <img src="' . htmlspecialchars($row["profile_img"]) . '" class="user-avatar">
                                        </a>
                                        <span class="user-name">' . htmlspecialchars($row["username"]) . '</span>
                                        <div class="keyword-labels">
                                            ';
                                            $keywords = explode(',', $row['keyword_names']);
                                            foreach ($keywords as $keyword) {
                                                echo '<span class="keyword-tag">' . htmlspecialchars(trim($keyword)) . '</span>';
                                            }
                                            echo '
                                        </div>
                                    </div>
                                    <div class="post-time-wrapper">
                                        ' . $verifiedText . '
                                        <span class="card-time">' . date('d M Y', strtotime($row["created_at"])) . '</span>
                                    </div>
                                </div>
                            </div>
                        </div>';
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