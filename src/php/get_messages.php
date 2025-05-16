<?php
session_start();
require_once 'dbconn.php';

$ticketId = intval($_GET['ticket_id'] ?? 0);
$userId = $_SESSION['id'] ?? 0;

$stmtMessages = $db->prepare(
    "SELECT m.*, u.Username, u.Image AS UserImage, r.Name AS RoleName
     FROM Messages m
     LEFT JOIN Users u ON m.Created_by = u.Id
     LEFT JOIN Roles r ON u.Role_id = r.Id
     WHERE m.Ticket_id = :ticketId
     ORDER BY m.Created_at ASC"
);
$stmtMessages->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
$stmtMessages->execute();
$messages = $stmtMessages->fetchAll(PDO::FETCH_ASSOC);

if ($messages) {
    foreach ($messages as $msg) {
        $isOwn = ($msg['Created_by'] == $userId);
        $alignClass = $isOwn ? 'justify-end' : 'justify-start';
        $bubbleClass = $isOwn ? 'bg-blue-100 dark:bg-blue-900/50 text-gray-900 dark:text-gray-300' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-300';
?>
        <div class="flex <?= $alignClass; ?> items-start gap-3">
            <?php if (!$isOwn): ?>
                <img src="src/images/<?= htmlspecialchars($msg['UserImage'] ?: 'image_defaut.avif'); ?>"
                    class="w-10 h-10 rounded-full object-cover">
            <?php endif; ?>

            <div class="max-w-[90%] p-4 rounded-lg shadow <?= $bubbleClass; ?>">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-semibold"><?= htmlspecialchars($msg['Username']); ?></span>
                    <span class="text-xs text-gray-500 dark:text-gray-400"><?= date('H:i', strtotime($msg['Created_at'])); ?></span>
                </div>
                <p class="whitespace-pre-line"><?= htmlspecialchars($msg['Message']); ?></p>
            </div>

            <?php if ($isOwn): ?>
                <img src="src/images/<?= htmlspecialchars($msg['UserImage'] ?: 'image_defaut.avif'); ?>"
                    class="w-10 h-10 rounded-full object-cover">
            <?php endif; ?>
        </div>
<?php
    }
} else {
    echo '<p class="italic text-gray-500 dark:text-gray-400">Aucun message pour ce ticket.</p>';
}
