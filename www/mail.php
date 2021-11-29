<?php
require_once "assets/api/api.mailHandler.php";
require_once "assets/inc/html.inc.php";
require_once "assets/inc/functions.inc.php";

reDirectIfNotLoggedIn();

htmlHeader("Mail")
?>
<style>

</style>
<script>

    const visResultat = (str) => {
        if (str.length === 0) {
            document.getElementById("mottaker").innerHTML="";
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {

                let kontakter = JSON.parse(this.responseText);
                let returnHtml;

                Object.entries(kontakter).forEach(kontakt => {
                    returnHtml += "<option value='" + kontakt[1][2] + "'>" + kontakt[1][0] + " " + kontakt[1][1] + " (" + kontakt[1][2] + ")</option>\n"
                    console.log(kontakt[1][0])
                });

                document.getElementById("mottakere").innerHTML = returnHtml;
            }
        }
        xhr.open("GET", "assets/api/api.medlemHandler.php?m="+str, true);
        xhr.send();
    }

    const sendMail = () => {

        const mottaker = document.getElementById("mottaker").valueOf().value;
        const emne = document.getElementById("emne").valueOf().value;
        const melding = document.getElementById("melding").valueOf().value;
        const alleMedlemmer = document.getElementById("alleMedlemmer");

        let formData = new FormData();
        formData.append('submit','submit')
        formData.append('alleMedlemmer', alleMedlemmer.checked)
        formData.append('mottaker', mottaker)
        formData.append('emne', emne)
        formData.append('melding', melding)

        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("status").innerHTML = this.responseText
            }
            else {document.getElementById("status").innerHTML = "Sender epost"}
        }
        xhr.open("POST", "assets/api/api.mailHandler.php", true);
        xhr.send(formData);
    }
</script>
<div class="container-md">
    <div class="m-auto w-50 p-3" style="box-shadow: #fff2 0 0 6px 3px;">
        <h4>Send mail</h4>
        <div class="mb-3 row">
            <label for="mottaker" class="col-sm-3 col-form-label">Mottaker</label>
            <div class="col-sm-9">
                <input list="mottakere" id="mottaker" onkeyup="visResultat(this.value)" class="form-control" placeholder="Mottaker"/>
                <datalist id="mottakere">

                </datalist>
                <input type="checkbox" name="checkBoxAlle" id="alleMedlemmer" value="true">
                <label for="alleMedlemmer" class="col-sm-5 col-form-label">Alle medlemmer</label>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="emne" class="col-sm col-form-label">Emne</label>
            <div class="col-sm-9">
                <input id="emne" type="text" class="form-control" placeholder="Emne">
            </div>
        </div>

        <div class="mb-3 row">
            <label for="melding" class="col-sm col-form-label">Melding</label>
            <div class="col-sm-9">
                <textarea id="melding" type="text" class="form-control" placeholder="Melding"></textarea>
            </div>
        </div>

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