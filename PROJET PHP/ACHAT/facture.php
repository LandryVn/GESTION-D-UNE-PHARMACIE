<?php
require_once __DIR__ . '/../FPDF/fpdf.php'; 

// 1. Vérifier le numéro d'achat
if (!isset($_GET['numAchat'])) {
    die('Erreur : Numéro d\'achat manquant');
}

$numAchat = $_GET['numAchat'];

// 2. Connexion à la base de données 
require_once __DIR__ . '/../MEDICAMENT/db.php';

// 3. Récupérer les données
$query = "SELECT A.numAchat, A.nomClient, A.dateAchat, 
                 M.Design, DA.prix_vente, DA.nbr, 
                 (DA.nbr * DA.prix_vente) AS total_ligne
          FROM ACHAT A
          JOIN DETAIL_ACHAT DA ON A.numAchat = DA.numAchat
          JOIN MEDICAMENT M ON DA.numMedoc = M.numMedoc
          WHERE A.numAchat = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$numAchat]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    die('Achat non trouvé');
}

// 4. Créer le PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// En-tête
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Facture #' . $numAchat), 0, 1, 'C');

// Infos client
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Date : ' . $items[0]['dateAchat']), 0, 1);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nom du Client : ' . $items[0]['nomClient']), 0, 1);
$pdf->Ln(10);

// Tableau
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Désignation'), 1);
$pdf->Cell(30, 10, 'Prix (Ar)', 1, 0, 'R');
$pdf->Cell(20, 10, 'Nombre', 1, 0, 'C');
$pdf->Cell(30, 10, 'Total (Ar)', 1, 1, 'R');

$pdf->SetFont('Arial', '', 12);
$total = 0;

foreach ($items as $item) {
    $designation = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $item['Design']);
    $sousTotal = $item['prix_vente'] * $item['nbr']; 
    $total += $sousTotal;

    $pdf->Cell(100, 10, $designation, 1);
    $pdf->Cell(30, 10, number_format($item['prix_vente'], 0, ',', ' '), 1, 0, 'R'); 
    $pdf->Cell(20, 10, $item['nbr'], 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($sousTotal, 0, ',', ' ') .'Ar', 1, 1, 'R');
}

// Total
$xPosition = $pdf->GetX() + 150; 
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetX($xPosition);
$pdf->Cell(30, 10, number_format($total, 0, ',', ' ') . ' Ar', 1, 1, 'R');

// 5. Générer le PDF
$pdf->Output('I', 'facture_' . $numAchat . '.pdf');
?>