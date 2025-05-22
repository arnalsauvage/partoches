
function sonTermine() {
    if (DEBUG){
        console.log("Son terminé !");
    }
    sonEnCours = false;
}

function joueSilence() {
    if (DEBUG){
    console.log('on va jouer un silence');
    }

    if (!sonEnCours) {
        sonEnCours = true;
        console.log('on joue un silence');
        let instance = createjs.Sound.play("silence");
        instance.on("complete", sonTermine);
    }
}

function metStrumEnFileDattente(chaineStrum, monTempo, bternaire) {
    var filedAttente = [];
    var numeroBattement = 0;
    var nombreDeTemps = 0;
    monTempsDeDebut = window.Date.now() + 1000;

    chaineStrumLance = chaineStrum;
    tempo = monTempo;
    let dernierTempsCalcule = 0;

    // on calcule le temps de lancement pour chaque battement
    for (let strum of chaineStrum) {
        if ((strum !== " ") && (strum !== "-")) {
            // if numeroBattement is even, it's a beat
            if ((nombreDeTemps % 2 === 0) || bternaire === false) {
                filedAttente[numeroBattement] = monTempsDeDebut + tempsDuneCrocheA60Bpm * nombreDeTemps / monTempo;
            }
            // if numeroBattement is even, it's a rest
            else {
                filedAttente[numeroBattement] = dernierTempsCalcule + (4 * tempsDuneCrocheA60Bpm) / (3 * monTempo);
            }
            numeroBattement++;
        }
        dernierTempsCalcule = monTempsDeDebut + tempsDuneCrocheA60Bpm * nombreDeTemps / monTempo;
        nombreDeTemps++;
    }
    return filedAttente;
}

function joueSon(sonAjouer) {
    if (DEBUG){
    console.log("on joue le son " + sonAjouer);
    }
    sonEnCours = true;
    let instance = createjs.Sound.play(sonAjouer);
    instance.on("complete", sonTermine);
}

// Pour tester un strum au démarrage de la page par exemple
function lanceStrumDeTest() {
    listeDeMoments[0] = monTempsDeDebut + 10000;
    listeDeMoments = metStrumEnFileDattente("B   B Bh h h hbh", 120);
}

// Traitement à appeler toutes les x millisecondes
const monIntervalle = 1;
var listeDeMoments;
setInterval(function() {
    var nombreDeTemps = 0;
    if (listeDeMoments.length > 0) {
        let tempsActuel = window.Date.now();
        if (tempsActuel < listeDeMoments[0]) {
            // console.log("attends !");
        } else {
            let nbreOccurrencesStrum = listeDeMoments.length;
            listeDeMoments[nbreOccurrencesStrum] = tempsActuel + tempsDuneCrocheA60Bpm * longueurStrum / tempo;
            if (DEBUG){
            console.log("Tempo : " + tempo);
            }
            // Todo repérer à quelle étape du strum on est
            nombreDeTemps = (tempsActuel - monTempsDeDebut) * tempo / (60000);
            if (tempsEnCours < nombreDeTemps) {
                if (DEBUG){
                console.log("Nombre de temps " + nombreDeTemps);
                }
                tempsEnCours = nombreDeTemps;
            }
            // Caractere du battement = chaineDuStrum[etape]
            // Puis, au lieu de jouer "strum" , jouer strum + caractereDuBattement
            joueSon("strum");
            listeDeMoments.splice(0, 1);
        }
    }
}, monIntervalle);