// ToDo  : ajouter une guidance applicative
// ToDo : ajouter la gestion des frettes > 9
// ToDo : proposer plusieurs versions d'un accord
// ToDo : ajouter d'autres accords
// ToDo : exporter en masse les fichiers png de tous les accords possibles

window.addEventListener("load", startup, true);
document.addEventListener("click", clicSurDiagramme);

let canvas;
let ctx;
let bGrilleTordue = true;
let margeGaucheGrille = 0;
let margeHauteurGrille = 0;
let taille = 40;
let epaisseurLigne = 0;
//const couleurTrait = '#4488EE';
let couleurTrait = '#000000';
let couleurRemplissage = '#ff4444';
let couleurGrille = '#444444';
let couleurFond = 'rgba(255	,255,255,1)';
let tailleGrillex = 4;
let tailleGrilley = 6;
let toolCouleurRemplissage;
let toolCouleurTrait;
let toolCouleurGrille;
let loupeChercheAccordParNom;
let loupeChercheAccordParValeurs;

// Initialisation
function startup() {

    canvas = document.getElementById('diagramme1');
    toolCouleurRemplissage = document.querySelector("#couleurRemplissage");
    toolCouleurRemplissage.value = couleurRemplissage;
    toolCouleurTrait = document.querySelector("#couleurTrait");
    toolCouleurTrait.value = couleurTrait;
    toolCouleurGrille = document.querySelector("#couleurGrille");
    toolCouleurGrille.value = couleurGrille;
    loupeChercheAccordParNom = document.getElementById("loupeChercheAccordParNom");
    loupeChercheAccordParValeurs = document.getElementById("loupeChercheAccordParValeurs");
    changeTaille(taille);

    /// Mise à jour des données lors du clic sur le colorpicker

    toolCouleurRemplissage.addEventListener("input", updateFirst, false);
    toolCouleurRemplissage.addEventListener("change", updateAll, false);
    toolCouleurTrait.addEventListener("input", updateFirst, false);
    toolCouleurTrait.addEventListener("change", updateAll, false);
    toolCouleurGrille.addEventListener("input", updateFirst, false);
    toolCouleurGrille.addEventListener("change", updateAll, false);
    canvas = document.getElementById('diagramme1');
    ctx = canvas.getContext('2d');
    dessineDiagramme();
    loupeChercheAccordParNom.addEventListener("touchend", chercheAccordParNom, false);
    loupeChercheAccordParValeurs.addEventListener("touchend", chercheAccordParPosition, false);
}

// Change la taille du diagramme : la taille en pixels d'un carré du diagramme
function changeTaille(nouvelleTaille) {
    taille = nouvelleTaille;
    epaisseurLigne = taille / 12;
    margeHauteurGrille = taille * 70 / 50;
    margeGaucheGrille = taille / 2;
    canvas.width = 4 * taille;
    canvas.height = 7 * taille;
}

// Appelé lors d'une mise à jour d'un outil
function updateFirst(event) {
    couleurRemplissage = toolCouleurRemplissage.value;
    couleurTrait = toolCouleurTrait.value;
    couleurGrille = toolCouleurGrille.value;
    dessineDiagramme();
}

function updateAll(event) {
    updateFirst(event);
}

// Changement de la taille
function outputUpdate(nouvelleTaille) {
    document.querySelector('#taille').value = nouvelleTaille;
    changeTaille(nouvelleTaille);
    dessineDiagramme();
}

