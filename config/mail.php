<?php
<<<<<<< HEAD

define('MAIL_HOST', 'sandbox.smtp.mailtrap.io');
define('MAIL_PORT', 2525);
define('MAIL_USERNAME', 'd63c713803c050');
define('MAIL_PASSWORD', 'e8585d60b418c2');
// Change this to any registered sender address you want Mailtrap to show.
define('MAIL_FROM_EMAIL', 'support@docbook.app');
define('MAIL_FROM_NAME', 'DocBook Support');
define('MAIL_ENCRYPTION', 'tls');
=======
$dotenv = parse_ini_file(__DIR__ . '/../.env');

define('MAIL_HOST',       $dotenv['MAIL_HOST']       ?? 'smtp.gmail.com');
define('MAIL_PORT',       (int)($dotenv['MAIL_PORT'] ?? 587));
define('MAIL_USERNAME',   $dotenv['MAIL_USERNAME']   ?? '');
define('MAIL_PASSWORD',   $dotenv['MAIL_PASSWORD']   ?? '');
define('MAIL_FROM_EMAIL', $dotenv['MAIL_FROM_EMAIL'] ?? '');
define('MAIL_FROM_NAME',  $dotenv['MAIL_FROM_NAME']  ?? 'DocBook');
define('MAIL_ENCRYPTION', $dotenv['MAIL_ENCRYPTION'] ?? 'tls');
>>>>>>> 5353f4c (Final complete work)
