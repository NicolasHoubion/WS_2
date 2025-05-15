<?php
session_start();
require_once 'src/php/dbconn.php';
require_once 'src/php/lang.php';

// Récupération des préférences utilisateur
$user_id = $_SESSION['id'] ?? 0;
$lang = getLanguage($db, $user_id);
$theme = getTheme($db, $user_id);

http_response_code(404);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('error404', $translations, $lang) ?> - <?= t('site_title', $translations, $lang) ?></title>
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
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        .dark .gradient-text {
            background: linear-gradient(135deg, #3730A3 0%, #6D28D9 100%);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }
        .gradient-text {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <?php require_once 'src/components/header.php'; ?>

    <main class="flex-grow flex items-center justify-center p-6">
        <div class="max-w-4xl w-full text-center bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-12 border border-gray-200 dark:border-gray-700 transform hover:scale-[1.01] transition-all duration-300">
            <div class="float-animation mb-12">
                <div class="gradient-text text-9xl font-black mb-8">404</div>
                <svg class="w-48 h-48 mx-auto text-gray-400 dark:text-gray-600 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <div class="space-y-6">
                <h2 class="text-4xl font-bold dark:text-gray-300">
                    <?= t('page_not_found', $translations, $lang) ?>
                </h2>
                
                <p class="text-xl text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto leading-relaxed">
                    <?= t('404_message', $translations, $lang) ?>
                </p>

                <div class="flex justify-center space-x-4">
                    <a href="/" class="gradient-bg text-white px-8 py-4 rounded-xl font-semibold shadow-lg hover:opacity-90 transition-transform hover:scale-105 flex items-center">
                        <i class="fas fa-home mr-2"></i>
                        <?= t('return_home', $translations, $lang) ?>
                    </a>
                    <a href="contact.php" class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300 px-8 py-4 rounded-xl font-semibold shadow-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-transform hover:scale-105 flex items-center">
                        <i class="fas fa-life-ring mr-2"></i>
                        <?= t('contact_support', $translations, $lang) ?>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php require_once 'src/components/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Animation au scroll
            const elements = document.querySelectorAll('.float-animation');
            elements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 100);
            });
        });
    </script>
</body>
</html>