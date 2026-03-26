#!/usr/bin/env bash
set -euo pipefail

OUTPUT_NAME="${1:-mzsmartlife-platform.zip}"
ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
OUTPUT_PATH="${ROOT_DIR}/${OUTPUT_NAME}"

rm -f "$OUTPUT_PATH"

cd "$ROOT_DIR"
zip -r "$OUTPUT_PATH" . \
  -x ".git/*" \
  -x "*.zip" \
  -x "cache/*.html" \
  -x "cache/install.lock" \
  -x "uploads/*"

echo "Created: $OUTPUT_PATH"
