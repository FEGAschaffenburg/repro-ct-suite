# Git Upload (Automatische GitHub Releases)

Das Plugin unterstützt jetzt automatische GitHub Releases via GitHub Actions.

## Wie funktioniert es?

Wenn Sie einen neuen Version-Tag erstellen und zu GitHub pushen, wird automatisch:
1. Ein Release-ZIP erstellt
2. Ein GitHub Release angelegt
3. Das ZIP als Release-Asset hochgeladen

## Verwendung

### Automatisches Release

```bash
# 1. Version in repro-ct-suite.php aktualisieren
# 2. Änderungen committen
git add repro-ct-suite.php
git commit -m "Version 0.9.5.2"

# 3. Version-Tag erstellen und pushen
git tag v0.9.5.2
git push origin v0.9.5.2
```

Das wars! GitHub Actions erstellt automatisch das Release.

### Manuelles Release (GitHub UI)

Sie können den Workflow auch manuell starten:

1. Gehen Sie zu **Actions** → **Build and publish release**
2. Klicken Sie auf **Run workflow**
3. Wählen Sie den Branch (normalerweise `main`)
4. Klicken Sie auf **Run workflow**

### Unterstützte Version-Formate

Der Workflow unterstützt beide Formate:
- 3-teilig: `v1.2.3`
- 4-teilig: `v1.2.3.4`

## Was wird erstellt?

Das Release enthält:
- **Tag**: z.B. `v0.9.5.2`
- **Release-Titel**: z.B. `v0.9.5.2`
- **Release-Asset**: `repro-ct-suite-0.9.5.2-fs.zip`
- **Beschreibung**: "Automated release for v0.9.5.2"

## Voraussetzungen

- GitHub Actions muss aktiviert sein
- Der Workflow benötigt die Permission `contents: write`
- Tags müssen das Format `v*.*.*` oder `v*.*.*.*` haben

## Fehlerbehebung

Wenn der Workflow fehlschlägt:

1. **Prüfen Sie die Actions**: Gehen Sie zu **Actions** → **Build and publish release**
2. **Logs ansehen**: Klicken Sie auf den fehlgeschlagenen Workflow
3. **Häufige Probleme**:
   - Version-Tag existiert bereits
   - Ungültiges Tag-Format
   - Fehlende Berechtigungen

## Manuelle Releases (Fallback)

Falls Sie ein Release manuell erstellen möchten:

**Windows (PowerShell):**
```powershell
.\scripts\create-wp-zip-simple.ps1 -Version "0.9.5.2"
gh release create v0.9.5.2 "repro-ct-suite.zip" --title "Version 0.9.5.2"
```

**Linux/macOS:**
```bash
./scripts/create-release-zip.sh 0.9.5.2
gh release create v0.9.5.2 "repro-ct-suite-0.9.5.2-fs.zip" --title "Version 0.9.5.2"
```

## Weitere Informationen

Siehe auch:
- `.github/workflows/release.yml` - Der Workflow
- `scripts/create-release-zip.sh` - Das Build-Script
- `scripts/README.md` - Detaillierte Script-Dokumentation
