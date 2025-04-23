<?php
session_start();
require_once '../db/file_system.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$parent_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Fetch subfolders
$stmt = $conn->prepare("SELECT * FROM tbl_subfolder WHERE user_id = ? AND parent_folder_id = ?");
$stmt->bind_param("ii", $user_id, $parent_id);
$stmt->execute();
$result = $stmt->get_result();
$subfolders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch files in current folder
$stmt = $conn->prepare("SELECT * FROM tbl_files WHERE user_id = ? AND parent_folder_id = ?");
$stmt->bind_param("ii", $user_id, $parent_id);
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check if we're in edit mode
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : null;

// Get success or error messages from query parameters
$success_msg = isset($_GET['success']) ? $_GET['success'] : '';
$error_msg = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Subfolders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <style>
        .folder-row td:first-child {
            cursor: pointer;
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
        <h2>Subfolders</h2>
        <a href="./dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addSubfolderModal">New Folder</button>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-12">
                <table id="subfolderTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Folder Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subfolders as $folder): ?>
                            <tr data-folder-id="<?= $folder['id'] ?>" class="<?= $edit_id != $folder['id'] ? 'folder-row' : '' ?>">
                                <td>
                                    <?php if ($edit_id == $folder['id']): ?>
                                        <!-- Edit form directly in the table -->
                                        <form method="POST" action="folder_actions.php" class="d-flex">
                                            <input type="hidden" name="edit_folder" value="1">
                                            <input type="hidden" name="folder_id" value="<?= $folder['id'] ?>">
                                            <input type="hidden" name="parent_id" value="<?= $parent_id ?>">
                                            <input type="text" name="folder_name" class="form-control form-control-sm me-2" value="<?= htmlspecialchars($folder['folder_name']) ?>" required>
                                            <button type="submit" class="btn btn-sm btn-success me-1">Save</button>
                                            <a href="folder.php?id=<?= $parent_id ?>" class="btn btn-sm btn-secondary">Cancel</a>
                                        </form>
                                    <?php else: ?>
                                        <?= htmlspecialchars($folder['folder_name']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($edit_id != $folder['id']): ?>
                                        <form method="POST" action="folder_actions.php" class="d-inline">
                                            <input type="hidden" name="folder_id" value="<?= $folder['id'] ?>">
                                            <input type="hidden" name="parent_id" value="<?= $parent_id ?>">
                                            <button type="submit" name="delete_folder" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                        <a href="folder.php?id=<?= $parent_id ?>&edit=<?= $folder['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Subfolder Modal -->
    <div class="modal fade" id="addSubfolderModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="folder_actions.php" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="folder_name" class="form-label">Folder Name</label>
                        <input type="text" class="form-control" id="folder_name" name="folder_name" required>
                    </div>
                    <input type="hidden" name="action" value="add_folder">
                    <input type="hidden" name="parent_id" value="<?= $parent_id ?>">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add Folder</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable for subfolder table
            $('#subfolderTable').DataTable({
                responsive: true,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    emptyTable: "No folders available"
                }
            });

            // Handle folder row click to navigate to folder
            $('.folder-row td:first-child').on('click', function() {
                const folderId = $(this).parent().data('folder-id');
                window.location.href = `folder.php?id=${folderId}`;
            });
            
            // Auto-close alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
            
            // Reset form when modal is closed
            $('#addSubfolderModal').on('hidden.bs.modal', function () {
                $(this).find('form')[0].reset();
            });
        });
    </script>
</body>
</html>
