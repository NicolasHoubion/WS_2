<?php
session_start();
require_once './src/php/dbconn.php';
require_once './src/php/lang.php';
require_once './src/components/header.php';

// Récupération des préférences
$user_id = $_SESSION['id'] ?? 0;
$lang = getLanguage($db, $user_id);
$theme = getTheme($db, $user_id);

// Récupération du rôle de l'utilisateur connecté
$user_role = '';
if ($user_id) {
    try {
        $stmtRole = $db->prepare(
            "SELECT r.Name AS RoleName 
             FROM Users u
             JOIN Roles r ON u.Role_id = r.Id
             WHERE u.Id = :userId"
        );
        $stmtRole->bindParam(':userId', $user_id, PDO::PARAM_INT);
        $stmtRole->execute();
        $user_role = strtolower($stmtRole->fetchColumn() ?: '');
    } catch (PDOException $e) {
        $user_role = '';
    }
}

// Suppression du ticket si demandé (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ticket_id'])) {
    $ticketIdToDelete = (int)$_POST['delete_ticket_id'];
    // Vérifier que l'utilisateur a le droit de supprimer ce ticket
    $canDelete = false;
    if ($user_id && $ticketIdToDelete > 0) {
        $stmt = $db->prepare("SELECT User_id FROM Ticket WHERE Id = :id AND Deleted_at IS NULL");
        $stmt->bindParam(':id', $ticketIdToDelete, PDO::PARAM_INT);
        $stmt->execute();
        $ticketOwner = $stmt->fetchColumn();
        if (
            in_array($user_role, ['admin', 'helper']) ||
            $ticketOwner == $user_id
        ) {
            $canDelete = true;
        }
    }
    if ($canDelete) {
        $stmt = $db->prepare("UPDATE Ticket SET Deleted_at = NOW() WHERE Id = :id");
        $stmt->bindParam(':id', $ticketIdToDelete, PDO::PARAM_INT);
        $stmt->execute();
        // Optionnel : message de succès
        header("Location: yourticket.php?deleted=1");
        exit;
    }
}

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

    <?php if (isset($_GET['deleted'])): ?>
      <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
        <?= t('ticket_deleted', $translations, $lang) ?>
      </div>
    <?php endif; ?>

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
      <ul class="grid gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($tickets as $t): ?>
          <li class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl hover:shadow-2xl transition p-8 pt-12 group border border-blue-100 dark:border-blue-900 hover:-translate-y-1 hover:scale-105 duration-200">
            <?php if ($t['unread_count'] > 0): ?>
              <span class="absolute top-4 right-4 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg ring-2 ring-blue-200 dark:ring-blue-900">
                <?= $t['unread_count'] ?>
              </span>
            <?php endif; ?>

            <?php if (
                in_array($user_role, ['admin', 'helper']) ||
                $t['User_id'] == $user_id
            ): ?>
              <form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ? Cette action est irréversible.');" style="display:inline;">
                <input type="hidden" name="delete_ticket_id" value="<?= $t['Id'] ?>">
                <button type="submit"
                  title="<?= t('delete', $translations, $lang) ?>"
                  class="absolute top-3 left-4 text-gray-400 hover:text-red-700 dark:hover:text-red-400 transition text-2xl"
                  style="font-size: 1.3rem; background: none; border: none; padding: 0; cursor: pointer;">
                  🗑️
                </button>
              </form>
            <?php endif; ?>

            <a href="ticket_view.php?id=<?= $t['Id'] ?>" class="block h-full">
              <h2 class="text-xl font-bold text-blue-700 dark:text-blue-300 group-hover:text-blue-800 dark:group-hover:text-blue-200 transition mb-2">
                <?= htmlspecialchars($t['Title']) ?>
              </h2>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <?= t('created_on', $translations, $lang) ?> <?= date('d/m/Y H:i', strtotime($t['Created_at'])) ?>
              </p>

              <div class="mt-8">
                <button class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-800 dark:hover:bg-blue-900 text-white px-6 py-2 rounded-xl font-semibold shadow transition">
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