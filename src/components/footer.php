<?php
require_once __DIR__ . '/../php/dbconn.php';
require_once __DIR__ . '/../php/lang.php';

$lang = 'fr';
$theme = 'light';
if (isset($_SESSION['id'])) {
    $lang = getLanguage($db, $_SESSION['id']);
    $theme = getTheme($db, $_SESSION['id']);
}
?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const html = document.documentElement;
        const serverTheme = '<?= $theme ?>';
        console.log('Thème depuis PHP :', serverTheme); // Ajoutez cette ligne pour vérifier la valeur dans la console
        html.classList.toggle('dark', serverTheme === 'dark');
        if (localStorage.getItem('theme') !== serverTheme) {
            localStorage.setItem('theme', serverTheme);
        }
    });
</script>

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
    --gradient-start: #6366f1;
    --gradient-end: #a5b4fc;
    --cta-bg: #f9fafb;
    --cta-text: #312e81;
    --section-bg: #f9fafb;
    --feature-bg: #f9fafb;
    --body-bg: #f9fafb;
}

.dark {
    --gradient-start: #3730A3;
    --gradient-end: #6D28D9;
    --cta-bg: #1e293b;
    --cta-text: #fff;
    --section-bg: #1e293b;
    --feature-bg: #374151;
    --body-bg: #111827;
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
    border: 1px solid #e5e7eb;
}

.cta-soft {
    background: var(--body-bg);
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

<footer class="bg-gray-100 text-gray-800 py-12 dark:bg-gray-900 dark:text-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Column 1 - About -->
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <i class="fas fa-ticket-alt text-xl"></i>
                    <h2 class="text-xl font-bold"><?= t('site_title', $translations, $lang) ?></h2>
                </div>
                <p class="text-gray-400 dark:text-gray-300">
                    <?= t('footer_description', $translations, $lang) ?>
                </p>
            </div>

            <!-- Column 2 - Quick Links -->
            <div>
                <h3 class="font-bold text-lg mb-4"><?= t('quick_links', $translations, $lang) ?></h3>
                <ul class="space-y-2 text-gray-400 dark:text-gray-300">
                    <li><a href="index.php" class="hover:text-white dark:hover:text-gray-100 transition"><?= t('home', $translations, $lang) ?></a></li>
                    <li><a href="yourticket.php" class="hover:text-white dark:hover:text-gray-100 transition"><?= t('my_tickets', $translations, $lang) ?></a></li>
                    <li><a href="create_ticket.php" class="hover:text-white dark:hover:text-gray-100 transition"><?= t('new_ticket', $translations, $lang) ?></a></li>
                    <?php if ($isAdmin): ?>
                        <li><a href="dashboard.php" class="hover:text-white dark:hover:text-gray-100 transition"><?= t('dashboard', $translations, $lang) ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Column 3 - Resources -->
            <div>
                <h3 class="font-bold text-lg mb-4"><?= t('resources', $translations, $lang) ?></h3>
                <ul class="space-y-2 text-gray-400 dark:text-gray-300">
                    <li><a href="#" class="hover:text-white dark:hover:text-gray-100 transition"><?= t('faq', $translations, $lang) ?></a></li>
                    <li><a href="#" class="hover:text-white dark:hover:text-gray-100 transition"><?= t('user_guide', $translations, $lang) ?></a></li>
                    <li><a href="#" class="hover:text-white dark:hover:text-gray-100 transition"><?= t('contact', $translations, $lang) ?></a></li>
                </ul>
            </div>

            <!-- Column 4 - Contact -->
            <div>
                <h3 class="font-bold text-lg mb-4"><?= t('contact', $translations, $lang) ?></h3>
                <ul class="space-y-2 text-gray-400 dark:text-gray-300">
                    <li class="flex items-center space-x-2">
                        <i class="fas fa-envelope"></i>
                        <span>support@yourticket.com</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i class="fas fa-phone"></i>
                        <span>+33 1 23 45 67 89</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Copyright & Social -->
        <div class="border-t border-gray-700 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-400 dark:text-gray-300">&copy; <?= date('Y') ?> <?= t('site_title', $translations, $lang) ?>. <?= t('all_rights_reserved', $translations, $lang) ?></p>
            <div class="flex space-x-4 mt-4 md:mt-0">
                <a target="_blank" href="https://www.facebook.com/nicolas.houbion.14/" class="text-gray-400 dark:text-gray-300 hover:text-white dark:hover:text-gray-100 transition">
                    <i class="fab fa-facebook"></i>
                </a>
                <a target="_blank" href="https://www.linkedin.com/in/nicolas-houbion-ba6bb5204/" class="text-gray-400 dark:text-gray-300 hover:text-white dark:hover:text-gray-100 transition">
                    <i class="fab fa-linkedin"></i>
                </a>
                <a target="_blank" href="https://github.com/NicolasHoubion" class="text-gray-400 dark:text-gray-300 hover:text-white dark:hover:text-gray-100 transition">
                    <i class="fab fa-github"></i>
                </a>
            </div>
        </div>
    </div>
</footer>
