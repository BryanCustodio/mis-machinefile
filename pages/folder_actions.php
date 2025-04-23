<?php
session_start();
require_once '../db/file_system.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Add folder action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_folder') {
    $folder_name = trim($_POST['folder_name'] ?? '');
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

    if (!empty($folder_name)) {
        // Check for duplicate folder name under the same parent
        $stmt = $conn->prepare("SELECT id FROM tbl_subfolder WHERE user_id = ? AND folder_name = ? AND parent_folder_id ".($parent_id === null ? "IS NULL" : "= ?"));
        if ($parent_id === null) {
            $stmt->bind_param("is", $user_id, $folder_name);
        } else {
            $stmt->bind_param("isi", $user_id, $folder_name, $parent_id);
        }
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 0) {
            $stmt->close();
            
            // Insert new folder
            $stmt = $conn->prepare("INSERT INTO tbl_subfolder (user_id, folder_name, parent_folder_id) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $user_id, $folder_name, $parent_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    header("Location: folder.php?id=" . $parent_id);
    exit();
}

// Edit folder action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_folder'])) {
    $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
    $folder_name = trim($_POST['folder_name'] ?? '');
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

    if (!empty($folder_name) && $folder_id > 0) {
        // Check if folder belongs to user
        $stmt = $conn->prepare("SELECT id FROM tbl_subfolder WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $folder_id, $user_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->close();
            
            // Update folder name
            $stmt = $conn->prepare("UPDATE tbl_subfolder SET folder_name = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sii", $folder_name, $folder_id, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    header("Location: folder.php?id=" . $parent_id);
    exit();
}

// Delete folder action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_folder'])) {
    $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

    if ($folder_id > 0) {
        // Check if folder belongs to user
        $stmt = $conn->prepare("SELECT id FROM tbl_subfolder WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $folder_id, $user_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->close();
            
            // Delete the folder
            $stmt = $conn->prepare("DELETE FROM tbl_subfolder WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $folder_id, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    header("Location: folder.php?id=" . $parent_id);
    exit();
}

// If we get here, it's an invalid request
header("Location: folder.php?id=" . (isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null));
exit();
?>
