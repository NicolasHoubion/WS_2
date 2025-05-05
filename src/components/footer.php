<?php
require_once __DIR__ . '/../php/dbconn.php'; // Chemin corrigé
require_once __DIR__ . '/../php/lang.php';   // Chemin corrigé

$lang = 'fr';
if (isset($_SESSION['id'])) {
    $lang = getLanguage($db, $_SESSION['id']);
}
?>

<footer class="bg-blue-600 p-4 shadow-lg mt-auto">
    <div class="max-w-7xl mx-auto text-center">
        <p class="text-white dark:text-gray-200 text-sm">
            © <?= date('Y') ?> YourTicket. <?= t('all_rights_reserved', $translations, $lang) ?>
        </p>
    </div>
</footer>
