<?php
session_start();
require_once 'src/php/dbconn.php';
require_once 'src/php/lang.php';
require_once 'src/components/header.php';

if (!isset($_SESSION['id'])) {
    echo "<div class='bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300 p-4 mb-4 rounded text-center w-full border border-red-400 dark:border-red-600'>" . t('login_required', $translations, $lang) . "</div>";
    exit;
}

$user_id = $_SESSION['id'];
$lang = getLanguage($db, $user_id);
$theme = getTheme($db, $user_id);

$stmt = $db->prepare("SELECT Users.*, Roles.Name AS RoleName FROM Users JOIN Roles ON Users.Role_id = Roles.Id WHERE Users.Id = :id");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<div class='bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300 p-4 mb-4 rounded text-center w-full border border-red-400 dark:border-red-600'>" . t('user_not_found', $translations, $lang) . "</div>";
    exit;
}

$successMessage = $_SESSION['success_message'] ?? '';
$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

if (isset($_POST['submit'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $fileInfo = pathinfo($_FILES['profile_image']['name']);
        $extension = strtolower($fileInfo['extension']);

        if (in_array($extension, $allowedExtensions)) {
            $newFileName = uniqid() . '.' . $extension;
            $uploadDir = __DIR__ . '/src/images/';
            $uploadPath = $uploadDir . $newFileName;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                // Récupération de l'ancienne image
                $stmt = $db->prepare("SELECT Image FROM Users WHERE Id = :id");
                $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $oldImage = $stmt->fetchColumn();

                // Mise à jour de la base de données
                $stmt = $db->prepare("UPDATE Users SET Image = :image WHERE Id = :id");
                $stmt->bindParam(':image', $newFileName, PDO::PARAM_STR);
                $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    // Suppression de l'ancienne image si nécessaire
                    if ($oldImage && $oldImage !== 'image_defaut.avif') {
                        $oldImagePath = $uploadDir . $oldImage;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $_SESSION['success_message'] = "Photo de profil mise à jour avec succès!";
                } else {
                    // En cas d'échec de la requête SQL, on supprime la nouvelle image
                    unlink($uploadPath);
                    $_SESSION['error_message'] = "Erreur lors de la mise à jour de la base de données.";
                }
            } else {
                $_SESSION['error_message'] = "Erreur lors de l'upload de l'image.";
            }
        } else {
            $_SESSION['error_message'] = "Seules les images JPG, JPEG et PNG sont autorisées.";
        }
    } else {
        $_SESSION['error_message'] = "Aucune image n'a été envoyée ou une erreur est survenue.";
    }
    header("Location: profil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<!-- Le reste de votre HTML reste inchangé -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('profile', $translations, $lang) ?></title>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <?php require_once 'src/components/header.php'; ?>

    <main class="flex-grow max-w-6xl mx-auto p-8">
        <?php if (!empty($errorMessage)): ?>
            <div class="bg-red-600/20 dark:bg-red-900/20 text-red-400 dark:text-red-300 border border-red-500 dark:border-red-600 p-4 mb-4 rounded text-center w-full">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="bg-green-600/20 dark:bg-green-900/20 text-green-400 dark:text-green-300 border border-green-500 dark:border-green-600 p-4 mb-4 rounded text-center w-full">
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>

        <h2 class="text-4xl font-bold mb-6 border-b border-gray-300 dark:border-gray-700 pb-2 dark:text-gray-300">
            <?= t('profile_of', $translations, $lang) ?> <?= htmlspecialchars($user['Firstname']) ?>
        </h2>

        <div class="mb-10 flex flex-col items-center">
            <img src="src/images/<?= htmlspecialchars($user['Image'] ?: 'image_defaut.avif'); ?>" 
                 alt="<?= t('profile_picture', $translations, $lang) ?>" 
                 class="w-36 h-36 rounded-full shadow-lg border-4 border-white dark:border-gray-800 object-cover">
        </div>

        <div class="bg-white dark:bg-gray-800 p-8 shadow-xl rounded-2xl mb-10">
            <h3 class="text-2xl font-semibold mb-4 border-b border-gray-300 dark:border-gray-700 pb-2 dark:text-gray-300">
                <?= t('profile_details', $translations, $lang) ?>
            </h3>
            <div class="space-y-4 text-lg dark:text-gray-400">
                <p><strong class="text-blue-600 dark:text-blue-400"><?= t('full_name', $translations, $lang) ?> :</strong> <?= htmlspecialchars($user['Firstname']) ?></p>
                <p><strong class="text-blue-600 dark:text-blue-400"><?= t('email', $translations, $lang) ?> :</strong> <?= htmlspecialchars($user['mail']) ?></p>
                <p><strong class="text-blue-600 dark:text-blue-400"><?= t('username', $translations, $lang) ?> :</strong> <?= htmlspecialchars($user['Username']) ?></p>
                <?php
                $roleName = $user['RoleName'];
                $badgeClass = match (strtolower($roleName)) {
                    'admin' => 'bg-red-500/20 dark:bg-red-900/20 text-red-600 dark:text-red-300 border border-red-400 dark:border-red-600',
                    'helper' => 'bg-purple-500/20 dark:bg-purple-900/20 text-purple-600 dark:text-purple-300 border border-purple-400 dark:border-purple-600',
                    'dev' => 'bg-emerald-500/20 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-600',
                    default => 'bg-blue-500/20 dark:bg-blue-900/20 text-blue-600 dark:text-blue-300 border border-blue-400 dark:border-blue-600',
                };
                ?>
                <p>
                    <strong class="text-blue-600 dark:text-blue-400"><?= t('role', $translations, $lang) ?> :</strong>
                    <span class="inline-block px-4 py-1 rounded-full text-sm font-semibold <?= $badgeClass ?>">
                        <?= htmlspecialchars($roleName) ?>
                    </span>
                </p>
            </div>
        </div>

        <form action="profil.php" method="post" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 p-8 shadow-xl rounded-2xl mb-10">
            <div class="mb-6">
                <label for="profile_image" class="block text-lg font-semibold mb-2 text-center dark:text-gray-300">
                    <?= t('change_profile_picture', $translations, $lang) ?>
                </label>
                <div class="flex justify-center">
                    <input type="file" name="profile_image" id="profile_image" accept="image/*" class="hidden">
                    <label for="profile_image" class="cursor-pointer bg-blue-600 dark:bg-blue-800 text-white py-3 px-8 rounded-full hover:bg-blue-700 dark:hover:bg-blue-900 transition">
                        <?= t('select_image', $translations, $lang) ?>
                    </label>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" name="submit" class="w-full bg-blue-600 dark:bg-blue-800 text-white py-3 px-8 rounded-full hover:bg-blue-700 dark:hover:bg-blue-900 transition">
                    <?= t('update', $translations, $lang) ?>
                </button>
            </div>
        </form>
    </main>

    <?php require_once 'src/components/footer.php'; ?>
</body>

</html>