<?php
session_start();
require_once './src/php/dbconn.php';
require_once './src/php/lang.php';

$user_id = $_SESSION['id'] ?? 0;
$lang = getLanguage($db, $user_id);
$theme = getTheme($db, $user_id);

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = t('no_user_specified', $translations, $lang);
    header("Location: admin.php");
    exit;
}

$userId = intval($_GET['id']);

try {
    $db->beginTransaction();

    // Suppression des tickets liés à l'utilisateur
    $deleteTickets = $db->prepare("DELETE FROM Ticket WHERE User_id = ?");
    $deleteTickets->execute([$userId]);

    // Suppression de l'utilisateur
    $deleteUser = $db->prepare("DELETE FROM Users WHERE Id = ?");
    $deleteUser->execute([$userId]);

    $db->commit();

    $_SESSION['success_message'] = t('user_deleted', $translations, $lang);
    header("Location: admin.php");
    exit;

} catch (PDOException $e) {
    $db->rollBack();
    $_SESSION['error_message'] = t('delete_error', $translations, $lang) . ": " . $e->getMessage();
    header("Location: admin.php");
    exit;
}
?>
