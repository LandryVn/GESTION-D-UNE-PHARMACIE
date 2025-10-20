<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["numMedoc"])) {
    $numMedoc = $_POST["numMedoc"];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT Design FROM MEDICAMENT WHERE numMedoc = ?");
        $stmt->execute([$numMedoc]);
        $design = $stmt->fetchColumn();

        $stmt = $pdo->prepare("INSERT INTO STATS_VENTES 
                             (numAchat, numMedoc, Design, quantite, prix_vente, date_vente)
                             VALUES ('supprime', ?, ?, 0, 0, NOW())");
        $stmt->execute([$numMedoc, $design]);
$stmt = $pdo->prepare("UPDATE DETAIL_ACHAT 
                      SET numMedoc = NULL 
                      WHERE numMedoc = ?");
$stmt->execute([$numMedoc]);


        $stmt = $pdo->prepare("DELETE FROM MEDICAMENT WHERE numMedoc = ?");
        $stmt->execute([$numMedoc]);

        $pdo->commit();
        header("Location: ../LOGIN/dashboard.php?success=Médicament supprimé ");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: ../LOGIN/dashboard.php?error=".urlencode($e->getMessage()));
        exit();
    }
}

header("Location: ../LOGIN/dashboard.php");
exit();
?>