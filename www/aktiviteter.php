<?php
require_once "assets/inc/html.inc.php";
require_once "assets/inc/functions.inc.php";
require_once "assets/inc/init.inc.php";

    reDirectIfNotLoggedIn();

    $db = database();
    $err = [];

    //Logikk for å gå gjennom dataen fra post requesten
    if (isset($_POST['submit'])) {
        //Skjekker om alle inputs er fullt ut.
        if (emptyInputs($_POST['aktivitet'], $_POST['beskrivelse'], $_POST['start'], $_POST['slutt'], $_POST['ansvarlig'])) {
            $err[] = "Fyll ut alle felt";
        } elseif (strtotime($_POST['start']) > strtotime($_POST['slutt'])) {
            $err[] = "Startdato kan ikke være etter sluttdato";
        } else {
            //Bruker prepared statement for å sette inn ny aktivitet.
            $sql = "INSERT INTO Aktivitet VALUES (NULL, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ssiss",
                $_POST['aktivitet'],
                $_POST['beskrivelse'],
                $_POST['ansvarlig'],
                $_POST['start'],
                $_POST['slutt']
            );
            $stmt->execute();
            if ($temp = $stmt->error) {
                echo $temp;
            }
        }
    }

    htmlHeader("Aktiviteter");
?>
    <!-- Printer ut feil om det er noen -->
    <?php
    if (!empty($err)) {
        foreach ($err as $error) {echo "<p class='alert alert-danger w-25'>$error</p>";}
    }
    ?>
    <br />
    <div class="container-md">

    <!-- Form for å opprette ny aktivitet/kurs -->
    <form method="post" class="m-auto w-50 p-3" style="box-shadow: #fff2 0 0 6px 3px;">
        <h4>Ny aktivitet</h4>
        <div class="mb-3 row">
            <label for="aktivitet" class="col-sm-3 col-form-label">Navn</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="aktivitet" id="aktivitet" placeholder="Aktivitet" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="beskrivelse" class="col-sm-3 col-form-label">Beskrivelse</label>
            <div class="col-sm-9">
                <textarea name="beskrivelse" class="form-control" id="beskrivelse" placeholder="Beskrivelse" required></textarea>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="start" class="col-sm-3 col-form-label">Start og slutt</label>
            <div class="col-sm-9">
                <input type="datetime-local" class="form-control" name="start" id="start" required>
                <input type="datetime-local" class="form-control" name="slutt" id="slutt" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="ansvarlig" class="col-sm-3 col-form-label">Ansvarlig</label>
            <div class="col-sm-9">
                <select name="ansvarlig" id="ansvarlig" class="form-select">
                    <!-- Henter ut alle medlemmer har rollen Kursansvarlig -->
                    <?php
                    $sql = "
                        SELECT m.medlemId, fornavn, etternavn 
                        FROM Medlem m
                        INNER JOIN Rolle_register rg ON rg.medlemId = m.medlemId
                        WHERE rg.rolleId = 2;
                    ";
                    $result = $db->query($sql);
                    echo "\n";
                    //Så lenge det er resultater igjen så printes det ut en <option> med medlemId som value.
                    while ($row = $result->fetch_assoc()) {
                        echo "\t\t\t<option value='{$row['medlemId']}'>{$row['fornavn']} {$row['etternavn']}</option>\n";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" name="submit" class="btn btn-primary w-50">Opprett aktivitet</button>
        </div>
    </form>

    <br />
    <br />

    <!-- Table som viser en oversikt over aktiviteter -->
    <table class="table table-dark table-striped mb-5">
        <tr>
            <th>Navn</th>
            <th>Start</th>
            <th>Slutt</th>
        </tr>
        <?php
        //Ved å sette WHERE start >= CURRENT_DATE så forsikrer vi
        //oss å bare få tilbake de aktiviteter vi vill ha.
        $sql = "SELECT * FROM Aktivitet WHERE start >= CURRENT_DATE";
        $result = $db->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo "\t\t\t\t<td>{$row['navn']}</td>\n";
            echo "\t\t\t\t<td>{$row['start']}</td>\n";
            echo "\t\t\t\t<td>{$row['slutt']}</td>\n";
            echo "\t\t\t</tr>\n";
        }
        ?>
    </table>
</div>
<?php
htmlFooter();
