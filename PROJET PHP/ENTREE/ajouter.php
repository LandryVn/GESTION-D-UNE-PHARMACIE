<?php
include("../MEDICAMENT/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numEntree = $_POST['numEntree'];
    $numMedoc = $_POST['numMedoc'];
    $stockEntree = $_POST['stockEntree'];
    $dateEntree = $_POST['dateEntree'];

    try {
        $stmt = $pdo->prepare("SELECT Design FROM MEDICAMENT WHERE numMedoc = ?");
        $stmt->execute([$numMedoc]);
        $medoc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$medoc) {
            header("Location: interface.php?error=Médicament non trouvé");
            exit();
        }
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ENTREE WHERE numEntree = ?");
        $stmt->execute([$numEntree]);
        
        if ($stmt->fetchColumn() > 0) {
            header("Location: interface.php?error=Ce numéro d'entrée existe déjà");
            exit();
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO ENTREE (numEntree, numMedoc, Design, stockEntree, dateEntree) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$numEntree, $numMedoc, $medoc['Design'], $stockEntree, $dateEntree]);

        $stmt = $pdo->prepare("UPDATE MEDICAMENT SET stock = stock + ? WHERE numMedoc = ?");
        $stmt->execute([$stockEntree, $numMedoc]);

        $pdo->commit();

        header("Location: interface.php?success= Ajoutée avec succès");
        exit();

    } catch (PDOException $e) {
   
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: interface.php?error=Erreur technique : " . urlencode($e->getMessage()));
        exit();
    }
}
?>