<?php

const STANDARD_ROLLE_ID = 3;

class Medlem {
    public static array $feltTekstObl = array("fornavn", "etternavn", "adresse", "epost", "kjoenn", "kontigentstatus");
    public static array $feltTekst = array("poststed", "mobilnummer");
    public static array $feltTallObl = array("postnummer");
    public static array $feltTall = array();
    public static array $feltDatoObl = array("dob", "medlemStart");

    public static function alleTekstFelt(): array { return array_merge(Medlem::$feltTekstObl, Medlem::$feltTekst); }
    public static function alleTallFelt(): array { return array_merge(Medlem::$feltTallObl, Medlem::$feltTall); }

    public static function alleOblFelt(): array {
        return array_merge(Medlem::$feltTekstObl, Medlem::$feltTallObl, Medlem::$feltDatoObl);
    }
    public static function alleFelt(): array {
        return array_merge(Medlem::$feltTekstObl, Medlem::$feltTekst, Medlem::$feltTallObl, Medlem::$feltTall, Medlem::$feltDatoObl);
    }

    public ?string $medlemId = null;
    public string $fornavn;
    public string $etternavn;
    public string $adresse;
    public int $postnummer;
    public String $poststed = "";
    public String $mobilnummer = "";
    public string $epost;
    public DateTime $dob;
    public string $kjoenn;
    public string $kontigentstatus;
    public DateTime $medlemStart;
    public array $rollerId;
    public array $roller;

    public array $endredeFelt = array();

    public function setFelt(string $felt, $verdi): bool {
        if(in_array($felt, $this::$feltDatoObl)){
            // DATO
            if($verdi->format('Y-m-d') !== $this->{$felt}->format('Y-m-d')){
                $this->endredeFelt[] = $felt;
            }
            $this->{$felt} = $verdi;
        }
        else if(in_array($felt, array_merge($this::$feltTallObl, $this::$feltTall)) && is_numeric($verdi)){
            // TALL
            if($verdi != $this->{$felt}){
                $this->endredeFelt[] = $felt;
            }
            $this->{$felt} = (int) $verdi;
        }
        else if(in_array($felt, array_merge($this::$feltTekstObl, $this::$feltTekst, $this::$feltTallObl, $this::$feltTall))){
            // TEKST
            if($verdi != $this->{$felt}){
                $this->endredeFelt[] = $felt;
            }
            $this->{$felt} = $verdi;
        }
        else {
            return false;
        }

        return true;
    }

    public function valider(): array {
        $feil = array();

        // Sjekk obligatoriske
        foreach (Medlem::alleOblFelt() as $felt){
            // empty() ser om variablen er tom eller null eller tilsvarende.
            if(empty($this->{$felt})){
                $feil[] = "Feltet '".$felt."' er tomt eller ugyldig.";
            }
        }

        foreach (Medlem::$feltTallObl as $felt){
            if(!is_numeric($this->{$felt})){
                $feil[] = "Tallet i feltet '".$felt."' er ikke et tall.";
            }
        }

        foreach (Medlem::$feltDatoObl as $felt){
            if(get_class($this->{$felt}) == "DateTime") {
                $diff = $this->{$felt}->diff(new DateTime());
                if($felt == "dob" && ($diff->invert == 1 || $diff->days == 0)){
                    $feil[] = "Fødselsdatoen er i dag eller fremover i tid.";
                }
            }
            else {
                $feil[] = "Datoen '".$felt."' er ikke en gyldig dato.";
            }
        }

        if(strlen( $this->mobilnummer ) > 0){
            $this->mobilnummer = str_replace(' ', '', $this->mobilnummer);
            if(strlen( $this->mobilnummer ) > 12){
                $feil[] = "Mobilnummeret er for langt. Systemet støtter nummer opp til 12 tegn.";
            }
        }

        if(empty($this->rollerId)){
            $feil[] = "Medlemmet må være tilegnet hvertfall én rolle.";
        }

        if(invalidEmail($this->epost)){
            $feil[] = "Epostadressen er ikke gyldig.";
        }

        return $feil;
    }

