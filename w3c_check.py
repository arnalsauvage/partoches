import urllib.request
import re

url_local = "http://localhost:8080/php/media/listeMedias.php"
try:
    req_local = urllib.request.Request(url_local)
    response_local = urllib.request.urlopen(req_local)
    html_content = response_local.read().decode('utf-8')
    
    with open("temp_media.html", "w", encoding="utf-8") as f:
        f.write(html_content)
    print("✅ Fichier HTML rendu sauvegardé localement dans temp_media.html")
    
except Exception as e:
    print(f"❌ Erreur : {e}")
