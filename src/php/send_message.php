<?php

setlocale(LC_ALL, 'en_US.UTF-8'); // Ajoute cette ligne tout en haut

session_start();
require_once './dbconn.php';
require_once './lang.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php'; // Assurez-vous que PHPMailer est installé via Composer

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$lang = getLanguage($db, $_SESSION['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticketId = intval($_POST['ticket_id']);
    $message = trim($_POST['message']);
    $createdBy = $_SESSION['id'];

    // Récupérer l'email de l'utilisateur connecté
    $stmtUser = $db->prepare("SELECT mail, Username FROM Users WHERE Id = ?");
    $stmtUser->execute([$createdBy]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $userMail = $user['mail'] ?? 'no-reply@example.com';
    $userName = $user['Username'] ?? 'Utilisateur';

    // Récupérer le titre du ticket
    $stmtTicket = $db->prepare("SELECT Title FROM Ticket WHERE Id = ?");
    $stmtTicket->execute([$ticketId]);
    $ticketData = $stmtTicket->fetch(PDO::FETCH_ASSOC);
    $ticketTitle = isset($ticketData['Title']) ? $ticketData['Title'] : '(Titre inconnu)';

    if (!empty($message)) {
        // Insertion du message dans la base de données
        $stmt = $db->prepare("INSERT INTO Messages (Ticket_id, Message, Created_by) VALUES (?, ?, ?)");
        if ($stmt->execute([$ticketId, $message, $createdBy])) {
            // Envoi du mail via Mailpit
            try {
                $mail = new PHPMailer(true);
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';
                $mail->isHTML(true); // Passer en HTML

                // Style HTML avec template (ajout du titre)
                $htmlContent = '
                <!DOCTYPE html>
                <html lang="fr">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Notification de ticket</title>
                </head>
                <body style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif; margin: 0; padding: 20px; background-color: #f6f6f6;">
                    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <!-- En-tête -->
                        <div style="background: #2b3a4a; padding: 20px; border-radius: 8px 8px 0 0;">
                            <h1 style="color: white; margin: 0; font-size: 1.5em;">🚀 Ticket System Notification</h1>
                        </div>

                        <!-- Contenu -->
                        <div style="padding: 25px; color: #444; line-height: 1.6;">
                            <h2 style="color: #2b3a4a; margin-top: 0;">Nouveau message sur le ticket <span style="color:#3d7ea6;">#'.$ticketId.' - '.htmlspecialchars($ticketTitle).'</span></h2>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #3d7ea6;">
                                '.nl2br(htmlspecialchars($message)).'
                            </div>
                            <p style="margin-top: 25px;">
                                <a href="http://localhost/ticket_view.php?id='.$ticketId.'" 
                                   style="background: #3d7ea6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
                                   Voir le ticket
                                </a>
                            </p>
                        </div>

                        <!-- Footer -->
                        <div style="background: #f8f9fa; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 0.9em; color: #666;">
                            © '.date('Y').' Ticket System. Tous droits réservés.
                        </div>
                    </div>
                </body>
                </html>';

                // Version texte brut (ajout du titre)
                $textContent = "Nouveau message sur le ticket #$ticketId - $ticketTitle:\n\n";
                $textContent .= $message . "\n\n";
                $textContent .= "Accéder au ticket: http://localhost/ticket_view.php?id=$ticketId";

                // Configuration SMTP
                $mail->isSMTP();
                $mail->Host = 'localhost';
                $mail->Port = 1025;
                $mail->SMTPAuth = false;

                // Utiliser l'email et le nom de l'utilisateur connecté comme expéditeur
                $mail->setFrom($userMail, $userName);
                $mail->addAddress('destinataire@example.com');
                
                // Sujet du mail avec le titre du ticket (SANS htmlspecialchars)
                $mail->Subject = '📬 Nouveau message - Ticket #' . $ticketId . ' - ' . $ticketTitle;
                $mail->Body = $htmlContent;
                $mail->AltBody = $textContent;

                $mail->send();
            } catch (Exception $e) {
                error_log('Erreur PHPMailer : ' . $e->getMessage());
                $_SESSION['error_message'] = t('message_error', $translations, $lang);
                header("Location: ../../ticket_view.php?id=" . $ticketId);
                exit;
            }

            $_SESSION['success_message'] = t('message_sent', $translations, $lang);
        } else {
            $_SESSION['error_message'] = t('message_error', $translations, $lang);
        }
    } else {
        $_SESSION['error_message'] = t('message_error', $translations, $lang);
    }

    header("Location: ../../ticket_view.php?id=" . $ticketId);
    exit;
}
?>