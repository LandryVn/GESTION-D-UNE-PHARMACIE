<?php
include("../MEDICAMENT/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['numAchat'])) {
    $numAchat = $_POST['numAchat'];
    $nomClient = trim($_POST['nomClient']);
    $dateAchat = $_POST['dateAchat'];
    $medicamentsExistants = $_POST['medicaments_existants'] ?? [];
    $supprimerMeds = $_POST['supprimer_meds'] ?? [];
    $nouveauxMeds = $_POST['nouveaux_medicaments'] ?? [];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT 
                DA.numMedoc,
                DA.nbr,
                DA.prix_vente,
                COALESCE(M.Design, DA.design_archive) AS Design,
                DA.design_archive,
                DATE_FORMAT(A.dateAchat, '%Y-m') AS ancien_mois
            FROM DETAIL_ACHAT DA
            JOIN ACHAT A ON DA.numAchat = A.numAchat
            LEFT JOIN MEDICAMENT M ON DA.numMedoc = M.numMedoc
            WHERE DA.numAchat = ?
        ");
        $stmt->execute([$numAchat]);
        $anciensDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("DELETE FROM STATS_VENTES WHERE numAchat = ?");
        $stmt->execute([$numAchat]);

        $stmt = $pdo->prepare("UPDATE ACHAT SET nomClient = ?, dateAchat = ? WHERE numAchat = ?");
        $stmt->execute([$nomClient, $dateAchat, $numAchat]);

        $nouvelleRecette = 0;
        $nouveau_mois = date('Y-m', strtotime($dateAchat));

        foreach ($anciensDetails as $ancien) {
            $numMedoc = $ancien['numMedoc'];
            
            if (in_array($numMedoc, $supprimerMeds)) {
                $stmt = $pdo->prepare("UPDATE MEDICAMENT SET stock = stock + ? WHERE numMedoc = ?");
                $stmt->execute([$ancien['nbr'], $numMedoc]);
                
                $stmt = $pdo->prepare("DELETE FROM DETAIL_ACHAT WHERE numAchat = ? AND numMedoc = ?");
                $stmt->execute([$numAchat, $numMedoc]);
                continue;
            }
            
            $newQte = $medicamentsExistants[$numMedoc] ?? $ancien['nbr'];
            $diffQte = $ancien['nbr'] - $newQte;
            
            $design = !empty($ancien['Design']) ? $ancien['Design'] : $ancien['design_archive'];
            
            $stmt = $pdo->prepare("UPDATE DETAIL_ACHAT SET nbr = ?, design_archive = ? WHERE numAchat = ? AND numMedoc = ?");
            $stmt->execute([$newQte, $design, $numAchat, $numMedoc]);

            $stmt = $pdo->prepare("
                INSERT INTO STATS_VENTES 
                (numAchat, numMedoc, Design, quantite, prix_vente, date_vente)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$numAchat, $numMedoc, $design, $newQte, $ancien['prix_vente'], $dateAchat]);

            if ($diffQte != 0) {
                $stmt = $pdo->prepare("UPDATE MEDICAMENT SET stock = stock + ? WHERE numMedoc = ?");
                $stmt->execute([$diffQte, $numMedoc]);
            }

            $nouvelleRecette += $newQte * $ancien['prix_vente'];
        }
        foreach ($nouveauxMeds as $nouveau) {
            $numMedoc = $nouveau['numMedoc'];
            $quantite = $nouveau['quantite'];
            
            $stmt = $pdo->prepare("SELECT stock, prix_unitaire, Design FROM MEDICAMENT WHERE numMedoc = ?");
            $stmt->execute([$numMedoc]);
            $medoc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$medoc) throw new Exception("Médicament non trouvé.");
            if ($medoc['stock'] < $quantite) throw new Exception("Stock insuffisant pour ".$medoc['Design']);
            
            $stmt = $pdo->prepare("INSERT INTO DETAIL_ACHAT (numAchat, numMedoc, nbr, prix_vente, design_archive) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$numAchat, $numMedoc, $quantite, $medoc['prix_unitaire'], $medoc['Design']]);
            
            $stmt = $pdo->prepare("
                INSERT INTO STATS_VENTES 
                (numAchat, numMedoc, Design, quantite, prix_vente, date_vente)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$numAchat, $numMedoc, $medoc['Design'], $quantite, $medoc['prix_unitaire'], $dateAchat]);
            
            $stmt = $pdo->prepare("UPDATE MEDICAMENT SET stock = stock - ? WHERE numMedoc = ?");
            $stmt->execute([$quantite, $numMedoc]);
            
            $nouvelleRecette += $quantite * $medoc['prix_unitaire'];
        }

        $ancienneRecette = array_sum(array_map(function($d) {
            return $d['nbr'] * $d['prix_vente'];
        }, $anciensDetails));
        
        if ($nouvelleRecette != $ancienneRecette) {
            $difference = $nouvelleRecette - $ancienneRecette;
            $stmt = $pdo->prepare("UPDATE stats_recette_totale SET montant_total = montant_total + ?");
            $stmt->execute([$difference]);

            $stmt = $pdo->prepare("
                INSERT INTO STATS_RECETTES_MENSUELLES (mois_annee, recette_totale)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE recette_totale = recette_totale + ?
            ");
            $stmt->execute([$nouveau_mois, $nouvelleRecette, $difference]);
        }

        $pdo->commit();
        header("Location: achat.php?success=Achat modifié avec succès");
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