    public function lagreMedlem(): array|bool {
        if(empty(($err = $this->valider()))){
            $db = database();

            foreach(array_merge(Medlem::alleTekstFelt(), Medlem::alleTallFelt()) as $felt){
                $this->{$felt} = $db->real_escape_string($this->{$felt});
            }

            if($this->medlemId == null){
                // NYTT MEDLEM
                $db->query("INSERT INTO Medlem (fornavn, etternavn, adresse, postnummer, mobilnummer, epost, dob, kjoenn, kontigentStatus, medlemStart) 
                           VALUES ('".$this->fornavn."', '".$this->etternavn."', '".$this->adresse."', '".$this->postnummer."', '".$this->mobilnummer."', '".$this->epost."', '".$this->dob->format('Y-m-d')."', '".$this->kjoenn."', '".$this->kontigentstatus."', '".$this->medlemStart->format('Y-m-d')."')");
                if($error = $db->error){
                    $err[] = "Fikk ikke registrert inn medlemmet. Feilmelding:<br>\n".$error;
                }
                else {
                    $this->medlemId = $db->insert_id;

                    $sqlRows = array();
                    foreach ($this->rollerId as $rid){
                        $sqlRows[] = "({$this->medlemId}, $rid)";
                    }
                    $rolleSql = "INSERT INTO Rolle_register VALUES ".implode(', ', $sqlRows);

                    $db->query($rolleSql);
                    if($error = $db->error){
                        if(str_contains($error, "Duplicate entry")){
                            $err[] = "Eposten du prøvde å registrere tilhører allerede et medlem. Skriv inn en annen epost-adresse.";
                        }
                        else {
                            $err[] = "Fikk ikke gitt rolle til medlemmet. Feilmelding:<br>\n".$error;
                        }
                    }
                    else {
                        return true;
                    }
                }
            }
            else {
                // REDIGERING AV MEDLEM
                if(!empty($this->endredeFelt)){
                    $sets = array();
                    foreach ($this->endredeFelt as $felt){
                        if(in_array($felt, Medlem::$feltDatoObl)){
                            $sets[] = "$felt = '{$this->{$felt}->format('Y-m-d')}'";
                        }
                        else {
                            $sets[] = "$felt = '{$this->{$felt}}'";
                        }
                    }
                    $sql = "UPDATE Medlem SET ".implode(', ', $sets)." WHERE medlemId = {$this->medlemId}";
                    $db->query($sql);

                    if($error = $db->error){
                        $err[] = "Oppdatering av medlem-feil: <br>\n".$error;
                    }
                }
                else {
                    $err[] = "Ingenting endringer gjort.";
                }
            }
        }
        if(empty($err)){
            return true;
        }
        else {
            return array_merge(array("Noe feilet. Meldlemmet ble ikke lagret."), $err);
        }
    }

    public function hentRoller(){
        global $db;

        if($db == null){
            $db = database();
        }

        if($this->medlemId != null){
            $sql = "SELECT Rr.rolleId, R.rolleNavn
                FROM Rolle_register Rr
                INNER JOIN Rolle R on Rr.rolleId = R.rolleId
                WHERE Rr.medlemId = ?;";
            $stmt = $db->prepare($sql);

            $stmt->bind_param('i', $this->medlemId);
            $stmt->execute();

            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $this->rollerId[$row['rolleId']] = $row['rolleId'];
                $this->roller[$row['rolleId']] = $row['rolleNavn'];
            }
        }
    }

