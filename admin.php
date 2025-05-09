<?php
session_start();
require_once __DIR__ . '/src/php/dbconn.php';
require_once __DIR__ . '/src/php/lang.php';
require_once __DIR__ . '/src/components/header.php';

header("Cache-Control: no-cache, must-revalidate");
header("Expires: 0");

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

// Suppression de ticket si requête POST
function hasPermission($permission, $permissions)
{
    return is_array($permissions) && in_array($permission, $permissions);
}

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

// Récupération des utilisateurs avec rôles
// Récupération des utilisateurs avec rôles (admin.php)
// Récupération du terme de recherche
$searchTermRaw = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchTerm = $searchTermRaw !== '' ? "%{$searchTermRaw}%" : null;

// Requête SQL
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
    $results = $queryUsers->fetchAll(); // ✅ Une seule récupération

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
    <title><?= t('admin_panel', $translations, $lang) ?></title>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <div class="container mx-auto p-6 flex-1 flex flex-col">
        <h1 class="text-4xl font-bold mb-8 text-center"><?= t('admin_panel', $translations, $lang) ?></h1>

        <!-- SECTION TICKETS -->
        <section class="mb-12"> 
            <h2 class="text-3xl font-semibold mb-4"><?= t('ticket_management', $translations, $lang) ?></h2>
            <div class="overflow-y-auto max-h-[500px] bg-gray-100 dark:bg-gray-800 p-4 rounded-2xl shadow-xl border border-blue-100 dark:border-blue-900">
                <?php if (count($tickets) > 0): ?>
                    <table class="min-w-full rounded-xl overflow-hidden">
                        <thead class="bg-blue-100 dark:bg-blue-900">
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left"><?= t('title', $translations, $lang) ?></th>
                                <th class="px-4 py-2 text-left"><?= t('user', $translations, $lang) ?></th>
                                <th class="px-4 py-2 text-left"><?= t('created_at', $translations, $lang) ?></th>
                                <th class="px-4 py-2 text-left"><?= t('actions', $translations, $lang) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr class="bg-white dark:bg-gray-900 hover:bg-blue-50 dark:hover:bg-blue-950 border-b transition">
                                    <td class="px-4 py-2 dark:text-gray-300"><?= htmlspecialchars($ticket['Id']) ?></td>
                                    <td class="px-4 py-2 dark:text-gray-300"><?= htmlspecialchars($ticket['Title']) ?></td>
                                    <td class="px-4 py-2 dark:text-gray-300"><?= htmlspecialchars($ticket['Username']) ?></td>
                                    <td class="px-4 py-2 dark:text-gray-300"><?= formatDateFr($ticket['Created_at']) ?></td>
                                    <td class="px-4 py-2">
                                        <a href="ticket_view.php?id=<?= $ticket['Id'] ?>"
                                            class="inline-block bg-blue-600 text-white px-5 py-2 rounded-xl hover:bg-blue-700 transition dark:bg-blue-800 dark:hover:bg-blue-900 font-semibold shadow">
                                            <?= t('view', $translations, $lang) ?>
                                        </a>
                                        <form method="POST" action="admin.php" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ? Cette action est irréversible.');">
                                            <input type="hidden" name="delete_ticket_id" value="<?= $ticket['Id'] ?>">
                                            <button type="submit"
                                                class="inline-block bg-red-500 text-white px-5 py-2 rounded-xl hover:bg-red-600 transition dark:bg-red-700 dark:hover:bg-red-800 font-semibold shadow ml-2">
                                                <?= t('delete', $translations, $lang) ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center text-gray-500 dark:text-gray-400"><?= t('no_tickets', $translations, $lang) ?></p>
                <?php endif; ?>
            </div>
        </section>

        <!-- SECTION UTILISATEURS -->
        <section>
            <h2 class="text-3xl font-semibold mb-4"><?= t('user_management', $translations, $lang) ?></h2>
            <form method="GET" class="mb-6 flex gap-2">
                <input type="text" name="search"
                    placeholder="<?= t('search_users', $translations, $lang) ?>"
                    class="flex-1 p-3 rounded-lg border dark:bg-gray-700 dark:border-gray-600">
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-xl hover:bg-blue-600 transition font-semibold shadow">
                    <?= t('search', $translations, $lang) ?>
                </button>
                <a href="admin.php" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-xl hover:bg-gray-400 transition dark:bg-gray-600 dark:text-gray-200 font-semibold shadow">
                    <?= t('reset', $translations, $lang) ?>
                </a>
            </form>
            <?php if (count($users) > 0 && (hasPermission('Manage Users', $currentUserPermissions) || hasPermission('Admin Access', $currentUserPermissions))): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <?php foreach ($users as $user): ?>
                        <?php
                        $roleName = strtolower($user['role']);
                        switch ($roleName) {
                            case 'admin':
                                $badgeClass = 'bg-red-100 dark:bg-red-200 text-red-700 dark:text-red-900';
                                break;
                            case 'helper':
                                $badgeClass = 'bg-purple-100 dark:bg-purple-200 text-purple-700 dark:text-purple-900';
                                break;
                            case 'dev':
                                $badgeClass = 'bg-emerald-100 dark:bg-emerald-200 text-emerald-700 dark:text-emerald-900';
                                break;
                            default:
                                $badgeClass = 'bg-blue-100 dark:bg-blue-200 text-blue-700 dark:text-blue-900';
                                break;
                        }
                        ?>
                        <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl border border-blue-100 dark:border-blue-900">
                            <div class="flex justify-between items-center">
                                <h3 class="text-2xl font-bold dark:text-gray-300"><?= htmlspecialchars($user['username']) ?></h3>
                                <span class="px-3 py-1 rounded-xl text-sm font-semibold <?= $badgeClass; ?>">
                                    <?= htmlspecialchars($user['role']) ?>
                                </span>
                            </div>
                            <div class="mt-4">
                                <p class="font-semibold dark:text-gray-400"><?= t('permissions', $translations, $lang) ?> :</p>
                                <ul class="list-disc list-inside text-sm dark:text-gray-300">
                                    <?php foreach ($user['permissions'] as $permission): ?>
                                        <li><?= htmlspecialchars($permission) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="mt-6 space-x-2">
                                <a href="edit_user.php?id=<?= $user['user_id'] ?>"
                                    class="px-4 py-2 bg-green-500 rounded-xl hover:bg-green-600 text-sm inline-block dark:bg-green-700 dark:hover:bg-green-800 font-semibold shadow">
                                    <?= t('edit', $translations, $lang) ?>
                                </a>
                                <a href="delete_user.php?id=<?= $user['user_id'] ?>"
                                    class="px-4 py-2 bg-red-500 rounded-xl hover:bg-red-600 text-sm inline-block dark:bg-red-700 dark:hover:bg-red-800 font-semibold shadow">
                                    <?= t('delete', $translations, $lang) ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-500 dark:text-gray-400"><?= t('no_users_permission', $translations, $lang) ?></p>
            <?php endif; ?>
        </section>
    </div>

    <?php require_once 'src/components/footer.php'; ?>
</body>
</html>