<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'connection.php';

if($db->connect_error){
    die("Connection failed: " . $blogdb->connect_error);
}
