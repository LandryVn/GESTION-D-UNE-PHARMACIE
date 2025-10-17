<?php
$host = "localhost"; // ou l'adresse du serveur MySQL
$dbname = "utilisateur";
$username = "root"; // Remplace par ton utilisateur MySQL
$password = ""; // Remplace par ton mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
