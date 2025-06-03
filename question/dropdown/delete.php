<?php
session_start();
include '../../Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['question_id'];
    $userId = $_SESSION['user_id'] ?? null;
    $role = $_SESSION['role'] ?? 'guest';

    // Ambil user pemilik pertanyaan
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($ownerId);
    $stmt->fetch();
    $stmt->close();

    if ($userId == $ownerId || $role == 'admin') {
        // Hapus gambar
        $deleteImages = $conn->prepare("DELETE FROM images WHERE post_id = ?");
        $deleteImages->bind_param("i", $id);
        $deleteImages->execute();
        $deleteImages->close();

        // Hapus komentar
        $deleteComments = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
        $deleteComments->bind_param("i", $id);
        $deleteComments->execute();
        $deleteComments->close();

        // Hapus pertanyaan
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Anda tidak memiliki izin untuk menghapus pertanyaan ini.";
        exit;
    }

    header("Location: ../Question.php");
    exit();
}
?>