<?php
require_once 'src/php/dbconn.php';
require_once 'src/php/lang.php';

session_start();
$user_id = $_SESSION['id'] ?? 0;
$lang = getLanguage($db, $user_id);
$theme = getTheme($db, $user_id);
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('login', $translations, $lang) ?> - <?= t('site_title', $translations, $lang) ?></title>
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
    </style>
</head>

<body class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <?php require_once 'src/components/header.php'; ?>

    <main class="flex-grow max-w-7xl mx-auto p-6">
        <div class="max-w-md mx-auto bg-white dark:bg-gray-800 p-10 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-center mb-8 text-gray-800 dark:text-gray-200"><?= t('login', $translations, $lang) ?></h2>

            <?php if (isset($_GET['error'])) { ?>
                <div class="bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded mb-4 text-center">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php } ?>

            <form action="src/php/login.php" method="post" class="space-y-6">
                <div>
                    <label for="uname" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Username</label>
                    <input type="text" id="uname" name="uname" class="w-full px-5 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus:ring-blue-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" value="<?php echo isset($_GET['uname']) ? htmlspecialchars($_GET['uname']) : '' ?>" required>
                </div>

                <div>
                    <label for="pass" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Password</label>
                    <input type="password" id="pass" name="pass" class="w-full px-5 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus:ring-blue-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" required>
                </div>

                <button type="submit" class="w-full gradient-bg text-white font-bold py-3 px-6 rounded-lg transition duration-200">Login</button>
            </form>

            <div class="mt-6 text-center">
                <span class="text-gray-600 dark:text-gray-400">No account ?</span>
                <a href="signup.php" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">Create an account</a>
            </div>
        </div>
    </main>

    <?php require_once 'src/components/footer.php'; ?>
</body>
</html>