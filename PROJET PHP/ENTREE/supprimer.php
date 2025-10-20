<?php
include("../MEDICAMENT/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numEntree = $_POST['numEntree'];

    $stmt = $pdo->prepare("SELECT stockEntree, numMedoc FROM ENTREE WHERE numEntree = ?");
    $stmt->execute([$numEntree]);
    $entree = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($entree) {
        $stockEntree = $entree['stockEntree'];
        $numMedoc = $entree['numMedoc'];

        $stmt = $pdo->prepare("DELETE FROM ENTREE WHERE numEntree = ?");
        $stmt->execute([$numEntree]);

        if ($numMedoc) {
            $stmt = $pdo->prepare("SELECT stock FROM MEDICAMENT WHERE numMedoc = ?");
            $stmt->execute([$numMedoc]);
            $currentStock = $stmt->fetchColumn();
            
            $newStock = $currentStock - $stockEntree;
            
            if ($newStock < 0) {
                $stmt = $pdo->prepare("UPDATE MEDICAMENT SET stock = 0 WHERE numMedoc = ?");
                $stmt->execute([$numMedoc]);
                
                error_log("Stock corrigé à 0 pour le médicament $numMedoc (stock initial: $currentStock, soustraction: $stockEntree)");
            } else {
                $stmt = $pdo->prepare("UPDATE MEDICAMENT SET stock = ? WHERE numMedoc = ?");
                $stmt->execute([$newStock, $numMedoc]);
            }
        }

        header("Location: interface.php?success=Suppression reussie");
        exit();
    } else {
        header("Location: interface.php?error=entree non trouvee");
        exit();
    }
}
?>