<?php
session_start();
require_once './src/php/dbconn.php';
require_once './src/php/lang.php';
require_once './src/components/header.php';

if (!isset($_SESSION['id']) || !in_array($_SESSION['role_id'], [1, 3, 4])) {
    header("Location: login.php");
    exit;
}

$lang = getLanguage($db, $_SESSION['id']);
$theme = getTheme($db, $_SESSION['id']);

if (!isset($_GET['id'])) {
    die(t('no_user_specified', $translations, $lang));
}

$userId = intval($_GET['id']);

$stmt = $db->prepare("SELECT * FROM Users WHERE Id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die(t('user_not_found', $translations, $lang));
}

// Récupérer la liste des rôles
$rolesStmt = $db->query("SELECT Id, Name FROM Roles WHERE Status = 'Y'");
$roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $role_id = intval($_POST['role_id']);

    $updateStmt = $db->prepare("UPDATE Users SET Username = ?, Role_id = ? WHERE Id = ?");
    if($updateStmt->execute([$username, $role_id, $userId])){
        $_SESSION['success_message'] = t('user_updated', $translations, $lang);
        header("Location: admin.php");
        exit;
    } else {
        $_SESSION['error_message'] = t('update_error', $translations, $lang);
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('edit_user', $translations, $lang) ?></title>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
    <?php require_once 'src/components/header.php'; ?>

    <main class="max-w-4xl mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6 dark:text-gray-300"><?= t('edit_user', $translations, $lang) ?></h1>
        
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded border border-red-400 dark:border-red-600">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <form method="POST" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <div class="mb-6">
                <label class="block mb-4 font-semibold dark:text-gray-300"><?= t('username', $translations, $lang) ?></label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['Username']) ?>" 
                       class="w-full p-3 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <label class="block mb-4 font-semibold dark:text-gray-300"><?= t('role', $translations, $lang) ?></label>
                <select name="role_id" class="w-full p-3 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['Id'] ?>" <?= $role['Id'] == $user['Role_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['Name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded transition">
                    <?= t('update', $translations, $lang) ?>
                </button>
                <a href="admin.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded transition">
                    <?= t('cancel', $translations, $lang) ?>
                </a>
            </div>
        </form>
    </main>

    <?php require_once 'src/components/footer.php'; ?>
</body>
</html>