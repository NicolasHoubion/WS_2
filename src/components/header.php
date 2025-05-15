<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$isAdmin = isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [1, 3, 4]);

require_once __DIR__ . '/../php/dbconn.php';
require_once __DIR__ . '/../php/lang.php';

$lang = 'fr';
$theme = 'light';
$userImage = 'image_defaut.avif'; // Image par défaut

if (isset($_SESSION['id'])) {
  $lang = getLanguage($db, $_SESSION['id']);
  $theme = getTheme($db, $_SESSION['id']);

  // Récupérer l'image de profil de l'utilisateur
  $stmt = $db->prepare("SELECT Image FROM Users WHERE Id = :id");
  $stmt->bindParam(':id', $_SESSION['id'], PDO::PARAM_INT);
  $stmt->execute();
  $profileImage = $stmt->fetchColumn();

  if ($profileImage) {
    $userImage = $profileImage;
  }
}
?>
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

<header class="gradient-bg text-white shadow-lg">
  <div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center">
      <!-- Logo -->
      <div class="flex items-center space-x-2">
        <i class="fas fa-ticket-alt text-2xl"></i>
        <h1 class="text-2xl font-bold"><?= t('site_title', $translations, $lang) ?></h1>
      </div>

      <!-- Navigation Desktop -->
      <nav class="hidden md:flex space-x-8">
        <a href="index.php" class="font-medium hover:text-indigo-200 transition">
          <?= t('home', $translations, $lang) ?>
        </a>
        <a href="yourticket.php" class="font-medium hover:text-indigo-200 transition">
          <?= t('my_tickets', $translations, $lang) ?>
        </a>
        <a href="create_ticket.php" class="font-medium hover:text-indigo-200 transition">
          <?= t('new_ticket', $translations, $lang) ?>
        </a>
        <?php if ($isAdmin): ?>
          <a href="admin.php" class="font-medium hover:text-indigo-200 transition">
            <?= t('dashboard', $translations, $lang) ?>
          </a>
        <?php endif; ?>
      </nav>

      <!-- Profile & Mobile Menu -->
      <div class="flex items-center space-x-4">
        <?php if (isset($_SESSION['fname'])) : ?>
          <!-- Dropdown menu for user profile - Desktop -->
          <div class="hidden md:block relative">
            <button id="userDropdownButton" class="flex items-center space-x-2 focus:outline-none">
              <div class="h-8 w-8 rounded-full bg-white/30 overflow-hidden">
                <img src="/src/images/<?= htmlspecialchars($userImage) ?>" alt="Profile" class="h-full w-full object-cover">
              </div>
              <span><?= htmlspecialchars($_SESSION['fname']) ?></span>
              <i class="fas fa-chevron-down ml-1 text-xs"></i>
            </button>
            <!-- Dropdown content -->
            <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden">
              <a href="profil.php" class="block px-4 py-2 text-gray-800 hover:bg-indigo-500 hover:text-white transition">
                <i class="fas fa-user-circle mr-2"></i><?= t('profile', $translations, $lang) ?>
              </a>
              <a href="param.php" class="block px-4 py-2 text-gray-800 hover:bg-indigo-500 hover:text-white transition">
                <i class="fas fa-cog mr-2"></i><?= t('settings', $translations, $lang) ?>
              </a>
              <div class="border-t border-gray-200"></div>
              <a href="/src/php/logout.php" class="block px-4 py-2 text-gray-800 hover:bg-indigo-500 hover:text-white transition">
                <i class="fas fa-sign-out-alt mr-2"></i><?= t('logout', $translations, $lang) ?>
              </a>
            </div>
          </div>
        <?php else: ?>
          <a href="login.php" class="hidden md:block font-medium hover:text-indigo-200 transition">
            <?= t('login', $translations, $lang) ?>
          </a>
        <?php endif; ?>

        <button class="md:hidden text-2xl" id="mobile-menu-button">
          <i class="fas fa-bars"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden" id="mobile-menu">
    <div class="bg-white dark:bg-gray-800 h-full w-64 p-6">
      <div class="flex justify-between items-center mb-8">
        <div class="flex items-center space-x-2">
          <i class="fas fa-ticket-alt text-xl text-indigo-600 dark:text-indigo-400"></i>
          <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200"><?= t('site_title', $translations, $lang) ?></h2>
        </div>
        <button class="text-2xl text-gray-800 dark:text-gray-200" id="close-menu">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <nav class="space-y-4">
        <a href="index.php" class="block py-2 text-gray-800 dark:text-gray-200 font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition">
          <?= t('home', $translations, $lang) ?>
        </a>
        <a href="yourticket.php" class="block py-2 text-gray-800 dark:text-gray-200 font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition">
          <?= t('my_tickets', $translations, $lang) ?>
        </a>
        <a href="create_ticket.php" class="block py-2 text-gray-800 dark:text-gray-200 font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition">
          <?= t('new_ticket', $translations, $lang) ?>
        </a>
        <?php if ($isAdmin): ?>
          <a href="dashboard.php" class="block py-2 text-gray-800 dark:text-gray-200 font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition">
            <?= t('dashboard', $translations, $lang) ?>
          </a>
        <?php endif; ?>
        <?php if (isset($_SESSION['fname'])) : ?>
          <!-- Ajout de l'image de profil dans le menu mobile -->
          <div class="flex items-center py-2">
            <div class="h-8 w-8 rounded-full overflow-hidden mr-2">
              <img src="/src/images/<?= htmlspecialchars($userImage) ?>" alt="Profile" class="h-full w-full object-cover">
            </div>
            <span class="text-gray-800 dark:text-gray-200 font-medium"><?= htmlspecialchars($_SESSION['fname']) ?></span>
          </div>
          <a href="profil.php" class="block py-2 text-gray-800 dark:text-gray-200 font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition">
            <?= t('profile', $translations, $lang) ?>
          </a>
          <a href="param.php" class="block py-2 text-gray-800 dark:text-gray-200 font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition">
            <?= t('settings', $translations, $lang) ?>
          </a>
          <a href="/src/php/logout.php" class="block py-2 text-gray-800 dark:text-gray-200 font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition">
            <?= t('logout', $translations, $lang) ?>
          </a>
        <?php endif; ?>
      </nav>
    </div>
  </div>
</header>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.getElementById('mobile-menu-button');
    const closeButton = document.getElementById('close-menu');
    const mobileMenu = document.getElementById('mobile-menu');
    const userDropdownButton = document.getElementById('userDropdownButton');
    const userDropdown = document.getElementById('userDropdown');

    if (menuButton) {
      menuButton.addEventListener('click', () => {
        mobileMenu.classList.remove('hidden');
      });
    }

    if (closeButton) {
      closeButton.addEventListener('click', () => {
        mobileMenu.classList.add('hidden');
      });
    }

    // Gestion du dropdown utilisateur
    if (userDropdownButton && userDropdown) {
      userDropdownButton.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('hidden');
      });

      // Fermer le dropdown quand on clique ailleurs
      document.addEventListener('click', () => {
        userDropdown.classList.add('hidden');
      });

      userDropdown.addEventListener('click', (e) => {
        e.stopPropagation();
      });
    }

    // Gestion du thème
    const html = document.documentElement;
    const savedTheme = localStorage.getItem('theme') || '<?= $theme ?>';
    html.classList.toggle('dark', savedTheme === 'dark');
  });
</script>
