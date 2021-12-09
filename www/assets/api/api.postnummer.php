<?php
require_once "../inc/init.inc.php";

// Forteller nettleseren at innholdet på siden er av typen JSON. Dette gjør at noen nettlesere viser JSON veldig oversiktlig.
header("Content-type: application/json");

if(isset($_GET['postnummer'])){
    // Oppretter database-tilkobling
    $db = database();

    $stmt = $db->prepare("SELECT poststed FROM Postnummer WHERE postnummer = ? LIMIT 1");
    $stmt->bind_param("i", $_GET['postnummer']);
    $stmt->execute();

    $result = $stmt->get_result();

    // Her forventer vi kun en rad, så hvis det er flere eller færre så gir vi feilmelding.
    if($result->num_rows == 1){
        jsonReturn($result->fetch_all()[0][0] ?? "");
    }
    else {
        jsonReturn("Postnummer ikke gjenkjent!", 404);
    }
}

jsonReturn("Ingenting å gjøre");

// Funksjon som skriver ut JSON til nettleserne, med litt handling for å effektivisere.
function jsonReturn($message, $code = 0){
    if($code == 0){
        echo json_encode(array($message));
    }
    else {
        // Forteller nettleseren at det er "404-Siden ikke funnet" for eksempel.
        http_response_code($code);
        echo json_encode(array("error"=>$message, "code"=>$code));
    }
    die();
}
