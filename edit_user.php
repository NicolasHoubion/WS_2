<?php
session_start();
require_once './src/php/dbconn.php';
require_once './src/php/lang.php';

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
    <title><?= t('edit_user', $translations, $lang) ?> - <?= t('site_title', $translations, $lang) ?></title>
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
    </style>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <?php require_once 'src/components/header.php'; ?>

    <main class="flex-grow py-12 px-4">
        <div class="container mx-auto max-w-4xl">
            <!-- En-tête -->
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-bold mb-2 dark:text-gray-200">
                    <i class="fas fa-user-edit mr-2 text-indigo-500"></i><?= t('edit_user', $translations, $lang) ?>
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    <?= t('edit_user_subtitle', $translations, $lang) ?? 'Modifier les informations de l\'utilisateur' ?>
                </p>
            </div>

            <!-- Messages d'erreur -->
            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg mb-6">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Formulaire -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <form method="POST" class="space-y-6">
                    <!-- Nom d'utilisateur -->
                    <div>
                        <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                            <i class="fas fa-user-circle mr-2 text-indigo-500"></i>
                            <?= t('username', $translations, $lang) ?>
                        </label>
                        <input 
                            type="text" 
                            name="username" 
                            value="<?= htmlspecialchars($user['Username']) ?>" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-300"
                        >
                    </div>

                    <!-- Rôle -->
                    <div>
                        <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                            <i class="fas fa-shield-alt mr-2 text-indigo-500"></i>
                            <?= t('role', $translations, $lang) ?>
                        </label>
                        <div class="relative">
                            <select 
                                name="role_id" 
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-300 appearance-none"
                            >
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['Id'] ?>" <?= $role['Id'] == $user['Role_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['Name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400 dark:text-gray-500">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="admin.php" class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                            <i class="fas fa-times mr-2"></i><?= t('cancel', $translations, $lang) ?>
                        </a>
                        <button 
                            type="submit" 
                            class="gradient-bg text-white px-6 py-2.5 rounded-lg font-medium shadow-lg hover:opacity-90 transition"
                        >
                            <i class="fas fa-save mr-2"></i><?= t('update', $translations, $lang) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php require_once 'src/components/footer.php'; ?>
</body>
</html>