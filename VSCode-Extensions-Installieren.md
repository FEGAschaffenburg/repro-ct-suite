# VS Code Extensions auf anderem Notebook installieren

## Schritt-für-Schritt-Anleitung

1. **Extensions exportieren**
   - Auf dem Quell-Notebook im Terminal ausführen:
     ```powershell
     code --list-extensions --show-versions > vscode-extensions.txt
     ```
   - Die Datei `vscode-extensions.txt` in den Projektordner kopieren.

2. **Extensions importieren**
   - Auf dem Ziel-Notebook die Datei `vscode-extensions.txt` in den Projektordner legen.
   - Im VS Code-Terminal ausführen:
     ```powershell
     Get-Content vscode-extensions.txt | ForEach-Object { code --install-extension $_.Split()[0] }
     ```
   - Dadurch werden alle Extensions installiert (nur Name, keine Version).

3. **VS Code Einstellungen übertragen**
   - Die Datei mit den Einstellungen findest du unter:
     ```
     %APPDATA%\Code\User\settings.json
     ```
   - Diese Datei kann ebenfalls kopiert und auf dem Ziel-Notebook ersetzt werden (vorher Backup machen!).

---

**Tipp:**
- Die Datei `vscode-extensions.txt` und diese Anleitung im Projektordner ablegen, damit alle Entwickler sie nutzen können.
