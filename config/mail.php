<?php
$dotenv = parse_ini_file(__DIR__ . '/../.env');

define('MAIL_HOST',       $dotenv['MAIL_HOST']       ?? 'smtp.gmail.com');
define('MAIL_PORT',       (int)($dotenv['MAIL_PORT'] ?? 587));
define('MAIL_USERNAME',   $dotenv['MAIL_USERNAME']   ?? '');
define('MAIL_PASSWORD',   $dotenv['MAIL_PASSWORD']   ?? '');
define('MAIL_FROM_EMAIL', $dotenv['MAIL_FROM_EMAIL'] ?? '');
define('MAIL_FROM_NAME',  $dotenv['MAIL_FROM_NAME']  ?? 'DocBook');
define('MAIL_ENCRYPTION', $dotenv['MAIL_ENCRYPTION'] ?? 'tls');
