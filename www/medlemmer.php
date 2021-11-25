<?php
require_once "assets/inc/html.inc.php";
require_once "assets/inc/functions.inc.php";
require_once "assets/inc/init.inc.php";
require_once "assets/lib/medlem.class.php";
require_once "assets/inc/medlemHandler.inc.php";

reDirectIfNotLoggedIn();
$db = database();
$err = [];
htmlHeader("Medlemmer");
?>
<script>
    const medlemmerMedFilter = () => {
        const rolle = document.getElementById("roller").valueOf().value;
        const status = document.getElementById("kontigentStatus").valueOf().value;
        const medlemSiden = document.getElementById("medlemStart").valueOf().value;
        console.log(medlemSiden)
        const kjoenn = [];
        document.querySelectorAll('input[name="kjoenn[]"]:checked').forEach(x => kjoenn.push(x.valueOf().value))
        let kjoennParam = '';
        kjoenn.map(k => kjoennParam += k)
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("medlemmer").innerHTML = this.responseText;
            }
        }
        xhr.open("GET", "assets/inc/medlemHandler.inc.php?rolle=" + rolle + "&&kjoenn=" + kjoennParam
            + "&&status="+status + "&&medlemSiden="+medlemSiden)
        xhr.send();
    }

    const fjernFilter = () => {
        let checkBoxes = document.querySelectorAll('input[type="checkbox"]:checked')
        let rolle = document.getElementById("roller")
        let status = document.getElementById("kontigentStatus")
        let startDato = document.getElementById("medlemStart")
        //En if statement bare for å belaste databasen derfor man prøver å fjerne filtre
        //når det er ingen filtre
        if (checkBoxes.length > 0 || rolle.valueOf().value !== '' || startDato.valueOf().value !== '') {
            checkBoxes.forEach(cb => cb.checked = false)
            rolle.selectedIndex = 0;
            status.selectedIndex = 0;
            startDato.valueOf().value = '';

            medlemmerMedFilter();
        }
    }
</script>
<div>
    <label for="roller">Rolle: </label>
    <select name="roller" id="roller" onchange="medlemmerMedFilter()">
        <option value="">Velg rolle</option>
        <?php
            $sql = "SELECT * FROM rolle";
            $result = $db->query($sql);
            while($row = $result->fetch_assoc()) {
                echo "\t<option value='{$row['rolleId']}'>{$row['rolleNavn']}</option>";
            }
        ?>
    </select>
    <label for="kontigentStatus">Kontigent status: </label>
    <select name="kontigentStatus" id="kontigentStatus" onchange="medlemmerMedFilter()">
        <option value="">Status</option>
        <option value="BETALT">Betalt</option>
        <option value="IKKE_BETALT">Ikke betalt</option>
    </select>
    <input type="checkbox" id="cbM" name="kjoenn[]" onchange="medlemmerMedFilter()" value="M"><label for="cbM">Mann</label>
    <input type="checkbox" id="cbF" name="kjoenn[]" onchange="medlemmerMedFilter()" value="F"><label for="cbF">Dame</label>
    <input type="checkbox" id="cbO" name="kjoenn[]" onchange="medlemmerMedFilter()" value="O"><label for="cbO">Annet</label>
    <label for="medlemStart">Medlem Siden: </label><input type="date" id="medlemStart" name="medlemStart" onchange="medlemmerMedFilter()">
    <button type="button" name="rFilter" onclick="fjernFilter()">Fjern filter</button>
</div>
<div id="medlemmer"><?php skrivUtMedlemmer(Medlem::hentAlleMedlemmer($db));?></div>

<a href="index.php"><button>Gå tilbake</button></a>

<?php
    htmlFooter();
