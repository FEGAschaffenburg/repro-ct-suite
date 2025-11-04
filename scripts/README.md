# Release & Permissions helper scripts# Release & Permissions helper scripts



This folder contains helper scripts for creating WordPress-compatible releases and managing debug log permissions.This folder contains small helper scripts useful when creating releases and ensuring the plugin's debug logs are writable.



## FilesFiles



### Release Creation- `create-release-zip.ps1` - PowerShell script to build a release ZIP on Windows. Usage: `.uild\create-release-zip.ps1 -Version 0.3.8.0`

- `create-wp-zip-simple.ps1` - **MAIN SCRIPT** - PowerShell script to build WordPress-compatible release ZIPs. - `create-release-zip.sh` - POSIX shell script for Linux/macOS to build a release ZIP. Usage: `./create-release-zip.sh 0.3.8.0`

  - Usage: `.\scripts\create-wp-zip-simple.ps1 -Version "0.4.2.1"`- `set-log-perms.sh` - Linux helper to create `debug.log` and `repro-ct-suite-debug.log` and set owner/permissions (uses `chown`/`chmod`). Run with root (sudo): `sudo ./set-log-perms.sh /path/to/wp-content www-data`

  - Output: `repro-ct-suite.zip` (ready for WordPress installation)- `set-log-perms.ps1` - PowerShell helper to set ACLs for IIS AppPool identity or `IIS_IUSRS` on Windows. Run as Administrator: `.	ools\set-log-perms.ps1 -LogPath 'C:\inetpub\wwwroot\wp-content' -AppPoolIdentity 'IIS AppPool\DefaultAppPool'`

  - Features: Automatic forward-slash normalization, WordPress structure validation

Notes

### Permission Management  

- `set-log-perms.sh` - Linux helper for debug log permissions- The release ZIP scripts ensure that archive entries use forward-slashes (`/`) which is required for WordPress plugin uploads.

  - Usage: `sudo ./set-log-perms.sh /path/to/wp-content www-data`- The permission scripts will try to create the log files if they don't exist and set appropriate ownership/ACLs so PHP (webserver) can write to them.

- `set-log-perms.ps1` - Windows helper for IIS AppPool permissions

  - Usage: `.\set-log-perms.ps1 -LogPath 'C:\inetpub\wwwroot\wp-content' -AppPoolIdentity 'IIS AppPool\DefaultAppPool'`Security



## WordPress Compatibility- Avoid using overly permissive permissions (e.g. 777). Use appropriate owner/group (the webserver/PHP user) and restrictive mode (660/644 as needed).



The release ZIP script ensures:If you want, I can wire the release scripts into your CI (GitHub Actions) so releases are produced automatically with correct ZIP format.
- ✅ **Correct structure**: `repro-ct-suite/` folder in ZIP root
- ✅ **Forward slashes**: All paths use `/` (required by WordPress)
- ✅ **Plugin detection**: `repro-ct-suite/repro-ct-suite.php` properly placed
- ✅ **Installation ready**: Direct upload to WordPress Plugins

## Usage for Releases

```powershell
# 1. Update version in repro-ct-suite.php
# 2. Create WordPress-compatible ZIP
.\scripts\create-wp-zip-simple.ps1 -Version "X.Y.Z.W"

# 3. Create GitHub release
gh release create vX.Y.Z.W "repro-ct-suite.zip" --title "Version X.Y.Z.W" --notes "Release notes..."
```

## Security Notes

- Use appropriate webserver permissions (660/644, not 777)
- Set correct owner/group for PHP write access
- Restrict debug log access to webserver only