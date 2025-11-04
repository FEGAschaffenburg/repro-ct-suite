#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Refactoring: Umbenennung von external_id zu event_id/calendar_id
    
.DESCRIPTION
    Dieses Script ersetzt alle Vorkommen von external_id in PHP-Dateien:
    - In Calendars-Kontext: external_id -> calendar_id
    - In Events-Kontext: external_id -> event_id
    - Methoden: get_by_external_id() -> get_by_calendar_id() / get_by_event_id()
    - Variablen: $external_id -> $calendar_id / $event_id
    
.EXAMPLE
    .\scripts\refactor-external-id.ps1
#>

param(
    [switch]$DryRun = $false
)

$ErrorActionPreference = "Stop"
$repoRoot = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $repoRoot

Write-Host "=== Refactoring: external_id -> event_id/calendar_id ===" -ForegroundColor Cyan
Write-Host "Repository: $repoRoot"
Write-Host "Dry Run: $DryRun"
Write-Host ""

# Dateien die bearbeitet werden sollen
$files = @(
    # Services
    "includes\services\class-repro-ct-suite-sync-service.php",
    "includes\services\class-repro-ct-suite-calendar-sync-service.php",
    
    # Admin Views
    "admin\views\admin-appointments.php",
    "admin\views\admin-events.php",
    
    # Admin Classes
    "admin\class-repro-ct-suite-admin.php"
)

$stats = @{
    FilesProcessed = 0
    FilesChanged = 0
    TotalReplacements = 0
}

function Replace-InFile {
    param(
        [string]$FilePath,
        [hashtable]$Replacements
    )
    
    if (-not (Test-Path $FilePath)) {
        Write-Host "  Warning: File not found: $FilePath" -ForegroundColor Yellow
        return $false
    }
    
    $content = Get-Content $FilePath -Raw -Encoding UTF8
    $originalContent = $content
    $changeCount = 0
    
    foreach ($pair in $Replacements.GetEnumerator()) {
        $pattern = [regex]::Escape($pair.Key)
        $matches = [regex]::Matches($content, $pattern)
        
        if ($matches.Count -gt 0) {
            $content = $content -replace $pattern, $pair.Value
            $changeCount += $matches.Count
            Write-Host "    OK: '$($pair.Key)' -> '$($pair.Value)' ($($matches.Count) times)" -ForegroundColor Green
        }
    }
    
    if ($changeCount -gt 0) {
        if (-not $DryRun) {
            Set-Content $FilePath -Value $content -Encoding UTF8 -NoNewline
        }
        $script:stats.FilesChanged++
        $script:stats.TotalReplacements += $changeCount
        return $true
    }
    
    return $false
}

Write-Host "Processing files..." -ForegroundColor Cyan
Write-Host ""

