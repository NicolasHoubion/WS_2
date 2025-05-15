<?php
session_start();
require_once __DIR__ . '/src/php/dbconn.php';
require_once __DIR__ . '/src/php/lang.php';
require_once __DIR__ . '/src/components/header.php';

// Récupération des préférences
$user_id = $_SESSION['id'] ?? 0;
$lang = getLanguage($db, $user_id);
$theme = getTheme($db, $user_id);

// Vérification des permissions (doit être AVANT la suppression)
$currentUserPermissions = [];
if ($user_id > 0) {
    try {
        $stmt = $db->prepare("
            SELECT p.Name 
            FROM Users u
            JOIN Roles r ON u.Role_id = r.Id
            JOIN Permission_Roles pr ON r.Id = pr.Role_id
            JOIN Permissions p ON pr.Permission_id = p.Id
            WHERE u.Id = :user_id
        ");
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $currentUserPermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        $currentUserPermissions = [];
    }
}

// Fonction pour vérifier les permissions
function hasPermission($permission, $permissions)
{
    return is_array($permissions) && in_array($permission, $permissions);
}

// Vérifier si l'utilisateur a accès à cette page
if (!hasPermission('Manage Users', $currentUserPermissions) && !hasPermission('Admin Access', $currentUserPermissions)) {
    header("Location: index.php");
    exit;
}

// Suppression de ticket si requête POST
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_ticket_id'])
    && (hasPermission('Delete Tickets', $currentUserPermissions) || hasPermission('Admin Access', $currentUserPermissions))
) {
    $ticketIdToDelete = intval($_POST['delete_ticket_id']);
    try {
        $stmt = $db->prepare("UPDATE Ticket SET Deleted_at = NOW() WHERE Id = :id");
        $stmt->bindValue(':id', $ticketIdToDelete, PDO::PARAM_INT);
        $stmt->execute();
        header("Location: admin.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        // Optionnel : message d'erreur
    }
}

// Fonction utilitaire pour formater la date en français
function formatDateFr($datetime)
{
    $fmt = new IntlDateFormatter(
        'fr_FR',
        IntlDateFormatter::LONG,
        IntlDateFormatter::SHORT,
        'Europe/Brussels',
        IntlDateFormatter::GREGORIAN,
        "d MMMM yyyy 'à' HH:mm"
    );
    return $fmt->format(new DateTime($datetime));
}

// Récupération des tickets
try {
    $queryTickets = $db->query("
        SELECT t.Id, t.Title, u.Username, t.Created_at 
        FROM Ticket t
        LEFT JOIN Users u ON t.User_id = u.Id
        WHERE t.Deleted_at IS NULL
        ORDER BY t.Created_at DESC
    ");
    $tickets = $queryTickets->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tickets = [];
}

// Récupération du terme de recherche
$searchTermRaw = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchTerm = $searchTermRaw !== '' ? "%{$searchTermRaw}%" : null;

// Requête SQL pour les utilisateurs
$sql = "
    SELECT 
        u.Id AS user_id,
        u.Username,
        u.Firstname,
        u.mail,
        r.Name AS role_name,
        (SELECT GROUP_CONCAT(p.Name SEPARATOR ', ') 
         FROM Permission_Roles pr 
         LEFT JOIN Permissions p ON pr.Permission_id = p.Id 
         WHERE pr.Role_id = r.Id) AS permissions
    FROM Users u
    JOIN Roles r ON u.Role_id = r.Id
    WHERE u.Deleted_at IS NULL
";

if ($searchTerm) {
    $sql .= " AND (
        u.Username LIKE :search1 
        OR SUBSTRING_INDEX(u.mail, '@', 1) LIKE :search2 
        OR u.Firstname LIKE :search3
    )";
}

// Exécution
try {
    $queryUsers = $db->prepare($sql);
    if ($searchTerm) {
        $queryUsers->bindValue(':search1', $searchTerm);
        $queryUsers->bindValue(':search2', $searchTerm);
        $queryUsers->bindValue(':search3', $searchTerm);
    }
    $queryUsers->execute();
    $results = $queryUsers->fetchAll();

    $users = [];
    foreach ($results as $row) {
        $id = $row['user_id'];
        $permissions = [];
        if (!empty($row['permissions'])) {
            $permissions = explode(', ', $row['permissions']);
        }
        $users[$id] = [
            'user_id' => $id,
            'username' => $row['Username'],
            'firstname' => $row['Firstname'],
            'email' => $row['mail'],
            'role' => $row['role_name'],
            'permissions' => $permissions
        ];
    }
} catch (PDOException $e) {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('admin_panel', $translations, $lang) ?> - <?= t('site_title', $translations, $lang) ?></title>
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
        .gradient-text {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            -webkit-background-clip: text;
            background-clip: text; /* Ajouté pour compatibilité */
            -webkit-text-fill-color: transparent;
        }
        .dark .gradient-text {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            -webkit-background-clip: text;
            background-clip: text; /* Ajouté pour compatibilité */
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <?php require_once 'src/components/header.php'; ?>

    <main class="flex-grow py-12 px-4">
        <div class="container mx-auto max-w-7xl">
            <!-- En-tête de la page -->
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-bold mb-2 gradient-text inline-block">
                    <i class="fas fa-shield-alt mr-2"></i><?= t('admin_panel', $translations, $lang) ?>
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-lg">
                    <?= t('admin_panel_description', $translations, $lang) ?? 'Gérez les tickets et les utilisateurs de votre plateforme.' ?>
                </p>
            </div>

            <!-- Messages flash -->
            <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                <div class="bg-green-100 dark:bg-green-900/20 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-6 text-center">
                    <i class="fas fa-check-circle mr-2"></i><?= t('ticket_deleted', $translations, $lang) ?? 'Le ticket a été supprimé avec succès.' ?>
                </div>
            <?php endif; ?>

            <!-- Navigation par onglets -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                    <li class="mr-2">
                        <a href="#tickets" class="inline-block p-4 border-b-2 border-indigo-600 dark:border-indigo-500 text-indigo-600 dark:text-indigo-500 rounded-t-lg active" aria-current="page">
                            <i class="fas fa-ticket-alt mr-2"></i><?= t('tickets', $translations, $lang) ?>
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="#users" class="inline-block p-4 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 rounded-t-lg">
                            <i class="fas fa-users mr-2"></i><?= t('users', $translations, $lang) ?>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- SECTION TICKETS -->
            <section id="tickets" class="mb-12">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold dark:text-gray-200">
                        <i class="fas fa-ticket-alt mr-2 text-indigo-500 dark:text-indigo-400"></i><?= t('ticket_management', $translations, $lang) ?>
                    </h2>
                    <span class="bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300 text-sm font-semibold px-3 py-1 rounded-full">
                        <?= count($tickets) ?> <?= t('tickets', $translations, $lang) ?>
                    </span>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-8">
                    <?php if (count($tickets) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"><?= t('title', $translations, $lang) ?></th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"><?= t('user', $translations, $lang) ?></th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"><?= t('created_at', $translations, $lang) ?></th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"><?= t('actions', $translations, $lang) ?></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($tickets as $ticket): ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200"><?= htmlspecialchars($ticket['Id']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-300"><?= htmlspecialchars($ticket['Title']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-300">
                                                <span class="inline-flex items-center">
                                                    <i class="fas fa-user-circle mr-2 text-indigo-500 dark:text-indigo-400"></i>
                                                    <?= htmlspecialchars($ticket['Username']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <span class="inline-flex items-center">
                                                    <i class="far fa-calendar-alt mr-2"></i>
                                                    <?= formatDateFr($ticket['Created_at']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="ticket_view.php?id=<?= $ticket['Id'] ?>" class="gradient-bg text-white px-4 py-2 rounded-lg shadow-sm hover:opacity-90 transition mr-2">
                                                    <i class="fas fa-eye mr-1"></i> <?= t('view', $translations, $lang) ?>
                                                </a>
                                                <?php if (hasPermission('Delete Tickets', $currentUserPermissions) || hasPermission('Admin Access', $currentUserPermissions)): ?>
                                                    <form method="POST" action="admin.php" class="inline-block" onsubmit="return confirm('<?= t('confirm_delete_ticket', $translations, $lang) ?? 'Êtes-vous sûr de vouloir supprimer ce ticket ? Cette action est irréversible.' ?>');">
                                                        <input type="hidden" name="delete_ticket_id" value="<?= $ticket['Id'] ?>">
                                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg shadow-sm transition">
                                                            <i class="fas fa-trash-alt mr-1"></i> <?= t('delete', $translations, $lang) ?>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-12 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 mb-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-500 dark:text-indigo-400">
                                <i class="fas fa-ticket-alt text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900 dark:text-gray-200 mb-2"><?= t('no_tickets_title', $translations, $lang) ?? 'Aucun ticket' ?></h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6"><?= t('no_tickets', $translations, $lang) ?? 'Il n\'y a actuellement aucun ticket dans le système.' ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- SECTION UTILISATEURS -->
            <section id="users" class="mb-12">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold dark:text-gray-200">
                        <i class="fas fa-users mr-2 text-indigo-500 dark:text-indigo-400"></i><?= t('user_management', $translations, $lang) ?>
                    </h2>
                    <span class="bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300 text-sm font-semibold px-3 py-1 rounded-full">
                        <?= count($users) ?> <?= t('users', $translations, $lang) ?>
                    </span>
                </div>

                <!-- Formulaire de recherche -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-8 p-6">
                    <form method="GET" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-search text-gray-400 dark:text-gray-500"></i>
                                </div>
                                <input type="text" name="search" value="<?= htmlspecialchars($searchTermRaw) ?>" 
                                    placeholder="<?= t('search_users', $translations, $lang) ?>"
                                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5">
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="gradient-bg text-white py-2.5 px-5 rounded-lg font-medium shadow-sm hover:opacity-90 transition">
                                <i class="fas fa-search mr-1"></i> <?= t('search', $translations, $lang) ?>
                            </button>
                            <a href="admin.php#users" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 py-2.5 px-5 rounded-lg font-medium shadow-sm hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                <i class="fas fa-redo mr-1"></i> <?= t('reset', $translations, $lang) ?>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Liste des utilisateurs -->
                <?php if (count($users) > 0 && (hasPermission('Manage Users', $currentUserPermissions) || hasPermission('Admin Access', $currentUserPermissions))): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php foreach ($users as $user): ?>
                            <?php
                            $roleName = strtolower($user['role']);
                            switch ($roleName) {
                                case 'admin':
                                    $badgeClass = 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-700';
                                    $iconClass = 'fas fa-crown text-red-500';
                                    break;
                                case 'helper':
                                    $badgeClass = 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border border-purple-200 dark:border-purple-700';
                                    $iconClass = 'fas fa-hands-helping text-purple-500';
                                    break;
                                case 'dev':
                                    $badgeClass = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700';
                                    $iconClass = 'fas fa-code text-emerald-500';
                                    break;
                                default:
                                    $badgeClass = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-700';
                                    $iconClass = 'fas fa-user text-blue-500';
                                    break;
                            }
                            ?>
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:-translate-y-1 hover:shadow-xl">
                                <div class="gradient-bg p-6 flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 rounded-full bg-white/20 flex items-center justify-center text-white">
                                            <i class="<?= $iconClass ?> text-xl"></i>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-xl font-bold text-white"><?= htmlspecialchars($user['username']) ?></h3>
                                            <p class="text-indigo-100 text-sm">
                                                <?= !empty($user['firstname']) ? htmlspecialchars($user['firstname']) : '' ?>
                                                <?= !empty($user['email']) ? '- ' . htmlspecialchars($user['email']) : '' ?>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass; ?> whitespace-nowrap">
                                        <?= htmlspecialchars($user['role']) ?>
                                    </span>
                                </div>
                                <div class="p-6">
                                    <div class="mb-4">
                                        <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                            <?= t('permissions', $translations, $lang) ?>
                                        </h4>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($user['permissions'] as $permission): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                                    <?= htmlspecialchars($permission) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="inline-flex items-center px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                            <i class="fas fa-edit mr-1"></i> <?= t('edit', $translations, $lang) ?>
                                        </a>
                                        <a href="delete_user.php?id=<?= $user['user_id'] ?>" class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors" onclick="return confirm('<?= t('confirm_delete_user', $translations, $lang) ?? 'Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.' ?>');">
                                            <i class="fas fa-trash-alt mr-1"></i> <?= t('delete', $translations, $lang) ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 mb-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-500 dark:text-indigo-400">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 dark:text-gray-200 mb-2">
                            <?= t('no_users_found', $translations, $lang) ?? 'Aucun utilisateur trouvé' ?>
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">
                            <?= t('no_users_permission', $translations, $lang) ?? 'Aucun utilisateur ne correspond à vos critères de recherche.' ?>
                        </p>
                        <?php if (!empty($searchTermRaw)): ?>
                            <a href="admin.php#users" class="gradient-bg text-white py-2.5 px-5 rounded-lg font-medium shadow-sm hover:opacity-90 transition">
                                <i class="fas fa-redo mr-1"></i> <?= t('clear_search', $translations, $lang) ?? 'Effacer la recherche' ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Bouton d'ajout d'utilisateur -->
                <?php if (hasPermission('Admin Access', $currentUserPermissions)): ?>
                    <div class="mt-8 text-center">
                        <a href="add_user.php" class="gradient-bg text-white py-3 px-8 rounded-lg font-medium shadow-lg hover:opacity-90 transition inline-flex items-center">
                            <i class="fas fa-user-plus mr-2"></i> <?= t('add_user', $translations, $lang) ?? 'Ajouter un utilisateur' ?>
                        </a>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <?php require_once 'src/components/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Navigation par onglets
            const tabLinks = document.querySelectorAll('a[href^="#"]');
            const sections = document.querySelectorAll('section[id]');
            
            function setActiveTab(targetId) {
                // Mise à jour des onglets
                tabLinks.forEach(link => {
                    const isActive = link.getAttribute('href') === `#${targetId}`;
                    
                    if (isActive) {
                        link.classList.add('border-indigo-600', 'dark:border-indigo-500', 'text-indigo-600', 'dark:text-indigo-500');
                        link.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                        link.setAttribute('aria-current', 'page');
                    } else {
                        link.classList.remove('border-indigo-600', 'dark:border-indigo-500', 'text-indigo-600', 'dark:text-indigo-500');
                        link.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                        link.removeAttribute('aria-current');
                    }
                });
            }
            
            // Gestion du clic sur les onglets
            tabLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetId = link.getAttribute('href').substring(1);
                    
                    // Mise à jour de l'URL sans recharger la page
                    history.pushState(null, null, `#${targetId}`);
                    
                    // Mise à jour des onglets
                    setActiveTab(targetId);
                    
                    // Scroll vers la section
                    document.getElementById(targetId).scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });
            
            // Détection de l'ancre dans l'URL au chargement
            const hash = window.location.hash.substring(1);
            if (hash && document.getElementById(hash)) {
                setActiveTab(hash);
            }

            // Mobile menu toggle
            const menuBtn = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (menuBtn && mobileMenu) {
                menuBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }
            
            // Gestion du thème sombre/clair
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    document.documentElement.classList.toggle('dark');
                    
                    // Sauvegarder la préférence
                    const isDark = document.documentElement.classList.contains('dark');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    
                    // Mettre à jour l'icône
                    const moonIcon = themeToggle.querySelector('.fa-moon');
                    const sunIcon = themeToggle.querySelector('.fa-sun');
                    moonIcon.classList.toggle('hidden');
                    sunIcon.classList.toggle('hidden');
                });
            }

            // Gestion des filtres utilisateurs
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('input', debounce(() => {
                    document.querySelector('form[method="GET"]').submit();
                }, 300));
            }

            function debounce(func, wait) {
                let timeout;
                return (...args) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }
        });
    </script>

    <script>
        // Confirmation avant suppression
        document.querySelectorAll('.delete-user-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (!confirm('<?= t('confirm_delete_user', $translations, $lang) ?>')) {
                    e.preventDefault();
                }
            });
        });

        // Gestion responsive des tableaux
        function adjustTableLayout() {
            const tables = document.querySelectorAll('table');
            tables.forEach(table => {
                if (window.innerWidth < 768) {
                    table.classList.add('responsive-table');
                } else {
                    table.classList.remove('responsive-table');
                }
            });
        }

        window.addEventListener('resize', adjustTableLayout);
        adjustTableLayout();
    </script>
</body>
</html>
<?php
// Fermer la connexion
$db = null;