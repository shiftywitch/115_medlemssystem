<?php
    function htmlHeader(string $title='title') {
        echo "<!DOCTYPE html>\n";
        echo "<html lang='en'>\n";
        echo "<head>\n";
        echo "\t<meta charset='UTF-8'>\n";
        echo "\t<meta http-equiv='X-UA-Compatible' content='IE=edge'>\n";
        echo "\t<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
        echo "\t<title>$title</title>\n";

        echo "\t<link rel='stylesheet' href='/www/css/bootstrap.min.css' type='text/css' />\n";
        echo "\t<link rel='stylesheet' href='/www/css/index.css' type='text/css' />\n";

        echo "\t<script src='/www/js/jquery-3.5.1.min.js'></script>\n";
        echo "\t<script src='/www/js/bootstrap.min.js'></script>\n";
        echo "</head>\n";
        echo "<body>\n";
    }

    function htmlFooter() {
        echo "</body>\n";
        echo "</html>";
    }