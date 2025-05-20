<?php
include '../Database.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT posts.*, users.username 
                            FROM posts 
                            JOIN users ON posts.user_id = users.id 
                            WHERE posts.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
} else {
    echo "ID tidak ditemukan.";
    exit;
}

$commentStmt = $conn->prepare("SELECT comments.content, comments.created_at, users.username 
                               FROM comments 
                               JOIN users ON comments.user_id = users.id 
                               WHERE comments.post_id = ? 
                               ORDER BY comments.created_at DESC");
$commentStmt->bind_param("i", $row['id']);
$commentStmt->execute();
$comments = $commentStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Thinker</title>
    <title><?= htmlspecialchars($row['title']) ?></title>
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
                <li><i class="fas fa-user"></i><a href="../user/UserForm.html">User</a></li>
                <li><i class="fas fa-question-circle"></i><a href="../question/QuestionForm.html">Question</a></li>
                <li><i class="fas fa-key"></i><a href="#">Keyword</a></li>
            </ul>
        </nav>

        <main class="content">
            <div class="post-container">
                <div class="post-user"><?= htmlspecialchars($row['username']) ?></div>
                <div class="post-time"><?= date('j F Y, H:i', strtotime($row['created_at'])) ?></div>
                <hr>
                <div class="post-title"><?= htmlspecialchars($row['title']) ?></div>
                <div class="post-content">
                    <?= nl2br(htmlspecialchars($row['content'])) ?>
                </div>
            </div>

            <div class="editor-container">
                <h2>Submit Answer</h2>
                <div class="toolbar">
                    <button type="button" onclick="format('bold')" title="Bold"><i class="fas fa-bold"></i></button>
                    <button type="button" onclick="format('italic')" title="Italic"><i class="fas fa-italic"></i></button>
                    <button type="button" onclick="insertImage()" title="Insert Image"><i class="fas fa-image"></i></button>
                    <button type="button" onclick="insertLink()" title="Insert Link"><i class="fas fa-link"></i></button>
                    <button type="button" onclick="insertCode()" title="Insert Code"><i class="fas fa-code"></i></button>
                </div>
                <div id="editor" contenteditable="true"></div>

                <form method="post" action="../submit.php" onsubmit="return submitContent()">
                    <input type="hidden" name="post_id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="content" id="hiddenContent">
                    <button type="submit" class=btnSubmit>Submit</button>
                </form>
            </div>

            <?php
                if ($comments->num_rows > 0) {
                    echo '<div class="comment-list">';
                    while ($comment = $comments->fetch_assoc()) {
                        echo '<div class="comment" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">';
                        echo '<div class="comment-meta" style="font-size: 0.9em; color: #555;">';
                        echo '<strong>' . htmlspecialchars($comment['username']) . '</strong>';
                        echo '<em>' . date('d M Y H:i', strtotime($comment['created_at'])) . '</em>';
                        echo '</div>';
                        echo '<div class="comment-content" style="margin-top: 5px;">';
                        echo $comment['content'];
                        echo '</div></div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p>Belum ada komentar.</p>';
                }
            ?>
        </main>
    </div>
    <script src="editor.js"></script>
</body>
</html>