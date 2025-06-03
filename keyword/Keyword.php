<?php
include '../Database.php';
session_start();

$searchKeyword = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
  $searchKeyword = strtolower(trim($_GET['search']));
  $stmt = $conn->prepare("SELECT DISTINCT k.id, k.name 
                          FROM keywords k 
                          LEFT JOIN post_keywords qk ON k.id = qk.keyword_id
                          WHERE LOWER(k.name) LIKE CONCAT('%', ?, '%')");
  $stmt->bind_param("s", $searchKeyword);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $query = "SELECT DISTINCT k.id, k.name 
            FROM keywords k
            LEFT JOIN post_keywords qk ON k.id = qk.keyword_id";
  $result = mysqli_query($conn, $query);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_keyword'])) {
  $newKeyword = trim($_POST['new_keyword']);

  if (!empty($newKeyword)) {
    // Cek apakah keyword sudah ada
    $checkStmt = $conn->prepare("SELECT id FROM keywords WHERE name = ?");
    $checkStmt->bind_param("s", $newKeyword);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
    // Belum ada, tambahkan
    $stmt = $conn->prepare("INSERT INTO keywords (name) VALUES (?)");
    $stmt->bind_param("s", $newKeyword);

      if ($stmt->execute()) {
        header("Location: ../keyword/Keyword.php");
        exit();
      } else {
        echo "<script>alert('Gagal menambahkan keyword');</script>";
      }
      $stmt->close();
    } else {
      echo "<script>alert('Keyword sudah ada');</script>";
    }
    $checkStmt->close();
    } else {
      echo "<script>alert('Keyword tidak boleh kosong');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="Keyword.css">
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
          <input type="text" class="search-form" name="search" placeholder="Search...">
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
                <h1>Keywords</h1>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                  <form action="" method="post">
                    <input type="text" name="new_keyword" class="addKey" placeholder="Add Keywords">
                  </form>
                <?php endif; ?>
            </div>
            <div>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <a href="../question/Question.php?keyword=<?= urlencode($row['name']) ?>" class="keyword-label">
                    <?= htmlspecialchars($row['name']) ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </main>
    </div>
</body>
</html>