<?php
session_start();
include 'Database.php';

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login.");
}

$post_id = $_POST['post_id'];
$content = $_POST['content'];
$user_id = $_SESSION['user_id'];

$plainText = strip_tags($content);
$plainText = trim($plainText);

if (empty($plainText)) {
    die("Komentar tidak boleh kosong.");
}

$sql = "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $post_id, $user_id, $content);

if ($stmt->execute()) {
    header("Location: /pjbl/question/QuestionForm.php?id=" . $post_id);
    exit();
} else {
    echo "Gagal menyimpan komentar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>