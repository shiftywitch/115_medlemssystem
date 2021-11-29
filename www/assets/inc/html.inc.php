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
        echo "<body>\n";
    }

    function htmlFooter() {
        echo "</body>\n";
        echo "</html>";
    }

    function htmlNavbar(): string {
        return "";
    }