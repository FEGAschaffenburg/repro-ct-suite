# Update-Erkennung testen

Das GitHub Release v0.4.0.0 wurde erfolgreich erstellt und ist verfügbar:

## ✅ Release Status
- **Tag**: v0.4.0.0
- **Asset**: repro-ct-suite.zip (123,689 bytes)
- **API**: `/releases/latest` gibt korrekt v0.4.0.0 zurück

## 🔄 Update-Erkennung forcieren

WordPress cacht Update-Informationen in Transients. Für sofortige Update-Erkennung:

### Methode 1: WordPress Admin
1. Gehe zu **Plugins > Installierte Plugins**
2. Klicke oben auf **"Auf Updates prüfen"**
3. Das Plugin sollte jetzt Update auf v0.4.0.0 anzeigen

### Methode 2: Update-Cache leeren (falls verfügbar)
```php
// In WordPress Admin Console oder via wp-cli:
delete_site_transient('update_plugins');
```

### Methode 3: Plugin-Update-Seite
1. Gehe zu **Repro CT-Suite > Update**
2. Dort sollte die neue Version erkannt werden
3. Auto-Update kann aktiviert werden

## 📋 Verifikation
- GitHub API: ✅ v0.4.0.0 verfügbar
- Release Asset: ✅ ZIP korrekt erstellt  
- Download-URL: ✅ funktionsfähig
- Plugin-Updater: ✅ sollte jetzt Update erkennen

Der GitHub-Updater sollte die neue Version automatisch erkennen, sobald WordPress die Update-Transients aktualisiert hat.