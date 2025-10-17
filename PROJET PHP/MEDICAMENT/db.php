<?php
$host = "localhost";
$dbname = "pharmacie"; 
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
        $username, 
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Erreur DB: " . $e->getMessage());
    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
}
?>