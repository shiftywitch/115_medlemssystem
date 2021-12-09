<?php
require_once "assets/api/api.mailHandler.php";
require_once "assets/inc/html.inc.php";
require_once "assets/inc/functions.inc.php";

reDirectIfNotLoggedIn();

htmlHeader("Mail")
?>
<script>

    //visResultat() tar imot en streng for å søke i medlemmer.
    const visResultat = (str) => {

        //Avslutter funksjonen om strengen er på 0 tegn.
        if (str.length === 0) {
            document.getElementById("mottaker").innerHTML="";
            return;
        }

        //Oppretter en ny ajax request
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            //Om requesten er ferdig og OK
            if (this.readyState === 4 && this.status === 200) {

                //Kontakter = responsen som sendes tilbake fra api.medlemHandler.php som json
                let kontakter = JSON.parse(this.responseText);
                let returnHtml;

                //For hvert js objekt i arrayen kontakter så lages det en option.
                Object.entries(kontakter).forEach(kontakt => {
                    //Value = medlemID
                    returnHtml += "<option value='" + kontakt[1][2] + "'>" + kontakt[1][0] + " " + kontakt[1][1] + " (" + kontakt[1][2] + ")</option>\n"
                });

                //Endrer innholdet i datalisten #mottakere til å inneholde <option> taggene som er i returnHtml.
                document.getElementById("mottakere").innerHTML = returnHtml;
            }
        }
        //Ajax requesten sendes som en asynkron get request til api.medlemHandler.php med parameteret m
        xhr.open("GET", "assets/api/api.medlemHandler.php?m="+str, true);
        xhr.send();
    }

    //sendMail() henter data fra html formen for å sende en request til api.mailHandler.php
    const sendMail = () => {

        const mottaker = document.getElementById("mottaker").valueOf().value;
        const emne = document.getElementById("emne").valueOf().value;
        const melding = document.getElementById("melding").valueOf().value;
        const alleMedlemmer = document.getElementById("alleMedlemmer");
        const tillegg = document.getElementById("fil").files[0];

        //Oppretter en formData som gjør det lettere å sette parametere for POST requests.
        let formData = new FormData();
        formData.append('submit','submit')
        formData.append('alleMedlemmer', alleMedlemmer.checked)
        formData.append('mottaker', mottaker)
        formData.append('emne', emne)
        formData.append('melding', melding)
        formData.append('tillegg', tillegg)

        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            //Når requesten er ferdig og OK
            if (this.readyState === 4 && this.status === 200) {
                //Statusen på mailen endres til enten at mailen er sendt, ellers får man ut feilmeldinger.
                document.getElementById("status").innerHTML = this.responseText
            }
            else {document.getElementById("status").innerHTML = "Sender epost"}
        }
        xhr.open("POST", "assets/api/api.mailHandler.php", true);
        //Data legges til i send() når det er en POST request.
        xhr.send(formData);
    }
</script>
<div class="container-md">
    <!-- Mottakere -->
    <div class="m-auto w-50 p-3" style="box-shadow: #fff2 0 0 6px 3px;">
        <h4>Send mail</h4>
        <div class="mb-3 row">
            <label for="mottaker" class="col-sm-3 col-form-label">Mottaker</label>
            <div class="col-sm-9">
                <!-- visResultat() kalles ved hver taste trykk i list inputen. -->
                <input list="mottakere" id="mottaker" onkeyup="visResultat(this.value)" class="form-control" placeholder="Mottaker"/>
                <datalist id="mottakere">
                    <!-- Options generert av javascript med data fra ajax forespørsel -->
                </datalist>
                <!-- Input for å velge alle medlemmer som mottakere -->
                <input type="checkbox" name="checkBoxAlle" id="alleMedlemmer" value="true">
                <label for="alleMedlemmer" class="col-form-label">Alle medlemmer</label>
            </div>
        </div>

        <!-- Mail emne -->
        <div class="mb-3 row">
            <label for="emne" class="col-sm col-form-label">Emne</label>
            <div class="col-sm-9">
                <input id="emne" type="text" class="form-control" placeholder="Emne">
            </div>
        </div>

        <!-- Innhold i mailen -->
        <div class="mb-3 row">
            <label for="melding" class="col-sm col-form-label">Melding</label>
            <div class="col-sm-9">
                <textarea id="melding" type="text" class="form-control" placeholder="Melding"></textarea>
            </div>
        </div>

        <!-- Fil opplastning -->
        <div class="mb-3 row">
            <label for="fil" class="col-sm col-form-label">Legg til fil: </label>
            <div class="col-sm-9">
                <input id="fil" type="file" accept="application/pdf, image/png, image/jpeg" class="form-control">
            </div>
        </div>
    </div>

    <br />

    <div class="text-center">
        <button id="sendMail" class="btn btn-primary w-30" onclick="sendMail()">Send mail!</button>
        <br /><br />
        <div id="status"></div>
    </div>
</div>
<?php
htmlFooter();