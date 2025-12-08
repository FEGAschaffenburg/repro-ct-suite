#!/bin/bash
#
# Create WordPress-compatible release ZIP for GitHub releases
# Usage: ./scripts/create-release-zip.sh VERSION
#
# Example: ./scripts/create-release-zip.sh 0.9.5.1
#
# This script:
# - Creates a ZIP with proper WordPress plugin structure (repro-ct-suite/ folder)
# - Uses forward slashes for all paths (WordPress requirement)
# - Excludes development files (.git, .gitignore, etc.)
# - Outputs: repro-ct-suite-VERSION-fs.zip

set -e  # Exit on error

VERSION="$1"
if [ -z "$VERSION" ]; then
    echo "ERROR: Version argument required"
    echo "Usage: $0 VERSION"
    echo "Example: $0 0.9.5.1"
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
TEMP_DIR="/tmp/repro-ct-suite-wp-$VERSION"
PLUGIN_DIR="$TEMP_DIR/repro-ct-suite"
OUTPUT_ZIP="$REPO_ROOT/repro-ct-suite-$VERSION-fs.zip"

echo "=== WordPress ZIP Creator ==="
echo "Version: $VERSION"
echo "Output: $OUTPUT_ZIP"
echo ""

# Cleanup
echo "Cleaning up old files..."
rm -rf "$TEMP_DIR"
rm -f "$OUTPUT_ZIP"

# Create temp directory structure
echo "Creating temporary directory..."
mkdir -p "$PLUGIN_DIR"

# Copy files (exclude development files)
echo "Copying plugin files..."
cd "$REPO_ROOT"

# Use rsync if available, otherwise use cp
if command -v rsync >/dev/null 2>&1; then
    rsync -a \
        --exclude='.git' \
        --exclude='.gitignore' \
        --exclude='repro-ct-suite*.zip' \
        --exclude='*.log' \
        --exclude='.github' \
        --exclude='node_modules' \
        --exclude='.DS_Store' \
        . "$PLUGIN_DIR/"
else
    # Fallback to cp with find
    find . -type f \
        ! -path '*/.git/*' \
        ! -name '.gitignore' \
        ! -name 'repro-ct-suite*.zip' \
        ! -name '*.log' \
        ! -path '*/.github/*' \
        ! -path '*/node_modules/*' \
        ! -name '.DS_Store' \
        -exec cp --parents {} "$PLUGIN_DIR/" \;
fi

# Create ZIP with proper structure
echo "Creating ZIP archive..."
cd "$TEMP_DIR"

# Use zip command with proper options
# -r: recursive
# -q: quiet
# -9: maximum compression
zip -r -q -9 "$OUTPUT_ZIP" repro-ct-suite/

# Validate the ZIP structure
echo ""
echo "Validating ZIP structure..."
if command -v unzip >/dev/null 2>&1; then
    echo "First 5 entries:"
    unzip -l "$OUTPUT_ZIP" | head -n 8 | tail -n 5
    
    # Check for main plugin file
    if unzip -l "$OUTPUT_ZIP" | grep -q "repro-ct-suite/repro-ct-suite.php"; then
        echo ""
        echo "✓ SUCCESS: WordPress structure OK"
        echo "✓ Found: repro-ct-suite/repro-ct-suite.php"
    else
        echo ""
        echo "✗ ERROR: repro-ct-suite/repro-ct-suite.php not found!"
        exit 1
    fi
fi

# Cleanup temporary directory
echo ""
echo "Cleaning up temporary files..."
rm -rf "$TEMP_DIR"

# Get file size
ZIP_SIZE_KB=$(du -k "$OUTPUT_ZIP" | cut -f1)
ZIP_SIZE_MB=$(echo "scale=2; $ZIP_SIZE_KB / 1024" | bc)
ENTRY_COUNT=$(unzip -l "$OUTPUT_ZIP" 2>/dev/null | tail -n 1 | awk '{print $2}' || echo "unknown")

# Result
echo ""
echo "========================================="
echo "✓ DONE!"
echo "========================================="
echo "File: $OUTPUT_ZIP"
echo "Size: ${ZIP_SIZE_MB} MB"
echo "Entries: $ENTRY_COUNT"
echo ""
echo "Ready for GitHub release upload!"
