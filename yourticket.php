<?php
session_start();
require_once './src/php/dbconn.php';
require_once './src/php/lang.php';

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('my_conversations', $translations, $lang) ?> - <?= t('site_title', $translations, $lang) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: {
              light: '#4F46E5',
              dark: '#3730A3'
            },
            secondary: {
              light: '#7C3AED',
              dark: '#6D28D9'
            }
          },
          gradientColorStops: theme => ({
            'gradient-start': theme('colors.primary.light'),
            'gradient-end': theme('colors.secondary.light'),
          })
        }
      }
    }
  </script>
  <style>
    :root {
      --gradient-start: #6366f1; /* indigo-500 */
      --gradient-end: #a5b4fc;   /* indigo-200 */
      --cta-bg: #f9fafb;         /* gray-50, plus doux */
      --cta-text: #312e81;       /* indigo-900 */
      --section-bg: #f9fafb;     /* gray-50, plus doux */
      --feature-bg: #f9fafb;     /* gray-50, plus doux */
      --body-bg: #f9fafb;        /* gray-50, plus doux */
    }

    .dark {
      --gradient-start: #3730A3;
      --gradient-end: #6D28D9;
      --cta-bg: #1e293b;         /* slate-800 */
      --cta-text: #fff;
      --section-bg: #1e293b;
      --feature-bg: #374151;     /* gray-700 */
      --body-bg: #111827;        /* gray-900 */
    }

    body {
      background: var(--body-bg) !important;
    }

    .gradient-bg {
      background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
    }

    .gradient-text {
      background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
      color: transparent;
    }

    .soft-section {
      background: var(--section-bg);
    }

    .feature-card {
      background: var(--feature-bg);
      border: 1px solid #e5e7eb; /* Ajout d'une bordure pour les cartes */
    }

    .cta-soft {
      background: var(--body-bg); /* même couleur que le body */
      color: var(--cta-text);
    }

    .cta-soft .cta-btn {
      background: var(--gradient-start);
      color: #fff;
    }

    .cta-soft .cta-btn:hover {
      opacity: 0.9;
    }
  </style>
</head>
<body class="min-h-screen transition-colors duration-200">
  <?php require_once './src/components/header.php'; ?>

  <main class="flex-grow py-12 px-4">
    <div class="container mx-auto max-w-7xl">
      <!-- En-tête -->
      <div class="mb-8 text-center">
        <h1 class="mb-2 inline-block">
          <i class="fas fa-ticket-alt mr-2 text-4xl align-middle text-indigo-500 dark:text-indigo-400"></i>
          <span class="gradient-text text-4xl font-bold align-middle"><?= t('my_conversations', $translations, $lang) ?></span>
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
          <?= t('my_conversations_subtitle', $translations, $lang) ?? 'Vos demandes de support en cours' ?>
        </p>
      </div>

      <!-- Notification -->
      <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg mb-6">
          <i class="fas fa-check-circle mr-2"></i><?= t('ticket_deleted', $translations, $lang) ?>
        </div>
      <?php endif; ?>

      <?php if (empty($tickets)): ?>
        <!-- Carte vide -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-12 text-center">
          <div class="inline-flex items-center justify-center w-16 h-16 mb-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-500 dark:text-indigo-400">
            <i class="fas fa-ticket-alt text-2xl"></i>
          </div>
          <h3 class="text-xl font-medium text-gray-900 dark:text-gray-200 mb-2">
            <?= t('no_tickets_title', $translations, $lang) ?>
          </h3>
          <p class="text-gray-500 dark:text-gray-400 mb-6">
            <?= t('no_tickets', $translations, $lang) ?>
          </p>
          <a href="create_ticket.php" class="gradient-bg text-white py-2.5 px-6 rounded-lg font-medium shadow-sm hover:opacity-90 transition">
            <i class="fas fa-plus mr-2"></i><?= t('create_ticket', $translations, $lang) ?>
          </a>
        </div>
      <?php else: ?>
        <!-- Grille de tickets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($tickets as $t): ?>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-transform duration-300 hover:-translate-y-1 group border border-gray-200 dark:border-gray-700">
              <!-- En-tête du ticket -->
              <div class="gradient-bg p-4 rounded-t-2xl">
                <div class="flex justify-between items-center">
                  <h2 class="text-lg font-bold text-white truncate">
                    <?= htmlspecialchars($t['Title']) ?>
                  </h2>
                  <?php if ($t['unread_count'] > 0): ?>
                    <span class="bg-white/20 text-white text-sm font-bold px-3 py-1 rounded-full">
                      <?= $t['unread_count'] ?> <?= t('unread', $translations, $lang) ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Corps du ticket -->
              <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    <i class="far fa-calendar-alt mr-2"></i>
                    <?= date('d/m/Y H:i', strtotime($t['Created_at'])) ?>
                  </p>
                  <?php if (in_array($user_role, ['admin', 'helper']) || $t['User_id'] == $user_id): ?>
                    <form method="post" class="inline-block"
                          onsubmit="return confirm('<?= t('confirm_delete_ticket', $translations, $lang) ?>');">
                      <input type="hidden" name="delete_ticket_id" value="<?= $t['Id'] ?>">
                      <button type="submit"
                              class="text-red-500 hover:text-red-700 dark:hover:text-red-400 transition"
                              title="<?= t('delete', $translations, $lang) ?>">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>

                <a href="ticket_view.php?id=<?= $t['Id'] ?>"
                   class="gradient-bg text-white block text-center py-2.5 px-6 rounded-lg font-medium shadow-sm hover:opacity-90 transition">
                  <i class="fas fa-comments mr-2"></i>
                  <?= t('view_conversation', $translations, $lang) ?>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <?php require_once './src/components/footer.php'; ?>
</body>
</html>
