<?php

require_once BASE_PATH . '/config/mail.php';

function render_password_reset_email(string $name, string $otp, string $expiresAt): string
{
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeOtp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');
    $safeExpiry = htmlspecialchars((new DateTimeImmutable($expiresAt))->format('g:i A, F j, Y'), ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocBook Password Reset OTP</title>
</head>
<body style="margin:0;background:#eef8fc;font-family:Arial,Helvetica,sans-serif;color:#1a2a3a;">
    <div style="max-width:640px;margin:0 auto;padding:32px 18px;">
        <div style="background:#ffffff;border:1px solid #cce8f0;border-radius:16px;padding:28px;box-shadow:0 10px 30px rgba(42,143,168,0.08);">
            <div style="font-size:24px;font-weight:700;margin-bottom:18px;color:#2a8fa8;">DocBook</div>
            <h1 style="font-size:22px;line-height:1.3;margin:0 0 14px;">Reset your password</h1>
            <p style="font-size:15px;line-height:1.7;margin:0 0 18px;">Hello {$safeName},</p>
            <p style="font-size:15px;line-height:1.7;margin:0 0 18px;">Use this one-time password to verify your password reset request:</p>
            <div style="font-size:34px;letter-spacing:6px;font-weight:700;text-align:center;padding:18px 16px;background:#eef8fc;border:1px dashed #5ab8d0;border-radius:14px;margin:22px 0;color:#1a2a3a;">{$safeOtp}</div>
            <p style="font-size:14px;line-height:1.7;margin:0 0 8px;">This code expires in 15 minutes.</p>
            <p style="font-size:14px;line-height:1.7;margin:0;">If you did not request this reset, you can safely ignore this email.</p>
        </div>
    </div>
</body>
</html>
HTML;
}

function send_smtp_mail(string $toEmail, string $subject, string $htmlBody, string $textBody, string $fromEmail = MAIL_FROM_EMAIL, string $fromName = MAIL_FROM_NAME): bool
{
    $socket = smtp_open_connection();
    if (!$socket) {
        return false;
    }

    try {
        smtp_expect_code($socket, 220);
        smtp_send_command($socket, 'EHLO ' . smtp_client_name(), [250]);

        if (MAIL_ENCRYPTION === 'tls') {
            smtp_send_command($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Unable to enable TLS for SMTP connection.');
            }
            smtp_send_command($socket, 'EHLO ' . smtp_client_name(), [250]);
        }

        smtp_send_command($socket, 'AUTH LOGIN', [334]);
        smtp_send_command($socket, base64_encode(MAIL_USERNAME), [334]);
        smtp_send_command($socket, base64_encode(MAIL_PASSWORD), [235]);
        smtp_send_command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
        smtp_send_command($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
        smtp_send_command($socket, 'DATA', [354]);

        $message = smtp_build_message($toEmail, $subject, $htmlBody, $textBody, $fromEmail, $fromName);
        smtp_write_data($socket, $message);
        smtp_expect_code($socket, 250);
        smtp_send_command($socket, 'QUIT', [221]);
        fclose($socket);
        return true;
    } catch (Throwable $throwable) {
        if (is_resource($socket)) {
            fclose($socket);
        }
        return false;
    }
}

function smtp_open_connection()
{
    $remote = sprintf('tcp://%s:%d', MAIL_HOST, MAIL_PORT);
    $socket = @stream_socket_client($remote, $errno, $errstr, 30);
    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, 30);
    return $socket;
}

function smtp_client_name(): string
{
    return gethostname() ?: 'localhost';
}

function smtp_send_command($socket, string $command, array $expectedCodes): string
{
    fwrite($socket, $command . "\r\n");
    $response = smtp_read_response($socket);
    $code = (int) substr($response, 0, 3);
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException('Unexpected SMTP response: ' . trim($response));
    }

    return $response;
}

function smtp_expect_code($socket, int $expectedCode): string
{
    $response = smtp_read_response($socket);
    $code = (int) substr($response, 0, 3);
    if ($code !== $expectedCode) {
        throw new RuntimeException('Unexpected SMTP response: ' . trim($response));
    }

    return $response;
}

function smtp_read_response($socket): string
{
    $response = '';
    while (!feof($socket)) {
        $line = fgets($socket, 515);
        if ($line === false) {
            break;
        }
        $response .= $line;
        if (preg_match('/^\d{3} /', $line)) {
            break;
        }
    }

    if ($response === '') {
        throw new RuntimeException('Empty SMTP response.');
    }

    return $response;
}

