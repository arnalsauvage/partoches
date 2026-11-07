$(function () {

    // --- Gestion de la largeur de fenêtre (Ajax) ---
    let windowWidth = window.innerWidth;
    $(window).on('resize', function() {
        // On attend la fin du redimensionnement pour envoyer l'ajax (debounce)
        clearTimeout(window.resizeTimer);
        window.resizeTimer = setTimeout(function() {
            if (window.innerWidth !== windowWidth) {
                windowWidth = window.innerWidth;
                $.post('../lib/ajaxappli.php', { largeur_fenetre: windowWidth });
            }
        }, 500);
    });

    // --- Configuration globale Toastr ---
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "positionClass": "toast-top-center",
            "closeButton": true,
            "progressBar": true,
            "timeOut": "5000"
        };
    }

    // --- Sécurisation universelle des uploads (Bouclier WAF / ModSecurity) ---
    $(document).on('change', 'input[type="file"]', function () {
        securiseInputFile(this);
    });

    $(document).on('submit', 'form', function () {
        const fileInputs = this.querySelectorAll('input[type="file"]');
        fileInputs.forEach(function (input) {
            securiseInputFile(input);
        });
    });

    // --- Initialisation automatique des Lecteurs Audio Canopée ---
    initCanopeeAudioPlayers();
});

/**
 * Fonction de confirmation de suppression moderne (Django Style)
 * Remplace l'ancienne fonction du fichier javascript.js
 * @param {string} url - L'URL de redirection en cas de succès
 * @param {string} message - Le message à afficher
 */
function confirmeSuppr(url, message) {
    const $modal = $('#modalConfirmation');
    if ($modal.length) {
        $('#modalConfirmationMessage').text(message || 'Voulez-vous vraiment supprimer cet élément ?');
        $('#btnConfirmAction').off('click').on('click', function() {
            window.location.href = url;
        });
        $modal.modal('show');
    } else {
        // Fallback si la modale n'est pas dans le DOM
        if (confirm(message || 'Voulez-vous vraiment supprimer cet élément ?')) {
            window.location.href = url;
        }
    }
}

/**
 * Change la vignette affichée dans un formulaire (hérité de javascript.js)
 */
function changeListeImage(formulaire) {
    const l1 = formulaire.elements["listeImage"];
    if (l1) {
        const image = l1.options[l1.selectedIndex].value;
        const $vignette = $('#vignette');
        if ($vignette.length) {
            $vignette.attr('src', "../../data/vignettes/" + image);
        }
    }
}

/**
 * Met à jour la liste des images via AJAX (hérité de javascript.js)
 */
function miseAjourListeImages(scriptPhpListeImages) {
    $.get(scriptPhpListeImages, function(data) {
        if (typeof toastr !== 'undefined') {
            toastr.info("Nouvelle liste reçue du serveur.");
        } else {
            alert("Nouvelle liste reçue du serveur.");
        }
        // Attention : eval est dangereux mais on garde la compatibilité pour l'instant
        eval(data);
    }).fail(function() {
        if (typeof toastr !== 'undefined') {
            toastr.error("Erreur lors de la mise à jour de la liste.");
        }
    });
}


/**
 * Assainit un nom de fichier pour les uploads (Bouclier pare-feu / WAF) :
 * - Supprime les accents via décomposition NFD
 * - Convertit '#' en 'diese'
 * - Remplace tous les caractères non-alphanumériques (espaces, apostrophes, ponctuation...) par '-'
 * - Réduit les tirets consécutifs en un seul '-'
 * - Supprime les tirets en début et fin de nom
 * @param {string} nomOriginal
 * @returns {string}
 */
