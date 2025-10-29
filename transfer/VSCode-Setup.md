# VS Code Setup & Übertragung

## Extensions exportieren

Auf dem Quell-Rechner im Terminal ausführen:
```powershell
code --list-extensions --show-versions > vscode-extensions.txt
```
Dadurch wird die Liste der installierten Extensions (mit Version) in die Datei `vscode-extensions.txt` im Ordner `transfer` geschrieben.

## Extensions importieren

Auf dem Ziel-Rechner:
1. Die Datei `vscode-extensions.txt` in den Projektordner kopieren.
2. Im Terminal ausführen:
   ```powershell
   Get-Content vscode-extensions.txt | ForEach-Object { code --install-extension $_.Split()[0] }
   ```
   Dadurch werden alle Extensions installiert (nur Name, keine Version).

## Einstellungen übertragen

Die VS Code-Einstellungen findest du unter:
```
%APPDATA%\Code\User\settings.json
```
Diese Datei kann ebenfalls kopiert und auf dem Ziel-Rechner ersetzt werden (vorher Backup machen!).

---

**Tipp:**
- Die Datei `vscode-extensions.txt` und diese Anleitung im Ordner `transfer` ablegen, damit alle Entwickler sie nutzen können.
