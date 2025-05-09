<?php
// Démarre la session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifie les permissions admin
$isAdmin = isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [1, 3, 4]);

require_once __DIR__ . '/../php/dbconn.php'; // Chemin corrigé
require_once __DIR__ . '/../php/lang.php';   // Chemin corrigé

$lang = 'fr';
if (isset($_SESSION['id'])) {
    $lang = getLanguage($db, $_SESSION['id']);
}
?>

<!-- Configuration Tailwind + Dark Mode -->
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: {
          light: '#3b82f6',  // Bleu Tailwind
          dark: '#1e40af'     // Bleu foncé
        }
      }
    }
  }
}
</script>

<!-- Barre de navigation -->
<nav class="bg-blue-600 p-4 shadow-xl rounded-b-2xl">
  <div class="max-w-7xl mx-auto flex justify-between items-center">
    
    <!-- Logo -->
    <h1 class="text-white dark:text-gray-100 text-2xl font-extrabold tracking-tight drop-shadow">
      <a href="index.php" class="hover:text-yellow-300 dark:hover:text-yellow-400 transition-colors">
        <?= t('YourTicket', $translations, $lang) ?>
      </a>
    </h1>

    <!-- Menu droit -->
    <ul class="flex space-x-6 text-white dark:text-gray-200 items-center">
      
      <!-- Lien Tickets -->
      <li>
        <a href="yourticket.php" class="hover:bg-blue-700 dark:hover:bg-blue-900 px-3 py-1 rounded-lg transition-colors font-medium">
          <?= t('Tickets', $translations, $lang) ?>
        </a>
      </li>
      
      <!-- Lien Paramètres -->
      <li>
        <a href="param.php" class="hover:bg-blue-700 dark:hover:bg-blue-900 px-3 py-1 rounded-lg transition-colors font-medium">
          <?= t('Settings', $translations, $lang) ?>
        </a>
      </li>

      <!-- Section Profil -->
      <?php if(isset($_SESSION['fname'])) : ?>
        <li class="relative group" id="profile-menu">
          
          <!-- Bouton Profil -->
          <button class="flex items-center space-x-2 hover:text-yellow-300 transition-colors focus:outline-none font-semibold">
            <span>👤 <?= htmlspecialchars($_SESSION['fname']) ?></span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>

          <!-- Sous-menu Déroulant -->
          <ul class="absolute right-0 mt-2 w-52 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200 invisible group-hover:visible z-20">
            <li>
              <a href="profil.php" class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-blue-100 dark:hover:bg-blue-800 rounded-t-xl">
                <?= t('Profile', $translations, $lang) ?>
              </a>
            </li>
            <li>
              <a href="src/php/logout.php" class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-blue-100 dark:hover:bg-blue-800 rounded-b-xl">
                <?= t('Logout', $translations, $lang) ?>
              </a>
            </li>
          </ul>
        </li>

        <!-- Lien Admin si autorisé -->
        <?php if($isAdmin): ?>
          <li>
            <a href="admin.php" class="hover:bg-red-700 dark:hover:bg-red-900 px-3 py-1 rounded-lg transition-colors font-bold">
              <?= t('Admin', $translations, $lang) ?>
            </a>
          </li>
        <?php endif; ?>

      <!-- Si non connecté -->
      <?php else: ?>
        <li>
          <a href="login.php" class="hover:bg-blue-700 dark:hover:bg-blue-900 px-3 py-1 rounded-lg transition-colors font-medium">
            <?= t('Login', $translations, $lang) ?>
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<!-- Scripts de gestion -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  
  // Gestion du menu déroulant
  const profileMenu = document.getElementById('profile-menu');
  if(profileMenu) {
    const dropdown = profileMenu.querySelector('ul');
    let timeout;

    profileMenu.addEventListener('mouseenter', () => {
      clearTimeout(timeout);
      dropdown.classList.remove('opacity-0', 'invisible');
      dropdown.classList.add('opacity-100', 'visible');
    });

    profileMenu.addEventListener('mouseleave', () => {
      timeout = setTimeout(() => {
        dropdown.classList.remove('opacity-100', 'visible');
        dropdown.classList.add('opacity-0', 'invisible');
      }, 300);
    });
  }

  // Synchronisation du thème entre PHP et localStorage
  const html = document.documentElement;
  const savedTheme = localStorage.getItem('theme');
  const phpTheme = html.classList.contains('dark') ? 'dark' : 'light';
  
  if(savedTheme !== phpTheme) {
    localStorage.setItem('theme', phpTheme);
    html.classList.toggle('dark', phpTheme === 'dark');
  }
});
</script>