import re
from html.parser import HTMLParser

class DjangoValidator(HTMLParser):
    VOID_ELEMENTS = {'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'}

    def __init__(self):
        super().__init__()
        self.stack = []
        self.errors = []
        self.ids = set()
        self.duplicate_ids = set()

    def handle_starttag(self, tag, attrs):
        for attr, value in attrs:
            if attr == 'id':
                if value in self.ids:
                    self.duplicate_ids.add(value)
                self.ids.add(value)
        
        if tag not in self.VOID_ELEMENTS:
            self.stack.append(tag)

    def handle_endtag(self, tag):
        if tag in self.VOID_ELEMENTS:
            return # Les balises void ne doivent pas avoir de fermante
            
        if not self.stack:
            self.errors.append(f"🔴 Balise fermante inattendue : </{tag}>")
            return
            
        last_tag = self.stack.pop()
        if last_tag != tag:
            if tag in self.stack:
                while last_tag != tag:
                    self.errors.append(f"🟡 Balise non fermée détectée : <{last_tag}> (avant </{tag}>)")
                    last_tag = self.stack.pop()
            else:
                self.errors.append(f"🔴 Erreur d'emboîtement : <{last_tag}> fermé par </{tag}>")

with open("temp_media.html", "r", encoding="utf-8") as f:
    html = f.read()

parser = DjangoValidator()
parser.feed(html)

print("🎸 --- RAPPORT D'INSPECTION DJANGO V2 --- 🎤")
if parser.duplicate_ids:
    print(f"❌ IDS EN DOUBLE : {', '.join(parser.duplicate_ids)}")
else:
    print("✅ Aucun ID en double trouvé.")

if parser.errors:
    print(f"\n❌ ERREURS DE STRUCTURE ({len(parser.errors)}) :")
    for err in parser.errors[:20]:
        print(f"   {err}")
else:
    print("✅ Structure des balises parfaite !")
