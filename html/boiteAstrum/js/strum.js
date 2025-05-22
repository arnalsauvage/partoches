// Object Name: Strum

class Strum {
    // crée un strum
    constructor() {
        this.stringStrum = "";
        this.ternaire = false;
    }

    ajoute(strum) {
        this.stringStrum += strum;
    }

    setStrum(strum) {
        this.stringStrum = strum;
    }

    getStrum() {
        return this.stringStrum;
    }

    getLongueur() {
        return this.stringStrum.length;
    }

    setTernaire(ternaire) {
        this.ternaire = ternaire;
    }

    getTernaire() {
        return this.ternaire;
    }

    compteTemps(temps) {
        return this.stringStrum.length;
    }

    compteLesTempsEtLEsMesures() {
        let chaineAafficher = "";
        let nombreDeDemiTempsRestants = this.stringStrum.length;
        let nombreDeMesures = Math.trunc(nombreDeDemiTempsRestants / 8);
        chaineAafficher = nombreDeMesures + " mesure";
        if (nombreDeMesures > 1) {
            chaineAafficher += "s";
        }
        nombreDeDemiTempsRestants = this.stringStrum.length - nombreDeMesures * 8;
        let nombreDeTemps = Math.trunc(nombreDeDemiTempsRestants / 2);
        if (nombreDeTemps > 0) {
            chaineAafficher += " et " + nombreDeTemps + " temps";
        }
        if (nombreDeDemiTempsRestants > nombreDeTemps * 2) {
            chaineAafficher += " et un demi-temps.";
        } else {
            chaineAafficher += ".";
        }
        return chaineAafficher;
    }

    getBattement(numeroDeBattement) {
        let battement = " ";
        const indice = numeroDeBattement - 1;
        if (this.stringStrum.length > indice) {
            battement = this.stringStrum.charAt(indice);
        }
        return battement;
    }
}

// Activer pour tester avec Jest
// module.exports = Strum;