<?php
session_start();
require_once '../db/file_system.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 400, 'msg' => 'User not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle file upload
if (isset($_POST['add_file']) && isset($_FILES['upload_files'])) {
    $parent_folder_id = isset($_POST['parent_folder_id']) ? intval($_POST['parent_folder_id']) : 0;
    
    // Check if file was uploaded without errors
    if ($_FILES['upload_files']['error'] == 0) {
        $file_name = $_FILES['upload_files']['name'];
        $file_size = $_FILES['upload_files']['size'];
        $file_tmp = $_FILES['upload_files']['tmp_name'];
        $file_type = $_FILES['upload_files']['type'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file extensions
        $allowed_extensions = array("pdf", "xlsx", "xls", "docx");
        
        // Check if file extension is allowed
        if (in_array($file_ext, $allowed_extensions)) {
            // Create upload directory if it doesn't exist
            $upload_dir = "../uploads/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename to prevent overwriting
            $new_file_name = uniqid() . '_' . $file_name;
            $upload_path = $upload_dir . $new_file_name;
            
            // Move uploaded file to destination
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Save file information to database
                $current_datetime = date('Y-m-d H:i:s');
                $relative_path = "uploads/" . $new_file_name;
                
                $stmt = $conn->prepare("INSERT INTO tbl_files (user_id, parent_folder_id, file_name, file_path, file_size, file_type, date_uploaded, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissssss", $user_id, $parent_folder_id, $file_name, $relative_path, $file_size, $file_type, $current_datetime, $username);
                
                if ($stmt->execute()) {
                    echo json_encode(['status' => 200, 'msg' => 'File uploaded successfully']);
                } else {
                    echo json_encode(['status' => 400, 'msg' => 'Failed to save file information to database: ' . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['status' => 400, 'msg' => 'Failed to move uploaded file']);
            }
        } else {
            echo json_encode(['status' => 400, 'msg' => 'Invalid file extension. Allowed extensions: ' . implode(', ', $allowed_extensions)]);
        }
    } else {
        echo json_encode(['status' => 400, 'msg' => 'Error uploading file: ' . $_FILES['upload_files']['error']]);
    }
    exit();
}

// Return error if no action was taken
echo json_encode(['status' => 400, 'msg' => 'Invalid request']);
