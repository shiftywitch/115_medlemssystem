<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../inc/config.inc.php';
require_once __DIR__ . '/../inc/functions.inc.php';
require_once __DIR__ . '/../lib/medlem.class.php';

$db = database();
global $config;

//Konfigurerer server instillinger
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = $config["mail"]["smtpServer"];
$mail->SMTPAuth = true;
$mail->Username = $config["mail"]["username"];
$mail->Password = $config["mail"]["password"];
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$mail->Port = 465;
$mail->setLanguage('nb', '/optional/path/to/language/directory/');


function sendMail(array $til, $emne, $msg, $fil = null) {
    global $mail;

    try {
        //Til og fra
        $mail->setFrom('neo.ungdomsklubb@gmail.com', 'NEO UK');
        foreach ($til as $email) {
            $mail->addAddress("" . $email . "");
        }
        $mail->addReplyTo('noreply@neo.com');

        //Body
        $mail->WordWrap = 70;
        $mail->isHTML(true);
        $mail->Subject = $emne;
        $mail->Body = $msg;
        $mail->AltBody = strip_tags($msg);

        echo ($mail->send()) ? "Epost sendt" : "Epost ble ikke sendt. Prøv igjen senere!";
    } catch (Exception $e) {
        echo $e;
    }
}

if (isset($_POST['submit'])) {
    $mottaker = strip_tags($_POST['mottaker']);
    $emne = strip_tags($_POST['emne']);
    $melding = strip_tags($_POST['melding']);
    $alleMedlemmer = strip_tags($_POST['alleMedlemmer']);
    $error = [];

    $mottakere = [];

    if ($alleMedlemmer == 'true') {
        $mottakere = Medlem::hentAlleMedlemMailAdresser($db);
        if ($mottaker != '' && !invalidEmail($mottaker)) {
            $mottakere[] = $mottaker;
            if (emptyInputs($emne, $melding)) {$error[] = "Fyll ut alle felt";}
        }
    } else {
        if (invalidEmail($mottaker)) {$error[] = "Ugyldig email";}
        if (emptyInputs($mottaker, $emne, $melding)) {$error[] = "Fyll ut alle felt";}
    }


    if (!empty($error)) {
        foreach ($error as $e) {
            echo "<p>$e</p>";
        }
        exit();
    }
    sendMail($mottakere, $emne, $melding);
}