<?php
session_start();
include 'Database.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(403);
    echo "Unauthorized.";
    exit;
}

$uploadDir = __DIR__ . "/uploads/avatar/";
$webPath = "/pjbl/uploads/avatar/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!isset($_FILES["image"])) {
    http_response_code(400);
    echo "Tidak ada file.";
    exit;
}

if ($_FILES["image"]["error"] === UPLOAD_ERR_OK) {
    $tmp = $_FILES["image"]["tmp_name"];
    $name = basename($_FILES["image"]["name"]);
    $uniqueName = uniqid() . "_" . $name;
    $destination = $uploadDir . $uniqueName;

    if (strpos(mime_content_type($tmp), "image/") !== 0) {
        http_response_code(400);
        echo "File bukan gambar.";
        exit;
    }

    if (move_uploaded_file($tmp, $destination)) {
        $relativePath = $webPath . $uniqueName;

        // Update kolom profile_img di database
        $stmt = $conn->prepare("UPDATE users SET profile_img = ? WHERE id = ?");
        $stmt->bind_param("si", $relativePath, $user_id);
        $stmt->execute();
        $stmt->close();

        echo $relativePath;
    } else {
        http_response_code(500);
        echo "Gagal upload.";
    }
} else {
    http_response_code(400);
    echo "Upload error.";
}
