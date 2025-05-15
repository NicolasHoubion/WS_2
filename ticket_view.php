<?php
session_start();
require_once 'src/php/dbconn.php';
require_once 'src/php/lang.php';
require_once 'src/components/header.php';

// Vérifie si l'ID du ticket est fourni
if (!isset($_GET['id'])) {
    die("Ticket non spécifié.");
}
$ticketId = intval($_GET['id']);

// Récupération du ticket
try {
    $stmtTicket = $db->prepare(
        "SELECT t.*, u.Username, u.Image AS UserImage, r.Name AS RoleName
         FROM Ticket t
         LEFT JOIN Users u ON t.User_id = u.Id
         LEFT JOIN Roles r ON u.Role_id = r.Id
         WHERE t.Id = :ticketId"
    );
    $stmtTicket->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
    $stmtTicket->execute();
    $ticket = $stmtTicket->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération du ticket : " . $e->getMessage());
}
if (!$ticket) {
    die("Ticket non trouvé.");
}

// Vérifie si l'utilisateur a accès au ticket
$userId = $_SESSION['id'] ?? 0;

// Récupère le rôle de l'utilisateur connecté
try {
    $stmtRole = $db->prepare(
        "SELECT r.Name AS RoleName
         FROM Users u
         JOIN Roles r ON u.Role_id = r.Id
         WHERE u.Id = :userId"
    );
    $stmtRole->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmtRole->execute();
    $userRole = $stmtRole->fetchColumn();

    if (!$userRole) {
        die("Erreur : rôle introuvable pour l'utilisateur.");
    }

    $_SESSION['role'] = strtolower($userRole); // Met à jour la session avec le rôle en minuscule
} catch (PDOException $e) {
    die("Erreur lors de la récupération du rôle : " . $e->getMessage());
}

// Vérifie l'accès au ticket
$isCreator = ($ticket['User_id'] == $userId);
$isAdminOrHelper = in_array($_SESSION['role'], ['admin', 'helper', 'dev']);

if (!$isCreator && !$isAdminOrHelper) {
    header("HTTP/1.1 403 Forbidden");
    die("Vous n'avez pas l'autorisation d'accéder à ce ticket.");
}

// Récupération des messages
try {
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
} catch (PDOException $e) {
    die("Erreur lors de la récupération des messages : " . $e->getMessage());
}

// Récupération des préférences utilisateur
$lang = getLanguage($db, $userId);
$theme = getTheme($db, $userId);

