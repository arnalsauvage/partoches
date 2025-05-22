class Cartes {
    // crée un jeu de cartes
    constructor() {
        this.nombre = 0;
        this.debug = false;
        // pour stocker les indexs des images mises dans le canvas
        this.tableauCartes = [];
    }

    ajoute(carte) {
        this.tableauCartes.push(carte);
        this.nombre++;
    }

    tire() {
        const carte = this.tableauCartes.pop();
        this.nombre--;
        return carte;
    }

    // On utilise l'algorithme de fisherYates https://www.delftstack.com/fr/howto/javascript/shuffle-array-javascript/
    melange() {
        for (let i = this.tableauCartes.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1)); //random index
            [this.tableauCartes[i], this.tableauCartes[j]] = [this.tableauCartes[j], this.tableauCartes[i]]; // swap
        }
        if (this.debug) {
            console.log("*-*-*-*-*-*-*-*-*-*-*-*-");
            for (let i = this.tableauCartes.length - 1; i >= 0; i--) {
                console.log(this.tableauCartes[i]);
            }
        }
    }

    jette() {
        // vide le jeu de carte
        this.tableauCartes.splice(0, this.tableauCartes.length);
    }

    cartesColopidous() {
        this.ajoute("rouge");
        this.ajoute("orange");
        this.ajoute("jaune");
        this.ajoute("vert");
        this.ajoute("bleu");
        this.ajoute("indigo");
        this.ajoute("marron");
        this.ajoute("rose");
        this.ajoute("noir");
    }
}