    public function oppdaterRoller(array $nyeRoller): bool {
        global $db;
        $db = $db ?? database();

        $hentRoller = $db->query("SELECT rolleId, rolleNavn FROM Rolle");
        while ($row = $hentRoller->fetch_assoc()){
            if(in_array($row['rolleId'], $nyeRoller) && !in_array($row['rolleId'], $this->rollerId)){
                // HAS ROLE, new
                $db->query("INSERT INTO Rolle_register VALUE ({$this->medlemId}, $row[rolleId]);");
            }
            else if(!in_array($row['rolleId'], $nyeRoller) && in_array($row['rolleId'], $this->rollerId)){
                // DOESNT HAVE ROLE, new
                $db->query("DELETE FROM Rolle_register WHERE medlemId = {$this->medlemId} AND rolleId = $row[rolleId] LIMIT 1;");
            }

            if($error = $db->error){
                echo $error;
                return false;
            }
        }

        return true;
    }

    public static function hentMedlem(mysqli $db, int $medlemId): Medlem {
        $hentMedlemmerSQL = "
            SELECT m.*, p.poststed FROM Medlem m
            INNER JOIN Postnummer p on m.postnummer = p.postnummer
            WHERE m.medlemId = $medlemId
            ORDER BY m.etternavn, m.fornavn
            LIMIT 1";

        $stmt = $db->prepare($hentMedlemmerSQL);

        return self::hentAlleMedlemmer($db, $stmt)[$medlemId];
    }

    public static function hentAlleMedlemmer(mysqli $db, mysqli_stmt $stmt = null):array {
        $medlemmer = [];

        $statement = null;

        if ($stmt == null) {
            $hentMedlemmerSQL = "
            SELECT m.*, p.poststed FROM Medlem m
            INNER JOIN Postnummer p on m.postnummer = p.postnummer
            ORDER BY m.etternavn, m.fornavn
            ";

            $statement = $db->prepare($hentMedlemmerSQL);
        } else {
            $statement = $stmt;
        }

        $statement->execute();
        $result = $statement->get_result();
        while ($row = $result->fetch_assoc()) {
            $medlem = new Medlem();

            $medlem->medlemId       = $row['medlemId'];
            $medlem->fornavn        = $row['fornavn'];
            $medlem->etternavn      = $row['etternavn'];
            $medlem->adresse        = $row['adresse'];
            $medlem->postnummer     = $row['postnummer'];
            $medlem->poststed       = $row['poststed'];
            $medlem->mobilnummer    = $row['mobilnummer'];
            $medlem->epost          = $row['epost'];
            $medlem->dob            = DateTime::createFromFormat('Y-m-d',$row['dob']);
            $medlem->kjoenn         = $row['kjoenn'];
            $medlem->kontigentstatus = $row['kontigentStatus'];
            $medlem->medlemStart    = DateTime::createFromFormat('Y-m-d', $row['medlemStart']);

            $medlem->hentRoller();

            $medlemmer[$medlem->medlemId] = $medlem;
        }

        $statement->close();
        return $medlemmer;
    }

    public static function hentAlleMedlemMailAdresser(mysqli $db):array {
        $medlemEmail = [];

        $stmt = "
            SELECT epost FROM Medlem;
        ";

        $stmt = $db->prepare($stmt);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $medlemEmail[] = $row['epost'];
        }

        return $medlemEmail;
    }

    public static function soekIMedlemmer(mysqli $db, string $needle) {
        $resultat = [];

        $needle = preg_replace('/(?<!\\\)([%_])/', '\\\$1',$needle);

        $sql = "
            SELECT medlemId, fornavn, etternavn, epost
            FROM Medlem
            WHERE fornavn LIKE CONCAT(?,'%') OR etternavn LIKE CONCAT(?,'%') OR epost LIKE CONCAT(?,'%')
        ";

        $statement = $db->prepare($sql);
        $statement->bind_param("sss", $needle, $needle, $needle);
        $statement->execute();
        $result = $statement->get_result();
        while ($row = $result->fetch_assoc()) {
            $resultat[$row['medlemId']] = array($row['fornavn'], $row['etternavn'], $row['epost']);
        }

        return $resultat;
    }
}