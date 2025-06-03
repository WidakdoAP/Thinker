<?php
session_start();
include '../../Database.php';

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $question_id = $_POST['question_id'] ?? null;

    if (!$question_id) {
        die("ID pertanyaan tidak valid.");
    }

    // Update kolom reported menjadi 0
    $stmt = $conn->prepare("UPDATE posts SET reported = 0 WHERE id = ?");
    $stmt->bind_param("i", $question_id);

    if ($stmt->execute()) {
        // Redirect kembali ke halaman pertanyaan atau halaman lain yang sesuai
        header("Location: ../Question.php");
        exit();
    } else {
        echo "Gagal memperbarui status denied: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    die("Metode tidak diizinkan.");
}
?>