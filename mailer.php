<?php
/**
 * V4U Cleaning Services – Quote Form Mailer
 * -----------------------------------------
 * Receives AJAX POST from index.html,
 * sends the enquiry to info@v4ucleaningservices.co.uk
 * via PHPMailer with SMTP.
 *
 * SETUP INSTRUCTIONS:
 *  1. Install Composer: https://getcomposer.org/
 *  2. Run: composer require phpmailer/phpmailer
 *  3. Fill in the SMTP CONFIG section below with your host credentials.
 *  4. Upload index.html, mailer.php, and the /vendor folder to your web root.
 */

/* ── Composer autoloader ── */
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/* ── Only accept POST ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

/* ─────────────────────────────────────────────────
   SMTP CONFIG  ← fill these in before going live
───────────────────────────────────────────────── */
define('SMTP_HOST',     'mail.v4ucleaningservices.co.uk'); // Your SMTP host
define('SMTP_PORT',     587);                               // 587 (STARTTLS) or 465 (SSL)
define('SMTP_SECURE',   PHPMailer::ENCRYPTION_STARTTLS);    // PHPMailer::ENCRYPTION_SMTPS for port 465
define('SMTP_USER',     'info@v4ucleaningservices.co.uk');  // SMTP username / sending address
define('SMTP_PASS',     'YOUR_EMAIL_PASSWORD_HERE');        // SMTP password
define('MAIL_FROM',     'info@v4ucleaningservices.co.uk');  // From address
define('MAIL_FROM_NAME','V4U Cleaning Services Website');   // From display name
define('MAIL_TO',       'info@v4ucleaningservices.co.uk');  // Recipient
define('MAIL_SUBJECT',  'New Quote Request – V4U Cleaning Services');
/* ─────────────────────────────────────────────────────────────────── */

/* ── Sanitise helper ── */
function clean(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

/* ── Collect & sanitise fields ── */
$fullName       = clean($_POST['fullName']       ?? '');
$companyName    = clean($_POST['companyName']    ?? '');
$emailAddress   = filter_var(trim($_POST['emailAddress'] ?? ''), FILTER_SANITIZE_EMAIL);
$phoneNumber    = clean($_POST['phoneNumber']    ?? '');
$serviceRequired= clean($_POST['serviceRequired']?? '');
$postcode       = clean($_POST['postcode']       ?? '');
$additionalInfo = clean($_POST['additionalInfo'] ?? '');

/* ── Basic server-side validation ── */
$required = [$fullName, $companyName, $emailAddress, $phoneNumber, $serviceRequired, $postcode];
foreach ($required as $field) {
    if ($field === '') {
        http_response_code(422);
        exit('validation_error');
    }
}
if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    exit('invalid_email');
}

/* ── Build HTML email body ── */
$htmlBody = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body  { font-family: Arial, sans-serif; color: #1a1a2e; background:#f0f6f9; margin:0; padding:0; }
  .wrap { max-width:620px; margin:30px auto; background:#ffffff; border-radius:10px; overflow:hidden;
          box-shadow:0 4px 20px rgba(0,0,0,0.08); }
  .hdr  { background:#0a1628; padding:28px 32px; }
  .hdr h1 { color:#00c4b4; font-size:1.3rem; margin:0; letter-spacing:0.05em; }
  .hdr p  { color:#8a9bb5; font-size:0.78rem; margin:6px 0 0; }
  .body { padding:32px; }
  .row  { border-bottom:1px solid #e8f0f7; padding:14px 0; display:flex; gap:16px; }
  .row:last-child { border-bottom:none; }
  .lbl  { font-size:0.72rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase;
          color:#8a9bb5; min-width:160px; padding-top:2px; }
  .val  { font-size:0.9rem; color:#0a1628; line-height:1.6; }
  .msg  { background:#f0faf9; border-left:3px solid #00c4b4; border-radius:4px;
          padding:14px 18px; margin-top:24px; font-size:0.88rem; color:#0a1628; line-height:1.7; }
  .ftr  { background:#0f2044; padding:18px 32px; font-size:0.75rem; color:#8a9bb5; text-align:center; }
</style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <h1>V4U Cleaning Services</h1>
    <p>New Quote Request Received – ' . date('d M Y, H:i') . '</p>
  </div>
  <div class="body">
    <div class="row">
      <span class="lbl">Full Name</span>
      <span class="val">' . $fullName . '</span>
    </div>
    <div class="row">
      <span class="lbl">Company</span>
      <span class="val">' . $companyName . '</span>
    </div>
    <div class="row">
      <span class="lbl">Email</span>
      <span class="val"><a href="mailto:' . $emailAddress . '" style="color:#00c4b4;">' . $emailAddress . '</a></span>
    </div>
    <div class="row">
      <span class="lbl">Phone</span>
      <span class="val">' . $phoneNumber . '</span>
    </div>
    <div class="row">
      <span class="lbl">Service Required</span>
      <span class="val">' . $serviceRequired . '</span>
    </div>
    <div class="row">
      <span class="lbl">Postcode</span>
      <span class="val">' . $postcode . '</span>
    </div>
    ' . ($additionalInfo ? '
    <div class="msg">
      <strong>Additional Information:</strong><br>' . nl2br($additionalInfo) . '
    </div>' : '') . '
  </div>
  <div class="ftr">
    This message was sent via the quote form at v4ucleaningservices.co.uk
  </div>
</div>
</body>
</html>';

/* ── Plain-text fallback ── */
$plainBody = "New Quote Request – V4U Cleaning Services\n"
           . str_repeat('-', 44) . "\n"
           . "Full Name      : $fullName\n"
           . "Company        : $companyName\n"
           . "Email          : $emailAddress\n"
           . "Phone          : $phoneNumber\n"
           . "Service        : $serviceRequired\n"
           . "Postcode       : $postcode\n"
           . ($additionalInfo ? "\nAdditional Info:\n$additionalInfo\n" : '')
           . "\nSent: " . date('d M Y H:i') . "\n";

/* ── Send with PHPMailer ── */
$mail = new PHPMailer(true);

try {
    /* Server settings */
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Uncomment to debug SMTP
    $mail->isSMTP();
    $mail->Host        = SMTP_HOST;
    $mail->SMTPAuth    = true;
    $mail->Username    = SMTP_USER;
    $mail->Password    = SMTP_PASS;
    $mail->SMTPSecure  = SMTP_SECURE;
    $mail->Port        = SMTP_PORT;
    $mail->CharSet     = 'UTF-8';

    /* Sender & recipient */
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress(MAIL_TO, 'V4U Cleaning Services');

    /* Reply-to: the person who filled the form */
    $mail->addReplyTo($emailAddress, $fullName);

    /* Content */
    $mail->isHTML(true);
    $mail->Subject = MAIL_SUBJECT;
    $mail->Body    = $htmlBody;
    $mail->AltBody = $plainBody;

    $mail->send();
    echo 'success';

} catch (Exception $e) {
    // Log error server-side (optional)
    error_log('[V4U Mailer] Mail error: ' . $mail->ErrorInfo);
    http_response_code(500);
    echo 'mail_error';
}
