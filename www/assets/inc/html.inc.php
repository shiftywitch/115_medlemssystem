<?php
    function htmlHeader(string $title='title', string $style = '', string $stylesheet='') {
        echo "<!DOCTYPE html>\n";
        echo "<html lang='no'>\n";
        echo "<head>\n";
        echo "\t<meta charset='UTF-8'>\n";
        echo "\t<meta http-equiv='X-UA-Compatible' content='IE=edge'>\n";
        echo "\t<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
        echo "\t<title>$title</title>\n";

        echo $stylesheet;
        echo "\t<style>$style</style>";

        echo "</head>\n";
        echo "<body>\n";
    }

    function htmlFooter() {
        echo "</body>\n";
        echo "</html>";
    }