# 1. Sync Service (Events-Kontext)
Write-Host "[1/5] Sync Service..." -ForegroundColor Yellow
$replacements = @{
    "->get_by_external_id(" = "->get_by_calendar_id("
    "->upsert_by_external_id(" = "->upsert_by_calendar_id("
    "'external_id' => `$external_id" = "'calendar_id' => `$calendar_id"
    "foreach ( `$args['calendar_ids'] as `$external_id )" = "foreach ( `$args['calendar_ids'] as `$calendar_id )"
    "`$calendar = `$this->calendars_repo->get_by_calendar_id( `$external_id )" = "`$calendar = `$this->calendars_repo->get_by_calendar_id( `$calendar_id )"
    "'name'        => `$calendar ? `$calendar->name : `"Kalender {`$external_id}`"" = "'name'        => `$calendar ? `$calendar->name : `"Kalender {`$calendar_id}`""
    "Kalender: ChurchTools-ID {`$external_id}" = "Kalender: ChurchTools-ID {`$calendar_id}"
    "'external_id' => `$external_id" = "'calendar_id' => `$calendar_id"
    "Externe Kalender-IDs: " = "ChurchTools Kalender-IDs: "
    "array_column( `$external_calendar_ids, 'external_id' )" = "array_column( `$external_calendar_ids, 'calendar_id' )"
    "`$cal_info['external_id']" = "`$cal_info['calendar_id']"
    "Kalender {`$external_id}:" = "Kalender {`$calendar_id}:"
    "is_event_relevant_for_calendar( `$event, `$external_id )" = "is_event_relevant_for_calendar( `$event, `$calendar_id )"
    "process_calendar_events( `$relevant_events, `$external_id" = "process_calendar_events( `$relevant_events, `$calendar_id"
    "Fehler bei Kalender {`$external_id}" = "Fehler bei Kalender {`$calendar_id}"
    "->get_by_event_id( `$event_data['event_id']" = "->get_by_event_id( `$event_data['event_id']"
    "'external_id'    => (string) `$event['id']" = "'event_id'    => (string) `$event['id']"
    "external_id={`$event_data['event_id']}" = "event_id={`$event_data['event_id']}"
    "'external_id' => `$event_data['event_id']" = "'event_id' => `$event_data['event_id']"
    "`$external_id = `$appointment_id . '_'" = "`$event_id = `$appointment_id . '_'"
    "'external_id'     => `$external_id" = "'event_id'     => `$event_id"
    "extract_appointment_data: Event-Daten erfolgreich extrahiert (external_id={`$external_id})" = "extract_appointment_data: Event-Daten erfolgreich extrahiert (event_id={`$event_id})"
}
Replace-InFile "includes\services\class-repro-ct-suite-sync-service.php" $replacements
$stats.FilesProcessed++

# 2. Calendar Sync Service  
Write-Host "[2/5] Calendar Sync Service..." -ForegroundColor Yellow
$replacements = @{
    "->get_by_external_id(" = "->get_by_calendar_id("
    "->upsert_by_external_id(" = "->upsert_by_calendar_id("
    "'external_id'     => sanitize_text_field( `$calendar_data['id'] )" = "'calendar_id'     => sanitize_text_field( `$calendar_data['id'] )"
}
Replace-InFile "includes\services\class-repro-ct-suite-calendar-sync-service.php" $replacements
$stats.FilesProcessed++

# 3. Admin Appointments View
Write-Host "[3/5] Admin Appointments View..." -ForegroundColor Yellow
$replacements = @{
    "SELECT id, external_id, appointment_id" = "SELECT id, event_id, appointment_id"
    "// Event-ID ist immer in external_id enthalten" = "// Event-ID ist immer in event_id enthalten"
    "`$parts = explode( '_', `$row->external_id )" = "`$parts = explode( '_', `$row->event_id )"
}
Replace-InFile "admin\views\admin-appointments.php" $replacements
$stats.FilesProcessed++

# 4. Admin Events View
Write-Host "[4/5] Admin Events View..." -ForegroundColor Yellow
$replacements = @{
    "SELECT id, external_id, appointment_id" = "SELECT id, event_id, appointment_id"
    "// Event-ID ist immer in external_id enthalten" = "// Event-ID ist immer in event_id enthalten"
    "`$parts = explode( '_', `$item->external_id )" = "`$parts = explode( '_', `$item->event_id )"
    "`$cal->external_id" = "`$cal->calendar_id"
    "->get_by_external_id( `$item->calendar_id )" = "->get_by_calendar_id( `$item->calendar_id )"
}
Replace-InFile "admin\views\admin-events.php" $replacements
$stats.FilesProcessed++

# 5. Admin Class
Write-Host "[5/5] Admin Class..." -ForegroundColor Yellow
$replacements = @{
    "->get_selected_external_ids()" = "->get_selected_calendar_ids()"
    "`$selected_external_calendar_ids" = "`$selected_calendar_ids"
    "external_id=%s" = "calendar_id=%s"
    "`$cal->external_id" = "`$cal->calendar_id"
    "`$calendar->external_id" = "`$calendar->calendar_id"
}
Replace-InFile "admin\class-repro-ct-suite-admin.php" $replacements
$stats.FilesProcessed++

Write-Host ""
Write-Host "=== Summary ===" -ForegroundColor Cyan
Write-Host "Files processed: $($stats.FilesProcessed)"
Write-Host "Files changed:   $($stats.FilesChanged)" -ForegroundColor Green
Write-Host "Total replacements: $($stats.TotalReplacements)" -ForegroundColor Green

if ($DryRun) {
    Write-Host ""
    Write-Host "DRY RUN - No files were modified" -ForegroundColor Yellow
    Write-Host "Run without -DryRun to apply changes"
} else {
    Write-Host ""
    Write-Host "✓ Refactoring complete!" -ForegroundColor Green
}
