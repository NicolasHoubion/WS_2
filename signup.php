<?php
require_once 'src/php/dbconn.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - YourTicket</title>
</head>

<body class="min-h-screen flex flex-col bg-gray-100 text-gray-900 font-sans">

	<?php require_once 'src/components/header.php'; ?>

    <main class="flex-grow max-w-7xl mx-auto p-6">
        <div class="max-w-md mx-auto bg-white p-10 rounded-2xl shadow-xl border border-blue-100 dark:border-blue-900">
            <h2 class="text-2xl font-bold text-center mb-8 text-blue-700 dark:text-blue-300">Create Account</h2>

            <?php if (isset($_GET['error'])) { ?>
                <div class="alert alert-danger text-red-600 bg-red-100 p-3 mb-4 rounded">
                    <?php echo $_GET['error']; ?>
                </div>
            <?php } ?>

            <?php if (isset($_GET['success'])) { ?>
                <div class="alert alert-success text-green-600 bg-green-100 p-3 mb-4 rounded">
                    <?php echo $_GET['success']; ?>
                </div>
            <?php } ?>

            <form action="./src/php/signup.php" method="post">

                <div class="mb-6">
                    <label for="fname" class="block text-gray-700 font-semibold mb-2">Full Name</label>
                    <input type="text" id="fname" name="fname" class="w-full px-5 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?php echo (isset($_GET['fname'])) ? $_GET['fname'] : "" ?>" required>
                </div>

                <div class="mb-6">
                    <label for="uname" class="block text-gray-700 font-semibold mb-2">User Name</label>
                    <input type="text" id="uname" name="uname" class="w-full px-5 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?php echo (isset($_GET['uname'])) ? $_GET['uname'] : "" ?>" required>
                </div>

                <div class="mb-6">
                    <label for="pass" class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input type="password" id="pass" name="pass" class="w-full px-5 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                </div>

                <div class="mb-6">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" id="email" name="email" class="w-full px-5 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?php echo (isset($_GET['email'])) ? $_GET['email'] : "" ?>" required>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">Sign Up</button>
            </form>

            <div class="mt-6 text-center">
                <span class="text-gray-600">Already have an account?</span>
                <a href="login.php" class="text-blue-600 hover:underline font-semibold">Sign In</a>
            </div>
        </div>
    </main>

    <?php require_once 'src/components/footer.php'; ?>

</body>
</html>