// Dessine la grille du diagramme, légèrement irrégulière
function dessineGrille() {
    let maxCasesVerticales = tailleGrilley - 1;
    let pointsGrille = [];
    // On crée les coordonnées des points de la grille de notre diagramme
    for (let y = 0; y <= maxCasesVerticales; y++) {
        for (let x = 0; x < tailleGrillex; x++) {
            pointsGrille[(x + tailleGrillex * y) * 2] = margeGaucheGrille + x * taille;
            pointsGrille[(x + tailleGrillex * y) * 2 + 1] = margeHauteurGrille + y * taille;
            if (bGrilleTordue && (getRandomInt(120) < taille)) {
                pointsGrille[(x + tailleGrillex * y) * 2] += getRandomInt(3) - 1;
                pointsGrille[(x + tailleGrillex * y) * 2 + 1] += getRandomInt(3) - 1;
            }
        }
    }

    // On tire les traits entre les points de notre diagramme
    // x1,y1 : point d'origine, x2,y2 point à droite, x3, y3, point en dessous
    ctx.strokeStyle = couleurGrille; //Nuance de noir
    ctx.lineWidth = epaisseurLigne;
    ctx.beginPath();
    for (let y = 0; y <= maxCasesVerticales; y++) {
        for (let x = 0; x <= tailleGrillex; x++) {
            let x1 = getx(x, y, pointsGrille, tailleGrillex);
            let y1 = gety(x, y, pointsGrille, tailleGrillex);
            let x2 = getx(x + 1, y, pointsGrille, tailleGrillex);
            let y2 = gety(x + 1, y, pointsGrille, tailleGrillex);
            let x3 = getx(x, y + 1, pointsGrille, tailleGrillex);
            let y3 = gety(x, y + 1, pointsGrille, tailleGrillex);

            if (x !== 3) {
                ctx.moveTo(x1, y1);
                ctx.lineTo(x2, y2);
            }
            ctx.moveTo(x1, y1 - epaisseurLigne / 2);
            ctx.lineTo(x3, y3 + epaisseurLigne / 2);
        }
    }
    ctx.stroke();
    return pointsGrille;
}

// Dessine le diagramme complet
function dessineDiagramme() {
    blank();
    let pointsGrille;
    // console.log("Nombre de cases nécessaires : " + maxCasesVerticales );
    pointsGrille = dessineGrille();

    let fretteZeroDuDiagramme = calculeFretteDepart(document.getElementById("valeurs").value);

    // Si la première frette est la frette zéro, on la met en gras!
    if (fretteZeroDuDiagramme === 0) {
        // On tire les traits entre les points de notre diagramme
        // x1,y1 : point d'origine, x2,y2 point à droite
        let y = 0;
        ctx.beginPath();
        ctx.lineWidth = epaisseurLigne;
        ctx.strokeStyle = couleurGrille;

        for (let x = 0; x <= 2; x++) {
            let x1 = getx(x, y, pointsGrille, tailleGrillex);
            let y1 = gety(x, y, pointsGrille, tailleGrillex);
            let x2 = getx(x + 1, y, pointsGrille, tailleGrillex);
            let y2 = gety(x + 1, y, pointsGrille, tailleGrillex);

            ctx.moveTo(x1 - epaisseurLigne / 2, y1 - epaisseurLigne / 2);
            ctx.lineTo(x2 + epaisseurLigne / 2, y2 - epaisseurLigne / 2);
            ctx.lineWidth = epaisseurLigne;
            ctx.moveTo(x1 - epaisseurLigne / 2, y1 - epaisseurLigne);
            ctx.lineTo(x2 + epaisseurLigne / 2, y2 - epaisseurLigne);
        }
        ctx.stroke();
    }
    ctx.beginPath();
    ctx.strokeStyle = couleurTrait; //Nuance de noir
    ctx.lineWidth = epaisseurLigne;
    ctx.strokeStyle = couleurRemplissage;
    metLesDoigts(document.getElementById("valeurs").value);
    ecritNomAccord(document.getElementById("name").value);
    ctx.stroke();
}

