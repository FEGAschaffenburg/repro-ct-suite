$file = "c:\privat\repro-ct-suite\includes\repositories\class-repro-ct-suite-schedule-repository.php"
$lines = Get-Content $file

$newLines = @()
$skip = $false

for ($i = 0; $i -lt $lines.Count; $i++) {
    # Zeile 31: "$appointments_table = ..."
    if ($i -eq 31) {
        # Diese Zeile überspringen (appointments_table Variable)
        $skip = $true
        continue
    }
    
    # Zeilen 32-113: Appointments-Abfrage und Schleife überspringen
    if ($i -ge 32 -and $i -le 113) {
        if ($i -eq 113) {
            # Nach der letzten übersprungenen Zeile den Kommentar einfügen
            $newLines += ""
            $newLines += "`t`t// HINWEIS: wp_rcts_appointments-Tabelle wurde in v0.4.8.0 entfernt"
            $newLines += "`t`t// Unified Sync speichert alle Termine (Events + Appointments) in wp_rcts_events"
            $newLines += ""
            $newLines += "`t`treturn array("
            $newLines += "`t`t`t'events' => `$count_events,"
            $newLines += "`t`t`t'appointments' => 0, // Legacy-Kompatibilität"
            $newLines += "`t`t);"
            $skip = $false
        }
        continue
    }
    
    $newLines += $lines[$i]
}

$newLines | Set-Content $file -Encoding UTF8
Write-Host "Datei aktualisiert: $file"
