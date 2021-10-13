<?php

function dbSetupSQL():array {
    $queries = array();

    $queries['createPostnummerTable'] = "
            CREATE OR REPLACE TABLE Postnummer (
                postnummer INT(4) NOT NULL PRIMARY KEY,
                poststed VARCHAR(30) NOT NULL
            );  
        ";

    $queries['createMedlemTable'] = "
            CREATE OR REPLACE TABLE Medlem (
                medlemId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                fornavn VARCHAR(40) NOT NULL,
                etternavn VARCHAR(40) NOT NULL,
                adresse VARCHAR(40) NOT NULL,
                postnummer INT(4) NOT NULL,
                epost VARCHAR(100) NOT NULL UNIQUE,
                dob DATE NOT NULL,
                kjoenn ENUM('M', 'F', 'O') NOT NULL,
                kontigentStatus ENUM('BETALT', 'IKKE_BETALT') NOT NULL DEFAULT 'IKKE_BETALT',
                medlemStart DATE NOT NULL,
                FOREIGN KEY (postnummer) REFERENCES Postnummer(postnummer)
            );
        ";

    $queries['createInteresseTable'] = "
            CREATE OR REPLACE TABLE Interesse (
                interesseId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                navn VARCHAR(30) NOT NULL UNIQUE
            );
        ";

    $queries['createInteresse_registerTable'] = "
            CREATE OR REPLACE TABLE Interesse_register (
                medlemId INT NOT NULL,
                interesseId INT NOT NULL,
                FOREIGN KEY (medlemId) REFERENCES Medlem(medlemId),
                FOREIGN KEY (interesseId) REFERENCES Interesse(interesseId),
                PRIMARY KEY (medlemId, interesseId)
            );
        ";

    $queries['createAktivitetTable'] = "
            CREATE OR REPLACE TABLE Aktivitet (
                aktivitetId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                navn VARCHAR(40) NOT NULL,
                beskrivelse VARCHAR(500) DEFAULT ' ',
                ansvarligId INT NOT NULL,
                start DATETIME NOT NULL,
                slutt DATETIME NOT NULL,
                FOREIGN KEY (ansvarligId) REFERENCES Medlem(medlemId)
            );
        ";

    $queries['createBrukerTable'] = "
            CREATE OR REPLACE TABLE Bruker (
                brukerId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                epost VARCHAR(100) NOT NULL UNIQUE,
                passord VARCHAR(500) NOT NULL,
                ckey VARCHAR(100) NOT NULL DEFAULT ' ',
                ctime VARCHAR(100) NOT NULL DEFAULT ' '
            );
    ";

    $queries['insertPostnummerData'] = "
            INSERT INTO Postnummer VALUES (4462, 'Hovsherad'),
                                          (4614, 'Kristiansand'); 
        ";

    $queries['inserInteresse'] = "
            INSERT INTO Interesse VALUES (NULL, 'Fotball'),
                                         (NULL, 'Bading'),
                                         (NULL, 'Dansing'),
                                         (NULL, 'Grilling');
        ";

    $queries['insertMedlemData'] = "
            INSERT INTO MEDLEM
                VALUES(
                    NULL,
                    'Johannes',
                    'Birkeland',
                    'Teian 6',
                    4462,
                    'johannesbi@uia.no',
                    '2000-11-19',
                    'M',
                    'BETALT',
                    '2019-11-11'
                ), (
                    NULL,
                    'Per',
                    'Persen',
                    'Per Gaten',
                    4462,
                    'perper@uia.no',
                    '2000-11-19',
                    'O',
                    'BETALT',
                    '2019-11-11'
                ), (
                    NULL,
                    'Lina',
                    'Ridley',
                    'En gate i krs',
                    4614,
                    'linaridley@uia.no',
                    '2002-07-26',
                    'F',
                    'BETALT',
                    '2019-11-11'
                );
        ";

    $queries['insertInteresseRegister'] = "
            INSERT INTO interesse_Register VALUES (1, 1),
                                                 (1, 2),
                                                 (2, 1),
                                                 (2, 3),
                                                 (3, 3),
                                                 (3, 1);
        ";

    $queries['insertAktiviteter'] = "
            INSERT INTO Aktivitet VALUES (NULL, 'Kino', 'Kinodag i kristiansand woho', 1, '2020-07-07 19:00', '2020-07-07 21:00'),
                                         (NULL, 'Tur', 'Tur til kina', 1, '2021-12-07 19:00', '2021-07-07 21:00'),
                                         (NULL, 'Turnering', 'Fotball Turnering', 1, '2022-07-07 19:00', '2022-07-07 21:00');
                                         
        ";

    $password = password_hash('password', PASSWORD_DEFAULT);
    $queries['insertBruker'] = "
        INSERT INTO Bruker VALUES (NULL, 'johbirk00@gmail.com', '$password', '', '')
    ";
    return $queries;
}