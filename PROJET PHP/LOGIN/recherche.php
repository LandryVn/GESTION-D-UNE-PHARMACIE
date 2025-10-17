<?php
require_once('../MEDICAMENT/db.php');

header('Content-Type: application/json');

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $query = "SELECT * FROM MEDICAMENT 
              WHERE Design LIKE :search
              ORDER BY Design";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':search' => '%'.$searchTerm.'%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}



