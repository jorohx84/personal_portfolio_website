<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['success' => false, 'message' => 'Methode nicht erlaubt.']);
    exit;
}

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $config = require __DIR__ . '/config.php';
} catch (Throwable $e) {
    error_log('Contact form bootstrap error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Serverfehler beim Laden der Mail-Konfiguration: ' . $e->getMessage()
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungueltige Anfrage.']);
    exit;
}

// Honeypot for simple bots.
if (!empty($input['website'])) {
    echo json_encode(['success' => true]);
    exit;
}

$name = trim((string)($input['name'] ?? ''));
$email = trim((string)($input['email'] ?? ''));
$subject = trim((string)($input['subject'] ?? ''));
$message = trim((string)($input['message'] ?? ''));

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Bitte fuelle alle Pflichtfelder aus.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Bitte gib eine gueltige E-Mail-Adresse ein.']);
    exit;
}

if (mb_strlen($name) > 200 || mb_strlen($email) > 254 || mb_strlen($subject) > 200 || mb_strlen($message) > 5000) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Eine Eingabe ist zu lang.']);
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_username'];
    $mail->Password = $config['smtp_password'];
    $mail->Port = (int)$config['smtp_port'];

    if (($config['smtp_secure'] ?? 'tls') === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    $mail->CharSet = 'UTF-8';
    $mail->setFrom('kontakt@johannes-roth.de', 'Johannes Roth');
    $mail->addAddress('kontakt@johannes-roth.de', 'Johannes Roth');
    $mail->addReplyTo($email, $name);

    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

    $mail->isHTML(true);
    $mail->Subject = 'Neue Kontaktanfrage – ' . $name;
    $mail->Body = '<!doctype html><html lang="de"><head><meta charset="UTF-8"></head>'
        . '<body style="margin:0;padding:0;background:#111111;font-family:Arial,Helvetica,sans-serif;color:#f2f2f2;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#111111;padding:32px 16px;">'
        . '<tr><td align="center"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;background:#191919;border:1px solid #303030;border-radius:18px;overflow:hidden;">'
        . '<tr><td style="padding:28px 34px;border-bottom:1px solid #303030;">'
        . '<div style="font-size:18px;font-weight:700;letter-spacing:.04em;color:#f2f2f2;">JOHANNES <span style="color:#ffb400;">ROTH</span></div>'
        . '</td></tr>'
        . '<tr><td style="padding:34px;">'
        . '<div style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#ffb400;margin-bottom:12px;">Neue Anfrage</div>'
        . '<h1 style="margin:0 0 26px;font-size:30px;line-height:1.15;color:#f2f2f2;">Neue Kontaktanfrage</h1>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #303030;border-radius:14px;overflow:hidden;margin-bottom:28px;">'
        . '<tr><td style="padding:14px 16px;background:#202020;color:#8d8d8d;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;width:110px;">Name</td><td style="padding:14px 16px;font-size:14px;font-weight:700;color:#f2f2f2;">' . $safeName . '</td></tr>'
        . '<tr><td style="padding:14px 16px;background:#202020;color:#8d8d8d;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;border-top:1px solid #303030;">E-Mail</td><td style="padding:14px 16px;font-size:14px;color:#ffb400;border-top:1px solid #303030;"><a href="mailto:' . $safeEmail . '" style="color:#ffb400;text-decoration:none;">' . $safeEmail . '</a></td></tr>'
        . '<tr><td style="padding:14px 16px;background:#202020;color:#8d8d8d;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;border-top:1px solid #303030;">Betreff</td><td style="padding:14px 16px;font-size:14px;color:#f2f2f2;border-top:1px solid #303030;">' . $safeSubject . '</td></tr>'
        . '</table>'
        . '<div style="font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#8d8d8d;margin-bottom:10px;">Nachricht</div>'
        . '<div style="padding:20px;border-radius:14px;background:#202020;border:1px solid #303030;font-size:15px;line-height:1.7;color:#c7c7c7;">' . $safeMessage . '</div>'
        . '<div style="margin-top:28px;"><a href="mailto:' . $safeEmail . '" style="display:inline-block;padding:12px 18px;background:#ffb400;color:#111111;text-decoration:none;font-size:13px;font-weight:700;">Antworten ↗</a></div>'
        . '</td></tr>'
        . '<tr><td style="padding:18px 34px;background:#151515;border-top:1px solid #303030;font-size:11px;color:#777;">Diese Nachricht wurde über das Kontaktformular von johannes-roth.de gesendet.</td></tr>'
        . '</table></td></tr></table></body></html>';

    $mail->AltBody = "Neue Kontaktanfrage\n\nName: {$name}\nE-Mail: {$email}\nBetreff: {$subject}\n\nNachricht:\n{$message}";
    $mail->send();

    // Confirmation to the sender.
    $mail->clearAllRecipients();
    $mail->clearReplyTos();
    $mail->setFrom('kontakt@johannes-roth.de', 'Johannes Roth');
    $mail->addAddress($email, $name);
    $mail->Subject = 'Vielen Dank für deine Nachricht';

    $mail->Body = '<!doctype html><html lang="de"><head><meta charset="UTF-8"></head>'
        . '<body style="margin:0;padding:0;background:#111111;font-family:Arial,Helvetica,sans-serif;color:#f2f2f2;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#111111;padding:32px 16px;"><tr><td align="center">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#191919;border:1px solid #303030;border-radius:18px;overflow:hidden;">'
        . '<tr><td style="padding:28px 34px;border-bottom:1px solid #303030;"><div style="font-size:18px;font-weight:700;letter-spacing:.04em;color:#f2f2f2;">JOHANNES <span style="color:#ffb400;">ROTH</span></div></td></tr>'
        . '<tr><td style="padding:42px 34px 38px;">'
        . '<div style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#ffb400;margin-bottom:14px;">Nachricht erhalten</div>'
        . '<h1 style="margin:0 0 22px;font-size:30px;line-height:1.15;color:#f2f2f2;">Vielen Dank für deine Nachricht</h1>'
        . '<p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:#b0b0b0;">Hallo ' . $safeName . ',</p>'
        . '<p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:#b0b0b0;">vielen Dank für deine Nachricht. Ich habe deine Anfrage erhalten und melde mich schnellstmöglich bei dir.</p>'
        . '<p style="margin:30px 0 0;font-size:16px;line-height:1.7;color:#f2f2f2;">Viele Grüße<br><strong>Johannes Roth</strong></p>'
        . '</td></tr>'
        . '<tr><td style="padding:24px 34px;background:#151515;border-top:1px solid #303030;text-align:center;font-size:12px;color:#777;">johannes-roth.de · Fullstack Software Developer</td></tr>'
        . '</table></td></tr></table></body></html>';

    $mail->AltBody = "Hallo {$name},\n\nvielen Dank für deine Nachricht. Ich habe deine Anfrage erhalten und melde mich schnellstmöglich bei dir.\n\nViele Grüße\nJohannes Roth\n\njohannes-roth.de";

    try {
        $mail->send();
    } catch (Exception $confirmationException) {
        error_log('Contact confirmation mail error: ' . $mail->ErrorInfo);
    }

    echo json_encode(['success' => true, 'message' => 'Vielen Dank für deine Nachricht.']);
} catch (Throwable $e) {
    error_log('Contact form mail error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Mailversand fehlgeschlagen: ' . $e->getMessage()
    ]);
}
