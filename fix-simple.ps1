$file = "c:\privat\repro-ct-suite\includes\repositories\class-repro-ct-suite-schedule-repository.php"
$content = Get-Content $file -Raw

# Ersetze die SQL-Abfrage mit einem leeren Array
$pattern = '\$sql_a = \$this->db->prepare\( "SELECT \* FROM \{\$appointments_table\} \{\$where_a\}", \.\.\.\$params \);[\r\n\t ]+\$appointments = \$this->db->get_results\( \$sql_a \);'
$replacement = '// LEGACY: wp_rcts_appointments table removed in v0.4.8.0$appointments = array(); // Skip query to non-existent table'

$newContent = $content -replace $pattern, $replacement

$newContent | Set-Content $file -NoNewline -Encoding UTF8
Write-Host "SQL-Abfrage auskommentiert"
