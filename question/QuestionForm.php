<?php
include '../Database.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT posts.*, users.username, users.profile_img, images.file_path
                            FROM posts 
                            JOIN users ON posts.user_id = users.id
                            LEFT JOIN images ON images.post_id = posts.id
                            WHERE posts.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $imageStmt = $conn->prepare("SELECT file_path FROM images WHERE post_id = ?");
    $imageStmt->bind_param("i", $row['id']);
    $imageStmt->execute();
    $imageResult = $imageStmt->get_result();
} else {
    echo "ID tidak ditemukan.";
    exit;
}

$commentsPerPage = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $commentsPerPage;

$commentStmt = $conn->prepare("SELECT comments.*, users.username, users.profile_img, images.file_path
                               FROM comments 
                               JOIN users ON comments.user_id = users.id 
                               LEFT JOIN images ON images.comment_id = comments.id
                               WHERE comments.post_id = ? 
                               ORDER BY comments.created_at DESC
                               LIMIT ? OFFSET ?");
$commentStmt->bind_param("iii", $row['id'], $commentsPerPage, $offset);
$commentStmt->execute();
$comments = $commentStmt->get_result();

$totalStmt = $conn->prepare("SELECT COUNT(*) as total FROM comments WHERE post_id = ?");
$totalStmt->bind_param("i", $row['id']);
$totalStmt->execute();
$totalResult = $totalStmt->get_result()->fetch_assoc();
$totalComments = $totalResult['total'];
$totalPages = ceil($totalComments / $commentsPerPage);
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
                <li><i class="fas fa-triangle-exclamation"></i><a href="../report/Report.php">Report</a><li>
            </ul>
        </nav>
        
        <main class="content">
            <a href="../question/Question.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>

            <div class="post-container">
                <?php
                    echo '<div class="post-user">
                        <img src="' . htmlspecialchars($row["profile_img"]) . '" class="post-avatar">
                        <span class="post-name">' . htmlspecialchars($row["username"]) . '</span>
                        </div>';
                ?>
                <title><?= htmlspecialchars($row['title']) ?></title>
                <div class="post-time"><?= date('j F Y, H:i', strtotime($row['created_at'])) ?></div>
                <hr>
                <div class="post-title"><?= htmlspecialchars($row['title']) ?></div>
                <div class="post-content">
                    <?= nl2br(htmlspecialchars($row['content'])) ?>
                    <?php
                        while ($img = $imageResult->fetch_assoc()) {
                            echo '<img src="' . htmlspecialchars($img['file_path']) . '" style="max-width: 100%; margin-top: 10px;">';
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
                        echo '<div class="comment-meta" style="display: flex; align-items: center; font-size: 0.9em; color: #000; gap: 10px; flex-wrap: wrap;">';
                        echo '<img src="' . htmlspecialchars($comment["profile_img"]) . '" class="post-avatar" style="width: 32px; height: 32px; border-radius: 50%;">';
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

                    echo '<div class="pagination">';
                    for ($i = 1; $i <= $totalPages; $i++) {
                        $active = ($i == $page) ? 'style="font-weight: bold;"' : '';
                        echo "<a href='?id={$row['id']}&page=$i' $active>$i</a> ";
                    }
                    echo '</div>';
                }
            ?>
        </main>
    </div>
    <script src="../editor.js"></script>
</body>
</html>