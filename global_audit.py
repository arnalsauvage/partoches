import urllib.request
import os
import subprocess

# Liste des pages à auditer
pages = {
    "Accueil_Medias": "/php/media/listeMedias.php",
    "Liste_Chansons": "/php/chanson/chanson_liste.php?razFiltres=1",
    "Fiche_Chanson_1": "/php/chanson/chanson_voir.php?id=1",
    "Liste_Songbooks": "/php/songbook/songbook_liste.php",
    "Portfolio_Songbooks": "/php/songbook/songbook-portfolio.php",
    "Voir_Songbook_40": "/php/songbook/songbook_voir.php?id=40",
    "Communaute": "/php/utilisateur/utilisateur_liste.php",
    "Repertoire_Strums": "/php/strum/strum_liste.php",
    "Galerie_Liens": "/php/liens/lienurl_liste.php",
    "Documents": "/php/document/documents_voir.php",
    "Parametrage": "/php/navigation/paramsEdit.php",
    "Roadbook": "/php/todo/todo_admin.php",
    "Form_Chanson": "/php/chanson/chanson_form.php",
    "Form_Utilisateur": "/php/utilisateur/utilisateur_form.php?id=1",
}

base_url = "http://localhost:8080"
output_dir = "rendered_html"

if not os.path.exists(output_dir):
    os.makedirs(output_dir)

print(f"🎸 Démarrage de l'audit pour {len(pages)} pages...\n")

for name, path in pages.items():
    url = f"{base_url}{path}"
    if '?' in url:
        url += "&smoke_test=1"
    else:
        url += "?smoke_test=1"
        
    print(f"🎵 Récupération : {name}...")
    try:
        req = urllib.request.Request(url)
        with urllib.request.urlopen(req) as response:
            html = response.read().decode('utf-8')
            file_path = os.path.join(output_dir, f"{name}.html")
            with open(file_path, "w", encoding="utf-8") as f:
                f.write(html)
    except Exception as e:
        print(f"⚠️ Erreur sur {name} : {e}")

print(f"\n📡 Lancement de htmlhint sur le dossier {output_dir}...\n")
try:
    # On lance htmlhint (il doit être installé globalement)
    result = subprocess.run(['htmlhint', output_dir], capture_output=True, text=True, shell=True)
    print(result.stdout)
    if result.stderr:
        print("❌ Erreurs de l'outil :\n", result.stderr)
except Exception as e:
    print(f"❌ Impossible de lancer htmlhint : {e}")
