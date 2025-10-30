<#
Create release ZIP for repro-ct-suite (PowerShell)

Usage:
  .\create-release-zip.ps1 -Version 0.3.8.0

What it does:
 - Creates a temporary distribution folder containing the plugin files
 - Creates a ZIP archive
 - Rebuilds the ZIP to normalize entry names to use forward-slashes (/) so WordPress accepts the ZIP
 - Outputs ZIP to workspace root: repro-ct-suite-<version>-fs.zip
#>
param(
    [Parameter(Mandatory=$true)]
    [string]$Version
)

$here = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent
$repoRoot = Resolve-Path "$here\.." | Select-Object -ExpandProperty Path
$distRoot = Join-Path $env:TEMP "repro-ct-suite-dist-$Version"
$distTop = Join-Path $distRoot "repro-ct-suite"
$outZip = Join-Path $repoRoot "repro-ct-suite-$Version-fs.zip"

Write-Host "Repo: $repoRoot"
Write-Host "Dist temp: $distRoot"
Write-Host "Output ZIP: $outZip"

# Cleanup
if (Test-Path $distRoot) { Remove-Item -Recurse -Force $distRoot }
New-Item -ItemType Directory -Path $distTop | Out-Null

# Copy files (exclude .git)
Get-ChildItem -Path $repoRoot -Force | Where-Object { $_.Name -ne '.git' -and $_.Name -ne '.gitignore' -and $_.Name -ne "repro-ct-suite-$Version-fs.zip" } | ForEach-Object {
    $dest = Join-Path $distTop $_.Name
    if ($_.PSIsContainer) {
        Copy-Item -Path $_.FullName -Destination $dest -Recurse -Force
    } else {
        Copy-Item -Path $_.FullName -Destination $dest -Force
    }
}

# Create initial zip using Compress-Archive
if (Test-Path $outZip) { Remove-Item $outZip -Force }
Compress-Archive -Path "$distTop\*" -DestinationPath $outZip -Force

# Normalize entries to forward slashes by rebuilding the archive
$fixedZip = Join-Path $env:TEMP "repro-ct-suite-$Version-fs-fixed.zip"
if (Test-Path $fixedZip) { Remove-Item $fixedZip -Force }
Add-Type -AssemblyName System.IO.Compression.FileSystem
$src = [System.IO.Compression.ZipFile]::OpenRead($outZip)
$dst = [System.IO.Compression.ZipFile]::Open($fixedZip, [System.IO.Compression.ZipArchiveMode]::Create)
foreach ($e in $src.Entries) {
    $name = $e.FullName -replace '\\','/'
    if ([string]::IsNullOrEmpty($name)) { continue }
    if ($name.EndsWith('/')) {
        $dst.CreateEntry($name) | Out-Null
    } else {
        $s = $e.Open()
        $entry = $dst.CreateEntry($name, [System.IO.Compression.CompressionLevel]::Optimal)
        $d = $entry.Open()
        $s.CopyTo($d)
        $d.Close(); $s.Close()
    }
}
$src.Dispose(); $dst.Dispose();

# Replace original
Move-Item -Force -Path $fixedZip -Destination $outZip

# Cleanup temp
Remove-Item -Recurse -Force $distRoot

Write-Host "Created: $outZip"
Write-Host "Done."