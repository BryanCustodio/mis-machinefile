<?php
session_start();
require_once '../db/file_system.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$folder_id = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : "";

if (!$folder_id) {
    die("Folder not found.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Upload File</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Upload File</a>
            <a href="folder.php?id=<?= $folder_id ?>" class="btn btn-outline-light">Back</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h3>Upload File to Folder</h3>
        <form action="upload_action.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="folder_id" value="<?= $folder_id ?>">
            <div class="mb-3">
                <label class="form-label">File</label>
                <input type="file" name="uploaded_file" class="form-control" accept=".doc,.docx,.xls,.xlsx,.pdf" required>
                <input type="hidden" name="subfolder" class="form-control" placeholder="Enter a name for the file" required>
            </div>
            <div class="mb-3">
                <label class="form-label">File Info / Description</label>
                <textarea name="file_info" class="form-control" rows="4" placeholder="Enter a short description about this file" required></textarea>
            </div>
            <button type="submit" class="btn btn-success">Upload File</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>