<?php

function emptyInputs(...$inputs):bool {
    $toReturn = false;
    foreach ($inputs as $input) {
        if (empty($input)) {
            $toReturn = true;
        }
    }
    return $toReturn;
}

function invalidEmail(string $email):bool {
    return !filter_var($email, FILTER_VALIDATE_EMAIL);
}

function getBrukerByEmail(string $email) {
    global $db;

    if($db == null){
        $db = database();
    }
    $sql = "SELECT * FROM Bruker WHERE epost = ?;";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row;
    } else {
        return false;
    }
}

function loginUser(string $email, string $passord):bool {
    global $db;

    if($db == null){
        $db = database();
    }

    $bruker = getBrukerByEmail($email);

    if ($bruker) {
        $hashedPwdFraDB = $bruker['passord'];

        if ((password_verify($passord, $hashedPwdFraDB))) {
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
        $result = $db->query("SELECT * FROM bruker WHERE brukerId = '{$_SESSION['brukerId']}'");
        return $result->num_rows > 0;
    }

    return false;

}

function reDirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("location: login.php?error=notLoggedIn");
        exit();
    }
}

function logOutBruker() {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );

    session_destroy();
}