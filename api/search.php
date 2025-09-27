<?php
// api/search.php
function searchFiles($query, $user_id) {
    $search_query = "SELECT * FROM files 
                    WHERE user_id = :user_id 
                    AND (original_name LIKE :query OR filename LIKE :query)
                    ORDER BY created_at DESC";
    $stmt = $this->conn->prepare($search_query);
    $search_term = '%' . $query . '%';
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':query', $search_term);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
