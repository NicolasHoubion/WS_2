<?php
try {
    // Connexion à la base
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_0900_ai_ci'"
    ];
    $db = new PDO('mysql:host=localhost;dbname=ticket_233;charset=utf8mb4', 'root', 'root', $options);
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
    die();
}