function assainitNomFichierUpload(nomOriginal) {
    if (!nomOriginal) return 'fichier';
    const dotIndex = nomOriginal.lastIndexOf('.');
    let baseName = dotIndex !== -1 ? nomOriginal.slice(0, dotIndex) : nomOriginal;
    const ext = dotIndex !== -1 ? nomOriginal.slice(dotIndex + 1) : '';

    // Décomposition Unicode pour retirer les accents
    let cleanBase = baseName.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    cleanBase = cleanBase.replace(/#/g, 'diese');
    // Tout ce qui n'est pas lettre ASCII ou chiffre devient un tiret
    cleanBase = cleanBase.replace(/[^a-zA-Z0-9]+/g, '-');
    // Réduction des suites de tirets
    cleanBase = cleanBase.replace(/-+/g, '-');
    // Nettoyage début et fin
    cleanBase = cleanBase.replace(/^-+|-+$/g, '');

    if (!cleanBase) {
        cleanBase = 'fichier';
    }

    return ext ? cleanBase + '.' + ext.toLowerCase() : cleanBase;
}

/**
 * Assainit automatiquement les fichiers sélectionnés dans un <input type="file"> via l'API DataTransfer
 * @param {HTMLInputElement} input
 */
function securiseInputFile(input) {
    if (!input || !input.files || input.files.length === 0) return;
    if (typeof DataTransfer !== 'function') return;

    try {
        const dt = new DataTransfer();
        let aEteModifie = false;

        for (let i = 0; i < input.files.length; i++) {
            const fichier = input.files[i];
            const nomNettoye = assainitNomFichierUpload(fichier.name);

            if (nomNettoye !== fichier.name) {
                const fichierRenomme = new File([fichier], nomNettoye, {
                    type: fichier.type,
                    lastModified: fichier.lastModified
                });
                dt.items.add(fichierRenomme);
                aEteModifie = true;
            } else {
                dt.items.add(fichier);
            }
        }

        if (aEteModifie) {
            input.files = dt.files;
            if (typeof toastr !== 'undefined') {
                toastr.info("Nom de fichier sécurisé pour l'envoi : " + input.files[0].name);
            }
        }
    } catch (e) {
        console.warn("Impossible d'assainir le fichier via DataTransfer :", e);
    }
}


/**
 * Initialise les lecteurs audio pédagogiques Canopée (.canopee-audio-player)
 */
function initCanopeeAudioPlayers() {
    $('.canopee-audio-player').each(function () {
        const $player = $(this);
        if ($player.data('cap-initialized')) return;
        $player.data('cap-initialized', true);
        $player.addClass('cap-enhanced');

        const audio = $player.find('audio.cap-native-audio')[0];
        if (!audio) return;

        const $btnPlay = $player.find('.cap-btn-play');
        const $playIcon = $btnPlay.find('.cap-play-icon');
        const $progressBar = $player.find('.cap-progress-bar');
        const $progressContainer = $player.find('.cap-progress-container');
        const $currentTime = $player.find('.cap-current-time');
        const $totalTime = $player.find('.cap-total-time');
        const $btnLoop = $player.find('.cap-btn-loop');
        const $speedBtns = $player.find('.cap-speed-btn');
        const $btnPrev = $player.find('.cap-btn-prev');
        const $btnNext = $player.find('.cap-btn-next');

        function formatTime(seconds) {
            if (isNaN(seconds) || seconds < 0) return '0:00';
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return m + ':' + (s < 10 ? '0' : '') + s;
        }

        function updatePlayIcon(isPlaying) {
            if (isPlaying) {
                $playIcon.removeClass('glyphicon-play').addClass('glyphicon-pause');
                $btnPlay.attr('title', 'Mettre en pause');
            } else {
                $playIcon.removeClass('glyphicon-pause').addClass('glyphicon-play');
                $btnPlay.attr('title', 'Lancer la lecture');
            }
        }

        // Lecture / Pause
        $btnPlay.on('click', function () {
            if (audio.paused) {
                // Pause tous les autres lecteurs audio de la page
                $('audio.cap-native-audio').each(function () {
                    if (this !== audio && !this.paused) {
                        this.pause();
                    }
                });
                audio.play().catch(function (e) {
                    console.warn("Erreur lecture audio :", e);
                });
            } else {
                audio.pause();
            }
        });

        audio.addEventListener('play', function () {
            updatePlayIcon(true);
        });

        audio.addEventListener('pause', function () {
            updatePlayIcon(false);
        });

        audio.addEventListener('ended', function () {
            if (!audio.loop) {
                updatePlayIcon(false);
                $progressBar.css('width', '0%');
                $currentTime.text('0:00');
            }
        });

        // Durée totale
        audio.addEventListener('loadedmetadata', function () {
            $totalTime.text(formatTime(audio.duration));
        });
        if (audio.duration) {
            $totalTime.text(formatTime(audio.duration));
        }

        // Progression de la lecture
        audio.addEventListener('timeupdate', function () {
            if (!audio.duration) return;
            const pct = (audio.currentTime / audio.duration) * 100;
            $progressBar.css('width', pct + '%');
            $currentTime.text(formatTime(audio.currentTime));
        });

        // Déplacement dans la piste (Seek)
        $progressContainer.on('click', function (e) {
            if (!audio.duration) return;
            const rect = this.getBoundingClientRect();
            const clickPos = (e.clientX - rect.left) / rect.width;
            audio.currentTime = clickPos * audio.duration;
        });

        // Saut arrière 5s
        $btnPrev.on('click', function () {
            audio.currentTime = Math.max(0, audio.currentTime - 5);
        });

        // Saut avant 5s
        $btnNext.on('click', function () {
            if (audio.duration) {
                audio.currentTime = Math.min(audio.duration, audio.currentTime + 5);
            }
        });

        // Mode boucle
        $btnLoop.on('click', function () {
            audio.loop = !audio.loop;
            $(this).toggleClass('active', audio.loop);
            const title = audio.loop ? 'Mode boucle : Activé (répétition continue)' : 'Mode boucle : Désactivé';
            $(this).attr('title', title);
        });

        // Sélecteur de vitesse pédagogique
        $speedBtns.on('click', function () {
            const spd = parseFloat($(this).data('speed'));
            if (!isNaN(spd)) {
                audio.playbackRate = spd;
                $speedBtns.removeClass('active');
                $(this).addClass('active');
            }
        });
    });
}
