<?php
session_start();
require_once 'src/php/dbconn.php';
require_once 'src/php/lang.php';
require_once 'src/components/header.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$lang = getLanguage($db, $user_id);
$theme = getTheme($db, $user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("
        INSERT INTO User_Settings (user_id, setting_key, setting_value) 
        VALUES (:user_id, :key, :value)
        ON DUPLICATE KEY UPDATE setting_value = :value
    ");

    foreach ($_POST['settings'] as $key => $value) {
        $stmt->execute([
            ':user_id' => $user_id,
            ':key'     => $key,
            ':value'   => $value
        ]);
    }

    // Mettre à jour $lang si la langue a été changée
    if (isset($_POST['settings']['language'])) {
        $lang = $_POST['settings']['language'];
    }

    $_SESSION['success_message'] = t('settings_updated', $translations, $lang);
    header("Location: param.php");
    exit;
}

$settings = $db->prepare("SELECT setting_key, setting_value FROM User_Settings WHERE user_id = ?");
$settings->execute([$_SESSION['id']]);
$userSettings = $settings->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('settings', $translations, $lang) ?></title>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors flex flex-col min-h-screen">
    <?php require_once 'src/components/header.php'; ?>

    <main class="max-w-4xl mx-auto p-6 flex-grow w-full min-h-[calc(100vh-12rem)]">
        <h2 class="text-3xl font-bold mb-6 dark:text-gray-300 text-center">
            <?= t('personal_settings', $translations, $lang) ?>
        </h2> 
        <?php 
        if (!empty($_SESSION['success_message'])): 
            $success_message = $_SESSION['success_message'];
            unset($_SESSION['success_message']);
        ?>
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded border border-green-400 dark:border-green-600">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-6 bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl max-w-xl mx-auto border border-blue-100 dark:border-blue-900">
            <div>
                <label class="block font-semibold mb-2 dark:text-gray-300"><?= t('language', $translations, $lang) ?></label>
                <select name="settings[language]" class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                    <option value="en" <?= ($userSettings['language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                    <option value="fr" <?= ($userSettings['language'] ?? 'en') === 'fr' ? 'selected' : '' ?>>Français</option>
                    <option value="es" <?= ($userSettings['language'] ?? 'en') === 'es' ? 'selected' : '' ?>>Español</option>
                    <option value="nl" <?= ($userSettings['language'] ?? 'en') === 'nl' ? 'selected' : '' ?>>Nederlands</option>
                    <option value="zh" <?= ($userSettings['language'] ?? 'en') === 'zh' ? 'selected' : '' ?>>中文</option>
                    <option value="pa" <?= ($userSettings['language'] ?? 'en') === 'pa' ? 'selected' : '' ?>>ਪੰਜਾਬੀ</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-2 dark:text-gray-300"><?= t('theme', $translations, $lang) ?></label>
                <div class="flex space-x-4">
                    <label class="flex items-center dark:text-gray-400">
                        <input type="radio" name="settings[theme]" value="light"
                            <?= ($userSettings['theme'] ?? 'light') === 'light' ? 'checked' : '' ?> class="mr-2 accent-blue-600">
                        <?= t('light_theme', $translations, $lang) ?>
                    </label>
                    <label class="flex items-center dark:text-gray-400">
                        <input type="radio" name="settings[theme]" value="dark"
                            <?= ($userSettings['theme'] ?? 'light') === 'dark' ? 'checked' : '' ?> class="mr-2 accent-blue-600">
                        <?= t('dark_theme', $translations, $lang) ?>
                    </label>
                </div>
            </div>

            <button type="submit" class="bg-blue-600 dark:bg-blue-800 text-white px-8 py-3 rounded-xl hover:bg-blue-700 dark:hover:bg-blue-900 transition font-semibold">
                <?= t('save', $translations, $lang) ?>
            </button>
        </form>
    </main>

    <?php require_once 'src/components/footer.php'; ?>
</body>
</html>