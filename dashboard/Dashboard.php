<?php
session_start();
include '../Database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../landing/Landing.php");
    exit;
}

$search = $_GET['keyword'] ?? '';
$searchParam = '%' . $search . '%';

$id = $_SESSION['user_id'];
$query = $conn->query("SELECT username FROM users WHERE id = $id");
$user = $query->fetch_assoc();
$nama_user = $user['username'];

// Pagination setup
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Total count for pagination
$countSql = "
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
";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("isss", $id, $searchParam, $searchParam, $searchParam);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalPosts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalPosts / $limit);

// Fetch paginated data
$sql = "
SELECT posts.*, users.username, users.profile_img, GROUP_CONCAT(DISTINCT keywords.name SEPARATOR ', ') AS keyword_names
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
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssii", $id, $searchParam, $searchParam, $searchParam, $limit, $offset);
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
        <form method="get">
          <input type="text" name="keyword" class="search-form" placeholder="Search...">
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
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <li><i class="fas fa-triangle-exclamation"></i><a href="../report/Report.php">Report</a></li>
              <li><i class="fas fa-user-secret"></i><a href="../register/Register.php?as=admin">Add Admin</a></li>
            <?php endif; ?>
          </ul>
        </nav>

        <main class="content">
          <div class="content-wrapper">
            <div class="content-header">
              <h1>Welcome, <?php echo htmlspecialchars($nama_user); ?>!</h1><hr>
              <button type="button" class="btnAsk" onclick="window.location.href='../ask/Ask.php'">Ask</button>
            </div><hr>
            <?php
              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  $verifiedText = '';
                  if ($row['verified']) {
                    $verifiedText = '<span class="verified">Verified |</span>';
                  }
                  echo '
                  <div class="card" onclick="window.location.href=\'../question/QuestionForm.php?id=' . $row["id"] . '\'" style="cursor: pointer;">
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
                  </div>';
                }
              } else {
                echo '<p>Belum ada Pertanyaan.</p>';
              }
              $conn->close();
            ?>
            <div class="pagination">
              <?php if ($page > 1): ?>
                <a href="?keyword=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">&laquo; Prev</a>
              <?php endif; ?>

              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?keyword=<?= urlencode($search) ?>&page=<?= $i ?>" <?= ($i == $page) ? 'class="active"' : '' ?>><?= $i ?></a>
              <?php endfor; ?>

              <?php if ($page < $totalPages): ?>
                <a href="?keyword=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
              <?php endif; ?>
            </div>
          </div>
        </main>
    </div>
</body>
</html>