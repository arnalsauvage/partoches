class Etoiles {
    // crée une jauge d'étoiles
    constructor(stage, nombre, nombreMax, x, y) {
        this.stage = stage;
        this.x = x;
        this.y = y;
        this.nombre = nombre;
        this.nombreMax = nombreMax;
        // pour stocker les indexs des images mises dans le canvas
        this.tableauChildIndex = new Array;
        this.imageEtoilePleine = new Image();
        this.imageEtoileVide = new Image();
        this.imageEtoilePleine.src = './medias/img/etoilePleine.png';
        this.imageEtoileVide.src = './medias/img/etoileVide.png';
    }

    // on dessine les étoiles vides et pleines
    init() {
        let child;
        this.tableauChildIndex = new Array;
        let etoilePleine;
        let bitmapEtoile;

        for (let i = 0; i < this.nombreMax; i++) {
            etoilePleine = (i < this.nombre);
            if (etoilePleine) {
                bitmapEtoile = new createjs.Bitmap(this.imageEtoilePleine);
            } else {
                bitmapEtoile = new createjs.Bitmap(this.imageEtoileVide);
            }
            bitmapEtoile.setTransform(this.x + 35 * i, this.y, 0.1, 0.1);
            child = this.stage.addChild(bitmapEtoile);
            this.tableauChildIndex.push(this.stage.getChildIndex(child));
        }
        this.stage.update();
    }

    set(nombreEtoiles) {
        this.efface();
        this.nombre = nombreEtoiles;
        this.init();
    }

    augmente() {
        this.set(this.nombre + 1);
    }
    efface() {
        // supprimer les child etoiles du stage
        for (let i = 0; i < this.nombreMax; i++) {
            this.stage.removeChildAt(this.tableauChildIndex[i]);
        }
        this.tableauChildIndex.splice(0, this.tableauChildIndex.length);
        this.stage.update();
    }
}