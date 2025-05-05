<?php
session_start();
require_once 'src/php/dbconn.php';
require_once 'src/php/lang.php';  // Fichier de traduction
require_once 'src/components/header.php';

// Récupère les préférences utilisateur
$user_id = $_SESSION['id'] ?? 0;
$lang = getLanguage($db, $user_id);
$theme = getTheme($db, $user_id);
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('site_title', $translations, $lang) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            light: '#3b82f6',  // blue-600
                            dark: '#1e40af'    // blue-800
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex flex-col bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200">

    <?php require_once 'src/components/header.php'; ?>

    <main class="flex-grow max-w-7xl mx-auto p-6">
        <!-- Messages flash -->
        <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true): ?>
            <div class="bg-green-100 dark:bg-green-900/20 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-6">
                ✅ <?= t('login_success', $translations, $lang) ?>
            </div>
            <?php unset($_SESSION['login_success']); ?>
            <?php endif; ?>

        <?php if (isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>
            <div class="bg-blue-100 dark:bg-blue-900/20 border border-blue-400 dark:border-blue-600 text-blue-700 dark:text-blue-300 px-4 py-3 rounded mb-6">
                🔒 <?= t('logout_success', $translations, $lang) ?>
            </div>
        <?php endif; ?>

        <!-- Contenu principal -->
        <h1 class="text-3xl font-bold mb-4"><?= t('welcome', $translations, $lang) ?></h1>
        <p class="text-lg mb-8"><?= t('welcome_subtext', $translations, $lang) ?></p>

        <!-- Cartes de fonctionnalités -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <h2 class="text-xl font-semibold mb-3"><?= t('create_ticket', $translations, $lang) ?></h2>
                <p class="mb-4"><?= t('create_ticket_help', $translations, $lang) ?></p>
                <a href="create_ticket.php" class="inline-block bg-primary-light dark:bg-primary-dark text-white px-4 py-2 rounded hover:opacity-90 transition">
                    <?= t('create', $translations, $lang) ?>
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <h2 class="text-xl font-semibold mb-3"><?= t('my_tickets', $translations, $lang) ?></h2>
                <p class="mb-4"><?= t('my_tickets_help', $translations, $lang) ?></p>
                <a href="yourticket.php" class="inline-block bg-primary-light dark:bg-primary-dark text-white px-4 py-2 rounded hover:opacity-90 transition">
                    <?= t('view', $translations, $lang) ?>
                </a>
            </div>
        </div>
        </main>
        
        <?php require_once 'src/components/footer.php'; ?>
        
        <!-- Script pour persister le thème -->
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Synchronise le thème entre les pages
            const html = document.documentElement;
            const theme = html.classList.contains('dark') ? 'dark' : 'light';
            localStorage.setItem('theme', theme);
        });
    </script>
</body>
</html>