<?php
session_start();
require_once 'src/php/dbconn.php';
require_once 'src/php/lang.php';
// require_once 'src/components/header.php'; // SUPPRIMER ou commenter la ligne suivante
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$lang = getLanguage($db, $user_id);
$theme = getTheme($db, $user_id);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $message = trim($_POST["message"]);
    
    if (empty($title) || empty($message)) {
        $_SESSION["error_message"] = t('fill_all_fields', $translations,    $lang);
    } else {
        try {
            // Création du ticket
            $stmtTicket = $db->prepare("
                INSERT INTO Ticket (Title, User_id, Created_by) 
                VALUES (:title, :user_id, :user_id)
            ");
            $stmtTicket->execute([
                ':title' => $title,
                ':user_id' => $user_id
            ]);
            
            $ticketId = $db->lastInsertId();
            
            // Ajout du premier message
            $stmtMsg = $db->prepare("
                INSERT INTO Messages (Ticket_id, Message, Created_by)
                VALUES (:ticket_id, :message, :user_id)
            ");
            $stmtMsg->execute([
                ':ticket_id' => $ticketId,
                ':message' => $message,
                ':user_id' => $user_id
            ]);

            // Récupérer l'email et le nom de l'utilisateur
            $stmtUser = $db->prepare("SELECT mail, Username FROM Users WHERE Id = ?");
            $stmtUser->execute([$user_id]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $userMail = $user['mail'] ?: 'no-reply@example.com';
                $userName = $user['Username'] ?: 'Utilisateur';
            } else {
                $userMail = 'no-reply@example.com';
                $userName = 'Utilisateur';
            }

            // Debug temporaire
            error_log("DEBUG MAILPIT: userMail = $userMail, userName = $userName");

            // Envoi du mail via Mailpit à la création du ticket
            try {
                $mail = new PHPMailer(true);
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';
                $mail->isHTML(true);

                $htmlContent = '
                <!DOCTYPE html>
                <html lang="fr">
                <head>
                    <meta charset="UTF-8">
                    <title>Nouveau ticket créé</title>
                </head>
                <body style="font-family: sans-serif; background: #f6f6f6; padding: 20px;">
                    <div style="max-width:600px;margin:0 auto;background:white;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
                        <div style="background:#2b3a4a;padding:20px;border-radius:8px 8px 0 0;">
                            <h1 style="color:white;margin:0;font-size:1.5em;">🎫 Nouveau ticket créé</h1>
                        </div>
                        <div style="padding:25px;color:#444;">
                            <h2 style="color:#2b3a4a;margin-top:0;">Titre : <span style="color:#3d7ea6;">'.htmlspecialchars($title).'</span></h2>
                            <div style="background:#f8f9fa;padding:15px;border-radius:6px;border-left:4px solid #3d7ea6;">
                                '.nl2br(htmlspecialchars($message)).'
                            </div>
                            <p style="margin-top:25px;">
                                <a href="http://localhost/ticket_view.php?id='.$ticketId.'" 
                                   style="background:#3d7ea6;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;">
                                   Voir le ticket
                                </a>
                            </p>
                        </div>
                        <div style="background:#f8f9fa;padding:15px;text-align:center;border-radius:0 0 8px 8px;font-size:0.9em;color:#666;">
                            © '.date('Y').' Ticket System. Tous droits réservés.
                        </div>
                    </div>
                </body>
                </html>';

                $textContent = "Nouveau ticket créé : $title\n\n";
                $textContent .= $message . "\n\n";
                $textContent .= "Accéder au ticket : http://localhost/ticket_view.php?id=$ticketId";

                $mail->isSMTP();
                $mail->Host = 'localhost';
                $mail->Port = 1025;
                $mail->SMTPAuth = false;

                // Adresse d'expéditeur et destinataire (à adapter si besoin)
                $mail->setFrom($userMail, $userName);
                $mail->addAddress('destinataire@example.com');

                $mail->Subject = '🎫 Nouveau ticket créé - ' . $title;
                $mail->Body = $htmlContent;
                $mail->AltBody = $textContent;

                $mail->send();
            } catch (Exception $e) {
                error_log('Erreur PHPMailer (création ticket) : ' . $e->getMessage());
            }
            
            $_SESSION["success_message"] = t('ticket_created', $translations, $lang);
            header("Location: ticket_view.php?id=" . $ticketId);
            exit;
            
        } catch (PDOException $e) {
            $_SESSION["error_message"] = t('creation_error', $translations, $lang) . ": " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('create_ticket', $translations, $lang) ?> - <?= t('site_title', $translations, $lang) ?></title>
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
            --gradient-start: #6366f1; /* indigo-500 */
            --gradient-end: #a5b4fc;   /* indigo-200 */
            --cta-bg: #f9fafb;         /* gray-50, plus doux */
            --cta-text: #312e81;       /* indigo-900 */
            --section-bg: #f9fafb;     /* gray-50, plus doux */
            --feature-bg: #f9fafb;     /* gray-50, plus doux */
            --body-bg: #f9fafb;        /* gray-50, plus doux */
        }

        .dark {
            --gradient-start: #3730A3;
            --gradient-end: #6D28D9;
            --cta-bg: #1e293b;         /* slate-800 */
            --cta-text: #fff;
            --section-bg: #1e293b;
            --feature-bg: #374151;     /* gray-700 */
            --body-bg: #111827;        /* gray-900 */
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
            border: 1px solid #e5e7eb; /* Ajout d'une bordure pour les cartes */
        }

        .cta-soft {
            background: var(--body-bg); /* même couleur que le body */
            color: var(--cta-text);
        }

        .cta-soft .cta-btn {
            background: var(--gradient-start);
            color: #fff;
        }

        .cta-soft .cta-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body class="min-h-screen transition-colors duration-200">
    <?php require_once 'src/components/header.php'; ?>

    <main class="flex-grow py-12 px-4">
        <div class="container mx-auto max-w-4xl">
            <!-- En-tête -->
            <div class="mb-8 text-center">
                <h1 class="mb-2 inline-block">
                    <i class="fas fa-ticket-alt mr-2 text-4xl align-middle text-indigo-500"></i>
                    <span class="gradient-text text-4xl font-bold align-middle"><?= t('create_ticket', $translations, $lang) ?></span>
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    <?= t('create_ticket_help', $translations, $lang) ?? 'Ouvrez un nouveau ticket pour obtenir de l\'aide' ?>
                </p>
            </div>

            <!-- Messages flash -->
            <?php if (isset($_SESSION["error_message"])): ?>
                <div class="bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg mb-6">
                    <?= htmlspecialchars($_SESSION["error_message"]) ?>
                </div>
                <?php unset($_SESSION["error_message"]); ?>
            <?php endif; ?>

            <!-- Formulaire -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
                <form method="post" class="space-y-6">
                    <!-- Titre -->
                    <div>
                        <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                            <i class="fas fa-heading mr-2 text-indigo-500"></i>
                            <?= t('title', $translations, $lang) ?>
                        </label>
                        <input
                            type="text"
                            name="title"
                            id="title"
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-300"
                            placeholder="<?= t('title_placeholder', $translations, $lang) ?? 'Exemple : Problème de connexion' ?>"
                        >
                    </div>

                    <!-- Message -->
                    <div>
                        <label class="block text-sm font-medium mb-2 dark:text-gray-300">
                            <i class="fas fa-comment-dots mr-2 text-indigo-500"></i>
                            <?= t('message', $translations, $lang) ?>
                        </label>
                        <textarea
                            name="message"
                            id="message"
                            rows="6"
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-300"
                            placeholder="<?= t('message_placeholder', $translations, $lang) ?? 'Décrivez votre problème en détail...' ?>"
                        ></textarea>
                    </div>

                    <!-- Bouton -->
                    <div class="text-center">
                        <button
                            type="submit"
                            class="gradient-bg text-white py-3 px-8 rounded-lg font-medium shadow-lg hover:opacity-90 transition transform hover:scale-105"
                        >
                            <i class="fas fa-plus-circle mr-2"></i>
                            <?= t('create_ticket_button', $translations, $lang) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php require_once 'src/components/footer.php'; ?>
</body>
</html>
