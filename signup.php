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
        <div class="max-w-md mx-auto bg-white p-8 rounded shadow-lg">
            <h2 class="text-2xl font-bold text-center mb-6">Create Account</h2>

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

                <div class="mb-4">
                    <label for="fname" class="block text-gray-700 font-semibold mb-2">Full Name</label>
                    <input type="text" id="fname" name="fname" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?php echo (isset($_GET['fname'])) ? $_GET['fname'] : "" ?>" required>
                </div>

                <div class="mb-4">
                    <label for="uname" class="block text-gray-700 font-semibold mb-2">User Name</label>
                    <input type="text" id="uname" name="uname" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?php echo (isset($_GET['uname'])) ? $_GET['uname'] : "" ?>" required>
                </div>

                <div class="mb-4">
                    <label for="pass" class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input type="password" id="pass" name="pass" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?php echo (isset($_GET['email'])) ? $_GET['email'] : "" ?>" required>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600">Sign Up</button>
                    <a href="login.php" class="text-blue-600 hover:text-blue-700">Already have an account? Login</a>
                </div>

            </form>
        </div>
    </main>

<?php
require_once 'src/components/footer.php';
?>
</body>

</html>
