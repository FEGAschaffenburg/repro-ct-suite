$file = "c:\privat\repro-ct-suite\includes\repositories\class-repro-ct-suite-schedule-repository.php"
$content = Get-Content $file -Raw

# Suche und ersetze den Appointments-Block
$oldPattern = @'
	// Appointments: only those not linked to an event
	\$where_a = 'WHERE 1=1 AND \(event_id IS NULL\)';
	\$params = array\(\);
	if \( ! empty\( \$args\['from'\] \) \) \{ \$where_a \.= ' AND start_datetime >= %s'; \$params\[\] = \$args\['from'\]; \}
	if \( ! empty\( \$args\['to'\] \) \) \{ \$where_a \.= ' AND start_datetime <= %s'; \$params\[\] = \$args\['to'\]; \}

	\$sql_a = \$this->db->prepare\( "SELECT \* FROM \{\$appointments_table\} \{\$where_a\}", \.\.\.\$params \);
	\$appointments = \$this->db->get_results\( \$sql_a \);

	\$count_appointments = 0;
	foreach \( \$appointments as \$a \) \{
		\$this->upsert_from_appointment\( array\(
			'id' => \(int\) \$a->id,
			'event_id' => \$a->event_id,
			'calendar_id' => \$a->calendar_id,
			'title' => \$a->title,
			'description' => \$a->description,
			'start_datetime' => \$a->start_datetime,
			'end_datetime' => \$a->end_datetime,
			'is_all_day' => \$a->is_all_day,
		\) \);
		\$count_appointments\+\+;
	\}

	return array\(
		'events' => \$count_events,
		'appointments' => \$count_appointments,
	\);
'@

$newText = @'
	// HINWEIS: wp_rcts_appointments-Tabelle wurde in v0.4.8.0 entfernt
	// Unified Sync speichert alle Termine (Events + Appointments) in wp_rcts_events

	return array(
		'events' => $count_events,
		'appointments' => 0, // Legacy-Kompatibilität
	);
'@

$newContent = $content -replace $oldPattern, $newText

# Entferne auch die $appointments_table Zeile
$newContent = $newContent -replace '(\$events_table = \$this->db->prefix \. ''rcts_events'';)\s*\$appointments_table = \$this->db->prefix \. ''rcts_appointments'';', '$1'

$newContent | Set-Content $file -Encoding UTF8 -NoNewline
Write-Host "Datei aktualisiert"
