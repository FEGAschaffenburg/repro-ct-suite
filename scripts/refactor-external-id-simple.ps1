#!/usr/bin/env pwsh
# Refactoring: external_id -> event_id/calendar_id

param([switch]$DryRun)

$files = @{
    "includes\services\class-repro-ct-suite-sync-service.php" = @(
        @("->get_by_external_id(", "->get_by_calendar_id("),
        @("'external_id' => " + '$external_id', "'calendar_id' => " + '$calendar_id'),
        @('foreach ( $args[''calendar_ids''] as $external_id )', 'foreach ( $args[''calendar_ids''] as $calendar_id )'),
        @('$calendar = $this->calendars_repo->get_by_calendar_id( $external_id )', '$calendar = $this->calendars_repo->get_by_calendar_id( $calendar_id )'),
        @('"Kalender {$external_id}"', '"Kalender {$calendar_id}"'),
        @('Kalender: ChurchTools-ID {$external_id}', 'Kalender: ChurchTools-ID {$calendar_id}'),
        @('array_column( $external_calendar_ids, ''external_id'' )', 'array_column( $external_calendar_ids, ''calendar_id'' )'),
        @('$cal_info[''external_id'']', '$cal_info[''calendar_id'']'),
        @('is_event_relevant_for_calendar( $event, $external_id )', 'is_event_relevant_for_calendar( $event, $calendar_id )'),
        @('process_calendar_events( $relevant_events, $external_id', 'process_calendar_events( $relevant_events, $calendar_id'),
        @('Fehler bei Kalender {$external_id}', 'Fehler bei Kalender {$calendar_id}'),
        @("->get_by_event_id( " + '$event_data[''event_id'']', "->get_by_event_id( " + '$event_data[''event_id'']'),
        @("'external_id'    => (string) " + '$event[''id'']', "'event_id'    => (string) " + '$event[''id'']'),
        @('external_id={$event_data[''event_id'']}', 'event_id={$event_data[''event_id'']}'),
        @("'external_id' => " + '$event_data[''event_id'']', "'event_id' => " + '$event_data[''event_id'']'),
        @('$external_id = $appointment_id', '$event_id = $appointment_id'),
        @("'external_id'     => " + '$external_id', "'event_id'     => " + '$event_id'),
        @('(external_id={$external_id})', '(event_id={$event_id})')
    )
    "includes\services\class-repro-ct-suite-calendar-sync-service.php" = @(
        @("->get_by_external_id(", "->get_by_calendar_id("),
        @("->upsert_by_external_id(", "->upsert_by_calendar_id("),
        @("'external_id'     => sanitize_text_field(", "'calendar_id'     => sanitize_text_field(")
    )
    "admin\views\admin-appointments.php" = @(
        @("SELECT id, external_id, appointment_id", "SELECT id, event_id, appointment_id"),
        @('$row->external_id', '$row->event_id')
    )
    "admin\views\admin-events.php" = @(
        @("SELECT id, external_id, appointment_id", "SELECT id, event_id, appointment_id"),
        @('$item->external_id', '$item->event_id'),
        @('$cal->external_id', '$cal->calendar_id'),
        @("->get_by_external_id( " + '$item->calendar_id', "->get_by_calendar_id( " + '$item->calendar_id')
    )
    "admin\class-repro-ct-suite-admin.php" = @(
        @("->get_selected_external_ids()", "->get_selected_calendar_ids()"),
        @('$selected_external_calendar_ids', '$selected_calendar_ids'),
        @('external_id=%s', 'calendar_id=%s'),
        @('$cal->external_id', '$cal->calendar_id'),
        @('$calendar->external_id', '$calendar->calendar_id')
    )
}

$total = 0
foreach ($file in $files.Keys) {
    $path = Join-Path "c:\privat\repro-ct-suite" $file
    if (-not (Test-Path $path)) {
        Write-Host "SKIP: $file (not found)" -ForegroundColor Yellow
        continue
    }
    
    $content = Get-Content $path -Raw -Encoding UTF8
    $changed = $false
    
    Write-Host "Processing: $file" -ForegroundColor Cyan
    
    foreach ($replacement in $files[$file]) {
        $old = $replacement[0]
        $new = $replacement[1]
        
        if ($content -match [regex]::Escape($old)) {
            $count = ([regex]::Matches($content, [regex]::Escape($old))).Count
            $content = $content -replace [regex]::Escape($old), $new
            Write-Host "  OK: $count replacement(s)" -ForegroundColor Green
            $total += $count
            $changed = $true
        }
    }
    
    if ($changed -and -not $DryRun) {
        Set-Content $path -Value $content -Encoding UTF8 -NoNewline
        Write-Host "  SAVED" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "Total: $total replacements" -ForegroundColor Green
if ($DryRun) {
    Write-Host "DRY RUN - No files modified. Run without -DryRun to apply." -ForegroundColor Yellow
}
