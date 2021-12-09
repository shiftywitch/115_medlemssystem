<?php
require_once __DIR__ . "/../inc/init.inc.php";
require_once __DIR__ . "/../lib/medlem.class.php";
$db = database();

$msg = $msg ?? array();
$err = $err ?? array();


if(isset($_POST['nyttMedlem'])){
    $nyttMedlem = new Medlem();

    $nyttMedlem->rolle = $_POST['rolle'] ?? 3;

    foreach (Medlem::alleTekstFelt() as $felt){
        // TEKST
        if(isset($_POST[$felt])){
            $nyttMedlem->{$felt} = $_POST[$felt] ?? '';
        }
    }
    foreach (Medlem::alleTallFelt() as $felt){
        // TALL
        if(isset($_POST[$felt]) && is_numeric($_POST[$felt])){
            $nyttMedlem->{$felt} = $_POST[$felt];
        }
    }
    foreach (Medlem::$feltDatoObl as $felt){
        // DATO
        $nyttMedlem->{$felt} = DateTime::createFromFormat('Y-m-d', $_POST[$felt]);
    }

    if(!empty($_POST['rolle'])){
        foreach ($_POST['rolle'] as $rid){
            $nyttMedlem->rollerId[] = $rid;
        }
    }
    else {
        $nyttMedlem->rollerId[] = STANDARD_ROLLE_ID;
    }

    if(($error = $nyttMedlem->lagreMedlem()) === true){
        $msg[] = "Medlemmet ble lagt inn!";
        unset( $_POST['nyttMedlem'] );
    }
    else {
        $err = $error;
    }
}
else if(isset($_POST['redigerMedlem'])){

    $redigerMedlem = Medlem::hentMedlem($db, $_POST['medlemid']);

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

    if($redigerMedlem->oppdaterRoller($_POST['rolle'] ?? array(STANDARD_ROLLE_ID)) === false){
        $err[] = "Det skjedde en feil med endringen av roller. Prøv igjen.";
    }

    if(!empty($redigerMedlem->endredeFelt)){
        $msg[] = "Endrede felter: <br>".implode('<br>', $redigerMedlem->endredeFelt);

        if(($errors = $redigerMedlem->lagreMedlem()) !== true){
            $err = array_merge($err, $errors);
        }
        else {
            $msg[] = "Oppdaterte medlemmet";
        }
    }

    unset($_POST);
}


function skrivMedlemsForm($medlemId = null){
    global $db;

    $felter = array_merge(Medlem::alleOblFelt(), ["mobilnummer", "poststed"]);

    if($medlemId == null){
        foreach ($felter as $key){
            ${$key} = $_POST[$key] ?? '';
        }
        $roller = $_POST['rolle'] ?? array();

        if($kontigentstatus == ""){ $kontigentstatus = "IKKE_BETALT"; }
        if($medlemStart == ""){ $medlemStart = (new DateTime())->format('Y-m-d'); }
    }
    else {
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

    <form method="post" action="<?=explode('?', $_SERVER['REQUEST_URI'])[0];?>" id="nyttMedlemForm" class="m-auto p-3 row" style="box-shadow: #fff2 0 0 6px 3px;">
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