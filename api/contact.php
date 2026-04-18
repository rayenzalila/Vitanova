<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/messages.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, MSG_GENERIC_SERVER_ERROR, [], 405);

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name)    jsonResponse(false, MSG_CONTACT_NAME_EMPTY, [], 422);
if (!$email)   jsonResponse(false, MSG_CONTACT_EMAIL_EMPTY, [], 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(false, MSG_CONTACT_EMAIL_INVALID, [], 422);
if (!$subject) jsonResponse(false, MSG_CONTACT_SUBJECT_EMPTY, [], 422);
if (!$message) jsonResponse(false, MSG_CONTACT_MESSAGE_EMPTY, [], 422);
if (mb_strlen($message) < 20) jsonResponse(false, MSG_CONTACT_MESSAGE_SHORT, [], 422);

try {
    $db   = getDB();
    $stmt = $db->prepare("INSERT INTO messages (name,email,subject,message) VALUES (?,?,?,?)");
    $stmt->execute([htmlspecialchars($name), $email, htmlspecialchars($subject), htmlspecialchars($message)]);

    // Envoi de l'email de notification
    $to = 'rayenzalila@gmail.com';
    $mailSubject = 'Nouveau message Vitanova — ' . $subject;
    $mailBody = "Nom : $name\nEmail : $email\n\nMessage :\n$message";
    $headers = "From: $email\r\n" .
               "Reply-To: $email\r\n" .
               "X-Mailer: PHP/" . phpversion();
    @mail($to, $mailSubject, $mailBody, $headers);

    jsonResponse(true, MSG_CONTACT_SUCCESS);
} catch (Exception $e) {
    jsonResponse(false, MSG_CONTACT_SERVER_ERROR, [], 500);
}