// dessine un point sur le diagramme
function dessinePoint(nCorde, nfrette) {
    ctx.beginPath();
    ctx.strokeStyle = couleurTrait;
    ctx.lineWidth = taille / 20;
    let monx = margeGaucheGrille + (nCorde - 1) * taille;
    let mony;
    // Corde jouée
    if (nfrette > 0) {
        ctx.fillStyle = couleurRemplissage;
        mony = margeHauteurGrille + nfrette * taille - taille / 2;
        ctx.arc(monx, mony, taille / 4, 0, 2 * Math.PI);
        ctx.fill();
    }
    // Corde à vide
    if (nfrette === 0) {
        ctx.fillStyle = couleurFond;
        mony = margeHauteurGrille;
        ctx.arc(monx, mony, taille / 6, 0, 2 * Math.PI);
        ctx.fill();
    }
    // Corde non jouée
    if (nfrette === "x") {
        mony = margeHauteurGrille;
        ctx.strokeStyle = couleurTrait;
        ctx.lineWidth = taille / 10;
        const ecart = taille / 6;
        ctx.moveTo(monx - ecart, mony - ecart);
        ctx.lineTo(monx + ecart, mony + ecart);
        ctx.moveTo(monx + ecart, mony - ecart);
        ctx.moveTo(monx + ecart, mony - ecart);
        ctx.lineTo(monx - ecart, mony + ecart);
    }
    ctx.stroke();
}

// compte le nombre de cases necessaires pour afficher l'accord
function compteCasesNecessaires(valeurs) {
    let valMax = 0;
    let valMin = 12;
    for (let compteur = 0; compteur < 4; compteur++) {
        if (valeurs[compteur] === 'x')
            continue;
        if (valeurs[compteur] > valMax) {
            valMax = valeurs[compteur];
        }
        if ((valeurs[compteur] > 0) && (valeurs[compteur] < valMin)) {
            valMin = valeurs[compteur];
        }
    }
    // Gestion du décalage : affichage de la frette de départ
    // On ne décalera pas si l'accord s'affiche dans les 4 premières cases,
    // avec les dex premières lignes vides ( ex C)
    if ((valMax < 5) && (valMin > 2)) {
        valMin = 0;
    }
    console.log("cases nécessaires pour " + valeurs + " : " + (valMax - valMin + 1));
    return (valMax - valMin + 2);
}

// trouve la frette la plus basse jouée dans l'accord
function getValeurMin(valeurs) {
    let valMin = 12;
    for (let compteur = 0; compteur < 4; compteur++) {
        if ((valeurs[compteur] > 0) && (valeurs[compteur] < valMin)) {
            valMin = valeurs[compteur];
        }
    }
    return (valMin);
}

// trouve la frette la plus hautee jouée dans l'accord
function getValeurMax(valeurs) {
    let valMax = 0;
    for (let compteur = 0; compteur < 4; compteur++) {
        if ((valeurs[compteur] > 0) && (valeurs[compteur] > valMax)) {
            valMax = valeurs[compteur];
        }
    }
    return (valMax);
}

// Vide le diagramme
function blank() {
    ctx.fillStyle = couleurFond;
    ctx.fillRect(0, 0, ctx.canvas.width, ctx.canvas.height);
}

// Ecrit le nom de l'accord au dessus du diagramme
function ecritNomAccord(nom) {
    ctx.font = 'bold ' + taille + 'px Verdana, Arial, serif';
    ctx.fillStyle = couleurTrait;
    ctx.textAlign = 'center'; //Le milieu du texte sera à 150
    ctx.fillText(nom, margeGaucheGrille + 1.5 * taille, 2 * margeHauteurGrille / 3, taille * 3);
}

// Calcule la frette de départ qui semble le plus appropriée pour la position
function calculeFretteDepart(valeurs) {
    let fretteDepart = 0;
    if ((getValeurMin(valeurs) > 1) && (getValeurMax(valeurs) > 4)) {
        fretteDepart = getValeurMin(valeurs);
    }
    return fretteDepart;
}

