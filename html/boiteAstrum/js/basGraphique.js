
function dessineFleches(chaineStrum) {
    if (DEBUG) {
        console.log("dessine fleches strum : " + chaineStrum.getStrum());
    }
    const iterations = chaineStrum.getLongueur();

    for (let i = 1; i <= iterations; i++) {
        choisitFleche(chaineStrum.getBattement(i), i);
    }
}

function choisitFleche(strum, temps) {
    //if temps is odd
    let flecheBas = true;
    let id;
    if (temps % 2 === 1) {
        id = "b" + (temps + 1) / 2;

    } else {
        id = "h" + temps / 2;
        flecheBas = false;
    }
    if (DEBUG) {
        console.log("id fleche : " + id + " strum : " + strum);
    }
    // if strum in SEPARATEURS
    if (SEPARATEURS.includes(strum)) {
        strum = " ";
    }
    switch (strum) {
        case "B":
            strum = "flecheBasForce2";
            break;
        case "H":
            strum = "flecheHautForce2";
            break;
        case "b":
            strum = "flecheBasForce1";
            break;
        case "h":
            strum = "flecheHautForce1";
            break;
        case "X":
            strum = "chunk-fort";
            break;
        case "x":
            strum = "chunk-faible";
            break;
        case "A":
            strum = "arpege-fort";
            break;
        case "a":
            strum = "arpege-doux";
            break;
        case " ":
            if (flecheBas) {
                strum = "flecheBasForce0";
            } else {
                strum = "flecheHautForce0";
            }
            break;
    }
    document.getElementById(id).src = "medias/img/" + strum + ".png";
}