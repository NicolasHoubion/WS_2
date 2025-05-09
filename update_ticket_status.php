<?php
session_start();
require_once 'src/php/dbconn.php';
require_once 'src/php/lang.php';

$userId = $_SESSION['id'] ?? 0;
$lang = getLanguage($db, $userId);
$theme = getTheme($db, $userId);

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? '';

// Vérification des paramètres
if (!isset($_GET['id'], $_GET['action']) || $_GET['action'] !== 'close') {
    $_SESSION['error_message'] = t('invalid_request', $translations, $lang);
    header("Location: yourticket.php");
    exit;
}

$ticketId = intval($_GET['id']);

// Vérification des permissions
if (!in_array($role, ['admin', 'helper'])) {
    $_SESSION['error_message'] = t('no_permission', $translations, $lang);
    header("Location: yourticket.php");
    exit;
}

try {
    // Vérification de l'existence du ticket
    $stmtCheck = $db->prepare("SELECT Id FROM Ticket WHERE Id = :ticketId AND Deleted_at IS NULL");
    $stmtCheck->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() === 0) {
        $_SESSION['error_message'] = t('ticket_not_found', $translations, $lang);
        header("Location: yourticket.php");
        exit;
    }

    // Fermeture du ticket
    $stmtClose = $db->prepare("UPDATE Ticket SET Deleted_at = NOW(), Updated_by = :userId WHERE Id = :ticketId");
    $stmtClose->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmtClose->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
    $stmtClose->execute();

    $_SESSION['success_message'] = t('ticket_closed', $translations, $lang);
    header("Location: yourticket.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['error_message'] = t('database_error', $translations, $lang) . ": " . $e->getMessage();
    header("Location: yourticket.php");
    exit;
}
?>