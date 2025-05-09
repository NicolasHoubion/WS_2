<?php
require_once __DIR__ . '/../php/dbconn.php';
require_once __DIR__ . '/../php/lang.php';

$lang = 'fr';
if (isset($_SESSION['id'])) {
    $lang = getLanguage($db, $_SESSION['id']);
}
?>

<footer class="bg-gradient-to-r from-blue-600 to-blue-800 p-4 shadow-xl rounded-t-2xl mt-auto">
    <div class="max-w-7xl mx-auto text-center">
        <p class="text-white dark:text-gray-200 text-sm tracking-wide font-medium drop-shadow">
            © <?= date('Y') ?> YourTicket. <?= t('all_rights_reserved', $translations, $lang) ?>
        </p>
    </div>
</footer>
