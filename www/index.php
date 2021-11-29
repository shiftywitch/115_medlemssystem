<?php
require_once "assets/inc/html.inc.php";
require_once "assets/inc/functions.inc.php";
require_once "assets/inc/init.inc.php";

reDirectIfNotLoggedIn();

if (isset($_POST['submit'])) {
    logOutBruker();
    header("location: login.php");
}

htmlHeader("Frontpage");
?>


<div class="container text-center">

    <h1>Velkommen tilbake <?php echo $_SESSION['brukerEpost']; ?></h1>

    <br />

    <p><a href="aktiviteter.php"><button type="button" class="btn btn-primary">Registrer en aktivitet</button></a></p>

    <p><a href="medlemmer.php"><button type="button" class="btn btn-primary">Se medlemmer</button></a></p>

    <p><a href="mail.php"><button type="button" class="btn btn-primary">Send mail</button></a></p>

    <p><a href="assets/util/setup.php"><button type="button" class="btn btn-secondary">Setup database</button></a></p>

    <form method="post"><button type="submit" name="submit" class="btn btn-warning">Logg ut!</button></form>

</div>
<?php
htmlFooter();
?>