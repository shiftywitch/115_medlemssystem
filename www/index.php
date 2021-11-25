<?php
require_once "assets/inc/html.inc.php";
require_once "assets/inc/functions.inc.php";
require_once "assets/inc/init.inc.php";

if (!isLoggedIn()) {
    header("location: login.php?error=notLoggedIn");
    exit();
} else {
    if (isset($_POST['submit'])) {
        logOutBruker();
        header("location: login.php");
    }
    htmlHeader("Frontpage");
?>

    <h1>Velkommen tilbake <?php echo $_SESSION['brukerEpost']; ?></h1>
    <br />
    <a href="aktiviteter.php">Registrer en aktivitet</a>
    <br />
    <a href="medlemmer.php">Se medlemmer</a>
    <br />
    <a href="assets/util/setup.php">Setup database</a>
    <form method="post"><button type="submit" name="submit">Logg ut!</button></form>

<?php
    htmlFooter();
}
?>