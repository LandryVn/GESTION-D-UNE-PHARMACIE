<?php
include("../MEDICAMENT/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numEntree = $_POST['numEntree'];
    $numMedoc = $_POST['numMedoc'];
    $stockEntree = $_POST['stockEntree'];
    $dateEntree = $_POST['dateEntree'];

    try {
        // Vérifier si le médicament existe
        $stmt = $pdo->prepare("SELECT Design FROM MEDICAMENT WHERE numMedoc = ?");
        $stmt->execute([$numMedoc]);
        $medoc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$medoc) {
            header("Location: interface.php?error=Médicament non trouvé");
            exit();
        }
        
        // Vérifier si le numéro d'entrée existe déjà (CORRECTION ICI)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ENTREE WHERE numEntree = ?");
        $stmt->execute([$numEntree]);
        
        if ($stmt->fetchColumn() > 0) {
            header("Location: interface.php?error=Ce numéro d'entrée existe déjà");
            exit();
        }

        // Démarrer une transaction
        $pdo->beginTransaction();

        // Insérer l'entrée
        $stmt = $pdo->prepare("INSERT INTO ENTREE (numEntree, numMedoc, Design, stockEntree, dateEntree) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$numEntree, $numMedoc, $medoc['Design'], $stockEntree, $dateEntree]);

        // Mettre à jour le stock
        $stmt = $pdo->prepare("UPDATE MEDICAMENT SET stock = stock + ? WHERE numMedoc = ?");
        $stmt->execute([$stockEntree, $numMedoc]);

        // Valider la transaction
        $pdo->commit();

        header("Location: interface.php?success= Ajoutée avec succès");
        exit();

    } catch (PDOException $e) {
        // Annuler en cas d'erreur
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: interface.php?error=Erreur technique : " . urlencode($e->getMessage()));
        exit();
    }
}
?>