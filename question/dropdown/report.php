<?php
session_start();
include '../../Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_id'], $_POST['reported'])) {
    $id = intval($_POST['question_id']);
    $reported = intval($_POST['reported']);
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        // Redirect ke login jika user belum login
        header("Location: ../../login/Login.php");
        exit();
    }

    // Ambil pemilik post
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($ownerId);
        $stmt->fetch();
        $stmt->close();

        // Jika bukan pemilik, boleh report
        if ($userId != $ownerId) {
            $stmt = $conn->prepare("UPDATE posts SET reported = ? WHERE id = ?");
            $stmt->bind_param("ii", $reported, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: ../QuestionForm.php?id=$id");
    exit();
}
?>