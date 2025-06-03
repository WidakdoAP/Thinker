<?php
$uploadDir = __DIR__ . "/uploads/images/";
$webPath = "/pjbl/uploads/images/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
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
        include 'Database.php';

        $related_id = $_POST['comment_id'] ?? $_POST['post_id'] ?? null;
        $related_type = isset($_POST['comment_id']) ? 'comment' : (isset($_POST['post_id']) ? 'post' : null);


        $stmt = $conn->prepare("INSERT INTO images (related_id, related_type, file_path) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $related_id, $related_type, $relativePath);
        $stmt->execute();
        $stmt->close();

        echo $relativePath;
    } else {
        http_response_code(500);
        echo "Gagal upload.";
    }
} else {
    http_response_code(400);
    echo "Tidak ada file.";
}
?>
