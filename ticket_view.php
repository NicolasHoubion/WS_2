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
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: {
              light: '#3b82f6',
              dark: '#1e40af'
            }
          }
        }
      }
    }
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
  <?php require_once 'src/components/header.php'; ?>

  <main class="flex-grow w-full max-w-5xl mx-auto p-8">
    <!-- Détails du ticket -->
    <header class="mb-6 border-b pb-4 dark:border-gray-700">
      <h1 class="text-3xl font-bold mb-1 dark:text-gray-300">🎫 Ticket #<?= htmlspecialchars($ticket['Id']); ?> - <?= htmlspecialchars($ticket['Title']); ?></h1>
      <p class="text-sm text-gray-600 dark:text-gray-400">
        Créé par <span class="font-medium text-blue-600 dark:text-blue-400"><?= htmlspecialchars($ticket['Username']); ?></span>
        le <?= htmlspecialchars($ticket['Created_at']); ?>
      </p>
    </header>

    <!-- Notifications -->
    <?php if ($errorMessage): ?>
      <div class="bg-red-100 dark:bg-red-900/20 border border-red-300 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($errorMessage); ?>
      </div>
    <?php endif; ?>
    <?php if ($successMessage): ?>
      <div class="bg-green-100 dark:bg-green-900/20 border border-green-300 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($successMessage); ?>
      </div>
    <?php endif; ?>

    <!-- Messages -->
    <section class="mb-10 space-y-4 px-4 py-10 min-h-[50vh]">
      <?php if ($messages): ?>
        <?php foreach ($messages as $msg): ?>
          <?php
            $isOwn = ($msg['Created_by'] == $_SESSION['id']);
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
        <?php endforeach; ?>
      <?php else: ?>
        <p class="italic text-gray-500 dark:text-gray-400">Aucun message pour ce ticket.</p>
      <?php endif; ?>
    </section>

    <!-- Formulaire -->
    <section class="mb-12">
      <form method="post" action="src/php/send_message.php" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticketId); ?>">
        <label for="message" class="block font-medium mb-2 dark:text-gray-300">Nouveau message</label>
        <textarea 
          name="message" 
          id="message" 
          rows="3" 
          required
          class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded resize-y focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4 dark:bg-gray-700 dark:text-gray-300"
          placeholder="Écrivez votre message..."
        ></textarea>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-800 dark:hover:bg-blue-900 text-white px-6 py-2 rounded transition">
          ✉️ Envoyer
        </button>
      </form>
    </section>

    <!-- Actions admin -->
    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'helper'])): ?>
      <section>
        <h2 class="text-2xl font-semibold mb-4 dark:text-gray-300">🔧 Actions administratives</h2>
        <a href="update_ticket_status.php?id=<?= htmlspecialchars($ticketId); ?>&action=close"
           class="inline-block bg-red-600 hover:bg-red-700 dark:bg-red-800 dark:hover:bg-red-900 text-white px-5 py-2 rounded transition">
          🚫 Fermer le Ticket
        </a>
      </section>
    <?php endif; ?>
  </main>

  <?php require_once 'src/components/footer.php'; ?>
</body>
</html>