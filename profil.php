<?php
session_start();
require_once 'src/php/dbconn.php';
require_once 'src/php/lang.php';
require_once 'src/components/header.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php?redirect=profil.php");
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
                $stmt = $db->prepare("SELECT Image FROM Users WHERE Id = :id");
                $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $oldImage = $stmt->fetchColumn();

                $stmt = $db->prepare("UPDATE Users SET Image = :image WHERE Id = :id");
                $stmt->bindParam(':image', $newFileName, PDO::PARAM_STR);
                $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    if ($oldImage && $oldImage !== 'image_defaut.avif') {
                        $oldImagePath = $uploadDir . $oldImage;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $_SESSION['success_message'] = t('profile_updated', $translations, $lang);
                } else {
                    unlink($uploadPath);
                    $_SESSION['error_message'] = t('db_error', $translations, $lang);
                }
            } else {
                $_SESSION['error_message'] = t('upload_error', $translations, $lang);
            }
        } else {
            $_SESSION['error_message'] = t('file_type_error', $translations, $lang);
        }
    } else if ($_FILES['profile_image']['error'] !== 4) {
        $_SESSION['error_message'] = t('image_error', $translations, $lang);
    }
    header("Location: profil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('profile', $translations, $lang) ?> - <?= t('site_title', $translations, $lang) ?></title>
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

        .profile-image-container:hover .profile-image-overlay {
            opacity: 1;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <?php require_once 'src/components/header.php'; ?>

    <main class="flex-grow py-12 px-4">
        <div class="container mx-auto max-w-4xl">
            <?php if (!empty($errorMessage)): ?>
                <div class="bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded mb-6 text-center">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="bg-green-100 dark:bg-green-900/20 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-6 text-center">
                    <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>

            <!-- Profile Header -->
            <div class="mb-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="gradient-bg py-8 px-6 flex flex-col items-center text-white">
                    <h1 class="text-3xl font-bold mb-2">
                        <?= htmlspecialchars($user['Firstname']) ?> <?= htmlspecialchars($user['Lastname'] ?? '') ?>
                    </h1>
                    <div class="inline-block px-4 py-1 rounded-full text-sm font-semibold bg-white/20 backdrop-blur-sm mb-4">
                        <?= htmlspecialchars($user['RoleName']) ?>
                    </div>
                    <div class="relative profile-image-container">
                        <img src="src/images/<?= htmlspecialchars($user['Image'] ?: 'image_defaut.avif'); ?>"
                             alt="<?= t('profile_picture', $translations, $lang) ?>"
                             class="w-32 h-32 rounded-full border-4 border-white object-cover shadow-md">
                        <div class="absolute inset-0 rounded-full profile-image-overlay bg-black bg-opacity-50 opacity-0 flex items-center justify-center transition-opacity duration-200">
                            <label for="profile_image" class="cursor-pointer text-white text-sm font-medium">
                                <i class="fas fa-camera text-lg"></i>
                                <span class="block"><?= t('change', $translations, $lang) ?></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fas fa-user text-indigo-500 dark:text-indigo-400 w-6"></i>
                                <span class="text-gray-500 dark:text-gray-400 ml-2"><?= t('username', $translations, $lang) ?>:</span>
                                <span class="ml-2 font-medium text-gray-900 dark:text-gray-200"><?= htmlspecialchars($user['Username']) ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-indigo-500 dark:text-indigo-400 w-6"></i>
                                <span class="text-gray-500 dark:text-gray-400 ml-2"><?= t('email', $translations, $lang) ?>:</span>
                                <span class="ml-2 font-medium text-gray-900 dark:text-gray-200"><?= htmlspecialchars($user['mail']) ?></span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fas fa-shield-alt text-indigo-500 dark:text-indigo-400 w-6"></i>
                                <span class="text-gray-500 dark:text-gray-400 ml-2"><?= t('role', $translations, $lang) ?>:</span>
                                <?php
                                $roleName = $user['RoleName'];
                                $badgeClass = match (strtolower($roleName)) {
                                    'admin' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-300 border border-red-200 dark:border-red-700',
                                    'helper' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-300 border border-purple-200 dark:border-purple-700',
                                    'dev' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700',
                                    default => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 border border-blue-200 dark:border-blue-700',
                                };
                                ?>
                                <span class="ml-2 px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass ?>">
                                    <?= htmlspecialchars($roleName) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-8">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                            <i class="fas fa-cog mr-2 text-indigo-500 dark:text-indigo-400"></i><?= t('profile_settings', $translations, $lang) ?>
                        </h2>
                    </div>
                </div>

                <div class="p-6">
                    <form action="profil.php" method="post" enctype="multipart/form-data">
                        <div class="mb-6">
                            <label for="profile_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <?= t('change_profile_picture', $translations, $lang) ?>
                            </label>
                            <input type="file" name="profile_image" id="profile_image" accept="image/*" class="hidden">
                            <div class="flex">
                                <label for="profile_image" class="cursor-pointer gradient-bg text-white py-2 px-4 rounded-lg font-medium shadow-sm hover:opacity-90 transition flex items-center">
                                    <i class="fas fa-upload mr-2"></i>
                                    <?= t('select_image', $translations, $lang) ?>
                                </label>
                                <span class="ml-4 text-sm text-gray-500 dark:text-gray-400 self-center" id="file-name">
                                    <?= t('no_file_selected', $translations, $lang) ?>
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                <?= t('image_formats', $translations, $lang) ?>: JPG, JPEG, PNG
                            </p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="submit" class="gradient-bg text-white py-2 px-6 rounded-lg font-medium shadow-sm hover:opacity-90 transition">
                                <?= t('update', $translations, $lang) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php require_once 'src/components/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fileInput = document.getElementById('profile_image');
            const fileNameSpan = document.getElementById('file-name');

            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) {
                    fileNameSpan.textContent = fileInput.files[0].name;
                } else {
                    fileNameSpan.textContent = '<?= t('no_file_selected', $translations, $lang) ?>';
                }
            });

            const html = document.documentElement;
            const theme = html.classList.contains('dark') ? 'dark' : 'light';
            localStorage.setItem('theme', theme);
        });
    </script>
</body>
</html>