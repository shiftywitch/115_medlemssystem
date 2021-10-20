<?php

$err = array();
$msg = array();


require_once "../inc/dbSql.inc.php";
require_once '../inc/config.inc.php';

global $config;

if(isset($_POST['brukerDatabase'])){
    require '../inc/init.inc.php';
    $db = database();

    $db->query("DROP DATABASE IF EXISTS {$config["db"]["database"]};");
    $db->query("CREATE DATABASE {$config["db"]["database"]};");
    $db->query("USE {$config["db"]["database"]};");

    foreach (dbSetupSQL() as $table => $query) {
        $db->query($query);
        if ($temp = $db->error) {
            $err[] = "Database error: " . $temp;
        } else {
            $msg[] = "$table query fullførte";
        }
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
