<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../db/file_system.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Add Folder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_folder'])) {
    $folder_name = $_POST['folder_name'];
    $current_datetime = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO tbl_mainfolder (user_id, folder_name, date_created, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $folder_name, $current_datetime, $username);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error adding folder: " . $stmt->error;
    }
}

// Edit Folder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_folder'])) {
    $folder_id = $_POST['folder_id'];
    $folder_name = $_POST['folder_name'];

    $stmt = $conn->prepare("UPDATE tbl_mainfolder SET folder_name = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $folder_name, $folder_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Delete Folder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_folder'])) {
    $folder_id = $_POST['folder_id'];

    $stmt = $conn->prepare("DELETE FROM tbl_mainfolder WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $folder_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch Folders
$stmt = $conn->prepare("SELECT * FROM tbl_mainfolder WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$main_folders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            position: relative;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card .btn {
            position: absolute;
            top: 10px;
            font-size: 12px;
            padding: 5px 10px;
            border: none;
            background-color: transparent;
            color: #000;
            z-index: 10;
        }

        .btn-warning {
            right: 60px;
        }

        .btn-danger {
            right: 10px;
        }

        .card-body {
            padding-top: 30px;
        }

        .folder-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">File System</a>
        <div class="d-flex">
            <span class="navbar-text me-3">Welcome, <?= htmlspecialchars($username) ?></span>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="mb-4">Dashboard</h1>
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Add New Folder</button>

    <div class="row">
        <?php foreach ($main_folders as $folder): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $folder['id'] ?>">Edit</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="folder_id" value="<?= $folder['id'] ?>">
                        <button type="submit" name="delete_folder" class="btn btn-danger" onclick="return confirm('Delete this folder?')">Delete</button>
                    </form>
                    <a href="folder.php?id=<?= $folder['id'] ?>" class="folder-link">
                        <div class="card-body text-center">
                            <h4 class="card-title"><?= htmlspecialchars($folder['folder_name']) ?></h4>
                            <p class="card-text text-muted">Click to open</p>
                            <p class="card-text text-muted">Created: <?= htmlspecialchars($folder['date_created']) ?></p>
                            <p class="card-text text-muted">By: <?= htmlspecialchars($folder['created_by']) ?></p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?= $folder['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Folder</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="folder_id" value="<?= $folder['id'] ?>">
                            <input type="hidden" name="edit_folder" value="1">
                            <div class="mb-2"><label>Folder Name</label><input type="text" name="folder_name" class="form-control" value="<?= htmlspecialchars($folder['folder_name']) ?>" required></div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Folder Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="add_folder" value="1">
                <div class="mb-2"><label>Folder Name</label><input type="text" name="folder_name" class="form-control" required></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Add Folder</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
