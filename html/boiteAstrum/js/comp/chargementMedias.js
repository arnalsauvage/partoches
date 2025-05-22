function chargementSons() {
    // if initializeDefaultPlugins returns false, we cannot play sound in this browser
    createjs.Sound.registerPlugins([createjs.WebAudioPlugin, createjs.HTMLAudioPlugin]);
    if (!createjs.Sound.initializeDefaultPlugins()) {
        console.error("Impossible d'initialiser le son !")
    } else {
        console.log("Librairie sonore chargée !");

        let audioPath = "medias/sounds/";
        const sounds = [{
                id: "strumB",
                src: "Strum.ogg"
            },
            {
                id: "strum",
                src: "Strum.ogg"
            }
        ];
        //createjs.Sound.alternateExtensions = ["mp3"];
        // create an array and audioPath (above)
        createjs.Sound.addEventListener("fileload", handleLoad, true);
        createjs.Sound.registerSounds(sounds, audioPath);
    }
}

function chargementMusique() {
    // Chargement de la musique
    const audioPath = "medias/music/";
    const music = [{
        id: "MusiquePiano",
        src: "GeneriquePiano.ogg"
    }];
    createjs.Sound.registerSounds(music, audioPath);
}

function handleLoad() {
    // Do something with the loaded sound
    preloadUpdate();
}

// Todo : gérer les chargements
function loadItem(url) {
    //Add the event listener and handler
    queue.on("fileload", function(event) {
        var type = event.item.type;
        if (type === createjs.LoadQueue.IMAGE) { //make a CreateJS Bitmap object from the result
            var imgItem = event.result;
            let image = new createjs.Bitmap(imgItem.src);
            stage.addChild(image);
            stage.update();
        }
    }, null, true, options); //create a LoadItem and set the crossOrigin property 
    loadItem = new createjs.LoadItem().set({
        src: url,
        crossOrigin: "Anonymous"
    }); //load it 
    queue.loadFile(loadItem);
}

function preloadUpdate() {
    preloadCount++;
    console.log('preloadCount :' + preloadCount);
    /* if (preloadCount === PRELOADTOTAL)
        launchGame(); */
}