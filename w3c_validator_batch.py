import urllib.request
import json
import time

# Liste des pages du Smoke Test à valider
urls = [
    "http://localhost:8080/php/media/listeMedias.php",
    "http://localhost:8080/php/songbook/songbook_liste.php",
    "http://localhost:8080/php/chanson/chanson_liste.php?razFiltres=1",
    "http://localhost:8080/php/strum/strum_liste.php",
    "http://localhost:8080/php/utilisateur/utilisateur_liste.php",
    "http://localhost:8080/php/navigation/paramsEdit.php"
]

w3c_url = "https://validator.w3.org/nu/?out=json"
headers = {'Content-Type': 'text/html; charset=utf-8', 'User-Agent': 'Mozilla/5.0'}

def validate_url(url):
    print(f"🎵 Analyse de : {url}")
    try:
        # 1. Récupération du HTML local
        req_local = urllib.request.Request(url)
        with urllib.request.urlopen(req_local) as response:
            html_content = response.read()
        
        # 2. Envoi au W3C
        req_w3c = urllib.request.Request(w3c_url, data=html_content, headers=headers)
        with urllib.request.urlopen(req_w3c) as response_w3c:
            result = json.loads(response_w3c.read().decode('utf-8'))
            
            messages = result.get('messages', [])
            errors = [m for m in messages if m.get('type') == 'error']
            warnings = [m for m in messages if m.get('type') != 'error']
            
            if not errors:
                print(f"✅ CONFORME ! (0 erreur, {len(warnings)} warnings)")
            else:
                print(f"❌ {len(errors)} ERREURS TROUVÉES :")
                for err in errors[:3]: # On affiche les 3 premières pour pas saturer
                    print(f"   - Ligne {err.get('lastLine', '?')} : {err.get('message')}")
                if len(errors) > 3:
                    print(f"   ... and {len(errors)-3} more errors.")
            print("-" * 30)
            
    except Exception as e:
        print(f"⚠️ Impossible de valider cette URL : {e}\n")

# On commence par la première comme demandé
validate_url(urls[0])
