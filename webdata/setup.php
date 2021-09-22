<?php

$err = array();
$msg = array();


$loginSQL = "
CREATE OR REPLACE TABLE Bruker (
    `brukerId` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `epost` VARCHAR(220) NOT NULL UNIQUE,
    `passord` VARCHAR(220) NOT NULL,
    `registrert` DATE NOT NULL DEFAULT(CURRENT_DATE),
    `ckey` VARCHAR(220),
    `ctime` VARCHAR(220)
);";


if(isset($_POST['brukerDatabase'])){
    require 'init.php';
    $db = database();

    $query = $db->query($loginSQL);
    if($temp = $db->error){
        $err[] = "Database error:<br>".$temp;
    }
    else {
        $msg[] = "Bruker-tabellen er satt opp.";
    }
}

?><!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup</title>
    <style>
        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }
        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }
        .alert {
            position: relative;
            padding: 1rem 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: .25rem;
        }
    </style>
</head>
<body>
<div style='max-width: 500px; margin: auto; text-align: center;'>
    <h1>Setup</h1>
    <?php
    if(!empty($err)){
        foreach ($err as $e) {
            echo "<div class='alert alert-danger'>$e</div>";
        }
    }
    if(!empty($msg)){
        foreach ($msg as $m) {
            echo "<div class='alert alert-success'>$m</div>";
        }
    }
    if(isset($fatalErr)){
        die();
    }
    ?>
    <form action='setup.php' method='POST'>
        <p><button name="brukerDatabase">Sett opp brukerdatabasen</button></p>
<!--        <p><button name="recipeTables">Setup recipe tables</button></p>-->
        <br>
        <p><button name="goProject">Gå til prosjektet</button></p>
    </form>
</body>
</html>
