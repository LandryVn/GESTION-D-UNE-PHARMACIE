<?php
include("../MEDICAMENT/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numAchat = $_POST['numAchat'];
    $nomClient = $_POST['nomClient'];
    $dateAchat = $_POST['dateAchat'];
    $numMedocs = $_POST['numMedoc'];
    $quantites = $_POST['nbr'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ACHAT WHERE numAchat = ?");
        $stmt->execute([$numAchat]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Ce numéro d'achat existe déjà.");
        }

        $stmt = $pdo->prepare("INSERT INTO ACHAT (numAchat, nomClient, dateAchat) VALUES (?, ?, ?)");
        $stmt->execute([$numAchat, $nomClient, $dateAchat]);

        $recetteAchat = 0;
        $mois_annee = date('Y-m', strtotime($dateAchat));

        for ($i = 0; $i < count($numMedocs); $i++) {
            $numMedoc = $numMedocs[$i];
            $nbr = $quantites[$i];

            if (empty($numMedoc) || $nbr <= 0) continue;

            $stmt = $pdo->prepare("SELECT stock, Design, prix_unitaire FROM MEDICAMENT WHERE numMedoc = ?");
            $stmt->execute([$numMedoc]);
            $medoc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$medoc) {
                throw new Exception("Médicament non trouvé.");
            }

            if ($medoc['stock'] < $nbr) {
                throw new Exception("Stock insuffisant pour ".$medoc['Design']." (Stock: ".$medoc['stock'].")");
            }

            $montant = $nbr * $medoc['prix_unitaire'];
            $recetteAchat += $montant;

            $stmt = $pdo->prepare("INSERT INTO DETAIL_ACHAT (numAchat, numMedoc, nbr, prix_vente) VALUES (?, ?, ?, ?)");
            $stmt->execute([$numAchat, $numMedoc, $nbr, $medoc['prix_unitaire']]);

            $stmt = $pdo->prepare("
                INSERT INTO STATS_VENTES 
                (numAchat, numMedoc, Design, quantite, prix_vente, date_vente)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $numAchat,
                $numMedoc,
                $medoc['Design'],
                $nbr,
                $medoc['prix_unitaire'],
                $dateAchat
            ]);

            $stmt = $pdo->prepare("UPDATE DETAIL_ACHAT SET design_archive = ? WHERE numAchat = ? AND numMedoc = ?");
            $stmt->execute([$medoc['Design'], $numAchat, $numMedoc]);

            $stmt = $pdo->prepare("UPDATE MEDICAMENT SET stock = stock - ? WHERE numMedoc = ?");
            $stmt->execute([$nbr, $numMedoc]);
        }

        if ($recetteAchat > 0) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM stats_recette_totale");
            if ($stmt->fetchColumn() == 0) {
                $stmt = $pdo->prepare("INSERT INTO stats_recette_totale (montant_total) VALUES (?)");
                $stmt->execute([$recetteAchat]);
            } else {
                $stmt = $pdo->prepare("UPDATE stats_recette_totale SET montant_total = montant_total + ?");
                $stmt->execute([$recetteAchat]);
            }

            $stmt = $pdo->prepare("
                INSERT INTO STATS_RECETTES_MENSUELLES (mois_annee, recette_totale)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE recette_totale = recette_totale + ?
            ");
            $stmt->execute([$mois_annee, $recetteAchat, $recetteAchat]);
        }

        $pdo->commit();
        header("Location: achat.php?success=Achat ajouté avec succès");
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