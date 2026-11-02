
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

function kirimEmail($tujuan, $subjek, $isiHtml, $isiText = '')
{
    try {
        $config = require __DIR__ . '/mail.php';

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $config['port'];

        $mail->CharSet = 'UTF-8';

        $mail->setFrom(
            $config['from_email'],
            $config['from_name']
        );

        $mail->addAddress($tujuan);

        $mail->isHTML(true);
        $mail->Subject = $subjek;
        $mail->Body    = $isiHtml;
        $mail->AltBody = $isiText ?: strip_tags($isiHtml);

        $mail->send();

        return true;

    } catch (Exception $e) {
    error_log('Gagal mengirim email: ' . $e->getMessage());
    echo 'PHPMailer Error: ' . $e->getMessage();
    return false;

    } catch (Throwable $e) {
        error_log('Kesalahan mailer: ' . $e->getMessage());
        echo 'PHP Error: ' . $e->getMessage();
        return false;
    }
}
