<?php
session_start();
include '../Database.php';

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login.");
}

$keyword_query = "SELECT * FROM keywords";
$keyword_result = mysqli_query($conn, $keyword_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $user_id = $_SESSION['user_id'];

    $plainText = strip_tags($content);
    $plainText = trim($plainText);

    if (empty($plainText)) {
        die("Komentar tidak boleh kosong.");
    }

    $sql = "INSERT INTO posts (user_id, title, content, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $title, $content);

    if ($stmt->execute()) {
    $post_id = $stmt->insert_id;

    if (!empty($_POST['keywords']) && is_array($_POST['keywords'])) {
        $keyword_stmt = $conn->prepare("INSERT INTO post_keywords (post_id, keyword_id) VALUES (?, ?)");
        foreach ($_POST['keywords'] as $keyword_id) {
            $keyword_stmt->bind_param("ii", $post_id, $keyword_id);
            $keyword_stmt->execute();
        }
        $keyword_stmt->close();
    }
        header("Location: ../question/Question.php");
        exit();
    }
    else {
        echo "Gagal menyimpan pertanyaan: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="Ask.css">
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
            <form class="ask-form" id="askForm" method="post" onsubmit="return submitContent()">
                <h2>You can ask your question here.</h2>

                <p>Title</p>
                <input type="text" id="title" name="title" placeholder="Your Title...">

                <p>Keywords</p>
                <select name="keywords[]" multiple size="5" style="width: 100%; padding: 8px;">
                    <?php while ($row = mysqli_fetch_assoc($keyword_result)) : ?>
                        <option value="<?= htmlspecialchars($row['id']) ?>">
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>


                <div class="editor-container">
                    <h2>Submit Question</h2>
                    <div class="toolbar">
                    <button type="button" onclick="format('bold')" title="Bold"><i class="fas fa-bold"></i></button>
                    <button type="button" onclick="format('italic')" title="Italic"><i class="fas fa-italic"></i></button>
                    </div>
                    <div id="editor" contenteditable="true"></div>
                </div>
                
                <input type="hidden" name="post_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="content" id="hiddenContent">
                <button type="submit" class="btnPost">Post</button>
            </form>
        </main>
    </div>
    <script src="../editor.js"></script>
</body>
</html>