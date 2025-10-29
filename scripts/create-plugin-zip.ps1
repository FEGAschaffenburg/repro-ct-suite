param(
    [string]$OutputDir = (Split-Path -Parent (Split-Path -Parent $PSCommandPath)),
    [string]$ZipName = "repro-ct-suite.zip",
    [switch]$VerboseLog
)

$ErrorActionPreference = 'Stop'
function Log($msg){ if($VerboseLog){ Write-Host $msg -ForegroundColor Cyan } }

$sourcePath = (Split-Path -Parent (Split-Path -Parent $PSCommandPath))
$tempRoot   = Join-Path $sourcePath 'build'
$stageRoot  = Join-Path $tempRoot 'stage'
$pkgFolder  = Join-Path $stageRoot 'repro-ct-suite'
$zipPath    = Join-Path $OutputDir $ZipName

# Cleanup
if (Test-Path $stageRoot) { Remove-Item -Recurse -Force $stageRoot }
New-Item -ItemType Directory -Path $pkgFolder | Out-Null

if (-not (Test-Path $sourcePath)) {
    throw "Source folder not found: $sourcePath"
}

# Copy full tree, then prune excludes
$excludeDirs = @('.git', '.github', '.vscode', 'scripts', 'tests', 'vendor', 'node_modules', 'assets-src', 'build')
$excludeFiles = @('composer.json','composer.lock','phpunit.xml*','.gitignore','.gitattributes','clear-update-cache.php','.DS_Store','Thumbs.db','repro-ct-suite.zip')

Log "Copy files from $sourcePath to $pkgFolder"
# Copy only top-level items, excluding unwanted directories; then prune residuals
$topItems = Get-ChildItem -Path $sourcePath -Force
foreach($item in $topItems){
    if ($item.PSIsContainer) {
        if ($excludeDirs -contains $item.Name) { continue }
        $destDir = Join-Path $pkgFolder $item.Name
        Copy-Item -Path $item.FullName -Destination $destDir -Recurse -Force
    } else {
        $skipFile = $false
        foreach($f in $excludeFiles){ if ($item.Name -like $f) { $skipFile = $true; break } }
        if ($skipFile) { continue }
        Copy-Item -Path $item.FullName -Destination (Join-Path $pkgFolder $item.Name) -Force
    }
}

# Precise filtering pass: remove excluded patterns if copied indirectly
foreach($d in $excludeDirs){ $p = Join-Path $pkgFolder $d; if(Test-Path $p){ Remove-Item -Recurse -Force $p }}
foreach($f in $excludeFiles){ Get-ChildItem -Path $pkgFolder -Recurse -Force -Include $f | Remove-Item -Force -ErrorAction SilentlyContinue }

# Sanity check
$mainFile = Join-Path $pkgFolder 'repro-ct-suite.php'
if (-not (Test-Path $mainFile)) { throw "Main plugin file not found at $($mainFile)" }

# Build zip (keep top-level folder) mit Unix-Pfadtrenner
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

$zip = [System.IO.Compression.ZipFile]::Open($zipPath, [System.IO.Compression.ZipArchiveMode]::Create)

try {
    # Alle Dateien rekursiv durchgehen
    $allFiles = Get-ChildItem -Path $pkgFolder -Recurse -File
    foreach ($file in $allFiles) {
        # Relativen Pfad berechnen
        $relativePath = $file.FullName.Substring($stageRoot.Length + 1)
        # Windows-Pfadtrenner durch Unix-Trenner ersetzen
        $entryName = $relativePath -replace '\\', '/'
        
        # Datei zum ZIP hinzufügen
        $entry = $zip.CreateEntry($entryName, [System.IO.Compression.CompressionLevel]::Optimal)
        $entryStream = $entry.Open()
        $fileStream = [System.IO.File]::OpenRead($file.FullName)
        $fileStream.CopyTo($entryStream)
        $fileStream.Close()
        $entryStream.Close()
    }
    
    # Leere Ordner hinzufügen (wichtig für Struktur)
    $allDirs = Get-ChildItem -Path $pkgFolder -Recurse -Directory
    foreach ($dir in $allDirs) {
        $relativePath = $dir.FullName.Substring($stageRoot.Length + 1)
        $entryName = ($relativePath -replace '\\', '/') + '/'
        
        # Prüfen ob Ordner leer ist
        if ((Get-ChildItem -Path $dir.FullName -Force).Count -eq 0) {
            $zip.CreateEntry($entryName) | Out-Null
        }
    }
    
    Log "ZIP erstellt mit Unix-Pfadtrenner"
} finally {
    $zip.Dispose()
}

# Verify zip listing
$zipRead = [System.IO.Compression.ZipFile]::OpenRead($zipPath)
$entries = $zipRead.Entries | Select-Object -First 10 | ForEach-Object { $_.FullName }
$mainEntry = $zipRead.Entries | Where-Object { $_.FullName -eq 'repro-ct-suite/repro-ct-suite.php' }
$zipRead.Dispose()

if ($mainEntry) {
    Log ("Created: {0}`nEntries (first):`n - {1}" -f $zipPath, ($entries -join "`n - "))
    Write-Host "OK: ZIP korrekt - repro-ct-suite/repro-ct-suite.php gefunden" -ForegroundColor Green
    Write-Host "OK: $zipPath" -ForegroundColor Green
} else {
    Write-Host "WARNUNG: Hauptdatei nicht an erwarteter Position!" -ForegroundColor Yellow
    Write-Host "OK: $zipPath" -ForegroundColor Green
}
