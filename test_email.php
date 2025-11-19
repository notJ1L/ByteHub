<?php
// Simple test email sender using PHPMailer and current SMTP config

require_once __DIR__ . '/includes/config.php';

// Prefer Composer autoload if available
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
	require_once $autoload;
}

// If Composer autoload not available, attempt legacy includes (may not work if library not installed)
if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
	$legacyBase = __DIR__ . '/includes/PHPMailer/';
	if (file_exists($legacyBase . 'PHPMailer.php')) {
		@require_once $legacyBase . 'Exception.php';
		@require_once $legacyBase . 'PHPMailer.php';
		@require_once $legacyBase . 'SMTP.php';
	}
}

if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer') && !class_exists('PHPMailer')) {
	header('Content-Type: text/plain; charset=utf-8');
	http_response_code(500);
	echo "ERROR: PHPMailer is not installed. Install via Composer: composer require phpmailer/phpmailer\n";
	echo "Or place the PHPMailer source in includes/PHPMailer with PHPMailer.php, SMTP.php, Exception.php.\n";
	exit;
}

$toEmail = 'caisipjonel29@gmail.com';
$toName  = 'Test Recipient';

try {
	if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
		$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
	} else {
		$mail = new \PHPMailer(true); // legacy global class
	}

	$mail->isSMTP();
	$mail->Host       = MAILTRAP_HOST;
	$mail->SMTPAuth   = true;
	$mail->Username   = MAILTRAP_USER;
	$mail->Password   = MAILTRAP_PASS;
	$mail->SMTPSecure = defined('PHPMailer\\PHPMailer\\PHPMailer::ENCRYPTION_STARTTLS')
		? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS
		: 'tls';
	$mail->Port       = MAILTRAP_PORT;

	$mail->setFrom(MAILTRAP_USER, 'ByteHub Test');
	$mail->addAddress($toEmail, $toName);

	$mail->isHTML(true);
	$mail->Subject = 'ByteHub SMTP Test (Gmail)';
	$mail->Body    = '<h2>SMTP Test Successful</h2><p>This is a test email sent via Gmail SMTP using PHPMailer.</p>';
	$mail->AltBody = 'SMTP Test Successful - This is a test email sent via Gmail SMTP using PHPMailer.';

	$mail->send();

	header('Content-Type: text/plain; charset=utf-8');
	echo "OK: Test email sent to {$toEmail}.\n";
} catch (\Throwable $e) {
	header('Content-Type: text/plain; charset=utf-8');
	http_response_code(500);
	echo "ERROR: " . $e->getMessage() . "\n";
}

