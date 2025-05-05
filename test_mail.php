<?php
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'localhost';
$mail->Port = 1025;
$mail->SMTPAuth = false;
$mail->setFrom('test@example.com', 'Test');
$mail->addAddress('test@example.com');
$mail->Subject = 'Test Mailpit';
$mail->Body = 'Ceci est un test';
if ($mail->send()) {
    echo "OK";
} else {
    echo "Erreur : " . $mail->ErrorInfo;
}