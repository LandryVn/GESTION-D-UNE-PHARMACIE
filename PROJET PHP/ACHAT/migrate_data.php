<?php
require_once '../MEDICAMENT/db.php';

try {
    $pdo->beginTransaction();

    $pdo->exec("DELETE FROM STATS_VENTES WHERE numMedoc IS NULL OR quantite IS NULL");

    $stmt = $pdo->query("
        INSERT INTO STATS_VENTES (numAchat, numMedoc, Design, quantite, prix_vente, date_vente)
        SELECT 
            da.numAchat,
            da.numMedoc,
            m.Design,
            da.nbr,
            da.prix_vente,
            a.dateAchat
        FROM DETAIL_ACHAT da
        JOIN MEDICAMENT m ON da.numMedoc = m.numMedoc
        JOIN ACHAT a ON da.numAchat = a.numAchat
        WHERE 
            da.numMedoc IS NOT NULL
            AND m.Design IS NOT NULL
            AND da.nbr > 0
            AND da.prix_vente > 0
    ");

    $pdo->commit();
    echo "Migration réussie! " . $stmt->rowCount() . " enregistrements migrés.";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Erreur migration: " . $e->getMessage();
}
?>