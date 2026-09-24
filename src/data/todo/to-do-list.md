##update : Page Paramétrage : peut-être ajouter des fichiers de log 

##newfeature : Utilisateurs : il faut pouvoir gérer une icône perso dans data/utilisateurs/iduser ou une image dans les communs /public/images/utilisateur

##newfeature : permettre de créer un compte

##newfeature : les audios et vidéos ne sont pas affichés pour les personnes non connectées. 
Une mention s'affiche : "il y a des vidéos/ audios, mais vous devez vous connecter pour les voir !
Créez un compte gratuitement !" ( dépend de la feature créer un compte)



##bugfix : les boutons "modifier" et "supprimer" pour les strums sont trop proches : il faudrait mettre "modifier" à gauche d el'icônet et "supprimer" à droite dans https://partoches.canopee-musique.fr/public/php/strum/strum_liste.php . 

Dans cette div : <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; border-top: 1px dashed #D2B48C; padding-top: 15px;">
                        <a title="Ouvrir dans la Boîte à Strum" href="../../html/boiteAstrum/index.html?strum=BHBHB-&amp;ternaire=false" style="text-decoration: none;">
                            <img src="../../html/boiteAstrum/medias/img/boiteAstrum.png" alt="Boîte à Strum" height="40" style="border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        </a>
                        <div style="display: flex; gap: 10px;"> <a href="strum_form.php?id=196" class="btn btn-md" title="Editer" style="background-color: #8B4513; color: white; border: none; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);"><i class="glyphicon glyphicon-pencil" style="font-size: 1.2em;"></i></a> <button type="button" class="btn btn-md btn-danger" title="Supprimer" onclick="supprimerStrum(196, &quot;BHBHB-&quot;)" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);"><i class="glyphicon glyphicon-trash" style="font-size: 1.2em;"></i></button>      </div>
                    </div>

##bugfix : erreur lors de la mise à jour d'un utilisateur avec envoi de son image : Fatal error: Uncaught Error: Failed opening required '../lib/vignette.php' (include_path='.:/opt/alt/php82/usr/share/pear:/opt/alt/php82/usr/share/php:/usr/share/pear:/usr/share/php') in /home/u715493341/domains/partoches.canopee-musique.fr/public_html/public/php/utilisateur/utilisateur_upload.php:4 Stack trace: #0 {main} thrown in /home/u715493341/domains/partoches.canopee-musique.fr/public_html/public/php/utilisateur/utilisateur_upload.php on line 4

##newfeature : Ajouter des tags de contenu pour exercices, strums, chansons, atelier, sondage