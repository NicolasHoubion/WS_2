<?php
session_start();
require_once './src/php/dbconn.php';

if (!isset($_GET['id'])) {
    die("Aucun utilisateur spécifié.");
}

$userId = intval($_GET['id']);

// Supprimer les tickets liés à cet utilisateur
try {
    $db->beginTransaction();

    // Suppression des tickets liés à l'utilisateur
    $deleteTickets = $db->prepare("DELETE FROM Ticket WHERE User_id = ?");
    $deleteTickets->execute([$userId]);

    // Suppression de l'utilisateur
    $deleteUser = $db->prepare("DELETE FROM Users WHERE Id = ?");
    $deleteUser->execute([$userId]);

    $db->commit();

    header("Location: admin.php");
    exit;

} catch (PDOException $e) {
    $db->rollBack();
    echo "Erreur lors de la suppression : " . $e->getMessage();
}
?>
