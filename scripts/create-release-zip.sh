#!/usr/bin/env bash
# Create release ZIP (POSIX shell)
# Usage: ./create-release-zip.sh 0.3.8.0
set -euo pipefail
VERSION="$1"
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DIST_ROOT="/tmp/repro-ct-suite-dist-$VERSION"
DIST_TOP="$DIST_ROOT/repro-ct-suite"
OUT_ZIP="$REPO_ROOT/repro-ct-suite-$VERSION-fs.zip"

echo "Repo: $REPO_ROOT"
echo "Dist: $DIST_TOP"
echo "Output: $OUT_ZIP"

rm -rf "$DIST_ROOT"
mkdir -p "$DIST_TOP"

# Copy everything except .git and zip files
shopt -s dotglob
for f in "$REPO_ROOT"/*; do
  name=$(basename "$f")
  if [ "$name" = ".git" ] || [ "$name" = "repro-ct-suite-$VERSION-fs.zip" ]; then
    continue
  fi
  cp -a "$f" "$DIST_TOP/"
done

# Create zip using zip -r (most unix zips will use forward slashes)
if [ -f "$OUT_ZIP" ]; then rm -f "$OUT_ZIP"; fi
( cd "$DIST_ROOT" && zip -r "$OUT_ZIP" repro-ct-suite )

# Verify entries: if any entry contains backslash, rebuild using python
if python3 - <<'PY'
import sys, zipfile
z='''$OUT_ZIP'''
with zipfile.ZipFile(z,'r') as zf:
    for n in zf.namelist():
        if '\\' in n:
            sys.exit(1)
print(0)
PY
then
  echo "ZIP entries ok"
else
  echo "Found backslashes in zip entries, rebuilding with normalized names..."
  TMP_FIXED="/tmp/repro-ct-suite-$VERSION-fixed.zip"
  python3 - <<'PY'
import zipfile,sys
src='''$OUT_ZIP'''
dst='''$TMP_FIXED'''
with zipfile.ZipFile(src,'r') as s, zipfile.ZipFile(dst,'w',compression=zipfile.ZIP_DEFLATED) as d:
    for e in s.infolist():
        name=e.filename.replace('\\','/')
        if name.endswith('/'):
            d.writestr(name,'')
        else:
            d.writestr(name,s.read(e.filename))
print('rebuilt')
PY
  mv "$TMP_FIXED" "$OUT_ZIP"
fi

# Cleanup
rm -rf "$DIST_ROOT"

echo "Created $OUT_ZIP"
exit 0