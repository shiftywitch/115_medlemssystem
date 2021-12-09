<?php
require_once __DIR__ . "/../inc/init.inc.php";
require_once __DIR__ . "/../lib/HtmlForm.class.php";
require_once __DIR__ . "/../lib/medlem.class.php";

// Fikser en databasetilkobling
$db = database();

$msg = $msg ?? array();
$err = $err ?? array();

// Hvis en av sidene denne er inkludert på har et skjema sendt inn med et nytt medlem
if(isset($_POST['nyttMedlem'])){

    // Ny tom Medlem klasse.
    $nyttMedlem = new Medlem();

    // Løkke som går gjennom klassens Tekst-felt (så Tall-felt og Dato-felter), definert statisk for klassen.
    foreach (Medlem::alleTekstFelt() as $felt){
        if(isset($_POST[$felt])){
            // Oppdaterer klassens verdier.
            $nyttMedlem->{$felt} = $_POST[$felt] ?? '';
        }
    }
    foreach (Medlem::alleTallFelt() as $felt){
        if(isset($_POST[$felt]) && is_numeric($_POST[$felt])){
            $nyttMedlem->{$felt} = $_POST[$felt];
        }
    }
    foreach (Medlem::$feltDatoObl as $felt){
        // Klassen vil ha DateTime-datoer, så her lager vi et DateTime objekt for dato-feltene.
        $nyttMedlem->{$felt} = DateTime::createFromFormat('Y-m-d', $_POST[$felt]);
    }

    // Her setter vi inn rollene som er valgt i skjemaet.
    if(!empty($_POST['rolle'])){
        foreach ($_POST['rolle'] as $rid){
            $nyttMedlem->rollerId[] = $rid;
        }
    }
    else {
        // Hvis ingen roller er valgt, settes det inn en standard-rolle som er definert i medlem.class.php
        $nyttMedlem->rollerId[] = STANDARD_ROLLE_ID;
    }

    // lagreMedlem() er en metode for klassen som verifiserer og lagrer info i databasen. Eventuelle feilmeldinger returneres som en array()
    if(($error = $nyttMedlem->lagreMedlem()) === true){
        $msg[] = "Medlemmet ble lagt inn!";

        // Unsetter _POST-variablen så skjemaet ikke vises når siden lastes igjen.
        unset( $_POST['nyttMedlem'] );
    }
    else {
        $err = $error;
    }
}
// Ellers hvis skjemaet har at et medlem skal redigeres
else if(isset($_POST['redigerMedlem'])){

    // I motsetning til over, så starter vi her med klasse-elementet til medlemmet som redigeres.
    $redigerMedlem = Medlem::hentMedlem($db, $_POST['medlemid']);

    // Her brukes metoden Medlem::setFelt() istedenfor å sette verdiene, da det gjør det mulig å loggføre hva som er endret.
    foreach (array_merge(Medlem::alleTekstFelt(), Medlem::alleTallFelt()) as $felt){
        if(isset($_POST[$felt])){
            $redigerMedlem->setFelt($felt, $_POST[$felt]);
        }
    }

    foreach (Medlem::$feltDatoObl as $felt){
        if(isset($_POST[$felt])){
            $dato = DateTime::createFromFormat('Y-m-d', $_POST[$felt]);
            $redigerMedlem->setFelt($felt, $dato);
        }
    }

    // Oppdatering av roller skjer i en egen metode.
    if($redigerMedlem->oppdaterRoller($_POST['rolle'] ?? array(STANDARD_ROLLE_ID)) === false){
        $err[] = "Det skjedde en feil med endringen av roller. Prøv igjen.";
    }

    // Manuell kontroll på hva som er endret, og skriver det til nettleseren.
    if(!empty($redigerMedlem->endredeFelt)){
        $msg[] = "Oppdaterte verdier: <br>".implode('<br>', $redigerMedlem->endredeFelt);

        // Her lagres medlemmet på nytt. Eventuelle feilmeldinger returneres fra metoden.
        if(($errors = $redigerMedlem->lagreMedlem()) !== true){
            // Hvis det er eventuelle tidligere feilmeldinger, blir de nye feilmeldingene lagt til på slutten.
            $err = array_merge($err, $errors);
        }
        else {
            $msg[] = "Oppdaterte medlemmet";
        }
    }

    // Vi unsetter POST her for å gjøre at verdiene ikke kommer inn i skjemaet igjen hvis det skal legges til et nytt medlem nå.
    unset($_POST);
}

// Hvis det kommer en GET-forespørsel hit, så skrives en redigerings-form ut tilbake, med gitt medlem-info
if(isset($_GET['redigeringForm'])){
    skrivMedlemsForm($_GET['redigeringForm']);
}