// Messages d'erreur/succès
$successMessage = $_SESSION['success_message'] ?? '';
$errorMessage   = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ticket #<?= htmlspecialchars($ticket['Id']); ?></title>
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
    .gradient-bg {
      background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
    }
    .dark .gradient-bg {
      background: linear-gradient(135deg, #3730A3 0%, #6D28D9 100%);
    }
    .gradient-text {
      background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .dark .gradient-text {
      background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
  </style>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200 flex flex-col">
  <?php require_once 'src/components/header.php'; ?>

  <main class="flex-grow py-12 px-4">
    <div class="container mx-auto max-w-3xl">
      <!-- Header du ticket -->
      <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold mb-2 gradient-text inline-block">
          <i class="fas fa-ticket-alt mr-2"></i>Ticket #<?= htmlspecialchars($ticket['Id']); ?> - <?= htmlspecialchars($ticket['Title']); ?>
        </h1>
        <p class="text-gray-600 dark:text-gray-400 text-lg">
          Créé par <span class="font-medium text-blue-600 dark:text-blue-400"><?= htmlspecialchars($ticket['Username']); ?></span>
          le <?= htmlspecialchars($ticket['Created_at']); ?>
        </p>
      </div>

      <!-- Notifications -->
      <?php if ($errorMessage): ?>
        <div class="bg-red-100 dark:bg-red-900/20 border border-red-300 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded mb-6 text-center">
          <?= htmlspecialchars($errorMessage); ?>
        </div>
      <?php endif; ?>
      <?php if ($successMessage): ?>
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-300 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-6 text-center">
          <?= htmlspecialchars($successMessage); ?>
        </div>
      <?php endif; ?>

      <!-- Section messages -->
      <section class="mb-10">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 min-h-[40vh]">
          <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200 flex items-center">
            <i class="fas fa-comments mr-2 text-indigo-500 dark:text-indigo-400"></i>Messages
          </h2>
          <div class="space-y-10">
            <?php if ($messages): ?>
              <?php foreach ($messages as $msg): ?>
                <?php
                  $isOwn = ($msg['Created_by'] == $_SESSION['id']);
                  $alignClass = $isOwn ? 'justify-end' : 'justify-start';
                  $bubbleClass = $isOwn
                    ? 'bg-blue-100 dark:bg-blue-900/50 text-gray-900 dark:text-gray-300'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200';
                ?>
                <div class="flex <?= $alignClass; ?> items-start gap-3 my-2">
                  <?php if (!$isOwn): ?>
                    <img src="src/images/<?= htmlspecialchars($msg['UserImage'] ?: 'image_defaut.avif'); ?>"
                         class="w-10 h-10 rounded-full object-cover border-2 border-indigo-400 dark:border-indigo-600">
                  <?php endif; ?>
                  <div class="max-w-[80%] p-6 rounded-xl shadow <?= $bubbleClass; ?>">
                    <div class="flex items-center justify-between mb-1">
                      <span class="font-semibold"><?= htmlspecialchars($msg['Username']); ?></span>
                      <span class="text-xs text-gray-500 dark:text-gray-400"><?= date('H:i', strtotime($msg['Created_at'])); ?></span>
                    </div>
                    <p class="whitespace-pre-line"><?= htmlspecialchars($msg['Message']); ?></p>
                  </div>
                  <?php if ($isOwn): ?>
                    <img src="src/images/<?= htmlspecialchars($msg['UserImage'] ?: 'image_defaut.avif'); ?>"
                         class="w-10 h-10 rounded-full object-cover border-2 border-indigo-400 dark:border-indigo-600">
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="italic text-gray-500 dark:text-gray-400 text-center">Aucun message pour ce ticket.</p>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- Formulaire de réponse -->
      <section class="mb-12">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
          <form method="post" action="src/php/send_message.php">
            <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticketId); ?>">
            <label for="message" class="block font-medium mb-2 dark:text-gray-300 text-lg">Nouveau message</label>
            <textarea
              name="message"
              id="message"
              rows="3"
              required
              class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded resize-y focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4 dark:bg-gray-700 dark:text-gray-300"
              placeholder="Écrivez votre message..."
            ></textarea>
            <button type="submit" class="gradient-bg text-white py-2 px-6 rounded-lg font-medium shadow-lg hover:opacity-90 transition">
              <i class="fas fa-paper-plane mr-2"></i>Envoyer
            </button>
          </form>
        </div>
      </section>

      <!-- Actions admin -->
      <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'helper'])): ?>
        <section class="mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 text-center">
            <h2 class="text-2xl font-semibold mb-4 dark:text-gray-300 flex items-center justify-center">
              <i class="fas fa-tools mr-2 text-red-500"></i>Actions administratives
            </h2>
            <a href="update_ticket_status.php?id=<?= htmlspecialchars($ticketId); ?>&action=close"
               class="bg-red-600 hover:bg-red-700 dark:bg-red-800 dark:hover:bg-red-900 text-white px-6 py-3 rounded-lg font-medium shadow-lg transition inline-flex items-center">
              <i class="fas fa-ban mr-2"></i>Fermer le Ticket
            </a>
          </div>
        </section>
      <?php endif; ?>
    </div>
  </main>
  <?php require_once 'src/components/footer.php'; ?>
</body>
</html>
