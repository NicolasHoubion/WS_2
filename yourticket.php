<?php
session_start();
require_once './src/php/dbconn.php';
require_once './src/php/lang.php';
require_once './src/components/header.php';

// Récupération des préférences
$user_id = $_SESSION['id'] ?? 0;
$lang = getLanguage($db, $user_id);
$theme = getTheme($db, $user_id);

// Requête pour les tickets
$sql = "
  SELECT
    t.*,
    (SELECT COUNT(*) FROM Messages m WHERE m.Ticket_id = t.Id AND m.Created_by != :user_id) AS unread_count
  FROM Ticket t
  WHERE t.User_id = :user_id AND t.Deleted_at IS NULL
  ORDER BY t.Created_at DESC
";

try {
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die(t('database_error', $translations, $lang) . ": " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= t('my_conversations', $translations, $lang) ?></title>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex flex-col min-h-screen">
  <?php require_once './src/components/header.php'; ?>

  <main class="flex-grow max-w-6xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6 text-blue-600 dark:text-blue-400">
      🎫 <?= t('my_conversations', $translations, $lang) ?>
    </h1>

    <?php if (empty($tickets)): ?>
      <p class="italic text-gray-500 dark:text-gray-400">
        <?= t('no_tickets', $translations, $lang) ?>
      </p>
      <div class="mt-4">
        <a href="create_ticket.php" class="inline-block bg-blue-600 hover:bg-blue-700 dark:bg-blue-800 dark:hover:bg-blue-900 text-white px-4 py-2 rounded-lg transition">
          <?= t('create_ticket', $translations, $lang) ?>
        </a>
      </div>
    <?php else: ?>
      <ul class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($tickets as $t): ?>
          <li class="relative bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition p-6 group">
            <?php if ($t['unread_count'] > 0): ?>
              <span class="absolute top-4 right-4 bg-blue-600 text-white text-xs font-semibold px-2 py-1 rounded-full">
                <?= $t['unread_count'] ?>
              </span>
            <?php endif; ?>

            <a href="ticket_view.php?id=<?= $t['Id'] ?>" class="block h-full">
              <h2 class="text-xl font-semibold text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 transition mb-2">
                <?= htmlspecialchars($t['Title']) ?>
              </h2>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <?= t('created_on', $translations, $lang) ?> <?= date('d/m/Y H:i', strtotime($t['Created_at'])) ?>
              </p>

              <div class="mt-6">
                <button class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-800 dark:hover:bg-blue-900 text-white px-4 py-2 rounded-lg transition">
                  <?= t('view_conversation', $translations, $lang) ?>
                </button>
              </div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>

  <?php require_once './src/components/footer.php'; ?>
</body>
</html>