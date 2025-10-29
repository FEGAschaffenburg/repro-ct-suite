# Repro CT-Suite – Entwickler-Quickstart

Diese Datei enthält die wichtigsten Hinweise für neue Entwickler, die an diesem Plugin arbeiten möchten.

## 1. Projekt einrichten

1. Repository klonen:
   ```sh
   git clone https://github.com/FEGAschaffenburg/repro-ct-suite.git
   ```
2. Mit VS Code öffnen.
3. Empfohlene Extensions installieren (Popup erscheint automatisch):
   - PHP Intelephense
   - Composer
   - Prettier
   - GitHub Pull Requests

## 2. VS Code Einstellungen

- Alle relevanten Einstellungen sind in `.vscode/settings.json` hinterlegt (Tabulatoren, Formatierung, PHP-Validierung, Suche etc.)
- Tasks für Linting in `.vscode/tasks.json`
- Empfohlene Extensions in `.vscode/extensions.json`

## 3. WordPress Plugin-Konfiguration

- Die Plugin-Einstellungen (ChurchTools-URL, Zugangsdaten, Kalender-Auswahl) werden **in der WordPress-Datenbank** gespeichert.
- Diese müssen im Zielsystem im Admin-Panel neu gesetzt werden.

## 4. Debugging & Logs

- Debug-Ausgaben erscheinen im WordPress-Admin unter dem Debug-Panel (Synchronisierung).
- Zusätzliche Logs ggf. in `wp-content/debug.log` (WP_DEBUG aktivieren).

## 5. Chat-Verlauf & Support

- Der Chat mit GitHub Copilot ist **nicht** im Projekt sichtbar und wird nicht gespeichert.
- Für wichtige Hinweise bitte diese Datei oder das `README.md` nutzen.

## 6. Release & Versionierung

- Änderungen committen und mit Tag versehen:
   ```sh
   git commit -m "Beschreibung"
   git tag -a vX.Y.Z -m "Release-Info"
   git push origin main --tags
   ```

## 7. Kontakt

- Fragen, Probleme oder neue Entwickler bitte an das GitHub-Repo wenden: https://github.com/FEGAschaffenburg/repro-ct-suite
