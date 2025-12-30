# Release & Permissions Helper Scripts

This folder contains helper scripts for creating WordPress-compatible releases and managing debug log permissions.

## Files

### Release Creation

- **`create-wp-zip-simple.ps1`** - PowerShell script to build WordPress-compatible release ZIPs on Windows
  - Usage: `.\scripts\create-wp-zip-simple.ps1 -Version "0.9.5.1"`
  - Output: `repro-ct-suite.zip` (ready for WordPress installation)
  - Features: Automatic forward-slash normalization, WordPress structure validation

- **`create-release-zip.sh`** - POSIX shell script for Linux/macOS/CI to build release ZIPs
  - Usage: `./scripts/create-release-zip.sh 0.9.5.1`
  - Output: `repro-ct-suite-VERSION-fs.zip` (ready for GitHub release)
  - Used by GitHub Actions workflow for automated releases
  - Features: WordPress structure validation, forward-slash paths

### Permission Management

- **`set-log-perms.sh`** - Linux helper for debug log permissions
  - Usage: `sudo ./set-log-perms.sh /path/to/wp-content www-data`
  - Creates debug log files and sets proper ownership/permissions

- **`set-log-perms.ps1`** - Windows helper for IIS AppPool permissions
  - Usage: `.\set-log-perms.ps1 -LogPath 'C:\inetpub\wwwroot\wp-content' -AppPoolIdentity 'IIS AppPool\DefaultAppPool'`
  - Sets ACLs for IIS AppPool identity or IIS_IUSRS

## WordPress Compatibility

The release ZIP scripts ensure:
- ✅ **Correct structure**: `repro-ct-suite/` folder in ZIP root
- ✅ **Forward slashes**: All paths use `/` (required by WordPress)
- ✅ **Plugin detection**: `repro-ct-suite/repro-ct-suite.php` properly placed
- ✅ **Installation ready**: Direct upload to WordPress Plugins

## Usage for Releases

### Manual Release (PowerShell)

```powershell
# 1. Update version in repro-ct-suite.php
# 2. Create WordPress-compatible ZIP
.\scripts\create-wp-zip-simple.ps1 -Version "X.Y.Z"

# 3. Create GitHub release
gh release create vX.Y.Z "repro-ct-suite.zip" --title "Version X.Y.Z" --notes "Release notes..."
```

### Automated Release (GitHub Actions)

The plugin now supports automated releases via GitHub Actions:

```bash
# 1. Update version in repro-ct-suite.php
# 2. Create and push a version tag
git tag v0.9.5.1
git push origin v0.9.5.1

# GitHub Actions will automatically:
# - Build the ZIP using create-release-zip.sh
# - Create a GitHub release
# - Upload the ZIP as a release asset
```

The workflow supports both 3-part (v1.2.3) and 4-part (v1.2.3.4) version tags.

### Manual Workflow Dispatch

You can also trigger the release workflow manually from GitHub:

1. Go to **Actions** → **Build and publish release**
2. Click **Run workflow**
3. Select the branch (usually `main`)
4. Click **Run workflow**

The workflow will use the current branch/tag name for the release.

## Security Notes

- Avoid using overly permissive permissions (e.g., 777)
- Use appropriate owner/group for the webserver/PHP user
- Restrict debug log access to webserver only
- Set permissions to 660/644 as needed
