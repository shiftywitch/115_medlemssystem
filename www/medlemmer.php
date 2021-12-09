<?php
require_once "assets/inc/html.inc.php";
require_once "assets/inc/functions.inc.php";
require_once "assets/inc/init.inc.php";
require_once "assets/lib/medlem.class.php";
require_once "assets/lib/HtmlForm.class.php";
require_once "assets/api/api.medlemHandler.php";
require_once "assets/api/api.medlemManager.php";

reDirectIfNotLoggedIn();
$db = database();
$err = $err ?? [];
$msg = $msg ?? [];
htmlHeader("Medlemmer");
?>
<script>
    const medlemmerMedFilter = () => {
        //Henter verdiene fra input feltene
        const rolle = document.getElementById("roller").valueOf().value;
        const status = document.getElementById("kontigentStatus").valueOf().value;
        const medlemSiden = document.getElementById("medlemStart").valueOf().value;

        const kjoenn = [];

        //Legger til for hvert kjønn det er krysset av for i kjoenn arryen
        document.querySelectorAll('input[name="kjoenn[]"]:checked').forEach(x => kjoenn.push(x.valueOf().value))

        let kjoennParam = '';
        //Lager parameter for kjønn som skal sendes med Get requesten
        kjoenn.map(k => kjoennParam += k)
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                //Diven medlemmer endres til responsen som sendes tilbake fra api.medlemHandler.php som er en table.
                document.getElementById("medlemmer").innerHTML = this.responseText;
            }
        }
        //Sender ved alle parametere selv om de er tomme.
        xhr.open("GET", "assets/api/api.medlemHandler.php?rolle=" + rolle + "&&kjoenn=" + kjoennParam
            + "&&status="+status + "&&medlemSiden="+medlemSiden)
        xhr.send();
    }

    const fjernFilter = () => {
        let checkBoxes = document.querySelectorAll('input[type="checkbox"]:checked')
        let rolle = document.getElementById("roller")
        let status = document.getElementById("kontigentStatus")
        let startDato = document.getElementById("medlemStart")
        //En if statement bare for å ikke belaste databasen dersom man prøver å fjerne filtre
        //når det er ingen filtre
        if (checkBoxes.length > 0
            || rolle.valueOf().value !== ''
            || startDato.valueOf().value !== ''
            || status.valueOf().value !== '') {
            checkBoxes.forEach(cb => cb.checked = false)
            rolle.selectedIndex = 0;
            status.selectedIndex = 0;
            startDato.valueOf().value = '';

            //Kaller medlemmerMedFilter uten noen verdier, som resulterer i at alle medlemmer blir returnert tilbake.
            medlemmerMedFilter();
        }
    }

    //Henter navn på posted når et postnummer oppgives.
    function hentPoststed(postnummer){
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "assets/api/api.postnummer.php?postnummer=" + postnummer)
        xhr.send();

        xhr.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if(this.responseText !== ""){
                    let json = JSON.parse(this.responseText)
                    document.getElementById("poststed").value = json[0];
                }
            }
            else {
                console.log(this);
            }
        }
    }
</script>

<div class="container-md mb-4">
    <div class="text-center">
        <button role="button" class="btn btn-primary text-center mb-3" onclick="$('#leggTilMedlemForm').slideToggle()">Legg til et medlem...</button>
    </div>

    <?php
    if (!empty($err)) {
        foreach ($err as $error) {echo "<p class='alert alert-danger'>$error</p>";}
    }
    if (!empty($msg)) {
        foreach ($msg as $message) {echo "<p class='alert alert-success'>$message</p>";}
    }
    ?>

    <!-- Skriver ut form for å legge til nytt medlem -->
    <!-- Om det er valgt en medlemId kommer en form for å redigere medlemmet -->
    <div class="p-2" id="leggTilMedlemForm" style="<?=(!isset($_POST['nyttMedlem']) && !isset($_GET['medlemid'])?'display: none;':'');?>">
        <?php
        skrivMedlemsForm($_GET['medlemid'] ?? null);
        ?>
    </div>
</div>

