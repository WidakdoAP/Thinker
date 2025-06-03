<?php
session_start();
include '../Database.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT posts.*, users.username, users.profile_img
                            FROM posts 
                            JOIN users ON posts.user_id = users.id
                            WHERE posts.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $imageStmt = $conn->prepare("SELECT file_path FROM images WHERE related_id = ? AND related_type = 'post'");
    $imageStmt->bind_param("i", $id);
    $imageStmt->execute();
    $imageResult = $imageStmt->get_result();
} else {
    echo "ID tidak ditemukan.";
    exit;
}

$commentsPerPage = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $commentsPerPage;

$commentStmt = $conn->prepare("SELECT comments.*, users.username, users.profile_img
                               FROM comments 
                               JOIN users ON comments.user_id = users.id 
                               WHERE comments.post_id = ?
                               ORDER BY comments.created_at DESC
                               LIMIT ? OFFSET ?");
$commentStmt->bind_param("iii", $id, $commentsPerPage, $offset);
$commentStmt->execute();
$comments = $commentStmt->get_result();

$totalStmt = $conn->prepare("SELECT COUNT(*) as total FROM comments WHERE post_id = ?");
$totalStmt->bind_param("i", $row['id']);
$totalStmt->execute();
$totalResult = $totalStmt->get_result()->fetch_assoc();
$totalComments = $totalResult['total'];
$totalPages = ceil($totalComments / $commentsPerPage);

$currentUserId = $_SESSION['user_id'] ?? null;
$currentUserRole = $_SESSION['role'] ?? 'guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Thinker</title>
    <link rel="stylesheet" href="QuestionForm.css">
</head>
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
            <div class="content-wrapper">
                <div class="header-btn">
                    <a onclick="window.location.href='Question.php'" class="icon-toggle back-button">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="dropdown-wrapper">
                        <a class="icon-toggle dropdown"><i class="fas fa-bars"></i></a>
                        <div class="dropdown-menu">
                            <?php if ($currentUserId == $row['user_id']): ?>
                                <form action="dropdown/verify.php" method="POST">
                                    <input type="hidden" name="question_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="verified" value="1">
                                    <button class="dropdown-item" type="submit">Verified</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($currentUserId == $row['user_id'] || $currentUserRole == 'admin'): ?>
                                <form action="dropdown/delete.php" method="POST">
                                    <input type="hidden" name="question_id" value="<?= $row['id'] ?>">
                                    <button class="dropdown-item" type="submit">Delete</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($currentUserId != $row['user_id']): ?>
                                <form action="dropdown/report.php" method="POST">
                                    <input type="hidden" name="question_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="reported" value="1">
                                    <button class="dropdown-item" type="submit">Report</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($currentUserRole == 'admin' && isset($row['reported']) && $row['reported'] == 1): ?>
                                <form action="dropdown/deny.php" method="POST">
                                    <input type="hidden" name="question_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="reported" value="0">
                                    <button class="dropdown-item" type="submit">Denied</button>
                                </form>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                </div>
                <div class="post-container">
                    <?php
                        echo '<div class="post-user">
                            <img src="' . htmlspecialchars($row["profile_img"]) . '" class="post-avatar">
                            <span class="post-name">' . htmlspecialchars($row["username"]) . '</span>
                            </div>';
                    ?>
                    <title><?= htmlspecialchars($row['title']) ?></title>
                    <div class="post-time-wrapper">
                        <div class="post-time"><?= date('j F Y, H:i', strtotime($row['created_at'])) ?></div>

                        <?php if ($row['verified']): ?>
                            <p class="verified">| Verified by <?= htmlspecialchars($row["username"]) ?></p>
                        <?php endif; ?>
                    </div>

                    <hr>
                    <div class="post-title"><?= htmlspecialchars($row['title']) ?></div>
                    <div class="post-content">
                        <?= $row['content'] ?>
                        <?php
                            while ($img = $imageResult->fetch_assoc()) {
                                echo '<img src="' . $img['file_path'] . '" style="max-width: 100%; margin-top: 10px;">';
                            }
                        ?>
                    </div>
                </div>

                <div class="editor-container">
                    <h2>Submit Answer</h2>
                    <div class="toolbar">
                        <button type="button" onclick="format('bold')" title="Bold"><i class="fas fa-bold"></i></button>
                        <button type="button" onclick="format('italic')" title="Italic"><i class="fas fa-italic"></i></button>
                    </div>
                    <div id="editor" contenteditable="true"></div>

                    <form method="post" action="../submit.php" onsubmit="return submitContent()">
                        <input type="hidden" name="comment_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="post_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="content" id="hiddenContent">
                        <button type="submit" class="btnSubmit">Submit</button>
                    </form>
                </div>

                <?php
                    if ($comments->num_rows > 0) {
                        echo '<div class="comment-container">';
                        echo '<div class="comment-list">';
                        while ($comment = $comments->fetch_assoc()) {
                            echo '<div class="comment" style="background-color: #fff; margin-bottom: 15px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 12px;">';
                            echo '<div class="comment-meta" style="display: flex; align-items: center; font-size: 0.9em; color: #000; flex-wrap: wrap;">';
                            echo '<img src="' . htmlspecialchars($comment["profile_img"]) . '" class="post-avatar" style="width: 36px; height: 36px; border-radius: 50%;">';
                            echo '<span class="post-name">' . htmlspecialchars($comment["username"]) . '</span>';
                            echo '</div>';
                            echo '<p style="font-size: 14px; color: #555; margin-bottom: 10px;">' . date('d M Y H:i', strtotime($comment['created_at'])) . '</p><hr>';
                            echo '<div class="comment-content" style="margin-top: 10px; font-size: 0.9rem; line-height: 1.5;">';
                            echo $comment['content'];
                            if (!empty($comment['file_path'])) {
                                echo '<img src="' . htmlspecialchars($comment['file_path']) . '" style="max-width: 100%;">';
                            }
                            echo '</div></div>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '<div class="pagination">';
                        for ($i = 1; $i <= $totalPages; $i++) {
                            $active = ($i == $page) ? 'style="font-weight: bold;"' : '';
                            echo "<a href='?id={$row['id']}&page=$i' $active>$i</a> ";
                        }
                    echo '</div>';
                ?>
            </div>
        </main>
    </div>
    <script src="../editor.js"></script>
    <script src="dropdown/dropdown.js"></script>
</body>
</html>