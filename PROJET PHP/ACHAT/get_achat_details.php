<?php
include("../MEDICAMENT/db.php");

if (isset($_GET['numAchat'])) {
    $numAchat = $_GET['numAchat'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM ACHAT WHERE numAchat = ?");
        $stmt->execute([$numAchat]);
        $achat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$achat) {
            throw new Exception("Achat non trouvé");
        }
        
        $stmt = $pdo->prepare("
            SELECT DA.numMedoc, M.Design, DA.nbr, DA.prix_vente
            FROM DETAIL_ACHAT DA
            JOIN MEDICAMENT M ON DA.numMedoc = M.numMedoc
            WHERE DA.numAchat = ?
        ");
        $stmt->execute([$numAchat]);
        $medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode([
            'achat' => $achat,
            'medicaments' => $medicaments
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'numAchat non fourni']);
}
?>