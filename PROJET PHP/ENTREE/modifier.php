<?php
include("../MEDICAMENT/db.php");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numEntree = $_POST['numEntree'];
    $stockEntree = $_POST['stockEntree'];
    $dateEntree = $_POST['dateEntree'];

    $sqlOld = "SELECT stockEntree, numMedoc FROM ENTREE WHERE numEntree = ?";
    $stmtOld = $pdo->prepare($sqlOld);
    $stmtOld->execute([$numEntree]);
    $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

    if ($oldData) {
        $ancienneQuantite = $oldData['stockEntree'];
        $numMedoc = $oldData['numMedoc'];

        $sql = "UPDATE ENTREE SET stockEntree = ?, dateEntree = ? WHERE numEntree = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$stockEntree, $dateEntree, $numEntree]);

        if ($numMedoc) {
            $difference = $stockEntree - $ancienneQuantite;
            $sqlUpdate = "UPDATE MEDICAMENT SET stock = stock + ? WHERE numMedoc = ?";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([$difference, $numMedoc]);
        }

        header("Location: interface.php?success=Modification reussie");
        exit();
    } else {
        header("Location: interface.php?error=Entree non trouvee");
        exit();
    }
}
?>