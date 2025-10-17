<?php
include '../MEDICAMENT/db.php';  


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numMedoc = $_POST['numMedoc'];
    $Design = $_POST['Design'];
    $prix_unitaire = $_POST['prix_unitaire'];

    $sql = "UPDATE MEDICAMENT SET Design = :Design, prix_unitaire = :prix_unitaire WHERE numMedoc = :numMedoc";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['Design' => $Design, 'prix_unitaire' => $prix_unitaire, 'numMedoc' => $numMedoc]);
    header("Location: ../LOGIN/dashboard.php?success=Médicament modifié avec succès");
    exit;

}

// Récupérer les données du médicament
$numMedoc = $_GET['numMedoc'];
$sql = "SELECT * FROM MEDICAMENT WHERE numMedoc = :numMedoc";
$stmt = $pdo->prepare($sql);
$stmt->execute(['numMedoc' => $numMedoc]);
$medicament = $stmt->fetch(PDO::FETCH_ASSOC);
?>


