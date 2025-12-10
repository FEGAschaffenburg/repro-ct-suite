$file = 'admin\js\repro-ct-suite-admin.js'
$content = Get-Content $file -Raw -Encoding UTF8

# Finde die Zeile mit "initAjaxActions: function() {"
$search = "initAjaxActions: function() {`r`n`t`t// Debug-Log Helper"
$replace = @"
initAjaxActions: function() {
		console.log('[DEBUG] initAjaxActions() wird aufgerufen');
		console.log('[DEBUG] jQuery Version:', `$.fn.jquery);
		
		// Prüfe ob Button existiert (nach kurzem Delay)
		setTimeout(function() {
			const btnCount = `$('.repro-ct-suite-sync-calendars-btn').length;
			console.log('[DEBUG] Anzahl .repro-ct-suite-sync-calendars-btn Buttons:', btnCount);
			if (btnCount > 0) {
				console.log('[DEBUG] Button gefunden:', `$('.repro-ct-suite-sync-calendars-btn')[0]);
			} else {
				console.warn('[WARNUNG] Kein Button mit Klasse .repro-ct-suite-sync-calendars-btn gefunden!');
			}
		}, 500);
		
		// Debug-Log Helper
"@

$content = $content.Replace($search, $replace)
[System.IO.File]::WriteAllText($file, $content, [System.Text.Encoding]::UTF8)
Write-Host "Debug-Logs hinzugefuegt" -ForegroundColor Green
