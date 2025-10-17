<?php
include '../MEDICAMENT/db.php';  // Ancien chemin
// Connexion à la base de données

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numMedoc = $_POST['numMedoc'];
    $Design = $_POST['Design'];
    $prix_unitaire = $_POST['prix_unitaire'];

    // Vérifier si le médicament existe déjà
    $check_sql = "SELECT COUNT(*) FROM MEDICAMENT WHERE numMedoc = :numMedoc";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['numMedoc' => $numMedoc]);
    $count = $check_stmt->fetchColumn();
    
    if ($count > 0) {
        header("Location: ../LOGIN/dashboard.php?error=Ce numero de médicament existe déjà");
        exit;
    }
    // Insérer le médicament avec stock = 0
    $sql = "INSERT INTO MEDICAMENT (numMedoc, Design, prix_unitaire, stock) VALUES (:numMedoc, :Design, :prix_unitaire, 0)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            'numMedoc' => $numMedoc,
            'Design' => $Design,
            'prix_unitaire' => $prix_unitaire
        ]);
        header("Location: ../LOGIN/dashboard.php?success=Médicament ajouté avec succès");
        exit;
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>