// pose les points qui doivent êtres joués sur le diagramme, ainsi que les autres détails
function metLesDoigts(valeurs) {
    // Ecrit la frette de départ si ce n'est pas zéro
    let fretteDepart = calculeFretteDepart(valeurs);
    if (fretteDepart > 1) {

        ctx.beginPath();
        ctx.font = 'bold ' + taille / 1.8 + 'px Verdana, Arial, serif';
        ctx.fillStyle = couleurTrait;
        ctx.fillText(fretteDepart.toString(), margeGaucheGrille - (.35 * taille), margeHauteurGrille + 12 * taille);
        ctx.stroke();
    }

    for (let corde = 0; corde < 4; corde++) {
        // Si la corde n'est pas jouée, on dessine une croix
        if (isNaN(valeurs[corde])) {
            dessinePoint(corde + 1, valeurs[corde]);
        } else {
            let numeroFrette = parseInt(valeurs[corde]);
            if (numeroFrette === 0) {
                dessinePoint(corde + 1, 0);
            }
            if (fretteDepart > 0) {
                dessinePoint(corde + 1, numeroFrette - fretteDepart + 1);
            } else {
                dessinePoint(corde + 1, numeroFrette);
            }
        }
    }
}

// récupérer la coordonnée x graphique du point x,y (diagr)
function getx(x, y, tableau, maxx) {
    return (tableau[(x + maxx * y) * 2]);
}

// récupérer la coordonnée x graphique du point x,y (diagr)
function gety(x, y, tableau, maxx) {
    return (tableau[1 + (x + maxx * y) * 2]);
}

// Tire un chiffre entier entre 0 et max-1
function getRandomInt(max) {
    // console.log ("random " + max +  " : "  + Math.floor(Math.random() * Math.floor(max)));
    return Math.floor(Math.random() * Math.floor(max));
}

// Propose de transformer en lien de download l'élément el
// pour télécharger le diagramme affiché avec le nom d'accord comme nom de fichier
function download_img(el) {
    let lienDownload = document.getElementById("download");
    let nomAccord = document.getElementById("name").value;
    lienDownload.download = nomAccord + ".jpg";
    el.href = canvas.toDataURL("image/jpg");
}

// Affiche la position et le diagramme pour l'accord portant le nom saisi dans le champ de saisie
function chercheAccordParNom() {
    let accord = document.getElementById("name").value;
    let saisieValeurs = document.getElementById("valeurs");
    saisieValeurs.value = tableauAccords[accord];
    dessineDiagramme();
}

// Cherche le nom de l'accord avec la position saisie
function chercheAccordParPosition() {
    console.log("coucou");
    let position = document.getElementById("valeurs").value;
    //ex : tableauAccords["C"] = "0003";
    let saisieName = document.getElementById("name");
    saisieName.value = "non repertorié";
    for (let key in tableauAccords) {
        if (tableauAccords[key] === position) {
            saisieName.value = key;
            break;
        }
    }
    dessineDiagramme();
}

// Tire un accord au hasard dans la bibliothèque !
function setAccordAuHasard() {
    const accords = Object.keys(tableauAccords);
    let nombreDaccords = accords.length;
    const numeroAccord = getRandomInt(nombreDaccords);
    let saisieName = document.getElementById("name");
    saisieName.value = accords[numeroAccord];
    // console.log("Saisiename : " + accords[numeroAccord]);
    chercheAccordParNom();
}

// Permet de changer la position quand on clique sur le diagramme
function clicSurDiagramme(event) {
    // console.log("Clic sur " + event.clientX + " y : " + event.clientY);
    let relatifX = event.clientX - margeGaucheGrille - document.getElementById("diagramme1").getBoundingClientRect().left;
    let relatifY = event.clientY - margeHauteurGrille - document.getElementById("diagramme1").getBoundingClientRect().top;

    relatifX = Math.round(relatifX / taille);
    relatifY = Math.round(relatifY / taille + 0.5);
    // console.log("Clic sur relatif " + relatifX + " y : " + relatifY);

    if (relatifX < 4 && relatifY < 6) {
        let position = document.getElementById("valeurs");
        if (relatifX < 4)
            position.value[relatifX] = relatifY;
        let maNouvelleChaine = "";
        for (let i = 0; i < 4; i++) {
            if (i === relatifX) {
                maNouvelleChaine += relatifY;
            } else {
                maNouvelleChaine += position.value.substr(i, 1);
            }
        }
        console.log("nouvelle position : " + maNouvelleChaine);
        position.value = maNouvelleChaine;
        dessineDiagramme();
    }
}