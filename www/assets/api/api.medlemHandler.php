<?php
require_once __DIR__ . "/../inc/init.inc.php";
require_once __DIR__ . "/../lib/medlem.class.php";
$db = database();

//Funksjon som skriver ut alle medlemmer i en table.
function skrivUtMedlemmer(array $medlemmer) {
     if (!empty($medlemmer)) {?>
        <table class="table table-dark table-striped">
            <thead>
            <tr>
                <th>Fornavn</th>
                <th>Etternavn</th>
                <th>Adresse</th>
                <th>Postnummer</th>
                <th>Epost</th>
                <th>Fødselsdato</th>
                <th>Kjønn</th>
                <th>Medlem siden</th>
                <th>Kontigentstatus</th>
            </tr>
            </thead>
            <tbody>
            <?php
                //Her loopes det gjennom arryen med medlemmer.
                foreach ($medlemmer as $medlemID => $medlem) {
                    echo "<tr>\n";
                    echo "    <td>".($medlem->fornavn ?? '')."</td>\n";
                    echo "    <td>".($medlem->etternavn ?? '')."</td>\n";
                    echo "    <td>".($medlem->adresse ?? '');
                    echo "    <td>{$medlem->postNummer} {$medlem->postSted}</td>\n";
                    echo "    <td>".($medlem->epost ?? '')."</td>\n";
                    echo "    <td>".($medlem->dob->format('d. M Y') ?? '')."</td>\n";
                    echo "    <td>".($medlem->kjoenn ?? '')."</td>\n";
                    echo "    <td>".($medlem->medlemStart->format('d. M Y') ?? '')."</td>\n";
                    echo "    <td>".($medlem->kontigentstatus ?? '')."</td>\n";
                    echo "</tr>\n";
                }
            ?>
            </tbody>
        </table>

<?php
     }
}

//For requester som skal søke etter medlemmer. Ser i requesten etter parameter: m
if (isset($_GET['m'])) {
    $resultat = [];

    //Fjerner tags i fra get requesten
    $soek = strip_tags($_GET['m']);
    //Kaller den statiske funksjonen soekIMedlemmer() for å hente tilbake en array med medlemmer.
    $resultat = Medlem::soekIMedlemmer($db, $soek);
    //Printer ut resultater som json format
    echo json_encode($resultat);
    //Avslutter scriptet.
    exit();
}

//For requester som skal søke etter medlemmer. Ser i requesten etter parameter: rolle, kjoenn, status, medlemSiden
if (isset($_GET['rolle'])) {
    $medlemmer = [];

    $rolle = strip_tags($_GET['rolle']);
    $kjoenn = strip_tags($_GET['kjoenn']);
    $status = strip_tags($_GET['status']);
    $medlemSiden = strip_tags($_GET['medlemSiden']);
    $where = [];

    //Lager først en default query
    $sql = 'SELECT m.*, p.poststed FROM Medlem m
        INNER JOIN Postnummer p on m.postnummer = p.postnummer
        INNER JOIN Rolle_register rr on m.medlemId = rr.medlemId
        INNER JOIN Rolle r on r.rolleId = rr.rolleId';

    //Så legges det til for en where syntax i $where[] for hvert filter som er sendt med.

    if ($rolle != '') {
        $where[] = "r.rolleId='$rolle'";
    }
    if ($kjoenn != '') {
        $kjoennList = str_split($kjoenn);
        $where[] = "(m.kjoenn='".implode("' OR m.kjoenn='", $kjoennList)."')";
    }
    if ($status != '') {
        $where[] = "m.kontigentStatus='$status'";
    }
    if ($medlemSiden != '') {
        $where[] = "medlemStart<='$medlemSiden'";
    }

    //Om det er noen filter
    if (count($where) > 0) {
        //Så setter vi sammen alle verdiene i $where[] med en AND mellom.
        $sql .= " WHERE " . implode(' AND ', $where);
        $stmt = $db->prepare($sql);
        //Henter ut alle medlemmer, legger til $stmt parameter som gjør det mulig å kjøre sql koden vi har laget.
        $medlemmer = Medlem::hentAlleMedlemmer($db, $stmt);

        //Kaller skrivUtMedlemmer med medlemmene som blir returnert etter filtrering
        skrivUtMedlemmer($medlemmer);
        //Avslutter scriptet
        exit();
    }

    //Skriver ut medlemmer uten filter
    skrivUtMedlemmer(Medlem::hentAlleMedlemmer($db));
    exit();
}