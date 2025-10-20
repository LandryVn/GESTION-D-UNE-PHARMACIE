<?php
require_once '../MEDICAMENT/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['numAchat'])) {
    $numAchat = $_POST['numAchat'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ACHAT WHERE numAchat = ?");
        $stmt->execute([$numAchat]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception("Achat introuvable");
        }

        $stmt = $pdo->prepare("DELETE FROM DETAIL_ACHAT WHERE numAchat = ?");
        $stmt->execute([$numAchat]);

        $stmt = $pdo->prepare("DELETE FROM ACHAT WHERE numAchat = ?");
        $stmt->execute([$numAchat]);

        $pdo->commit();
        header("Location: achat.php?success=Achat supprimé");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: achat.php?error=".urlencode($e->getMessage()));
        exit();
    }
}

header("Location: achat.php");
exit();
?>