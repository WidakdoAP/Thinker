<?php
include '../Database.php';

$username = $_POST['username'];
$password = $_POST['password'];

if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password)) {
    die("Password harus minimal 8 karakter dan mengandung huruf.");
}

$checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$checkStmt->bind_param("s", $username);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    die("Username sudah digunakan. Silakan pilih username lain.");
}
$checkStmt->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashedPassword);

if ($stmt->execute()) {
    header("Location: ../login/LoginForm.html");
} else {
    echo "Gagal registrasi: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>