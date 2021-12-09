<?php

require_once "init.inc.php";
require_once __DIR__ . "/../lib/medlem.class.php";

//emptyInputs() tar imot et ubegrenset anntall argumenter som skal sjekkes.
//Returnerer true om det er noen inputs som er tomme.
function emptyInputs(...$inputs):bool {
    $toReturn = false;
    foreach ($inputs as $input) {
        if (empty($input) || $input == '') {
            $toReturn = true;
        }
    }
    return $toReturn;
}

//invalidEmail() returnerer true om email addressen ikke er gyldig
function invalidEmail(string $email):bool {
    return !filter_var($email, FILTER_VALIDATE_EMAIL);
}

//loginUser() tar imot login info for å logge inn bruker å opprette session.
function loginUser(string $email, string $passord):bool {
    global $db;

    if($db == null){
        $db = database();
    }

    //Henter bruker om den eksisterer
    $bruker = Medlem::getBrukerByEmail($email);

    //Om brukeren eksisterer
    if ($bruker) {
        $hashedPwdFraDB = $bruker['passord'];

        //Validerer om passordet stemmer med det hashet passordet som ligger i databasen
        if (password_verify($passord, $hashedPwdFraDB)) {
            //Starter da en session, og setter session attributter
            session_start();
            $_SESSION['brukerId'] = $bruker['brukerId'];
            $_SESSION['brukerEpost'] = $bruker['epost'];
            $_SESSION['brukerAgent'] = md5($_SERVER['HTTP_USER_AGENT']);
            return true;
        } else {
            return false;
        }
    }
    return false;
}

//isLoggedIn() skjekker om det er en logget inn bruker
function isLoggedIn():bool {
    global $db, $_SESSION;

    if($db == null){
        $db = database();
    }

    if(!isset($_SESSION)){
        session_start();
    }

    //Skjekker om session variabler stemmer. Har med USER_AGENT bare for litt ekstra sikkerhet
    //Kunne hatt med en nøkkel i databasen som også valideres.
    if (md5($_SERVER['HTTP_USER_AGENT']) == @$_SESSION['brukerAgent']) {
        $result = $db->query("SELECT * FROM Bruker WHERE brukerId = '{$_SESSION['brukerId']}'");
        return $result->num_rows > 0;
    }

    return false;

}

//reDirectIfNotLoggedIn() redirecter til login siden om bruker ikke er logget inn.
function reDirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("location: login.php?error=notLoggedIn");
        exit();
    }
}

//logOutBruker() logger ut bruker
function logOutBruker() {
    //Starter ved å sette session cookien til å være utløpt
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );

    //Ødelegger sessionen til slutt.
    session_destroy();
}