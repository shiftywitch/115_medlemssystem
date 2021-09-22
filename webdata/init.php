<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once 'config.php';

if(!isset($config) || empty($config)){
    die("<h1>Configuration error</h1><p>Copy the webdata/config.sample.php file to webdata/config.php and fill out your connection settings.</p>");
}

$projectRoot = $config["general"]["projectRoot"];

function getConfig($val, $group = "general"){
    global $config;

    if(isset($config[$group][ $val ])){
        return $config[$group][$val];
    }
    return false;
}

function database(){
    global $config;

    $db = new mysqli($config["db"]["host"], $config["db"]["user"], $config["db"]["pass"], $config["db"]["database"]);

    if($db->connect_error){
        die("Connection failed: " . $db->connect_error);
    }

    return $db;
}
