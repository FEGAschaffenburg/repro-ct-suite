<#
Set ACLs for plugin debug logs on Windows (IIS)
Usage (Admin PowerShell):
  .\set-log-perms.ps1 -LogPath 'C:\inetpub\wwwroot\wp-content' -AppPoolIdentity 'IIS AppPool\DefaultAppPool'

If AppPoolIdentity is omitted, the script grants Modify rights to IIS_IUSRS group.
#>
param(
    [string]$LogPath = "C:\inetpub\wwwroot\wp-content",
    [string]$AppPoolIdentity = ''
)

$debug = Join-Path $LogPath 'debug.log'
$plugin = Join-Path $LogPath 'repro-ct-suite-debug.log'

if (-not (Test-Path $LogPath)) { Write-Host "Log path does not exist: $LogPath"; exit 1 }

# Ensure files exist
if (-not (Test-Path $debug)) { New-Item -ItemType File -Path $debug | Out-Null }
if (-not (Test-Path $plugin)) { New-Item -ItemType File -Path $plugin | Out-Null }

# Choose identity
if ([string]::IsNullOrEmpty($AppPoolIdentity)) {
    $identity = 'IIS_IUSRS'
} else {
    $identity = $AppPoolIdentity
}

Write-Host "Granting Modify rights to $identity on $debug and $plugin"

$acl = Get-Acl $debug
$rule = New-Object System.Security.AccessControl.FileSystemAccessRule($identity,'Modify','ContainerInherit,ObjectInherit','None','Allow')
$acl.SetAccessRule($rule)
Set-Acl -Path $debug -AclObject $acl

$acl2 = Get-Acl $plugin
$acl2.SetAccessRule($rule)
Set-Acl -Path $plugin -AclObject $acl2

Write-Host "Done. Current ACLs:"
Get-Acl $debug | Format-List
Get-Acl $plugin | Format-List
