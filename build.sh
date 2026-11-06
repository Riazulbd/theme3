#!/usr/bin/env bash
# Builds ONE uploadable zip: dist/narayanganj-news.zip (replaces the old one).
# The zip's top folder is always "narayanganj-news", so WordPress treats an
# upload as an update of the same theme, never as a second theme.
set -euo pipefail
cd "$(dirname "$0")"
SLUG="narayanganj-news"
VER=$(sed -n 's/^Version:[[:space:]]*//p' style.css | head -1)
# Every build raises the patch number, so WordPress always sees an update.
# Use ./build.sh --no-bump to rebuild the same version.
if [ "${1:-}" != "--no-bump" ]; then
  NEW="${VER%.*}.$(( ${VER##*.} + 1 ))"
  sed -i.bak "s/^Version:.*/Version:           $NEW/" style.css && rm -f style.css.bak
  sed -i.bak "s/define( 'NN_VERSION', '[^']*' );/define( 'NN_VERSION', '$NEW' );/" functions.php && rm -f functions.php.bak
  VER="$NEW"
fi
STAGE=$(mktemp -d)
trap 'rm -rf "$STAGE"' EXIT
mkdir -p "$STAGE/$SLUG" dist
rsync -a --exclude 'dist' --exclude 'build.sh' --exclude 'index.html' --exclude '.DS_Store' --exclude '*.zip' --exclude '.git' ./ "$STAGE/$SLUG/"
for f in $(find "$STAGE/$SLUG" -name '*.php'); do
  if command -v php >/dev/null; then php -l "$f" >/dev/null; fi
done
rm -f "dist/$SLUG.zip"
(cd "$STAGE" && zip -qrX "$OLDPWD/dist/$SLUG.zip" "$SLUG")
echo "Built dist/$SLUG.zip (version $VER)"
