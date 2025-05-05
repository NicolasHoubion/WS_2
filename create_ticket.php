<?php
session_start();
require_once 'src/php/dbconn.php';
require_once 'src/php/lang.php';
require_once 'src/components/header.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$lang = getLanguage($db, $user_id);
$theme = getTheme($db, $user_id);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $message = trim($_POST["message"]);
    
    if (empty($title) || empty($message)) {
        $_SESSION["error_message"] = t('fill_all_fields', $translations, $lang);
    } else {
        try {
            // Création du ticket
            $stmtTicket = $db->prepare("
                INSERT INTO Ticket (Title, User_id, Created_by) 
                VALUES (:title, :user_id, :user_id)
            ");
            $stmtTicket->execute([
                ':title' => $title,
                ':user_id' => $user_id
            ]);
            
            $ticketId = $db->lastInsertId();
            
            // Ajout du premier message
            $stmtMsg = $db->prepare("
                INSERT INTO Messages (Ticket_id, Message, Created_by)
                VALUES (:ticket_id, :message, :user_id)
            ");
            $stmtMsg->execute([
                ':ticket_id' => $ticketId,
                ':message' => $message,
                ':user_id' => $user_id
            ]);
            
            $_SESSION["success_message"] = t('ticket_created', $translations, $lang);
            header("Location: ticket_view.php?id=" . $ticketId);
            exit;
            
        } catch (PDOException $e) {
            $_SESSION["error_message"] = t('creation_error', $translations, $lang) . ": " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('create_ticket', $translations, $lang) ?></title>
</head>
<body class="min-h-screen flex flex-col bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <?php require_once 'src/components/header.php'; ?>

    <main class="flex-1 p-6">
        <div class="max-w-7xl mx-auto min-h-[500px] py-12">
            <h2 class="text-3xl font-bold mb-6 dark:text-gray-300">
                <?= t('create_ticket', $translations, $lang) ?>
            </h2>

            <?php if (isset($_SESSION["error_message"])): ?>
                <div class="bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 p-4 mb-6 rounded">
                    <?= htmlspecialchars($_SESSION["error_message"]) ?>
                </div>
                <?php unset($_SESSION["error_message"]); ?>
            <?php endif; ?>

            <form method="post" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <div class="mb-6">
                    <label class="block font-semibold mb-2 dark:text-gray-300">
                        <?= t('title', $translations, $lang) ?>
                    </label>
                    <input type="text" 
                           name="title" 
                           id="title"
                           required
                           class="w-full p-3 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-6">
                    <label class="block font-semibold mb-2 dark:text-gray-300">
                        <?= t('message', $translations, $lang) ?>
                    </label>
                    <textarea 
                        name="message" 
                        id="message"
                        rows="5"
                        required
                        class="w-full p-3 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-blue-500"
                    ></textarea>
                </div>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">
                    <?= t('create_ticket_button', $translations, $lang) ?>
                </button>
            </form>
        </div>
    </main>

    <?php require_once 'src/components/footer.php'; ?>
</body>
</html>