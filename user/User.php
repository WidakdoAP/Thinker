<?php
session_start();
require '../Database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/Login.php");
    exit();
}

// Apakah URL menyertakan username (untuk lihat profil orang lain)?
if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Mencegah akses karakter ilegal
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo "Username tidak valid.";
        exit();
    }

    // Ambil data berdasarkan username
    $stmt = $conn->prepare("SELECT username, profile_img, about FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
} else {
    // Kalau tidak ada parameter, lihat profil diri sendiri
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, profile_img, about FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$usernameForPosts = $row['username'];
$stmtPosts = $conn->prepare("
    SELECT p.id, p.title, p.content, p.created_at, u.username, u.profile_img, p.verified, 
    GROUP_CONCAT(k.name SEPARATOR ', ') AS keyword_names
    FROM posts p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN post_keywords pk ON p.id = pk.post_id
    LEFT JOIN keywords k ON pk.keyword_id = k.id
    WHERE u.username = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmtPosts->bind_param("s", $usernameForPosts);
$stmtPosts->execute();
$postsResult = $stmtPosts->get_result();


if (!$row) {
    echo "User not found.";
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="User.css">
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
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><i class="fas fa-triangle-exclamation"></i><a href="../report/Report.php">Report</a></li>
                    <li><i class="fas fa-user-secret"></i><a href="../register/Register.php?as=admin">Add Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <main class="content">
            <div class="content-wrapper">
                <div class="content-header">
                    <img src="<?= htmlspecialchars($row['profile_img']) ?>" class="profile-avatar" alt="Profile Image">
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($row['username']) ?></h2><hr>
                        <p><?= nl2br(htmlspecialchars($row['about'])) ?></p>
                    </div>
                    <div class="button-group">
                        <a href="edit.php" class="edit-button">Edit</a>
                        <a href="../logout.php" class="logout-button">Logout</a>
                    </div>
                </div>
                
                <div class="activity-section">
                    <h2>Activity</h2>
                    <?php
                    if ($postsResult->num_rows > 0) {
                    while ($row = $postsResult->fetch_assoc()) {
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
                            <div class="keyword-labels">';
                                $keywords = explode(',', $row['keyword_names']);
                                foreach ($keywords as $keyword) {
                                echo '<span class="keyword-tag">' . htmlspecialchars(trim($keyword)) . '</span>';
                                }
                            echo '</div>
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
                </div>
            </div>
        </main>
    </div>
</body>
</html>
