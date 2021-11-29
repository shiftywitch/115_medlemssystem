<?php

require_once 'assets/inc/init.inc.php';
require_once 'assets/inc/functions.inc.php';

if (isLoggedIn()) {
    header("location: ./");
    exit();
}

$err = [];

if (isset($_POST['submit'])) {

    $email = $_POST['email'];
    $passord = $_POST['pwd'];

    $db = database();

    if (emptyInputs($email, $passord) !== false) {
        $err[] = 'Fyll ut alle felt';
    }
    if (invalidEmail($email) !== false) {
        $err[] = 'Fyll ut en gyldig email';
    }
    if (!getBrukerByEmail($email)) {
        $err[] = 'Feil brukernavn og/eller passord';
    }

    if (empty($err)) {
        //Om loggin feiler
        if (!loginUser($email, $passord)) {
            $err[] = 'Feil brukernav og/eller passord';
        } else {
            //Om loggin er en suksess.
            header("location: ./");
            exit();
        }
    }
}

require_once 'assets/inc/html.inc.php';
htmlHeader("Login");
?>

    <div class="login">
        <h2>Log In</h2>
        <div>
            <form method="post">
                <input type="email" required name="email" placeholder="Email...">
                <input type="password" required name="pwd" placeholder="Password...">
                <button type="submit" name="submit">Log in</button>
            </form>
        </div>
        <?php
            if (!empty($err)) {
                foreach ($err as $error) {echo "<p>$error</p>";}
            }
        ?>
    </div>

<?php
htmlFooter();