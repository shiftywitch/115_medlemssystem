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
        const kjoenn = [];
        document.querySelectorAll('input[name="kjoenn[]"]:checked').forEach(x => kjoenn.push(x.valueOf().value))
        let kjoennParam = '';
        kjoenn.map(k => kjoennParam += k)
        console.log(rolle)
        console.log(kjoenn)
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("medlemmer").innerHTML = this.responseText;
            }
        }
        xhr.open("GET", "assets/inc/medlemHandler.inc.php?rolle=" + rolle + "&&kjoenn=" + kjoennParam)
        xhr.send();
    }

    const fjernFilter = () => {
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false)
        document.getElementById("roller").selectedIndex = 0;
        medlemmerMedFilter();
    }
</script>
<div>
    <label for="roller">Rolle: </label>
    <select name="roller" id="roller" onchange="medlemmerMedFilter()">
        <option value=""></option>
        <?php
            $sql = "SELECT * FROM rolle";
            $result = $db->query($sql);
            while($row = $result->fetch_assoc()) {
                echo "\t<option value='{$row['rolleId']}'>{$row['rolleNavn']}</option>";
            }
        ?>
    </select>
    <input type="checkbox" id="cbM" name="kjoenn[]" onchange="medlemmerMedFilter()" value="M"><label for="cbM">Mann</label>
    <input type="checkbox" id="cbF" name="kjoenn[]" onchange="medlemmerMedFilter()" value="F"><label for="cbF">Dame</label>
    <input type="checkbox" id="cbO" name="kjoenn[]" onchange="medlemmerMedFilter()" value="O"><label for="cbO">Annet</label>
    <button type="button" name="rFilter" onclick="fjernFilter()">Fjern filter</button>
</div>
<div id="medlemmer"><?php skrivUtMedlemmer(Medlem::hentAlleMedlemmer($db));?></div>

<?php
    htmlFooter();
