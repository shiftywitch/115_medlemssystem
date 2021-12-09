<?php

require_once 'assets/inc/init.inc.php';
require_once 'assets/inc/functions.inc.php';
require_once 'assets/lib/medlem.class.php';

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
        $err[] = 'Fyll ut en gyldig epost';
    }
    if (!Medlem::getBrukerByEmail($email)) {
        $err[] = 'Feil brukernavn og/eller passord';
    }

    if (empty($err)) {
        //Om loggin feiler
        if (!loginUser($email, $passord)) {
            $err[] = 'Feil brukernavn og/eller passord';
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

    <div class="container d-flex justify-content-center align-items-center h-100" style="min-height: calc(100vh - 100px);">

        <div class="login p-2 rounded-3" style="box-shadow: #111e 0 0 3px 3px; background-color: #111;">
            <h2>Logg inn</h2>
            <?php
            if (!empty($err)) {
                foreach ($err as $error) {
                    echo "<div class='alert alert-danger' role='alert'>$error</div>";
                }
            }
            ?>
            <form method="post">
                <div class="form-floating text-dark mb-3">
                    <input type="email" name="email" class="form-control" id="floatingInput" placeholder="name@example.com">
                    <label for="floatingInput">Epost</label>
                </div>
                <div class="form-floating text-dark mb-3">
                    <input type="password" class="form-control" id="floatingPassword" name="pwd" placeholder="Password">
                    <label for="floatingPassword">Passord</label>
                </div>
                <button type="submit" name="submit" class="btn btn-primary w-100">Log in</button>
            </form>
        </div>

    </div>


<?php
htmlFooter();