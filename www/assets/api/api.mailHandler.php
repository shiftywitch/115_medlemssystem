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
//Henter ut variabler fra config filen for smtp
$mail->Host = $config["mail"]["smtpServer"];
$mail->SMTPAuth = true;
$mail->Username = $config["mail"]["username"];
$mail->Password = $config["mail"]["password"];
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$mail->Port = 465;
$mail->setLanguage('nb', '/optional/path/to/language/directory/');

//sendMail() tar imot informasjon om mottakere og mail. Og har muligheten til å sende filer.
//Bruker biblioteket phpMailer for å sende mail
function sendMail(array $til, $emne, $msg, $filTmpName = null, $filName = null) {
    global $mail;

    try {
        //Til og fra
        $mail->setFrom('neo.ungdomsklubb@gmail.com', 'NEO UK');
        //Går gjennom en array. Ettersom det kan være flere mottakere
        //Adder hver av dem som mottaker.
        foreach ($til as $email) {
            $mail->addAddress("" . $email . "");
        }
        $mail->addReplyTo('noreply@neo.com');

        //Body
        $mail->WordWrap = 70;
        //Gjør det mulig å skrive html i mailen
        $mail->isHTML(true);
        $mail->Subject = $emne;
        $mail->Body = $msg;
        $mail->AltBody = strip_tags($msg);
        //Om det er lagt til en fil, så legges den til som attachment
        if ($filTmpName != null && $filName != null) {$mail->addAttachment($filTmpName, $filName);}

        echo ($mail->send()) ? "<p>Epost sendt</p>" : "<p>Epost ble ikke sendt. Prøv igjen senere!</p>";
    } catch (Exception $e) {
        echo $e;
    }
}

//Intern funksjon i filen for å skrive ut errors og avslutte scriptet om det er noen.
function anyError(array $errorList) {
    if (!empty($errorList)) {
        foreach ($errorList as $e) {
            echo "<p>$e</p>";
        }
        exit();
    }
}

//Om det blir sendt en post request til denne filen, om en vil sende en mail
if (isset($_POST['submit'])) {
    //Får tak i Post variabler som skal brukes i mailen
    $mottaker = strip_tags($_POST['mottaker']);
    $emne = strip_tags($_POST['emne']);
    $melding = $_POST['melding'];
    $alleMedlemmer = strip_tags($_POST['alleMedlemmer']);
    $error = [];

    //Mottakere lagres som en array uansett.
    $mottakere = [];

    //Om allemedlemmer er satt
    if ($alleMedlemmer == 'true') {
        //Henter ut alle medlemmer
        $mottakere = Medlem::hentAlleMedlemMailAdresser($db);
        //Om det er skrevet inn en enkel email og
        if ($mottaker != '' && !invalidEmail($mottaker)) {
            //Så legges den også i $mottakere[]
            $mottakere[] = $mottaker;
        }
        //Feilvalidering
        if (emptyInputs($emne, $melding)) {$error[] = "Fyll ut alle felt";}
        //Ellers gjørs vanlig feilvalidering
    } else {
        if (invalidEmail($mottaker)) {$error[] = "Ugyldig email";}
        if (emptyInputs($mottaker, $emne, $melding)) {$error[] = "Fyll ut alle felt";}
        //Legges mottakeren i $mottakere[]. Om den er '', så stoppes det uansett av anyError() under.
        if (!emptyInputs($mottaker)) {$mottakere[] = $mottaker;}
    }

    //Om det er noen feil så printes feilmeldinger og scriptet avsluttes
    anyError($error);

    //Om det er lastet opp en fil
    if (!empty($_FILES['tillegg'] ?? null)) {
        $filName = $_FILES['tillegg']['name'];
        $filTmpName = $_FILES['tillegg']['tmp_name'];
        $filType = $_FILES['tillegg']['type'];

        //Filtyper som er lov
        $filTyper = ['application/pdf', 'image/jpeg', 'image/png'];

        //Ser om opplastet fil har gyldig filtype
        if (in_array($filType, $filTyper) === false) {
            $error[] = "Fil er ikke av akseptert type. Vennligst bruk jpg/png eller pdf.";
        }

        anyError($error);
        //Sender mail om det ikke er noen feil
        sendMail($mottakere, $emne, $melding, $filTmpName, $filName);
        exit();
    }

    //Sender mail om det ikke er lastet opp en fil.
    sendMail($mottakere, $emne, $melding);
}