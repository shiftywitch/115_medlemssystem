<?php
    function htmlHeader(string $title='title', string $style = '', string $stylesheet='') {
        global $config;
        $root = $config["general"]["projectRoot"] ?? "";

        echo "<!DOCTYPE html>\n";
        echo "<html lang='no'>\n";
        echo "<head>\n";
        echo "\t<meta charset='UTF-8'>\n";
        echo "\t<meta http-equiv='X-UA-Compatible' content='IE=edge'>\n";
        echo "\t<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
        echo "\t<title>$title</title>\n";

        echo $stylesheet;
        echo "\t<link rel='stylesheet' href='$root/css/bootstrap.min.css' type='text/css' />\n";
        echo "\t<link rel='stylesheet' href='$root/css/index.css' type='text/css' />\n";
        echo "\t<style>$style</style>";
        echo "\t<script src='$root/js/jquery-3.5.1.min.js'></script>\n";
        echo "\t<script src='$root/js/bootstrap.min.js'></script>\n";

        echo "</head>\n";
        echo "<body class='text-white bg-dark'>\n";
        htmlNavbar();
    }

    function htmlFooter() {
        echo "</body>\n";
        echo "</html>";
    }

    function htmlNavbar() {
        global $config;
        $root = $config["general"]["projectRoot"] ?? "";
        $request = explode('/', $_SERVER['REQUEST_URI']);
        $side = $request[ array_key_last($request)];

        $meny["Hjem"] = "/";
        $meny["Medlemmer"] = "/medlemmer.php";
        $meny["Aktiviteter"] = "/aktiviteter.php";
        $meny[""] = "/mail.php";

        echo "<nav class='navbar navbar-expand-md navbar-dark bg-primary mb-3'>
    <div class='container-md '>
        <a class='navbar-brand' href='$root/'>
        <img src='$root/img/logo.png' alt='NEO' height='24' class='d-inline-block align-text-top'>
        <img src='$root/img/logotext.png' alt='NEO' height='24' class='d-inline-block align-text-top' style='filter: invert(0.9);'>
        </a>
        <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav' aria-controls='navbarNav' aria-expanded='false' aria-label='Vis navigasjon'>
            <span class='navbar-toggler-icon'></span>
        </button>
        <div class='collapse navbar-collapse justify-content-end' id='navbarNav'>
            <ul class='navbar-nav d-flex'>
            ";

        foreach ($meny as $tekst => $link){

            echo "<li class='nav-item'><a class='nav-link";
            if("/".$side == $link){ echo " active' aria-current='page"; }
            echo ($link == "/mail.php") ? "' href='$root$link'><img width='17px' src='img/mail.svg' alt='mail'> $tekst</a></li>" : "' href='$root$link'>$tekst</a></li>";
        }
        echo "
            </ul>
        </div>
    </div>
</nav>";
    }