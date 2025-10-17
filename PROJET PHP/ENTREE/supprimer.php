<?php
include("../MEDICAMENT/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numEntree = $_POST['numEntree'];

    // Récupérer la quantité et le médicament associé
    $stmt = $pdo->prepare("SELECT stockEntree, numMedoc FROM ENTREE WHERE numEntree = ?");
    $stmt->execute([$numEntree]);
    $entree = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($entree) {
        $stockEntree = $entree['stockEntree'];
        $numMedoc = $entree['numMedoc'];

        // Supprimer l'entrée
        $stmt = $pdo->prepare("DELETE FROM ENTREE WHERE numEntree = ?");
        $stmt->execute([$numEntree]);

        // Mettre à jour le stock du médicament
        if ($numMedoc) {
            // 1. Vérifier si le stock deviendra négatif
            $stmt = $pdo->prepare("SELECT stock FROM MEDICAMENT WHERE numMedoc = ?");
            $stmt->execute([$numMedoc]);
            $currentStock = $stmt->fetchColumn();
            
            // 2. Calculer le nouveau stock
            $newStock = $currentStock - $stockEntree;
            
            // 3. Mettre à jour avec contrôle
            if ($newStock < 0) {
                $stmt = $pdo->prepare("UPDATE MEDICAMENT SET stock = 0 WHERE numMedoc = ?");
                $stmt->execute([$numMedoc]);
                
                // Optionnel : Logger cette correction
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