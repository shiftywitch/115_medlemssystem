<?php
require_once "../inc/init.inc.php";

header("Content-type: application/json");

if(isset($_GET['postnummer'])){
    $db = database();

    $stmt = $db->prepare("SELECT poststed FROM Postnummer WHERE postnummer = ? LIMIT 1");
    $stmt->bind_param("i", $_GET['postnummer']);
    $stmt->execute();

    $result = $stmt->get_result();
    if($result->num_rows == 1){
        jsonReturn($result->fetch_all()[0][0] ?? "");
    }
    else {
        jsonReturn("Postnummer ikke gjenkjent!", 404);
    }
}

jsonReturn("Ingenting å gjøre");

function jsonReturn($message, $code = 0){
    if($code == 0){
        echo json_encode(array($message));
    }
    else {
        http_response_code($code);
        echo json_encode(array("error"=>$message, "code"=>$code));
    }
    die();
}