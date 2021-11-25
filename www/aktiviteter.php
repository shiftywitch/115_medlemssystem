<?php
require_once "assets/inc/html.inc.php";
require_once "assets/inc/functions.inc.php";
require_once "assets/inc/init.inc.php";

    reDirectIfNotLoggedIn();

    $db = database();
    $err = [];

    if (isset($_POST['submit'])) {
        if (emptyInputs($_POST['aktivitet'], $_POST['beskrivelse'], $_POST['start'], $_POST['slutt'], $_POST['ansvarlig'])) {
            $err[] = "Fyll ut alle felt";
        } else {
            $sql = "INSERT INTO aktivitet VALUES (NULL, ?, ?, ?, ?, ?)";
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
    <table>
        <tr>
            <th>Navn</th>
            <th>Start</th>
            <th>Slutt</th>
        </tr>
        <?php
        //Ved å sette WHERE start >= CURRENT_DATE så forsikrer vi
        //oss å bare få tilbake de aktiviteter vi vill ha.
        $sql = "SELECT * FROM aktivitet WHERE start >= CURRENT_DATE";
        $result = $db->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo "\t\t\t\t<td>{$row['navn']}</td>\n";
            echo "\t\t\t\t<td>{$row['start']}</td>\n";
            echo "\t\t\t\t<td>{$row['slutt']}</td>\n";
            echo "\t\t\t</tr>\n";
        }
        ?>
    </table>

    <form method="post">
        <p>Navn:</p>
        <input type="text" name="aktivitet" placeholder="Aktivitet" required>
        <br />
        <p>Beskrivelse:</p>
        <textarea name="beskrivelse" placeholder="Beskrivelse" required></textarea>
        <br />
        <p>Start og slutt:</p>
        <input type="datetime-local" name="start" required>
        <input type="datetime-local" name="slutt" required>
        <br />
        <p>Ansvarlig: </p>
        <select name="ansvarlig">
            <?php
            $sql = "
                SELECT m.medlemId, fornavn, etternavn 
                FROM medlem m
                INNER JOIN Rolle_register rg ON rg.medlemId = m.medlemId
                WHERE rg.rolleId = 2;
            ";
            $result = $db->query($sql);
            echo "\n";
            while ($row = $result->fetch_assoc()) {
                echo "\t\t\t<option value='{$row['medlemId']}'>{$row['fornavn']} {$row['etternavn']}</option>\n";
            }
            ?>
        </select>
        <br />
        <br />
        <button type="submit" name="submit">Opprett aktivitet</button>
    </form>

    <?php
    if (!empty($err)) {
        foreach ($err as $error) {echo "<p>$error</p>";}
    }
    ?>
<?php
htmlFooter();
