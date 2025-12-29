$file = "c:\privat\repro-ct-suite\includes\repositories\class-repro-ct-suite-schedule-repository.php.bak"
$outfile = "c:\privat\repro-ct-suite\includes\repositories\class-repro-ct-suite-schedule-repository.php"

$lines = Get-Content $file
$result = New-Object System.Collections.ArrayList

for ($i = 0; $i -lt $lines.Count; $i++) {
    $line = $lines[$i]
    
    # Zeile 31 (Index 30): $appointments_table entfernen
    if ($i -eq 30 -and $line -match 'appointments_table') {
        continue  # Skip this line
    }
    
    # Zeilen 112-147: Von "// Appointments..." bis zum }; nach return überspringen
    # (Index 111-146)
    if ($i -ge 111 -and $i -le 146) {
        if ($i -eq 111) {
            # Ersetze den Appointments-Block durch Kommentar und return
            [void]$result.Add("")
            [void]$result.Add("`t`t// HINWEIS: wp_rcts_appointments-Tabelle wurde in v0.4.8.0 entfernt")
            [void]$result.Add("`t`t// Unified Sync speichert alle Termine (Events + Appointments) in wp_rcts_events")
            [void]$result.Add("")
            [void]$result.Add("`t`treturn array(")
            [void]$result.Add("`t`t`t'events' => `$count_events,")
            [void]$result.Add("`t`t`t'appointments' => 0, // Legacy-Kompatibilität")
            [void]$result.Add("`t`t);")
        }
        continue  # Skip all lines in this range
    }
    
    [void]$result.Add($line)
}

$result | Set-Content $outfile -Encoding UTF8
Write-Host "Datei erfolgreich aktualisiert!"
Write-Host "Original: $($lines.Count) Zeilen"
Write-Host "Neu: $($result.Count) Zeilen"
