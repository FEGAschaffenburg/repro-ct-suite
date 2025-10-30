# Release & Permissions helper scripts

This folder contains small helper scripts useful when creating releases and ensuring the plugin's debug logs are writable.

Files

- `create-release-zip.ps1` - PowerShell script to build a release ZIP on Windows. Usage: `.uild\create-release-zip.ps1 -Version 0.3.8.0`
- `create-release-zip.sh` - POSIX shell script for Linux/macOS to build a release ZIP. Usage: `./create-release-zip.sh 0.3.8.0`
- `set-log-perms.sh` - Linux helper to create `debug.log` and `repro-ct-suite-debug.log` and set owner/permissions (uses `chown`/`chmod`). Run with root (sudo): `sudo ./set-log-perms.sh /path/to/wp-content www-data`
- `set-log-perms.ps1` - PowerShell helper to set ACLs for IIS AppPool identity or `IIS_IUSRS` on Windows. Run as Administrator: `.	ools\set-log-perms.ps1 -LogPath 'C:\inetpub\wwwroot\wp-content' -AppPoolIdentity 'IIS AppPool\DefaultAppPool'`

Notes

- The release ZIP scripts ensure that archive entries use forward-slashes (`/`) which is required for WordPress plugin uploads.
- The permission scripts will try to create the log files if they don't exist and set appropriate ownership/ACLs so PHP (webserver) can write to them.

Security

- Avoid using overly permissive permissions (e.g. 777). Use appropriate owner/group (the webserver/PHP user) and restrictive mode (660/644 as needed).

If you want, I can wire the release scripts into your CI (GitHub Actions) so releases are produced automatically with correct ZIP format.