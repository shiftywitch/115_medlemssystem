<?php
require_once "init.inc.php";
require_once __DIR__ ."/../lib/medlem.class.php";
$db = database();

function skrivUtMedlemmer(array $medlemmer) {
     if (!empty($medlemmer)) {?>
        <table>
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
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
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

if (isset($_GET['rolle'])) {
    $medlemmer = [];

    $rolle = strip_tags($_GET['rolle']);
    $kjoenn = strip_tags($_GET['kjoenn']);
    $status = strip_tags($_GET['status']);
    $medlemSiden = strip_tags($_GET['medlemSiden']);
    $where = [];

    $sql = 'SELECT m.*, p.poststed FROM Medlem m
        INNER JOIN Postnummer p on m.postnummer = p.postnummer
        INNER JOIN Rolle_register rr on m.medlemId = rr.medlemId
        INNER JOIN Rolle r on r.rolleId = rr.rolleId';

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

    if (count($where) > 0) {
        $sql .= " WHERE " . implode(' AND ', $where);
        $stmt = $db->prepare($sql);
        $medlemmer = Medlem::hentAlleMedlemmer($db, $stmt);
        skrivUtMedlemmer($medlemmer);
        exit();
    }

    skrivUtMedlemmer(Medlem::hentAlleMedlemmer($db));
}