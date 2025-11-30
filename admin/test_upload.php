<?php
session_start();
include dirname(__DIR__) . '/includes/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Not logged in");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Direct Upload Test</h2>
        <form action="upload_document.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Client ID</label>
                <input type="number" name="client_id" class="form-control" value="1" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Document Title</label>
                <input type="text" name="document_title" class="form-control" value="Test Document" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Document Type</label>
                <select name="document_type" class="form-control">
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">File</label>
                <input type="file" name="document_file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</body>
</html>