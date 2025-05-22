const tempsDuneCrocheA60Bpm = 1000 * 60 / 2;
// Nombre de medias à reloader
const PRELOADTOTAL = 2;
const SEPARATEURS = "-. ";
const DEBUG = false;
let sonEnCours = false;
let preloadCount = 0;

var monTempsDeDebut = window.Date.now();
var listeDeMoments = {};
var tempo = 120;
var tempsActuel;
var longueurStrum = 8;
var chaineStrumLance = "";
var tempsDeLancement;
var tempsEnCours = 0;
var ternaire = false;

function init() {
    chargementSons();
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);

    if (urlParams.has('strum'))
    {
        const strum = urlParams.get('strum')
        document.getElementById("strumVersionTexte").value = strum;
        majTexteStrum();
    }
    //lanceStrumDeTest();
}

function clickSurPlay() {
    tempo = document.getElementById("tempo").value;
    ternaire = document.getElementById("ternaire").checked;
    listeDeMoments = metStrumEnFileDattente(document.getElementById("strumVersionTexte").value, tempo, ternaire);
    longueurStrum = document.getElementById("strumVersionTexte").value.length;
}

function clickSurPause() {
    listeDeMoments.splice(0, listeDeMoments.length);
}

function majTexteStrum() {
    let strumTape = new Strum()
    strumTape.setStrum(document.getElementById("strumVersionTexte").value);
    document.getElementById("mesure").textContent = strumTape.compteLesTempsEtLEsMesures(strumTape);
    dessineFleches(strumTape);
}