<?php
session_start();
require_once '../db/file_system.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle edit folder
if (isset($_POST['edit_folder']) && isset($_POST['folder_id']) && isset($_POST['folder_name'])) {
    $folder_id = intval($_POST['folder_id']);
    $folder_name = trim($_POST['folder_name']);
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    
    // Validate folder belongs to user
    $stmt = $conn->prepare("SELECT id FROM folders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $folder_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update folder name
        $update_stmt = $conn->prepare("UPDATE folders SET folder_name = ? WHERE id = ? AND user_id = ?");
        $update_stmt->bind_param("sii", $folder_name, $folder_id, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['message'] = "Folder updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update folder: " . $conn->error;
        }
        
        $update_stmt->close();
    } else {
        $_SESSION['error'] = "You don't have permission to edit this folder.";
    }
    $stmt->close();
    
    // Redirect back to the folder page
    if (isset($_POST['parent_id']) && !empty($_POST['parent_id'])) {
        header("Location: folder.php?id=" . intval($_POST['parent_id']));
    } else {
        header("Location: dashboard.php");
    }
    exit();
}


// Handle delete folder
if (isset($_POST['delete_folder']) && isset($_POST['folder_id'])) {
    $folder_id = intval($_POST['folder_id']);
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    
    // Validate folder belongs to user
    $stmt = $conn->prepare("SELECT id FROM folders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $folder_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Delete folder
        $delete_stmt = $conn->prepare("DELETE FROM folders WHERE id = ?");
        $delete_stmt->bind_param("i", $folder_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }
    $stmt->close();
    
    // Redirect back to the folder page
    if (isset($_POST['parent_id'])) {
        header("Location: folder.php?id=" . $_POST['parent_id']);
    } else {
        header("Location: ./dashboard.php");
    }
    exit();
}

// Handle add folder
if (isset($_POST['add_folder']) && isset($_POST['folder_name'])) {
    $folder_name = trim($_POST['folder_name']);
    $parent_folder_id = isset($_POST['parent_folder_id']) ? intval($_POST['parent_folder_id']) : null;
    
    $stmt = $conn->prepare("INSERT INTO folders (folder_name, user_id, parent_folder_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $folder_name, $user_id, $parent_folder_id);
    $stmt->execute();
    $stmt->close();
    
    // Redirect back to the folder page
    if ($parent_folder_id) {
        header("Location: folder.php?id=" . $parent_folder_id);
    } else {
        header("Location: ./dashboard.php");
    }
    exit();
}

// If no action was taken, redirect to dashboard
header("Location: ./dashboard.php");
exit();
