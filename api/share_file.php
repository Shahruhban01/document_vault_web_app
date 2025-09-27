<?php
// api/share_file.php
function shareFile($file_id, $user_email, $permission = 'view') {
    // Check if user exists
    $user_query = "SELECT id FROM users WHERE email = :email";
    $user_stmt = $this->conn->prepare($user_query);
    $user_stmt->bindParam(':email', $user_email);
    $user_stmt->execute();
    
    if ($user_stmt->rowCount() > 0) {
        $user_row = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Create share record
        $share_query = "INSERT INTO file_shares (file_id, shared_with_user_id, permission_level) 
                       VALUES (:file_id, :user_id, :permission)";
        $share_stmt = $this->conn->prepare($share_query);
        $share_stmt->bindParam(':file_id', $file_id);
        $share_stmt->bindParam(':user_id', $user_row['id']);
        $share_stmt->bindParam(':permission', $permission);
        
        return $share_stmt->execute();
    }
    return false;
}
