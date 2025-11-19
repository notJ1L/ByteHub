<?php
// Mailtrap SMTP configuration (fallbacks keep local dev simple)
define('MAILTRAP_HOST', getenv('MAILTRAP_HOST') ?: 'sandbox.smtp.mailtrap.io');
define('MAILTRAP_PORT', (int) (getenv('MAILTRAP_PORT') ?: 2525));
define('MAILTRAP_USER', getenv('MAILTRAP_USER') ?: '3329efdf929ecd');
define('MAILTRAP_PASS', getenv('MAILTRAP_PASS') ?: '32522a7460890d');
