<?php
session_start();
include '../Database.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(403);
    echo "Unauthorized.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data lama
    $stmt = $conn->prepare("SELECT username, about, profile_img FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($existingUsername, $existingAbout, $existingImg);
    $stmt->fetch();
    $stmt->close();

    // Ambil input baru, jika kosong, pakai data lama
    $username = trim($_POST['username'] ?? '') ?: $existingUsername;
    $about = trim($_POST['about'] ?? '') ?: $existingAbout;
    $profile_img = trim($_POST['profile_img'] ?? '') ?: $existingImg;

    // Update
    $stmt = $conn->prepare("UPDATE users SET username = ?, about = ?, profile_img = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $about, $profile_img, $user_id);

    if ($stmt->execute()) {
        header("Location: User.php");
        exit();
    } else {
        http_response_code(500);
        echo "Gagal memperbarui profil.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../asset/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="edit.css">
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
            <form class="ask-form" id="askForm" method="post">
                <h2>You can ask your question here.</h2>

                <p>Username</p>
                <input type="text" id="username" name="username" placeholder="Your Title...">

                <p>About</p>
                <input type="text" id="about" name="about" placeholder="Describe Yourself">

                <div class="editor-container">
                    <h2>Avatar</h2>
                    <div id="editor" contenteditable="true"></div>
                </div>
                
                <input type="hidden" name="profile_img" id="profileImgPath">
                <button type="submit" class="btnPost">Save Changes</button>
            </form>
        </main>
    </div>
    <script src="../edit.js"></script>
</body>
</html>