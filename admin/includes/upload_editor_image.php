<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$upload_dir = "../uploads/posts/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    exit;
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','gif','webp'];

if (!in_array($ext, $allowed)) {
    http_response_code(400);
    exit;
}

$filename = "editor_" . time() . "_" . rand(1000,9999) . "." . $ext;
$target = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode([
        'location' => "/uploads/posts/" . $filename
    ]);
}
