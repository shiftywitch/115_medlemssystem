<?php

class Medlem
{
    public string $medlemId;
    public string $fornavn;
    public string $etternavn;
    public string $adresse;
    public int $postNummer;
    public String $postSted;
    public string $epost;
    public DateTime $dob;
    public string $kjoenn;
    public string $kontigentstatus;
    public DateTime $medlemStart;

    public function __construct(string $medlemId,
                                string $fornavn,
                                string $etternavn,
                                string $adresse,
                                int $postNummer,
                                String $postSted,
                                string $epost,
                                DateTime $dob,
                                string $kjoenn,
                                string $kontigentstatus,
                                DateTime $medlemStart,
    ) {
        $this->medlemId = $medlemId ?? null;
        $this->fornavn = $fornavn;
        $this->etternavn = $etternavn;
        $this->adresse = $adresse;
        $this->postNummer = $postNummer;
        $this->postSted = $postSted;
        $this->epost = $epost;
        $this->dob = $dob;
        $this->kjoenn = $kjoenn;
        $this->kontigentstatus = $kontigentstatus;
        $this->medlemStart = $medlemStart;

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
            $medlem = new Medlem(
                $row['medlemId'],
                $row['fornavn'],
                $row['etternavn'],
                $row['adresse'],
                $row['postnummer'],
                $row['poststed'],
                $row['epost'],
                DateTime::createFromFormat('Y-m-d',$row['dob']),
                $row['kjoenn'],
                $row['kontigentStatus'],
                DateTime::createFromFormat('Y-m-d', $row['medlemStart'])
            );

            $medlemmer[$medlem->medlemId] = $medlem;
        }

        $statement->close();
        return $medlemmer;
    }

    public static function getBrukerByEmail(string $email) {
        global $db;

        if($db == null){
            $db = database();
        }
        $sql = "SELECT * FROM Bruker WHERE epost = ?;";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        } else {
            return false;
        }
    }
}