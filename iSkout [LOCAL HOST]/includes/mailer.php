<?php
// =============================================================
// iSkout — includes/mailer.php
// PHPMailer wrapper. Call sendOTP($to, $code) to send an OTP email.
// Uses SMTP credentials defined in includes/config.php.
// PHPMailer files live in lib/PHPMailer/ (no Composer needed).
// =============================================================

require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send a 4-digit OTP email to the given address.
 *
 * @param  string $to   Recipient email address (PUP webmail)
 * @param  string $code 4-digit OTP string
 * @param  string $subject Optional subject override
 * @return bool   true on success, false on failure
 */
function sendOTP(string $to, string $code, string $subject = 'Your iSkout Verification Code'): bool {
    $mail = new PHPMailer(true);

    try {
        // ── Server settings ───────────────────────────────────
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // ── Recipients ────────────────────────────────────────
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // ── Content ───────────────────────────────────────────
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = buildOTPEmailHTML($code, $to);
        $mail->AltBody = "Your iSkout verification code is: {$code}. It expires in " . OTP_EXPIRY_MINUTES . " minutes.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Log error silently; caller handles the failure
        error_log('iSkout Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Build the HTML body for an OTP email.
 */
function buildOTPEmailHTML(string $code, string $recipientEmail): string {
    $digits  = implode('', array_map(fn($c) => "<span style=\"display:inline-block;width:52px;height:60px;line-height:60px;text-align:center;background:#f4f4f4;border-radius:12px;font-size:32px;font-weight:800;color:#730000;margin:0 4px;\">{$c}</span>", str_split($code)));
    $expiry  = OTP_EXPIRY_MINUTES;
    $year    = date('Y');
    $escaped = htmlspecialchars($recipientEmail, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#f9f9f9;font-family:'Inter',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f9f9;padding:40px 0;">
    <tr><td align="center">
      <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
        <tr>
          <td style="background:#730000;padding:32px 40px;text-align:center;">
            <h1 style="margin:0;font-size:28px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">iSkout</h1>
            <p style="margin:8px 0 0;color:rgba(255,255,255,0.75);font-size:14px;">Campus Merchant Discovery</p>
          </td>
        </tr>
        <tr>
          <td style="padding:40px;">
            <h2 style="margin:0 0 8px;font-size:22px;font-weight:800;color:#121212;">Email Verification</h2>
            <p style="margin:0 0 24px;color:#555;font-size:15px;line-height:1.5;">
              Hi there! Enter the code below to verify your PUP Webmail and complete your iSkout registration.
            </p>
            <div style="text-align:center;margin:0 0 24px;">{$digits}</div>
            <p style="text-align:center;margin:0 0 24px;font-size:13px;color:#888;">
              This code expires in <strong>{$expiry} minutes</strong>. Do not share it with anyone.
            </p>
            <hr style="border:0;border-top:1px solid #e8e8e8;margin:0 0 24px;">
            <p style="font-size:12px;color:#aaa;margin:0;">
              If you did not request this, you can safely ignore this email.<br>
              This message was sent to <strong>{$escaped}</strong>.
            </p>
          </td>
        </tr>
        <tr>
          <td style="background:#f4f4f4;padding:20px 40px;text-align:center;">
            <p style="margin:0;font-size:12px;color:#aaa;">© {$year} iSkout — PUP Sta. Mesa</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}