function skrivMedlemsForm($medlemId = null){
    global $db, $projectRoot;

    $felter = Medlem::alleFelt();

    if($medlemId == null){
        // For hvert av feltene hentet fra Medlem-klassen lages en variabel, som enten holder det som er sendt inn (praktisk hvis noe er feil), eller en tom verdi.
        foreach ($felter as $key){
            ${$key} = $_POST[$key] ?? '';
        }
        $roller = $_POST['rolle'] ?? array();

        // Her settes noen standard-verdier
        if($kontigentstatus == ""){ $kontigentstatus = "IKKE_BETALT"; }
        if($medlemStart == ""){ $medlemStart = (new DateTime())->format('Y-m-d'); }
    }
    else {
        // Her hentes dataen på medlemmet som skal bli redigert.
        $redigerMedlem = Medlem::hentMedlem($db, $medlemId);

        foreach ($felter as $felt){
            if(in_array($felt, Medlem::$feltDatoObl)){
                ${$felt} = $redigerMedlem->{$felt}->format('Y-m-d');
            }
            else {
                ${$felt} = $redigerMedlem->{$felt};
            }
        }

        $poststed = $redigerMedlem->poststed;
        $roller = $redigerMedlem->rollerId;
    }

    ?>

    <form method="post" action="<?=$projectRoot ?? "";?>/medlemmer.php" id="nyttMedlemForm" class="m-auto p-3 row" style="box-shadow: #fff2 0 0 6px 3px;">
        <h4><?=$medlemId == null?"Nytt medlem":"Rediger medlem";?></h4>

        <div class="col-md-6">
            <?=HtmlForm::inputText("fornavn", "Fornavn", "Harald", $fornavn);?>
        </div>
        <div class="col-md-6">
            <?=HtmlForm::inputText("etternavn", "Etternavn", "Rex", $etternavn);?>
        </div>

        <div class="mb-4"></div>

        <div class="col-12">
            <?=HtmlForm::inputText("adresse", "Adresse", "Slottsplassen 1", $adresse);?>
        </div>
        <div class="col-md-6">
            <?=HtmlForm::inputTall("postnummer", "Postnummer", "0010", $postnummer, true, 0, 9999);?>
        </div>
        <div class="col-md-6">
            <?=HtmlForm::inputText("poststed", "Poststed", "OSLO", $poststed, null, 'disabled readonly');?>
        </div>

        <div class="mb-4"></div>

        <div class="col-md-3">
            <?=HtmlForm::inputText("mobilnummer", "Mobilnummer", "+47 123 45 678", $mobilnummer, false);?>
        </div>
        <div class="col-md-6">
            <?=HtmlForm::inputEmail("epost", "Epost", "kongen@norge.no", $epost);?>
        </div>
        <div class="col-md-3">
            <?=HtmlForm::inputDate("dob", "Fødselsdato", $dob, true, null, "today");?>
        </div>

        <div class="mb-4"></div>

        <div class="col-md-3">
            <?=HtmlForm::inputSelect("kjoenn", "Kjønn", $kjoenn, array(""=>"-- Velg kjønn --", "M"=>"Mann", "F"=>"Kvinne", "O"=>"Annet"));?>
        </div>
        <div class="col-md-3">
            <?=HtmlForm::inputDate("medlemStart", "Medlem siden", $medlemStart);?>
        </div>
        <div class="col-md-3">
            <?=HtmlForm::inputSelect("kontigentstatus", "Kontigentstatus", $kontigentstatus, array("BETALT"=>"Betalt", "IKKE_BETALT"=>"Ikke betalt"));?>
        </div>
        <div class="col-md-3">
            Velg rolle(r):
            <ul>
            <?php
            $result = $db->query("SELECT rolleId, rolleNavn FROM Rolle");

            while ($row = $result->fetch_assoc()){
                echo "<label><input type='checkbox' name='rolle[]' value='$row[rolleId]' ".(in_array($row['rolleId'], $roller)?" checked":"").">&nbsp;$row[rolleNavn]</label><br>";
            }
            ?>
            </ul>
        </div>

        <div class="mb-4"></div>

        <div class="text-center">
            <?php
            if($medlemId == null){
                echo "<button type='submit' name='nyttMedlem' class='btn btn-primary w-50'>Legg til medlem</button>";
            }
            else {
                echo "<input type='hidden' name='medlemid' value='$medlemId'>";
                echo "<button type='submit' name='redigerMedlem' class='btn btn-primary w-50'>Rediger medlem</button>";
            }
            ?>
        </div>
    </form>
    <?php
}