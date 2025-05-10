<?php
session_start();
require_once 'src/php/dbconn.php';
require_once 'src/php/lang.php';

// Récupération des préférences utilisateur
$user_id = $_SESSION['id'] ?? 0;
$lang = getLanguage($db, $user_id);
$theme = getTheme($db, $user_id);

// Définition du code HTTP
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('error_404', $translations, $lang) ?> - <?= t('site_title', $translations, $lang) ?></title>
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
<body class="min-h-screen flex flex-col bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200">

    <main class="flex-grow flex items-center justify-center p-6">
        <div class="max-w-2xl w-full text-center bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-blue-100 dark:border-blue-900 hover:shadow-2xl transition-shadow">
            <div class="mb-8">
                <h1 class="text-9xl font-extrabold text-red-600 dark:text-red-400 mb-4 animate-pulse">404</h1>
                <h2 class="text-3xl font-bold mb-4 dark:text-gray-300">
                    <?= t('page_not_found', $translations, $lang) ?>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
                    <?= t('404_message', $translations, $lang) ?>
                </p>
                <a href="/" class="inline-block bg-blue-600 dark:bg-blue-800 hover:bg-blue-700 dark:hover:bg-blue-900 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transition-transform hover:scale-105">
                    <?= t('return_home', $translations, $lang) ?>
                </a>
            </div>
            
            <!-- Illustration optionnelle -->
            <div class="mt-8 opacity-75">
                <svg class="w-32 h-32 mx-auto text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </main>

    <!-- Script pour le thème -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const html = document.documentElement;
            const theme = html.classList.contains('dark') ? 'dark' : 'light';
            localStorage.setItem('theme', theme);
        });
    </script>
</body>
</html>