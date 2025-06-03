<?php
session_start();
include '../../Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['question_id'] ?? null;
    $verified = $_POST['verified'] ?? null;
    $userId = $_SESSION['user_id'] ?? null;

    if (!is_numeric($id) || !in_array($verified, ['0', '1']) || !$userId) {
        header("Location: Question.php");
        exit();
    }

    // Cek apakah user adalah pemilik pertanyaan
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($ownerId);
    $stmt->fetch();
    $stmt->close();

    if ($userId == $ownerId) {
        // Update status verified
        $stmt = $conn->prepare("UPDATE posts SET verified = ? WHERE id = ?");
        $stmt->bind_param("ii", $verified, $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../QuestionForm.php?id=$id");
    exit();
}
?>