<script>
    let postnummerTimer;
    let postStedEdited = false;
    document.getElementById('poststed').addEventListener('keyup', ev => {
        postStedEdited = ev.target.value.length > 0;
    });

    let postnummerVal = document.getElementById("postnummer").value;
    postNummerEdit = ev => {
        let postnummer = ev.target.value;
        console.log(ev);
        if(!postStedEdited && postnummer.length > 2 && postnummer !== postnummerVal){
            clearTimeout(postnummerTimer);

            postnummerVal = postnummer;
            document.getElementById("poststed").value = "";
            document.getElementById("poststed").placeholder = ""

            postnummerTimer = setTimeout(()=>{
                hentPoststed(postnummer);
            }, 500);
        }
    }

    document.getElementById('postnummer').addEventListener('keyup', postNummerEdit);
    document.getElementById('postnummer').addEventListener('change', postNummerEdit);
</script>

<!-- Inneholder filtrering og oversikt over medlemmer. -->
<div class="container">
    <div class="mb-3 row m-auto">
        <div class="col-auto m-auto">
            <!-- Select liste med roller for filtrering -->
            <label for="roller" class="">Rolle: </label>
            <!-- Kaller medlemmerMedFilter() når verdien endres -->
            <select class="form-select-sm" name="roller" id="roller" onchange="medlemmerMedFilter()">
                <option value="">Velg rolle</option>
                <?php
                    //Henter roller fra databasen og lager options med dataen.
                    $sql = "SELECT * FROM Rolle";
                    $result = $db->query($sql);
                    while($row = $result->fetch_assoc()) {
                        echo "\t<option value='{$row['rolleId']}'>{$row['rolleNavn']}</option>";
                    }
                ?>
            </select>
        </div>
        <div class="col-auto m-auto">
            <!-- Select med options for å filtrere kontigentstatus -->
            <label for="kontigentStatus" class="">Kontigentstatus: </label>
            <!-- Kaller medlemmerMedFilter() når verdien endres -->
            <select class="form-select-sm" name="kontigentStatus" id="kontigentStatus" onchange="medlemmerMedFilter()">
                <option value="">Status</option>
                <option value="BETALT">Betalt</option>
                <option value="IKKE_BETALT">Ikke betalt</option>
            </select>
        </div>
        <!-- Checkbox hvor en kan filtrere etter kjønn -->
        <!-- Kaller medlemmerMedFilter() når verdien endres -->
        <div class="col-auto m-auto">
            <input type="checkbox" id="cbM" name="kjoenn[]" onchange="medlemmerMedFilter()" value="M"> <label for="cbM">Mann</label>
            <input type="checkbox" id="cbF" name="kjoenn[]" onchange="medlemmerMedFilter()" value="F"> <label for="cbF">Dame</label>
            <input type="checkbox" id="cbO" name="kjoenn[]" onchange="medlemmerMedFilter()" value="O"> <label for="cbO">Annet</label>
        </div>
        <div class="col-auto m-auto">
            <label for="medlemStart">Medlem Siden: </label>
            <input type="date" class="" id="medlemStart" name="medlemStart" onchange="medlemmerMedFilter()">
        </div>
        <div class="col-auto m-auto">
            <!-- Fjerner alle filtre ved å kalle fjernFilter() når knappen trykkes -->
            <button type="button" class="btn btn-secondary" name="rFilter" onclick="fjernFilter()">Fjern filter</button>
        </div>
    </div>

    <!-- Diven hvor alle medlemmer skrives ut -->
    <div id="medlemmer">
        <?php
        skrivUtMedlemmer(Medlem::hentAlleMedlemmer($db));
        ?>
    </div>
    <script>
        $(".medlemForm").on('submit', ev => {
            ev.preventDefault();
            console.log("Submit form");

            const formElem = $(ev.target);

            $(".editForm").remove();
            formElem.parent().parent().after( "<tr class='editForm'><td colspan='11'></td></tr>" );
            $(".editForm td").load('assets/api/api.medlemManager.php?redigeringForm='+formElem.attr('data-medlemid'));
        });
    </script>
</div>


<?php
    htmlFooter();
