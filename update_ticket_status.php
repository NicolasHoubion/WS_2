<?php
session_start();
require_once 'src/php/dbconn.php';
require_once 'src/php/lang.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['id'];
$role = $_SESSION['role'] ?? '';
$lang = getLanguage($db, $userId);

// Vérification des paramètres
if (!isset($_GET['id'], $_GET['action']) || $_GET['action'] !== 'close') {
    echo t('invalid_request', $translations, $lang);
    exit;
}

$ticketId = intval($_GET['id']);

// Vérification des permissions
if (!in_array($role, ['admin', 'helper'])) {
    header("HTTP/1.1 403 Forbidden");
    echo t('no_permission', $translations, $lang);
    exit;
}

try {
    // Vérification de l'existence du ticket
    $stmtCheck = $db->prepare("SELECT Id FROM Ticket WHERE Id = :ticketId AND Deleted_at IS NULL");
    $stmtCheck->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() === 0) {
        echo t('ticket_not_found', $translations, $lang);
        exit;
    }

    // Fermeture du ticket
    $stmtClose = $db->prepare("UPDATE Ticket SET Deleted_at = NOW(), Updated_by = :userId WHERE Id = :ticketId");
    $stmtClose->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmtClose->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
    $stmtClose->execute();

    header("Location: yourticket.php");
    exit;
} catch (PDOException $e) {
    echo t('database_error', $translations, $lang) . ": " . $e->getMessage();
    exit;
}
?>