function smtp_build_message(string $toEmail, string $subject, string $htmlBody, string $textBody, string $fromEmail, string $fromName): string
{
    $boundary = '=_docbook_' . bin2hex(random_bytes(8));
    $headers = [
        'From: ' . smtp_format_address($fromEmail, $fromName),
        'To: <' . $toEmail . '>',
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];

    $body = implode("\r\n", [
        '--' . $boundary,
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 7bit',
        '',
        $textBody,
        '--' . $boundary,
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: 7bit',
        '',
        $htmlBody,
        '--' . $boundary . '--',
        '',
    ]);

    $message = implode("\r\n", $headers) . "\r\n\r\n" . $body;
    return preg_replace('/^\./m', '..', $message);
}

function smtp_write_data($socket, string $message): void
{
    fwrite($socket, $message . "\r\n.\r\n");
}

function smtp_format_address(string $email, string $name): string
{
    return sprintf('%s <%s>', $name, $email);
}

// Email templates — added for magic-link reset and signup OTP

function render_signup_otp_email(string $firstName, string $otp, string $expiresAt): string
{
    $safeName   = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
    $safeOtp    = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');
    $safeExpiry = htmlspecialchars(
        (new DateTimeImmutable($expiresAt))->format('g:i A, F j, Y'),
        ENT_QUOTES, 'UTF-8'
    );

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>DocBook — Verify Your Email</title></head>
<body style="margin:0;background:#eef8fc;font-family:Arial,Helvetica,sans-serif;color:#1a2a3a;">
<div style="max-width:640px;margin:0 auto;padding:32px 18px;">
  <div style="background:#fff;border:1px solid #cce8f0;border-radius:16px;padding:28px;box-shadow:0 10px 30px rgba(42,143,168,.08);">
    <div style="font-size:24px;font-weight:700;margin-bottom:18px;color:#2a8fa8;">DocBook</div>
    <h1 style="font-size:22px;line-height:1.3;margin:0 0 14px;">Verify your email address</h1>
    <p style="font-size:15px;line-height:1.7;margin:0 0 18px;">Hello {$safeName},</p>
    <p style="font-size:15px;line-height:1.7;margin:0 0 18px;">Use this one-time code to complete your DocBook registration:</p>
    <div style="font-size:38px;letter-spacing:10px;font-weight:700;text-align:center;padding:18px 16px;background:#eef8fc;border:1px dashed #5ab8d0;border-radius:14px;margin:22px 0;color:#1a2a3a;">{$safeOtp}</div>
    <p style="font-size:14px;line-height:1.7;margin:0 0 8px;">This code expires in 1 hour.</p>
    <p style="font-size:14px;line-height:1.7;margin:0;">If you did not sign up for DocBook, you can safely ignore this email.</p>
  </div>
</div>
</body>
</html>
HTML;
}

function render_magic_link_reset_email(string $name, string $resetUrl, string $expiresAt): string
{
    $safeName   = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeUrl    = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
    $safeExpiry = htmlspecialchars(
        (new DateTimeImmutable($expiresAt))->format('g:i A, F j, Y'),
        ENT_QUOTES, 'UTF-8'
    );

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>DocBook — Reset Your Password</title></head>
<body style="margin:0;background:#eef8fc;font-family:Arial,Helvetica,sans-serif;color:#1a2a3a;">
<div style="max-width:640px;margin:0 auto;padding:32px 18px;">
  <div style="background:#fff;border:1px solid #cce8f0;border-radius:16px;padding:28px;box-shadow:0 10px 30px rgba(42,143,168,.08);">
    <div style="font-size:24px;font-weight:700;margin-bottom:18px;color:#2a8fa8;">DocBook</div>
    <h1 style="font-size:22px;line-height:1.3;margin:0 0 14px;">Reset your password</h1>
    <p style="font-size:15px;line-height:1.7;margin:0 0 18px;">Hello {$safeName},</p>
    <p style="font-size:15px;line-height:1.7;margin:0 0 22px;">Click the button below to set a new password. This link is single-use and expires in 1 hour.</p>
    <div style="text-align:center;margin:28px 0;">
      <a href="{$safeUrl}"
         style="display:inline-block;background:#2a8fa8;color:#fff;font-weight:700;font-size:16px;
                padding:14px 36px;border-radius:10px;text-decoration:none;letter-spacing:.3px;">
        Reset Password
      </a>
    </div>
    <p style="font-size:13px;color:#8aa3b8;line-height:1.6;margin:0 0 6px;">
      Or paste this URL into your browser:
    </p>
    <p style="font-size:12px;word-break:break-all;color:#5ab8d0;margin:0 0 18px;">{$safeUrl}</p>
    <p style="font-size:14px;line-height:1.7;margin:0;">If you did not request a password reset, you can safely ignore this email — your password will not change.</p>
  </div>
</div>
</body>
</html>
HTML;
}