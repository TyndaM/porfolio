<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

function json_response(bool $ok, string $message, int $status = 200): void
{
    http_response_code($status);
    echo json_encode([
        'ok' => $ok,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function safe_substr(string $value, int $maxLength): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }

    return substr($value, 0, $maxLength);
}

function safe_strlen(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    return strlen($value);
}

function field(string $name, int $maxLength): string
{
    $value = trim((string)($_POST[$name] ?? ''));
    $value = str_replace(["\r", "\n"], ' ', $value);
    return safe_substr($value, $maxLength);
}

function clean_message(string $name, int $maxLength): string
{
    $value = trim((string)($_POST[$name] ?? ''));
    return safe_substr($value, $maxLength);
}

function header_address(string $name, string $email): string
{
    $cleanName = preg_replace('/[^\pL\pN\s\.\-_\']/u', '', $name) ?: 'Contact portfolio';
    return sprintf('"%s" <%s>', addcslashes($cleanName, '"\\'), $email);
}

function rate_limit_key(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'portfolio-contact-' . hash('sha256', $ip) . '.txt';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Méthode non autorisée.', 405);
}

if (!empty($_POST['website'] ?? '')) {
    json_response(false, 'Soumission invalide.', 400);
}

$rateLimitFile = rate_limit_key();
$now = time();
if (is_file($rateLimitFile)) {
    $lastSubmission = (int)file_get_contents($rateLimitFile);
    if ($lastSubmission > 0 && ($now - $lastSubmission) < 45) {
        json_response(false, 'Merci de patienter avant de renvoyer un message.', 429);
    }
}

$name = field('name', 80);
$email = field('email', 120);
$subject = field('subject', 120);
$message = clean_message('message', 3000);

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    json_response(false, 'Tous les champs sont obligatoires.', 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Adresse email invalide.', 422);
}

if (safe_strlen($message) < 12) {
    json_response(false, 'Le message doit contenir au moins 12 caractères.', 422);
}

$receiver = getenv('CONTACT_RECEIVER_EMAIL') ?: 'tindameshoullam@gmail.com';
$fromEmail = getenv('CONTACT_FROM_EMAIL') ?: $receiver;
$siteName = getenv('CONTACT_SITE_NAME') ?: 'Portfolio Meshoulam Tinda';

if (!filter_var($receiver, FILTER_VALIDATE_EMAIL) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Configuration email invalide côté serveur.', 500);
}

$emailSubject = '[Portfolio] ' . $subject;
$body = implode("\n", [
    'Nouveau message depuis le portfolio.',
    '',
    'Nom: ' . $name,
    'Email: ' . $email,
    'Sujet: ' . $subject,
    '',
    'Message:',
    $message,
    '',
    'Répondez directement à cet email: le champ Reply-To contient l’adresse du visiteur.',
]);

$headers = [
    'From: ' . header_address($siteName, $fromEmail),
    'Reply-To: ' . header_address($name, $email),
    'Content-Type: text/plain; charset=UTF-8',
    'MIME-Version: 1.0',
];

$sent = mail($receiver, $emailSubject, $body, implode("\n", $headers));

if (!$sent) {
    json_response(false, 'Le serveur mail n’a pas accepté le message. Vérifiez la configuration d’hébergement.', 500);
}

@file_put_contents($rateLimitFile, (string)$now, LOCK_EX);

json_response(true, 'Votre message a bien été envoyé. Vous pouvez recevoir une réponse directement par email.');
