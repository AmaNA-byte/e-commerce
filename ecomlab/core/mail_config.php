<?php
// Email settings, used by forgot-password and order confirmations. Sending goes through PHPMailer (lib/PHPMailer).
//
// The address and app password are left blank here on purpose: fill them in on your own machine/server only.
// Gmail setup:
//   1. Turn on 2-Step Verification for the Gmail account: https://myaccount.google.com/security
//   2. Create an App Password: https://myaccount.google.com/apppasswords
//   3. Put the Gmail address in MAIL_USERNAME and the 16-letter app password in MAIL_PASSWORD.
// Never use the normal Gmail password here.
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', ''); // e.g. yourshop@gmail.com
define('MAIL_PASSWORD', ''); // the 16-letter app password
define('MAIL_FROM_NAME', 'Shoppn');

require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

function mail_is_configured() {
  return MAIL_USERNAME !== '' && MAIL_PASSWORD !== '';
}

// Why the last send_email() failed ('' if it worked), so localhost pages can show it while testing.
$GLOBALS['mail_error'] = '';

// Sends one email. $html is the formatted message, $text the plain-text version.
// Returns true if sent, false otherwise (the reason goes to the PHP error log and $GLOBALS['mail_error']).
function send_email($to, $to_name, $subject, $html, $text) {
  if (!mail_is_configured()) {
    $GLOBALS['mail_error'] = 'Email is not set up yet: fill in MAIL_USERNAME and MAIL_PASSWORD in core/mail_config.php.';
    error_log('Email not sent: ' . $GLOBALS['mail_error']);
    return false;
  }
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->Port = MAIL_PORT;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
    $mail->addAddress($to, $to_name);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $html;
    $mail->AltBody = $text;
    $mail->send();
    return true;
  } catch (Exception $ex) {
    $GLOBALS['mail_error'] = 'Gmail refused the email: ' . $mail->ErrorInfo
      . (stripos($mail->ErrorInfo, 'authenticate') !== false ? ' (check MAIL_USERNAME, and that MAIL_PASSWORD is a 16-letter app password, not your normal Gmail password).' : '');
    error_log('Email to ' . $to . ' failed: ' . $mail->ErrorInfo);
    return false;
  }
}
