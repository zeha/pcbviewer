#!/bin/bash
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TARGET_FILE="$SCRIPT_DIR/htdocs/kicanvas.js"
TEMP_FILE="$SCRIPT_DIR/.kicanvas.js.tmp"
LOG_FILE="$SCRIPT_DIR/update-kicanvas.log"
TIMEOUT_SECONDS=60

KICANVAS_URL="https://kicanvas.org/kicanvas/kicanvas.js"

# Parse arguments
VERBOSE=0
if [ "$1" = "-v" ] || [ "$1" = "--verbose" ]; then
    VERBOSE=1
fi

# Timestamp for logging
timestamp() {
    date '+%Y-%m-%d %H:%M:%S'
}

# Log function - always to file, to stdout only if verbose
log() {
    echo "[$(timestamp)] $*" >> "$LOG_FILE"
    if [ $VERBOSE -eq 1 ]; then
        echo "[$(timestamp)] $*"
    fi
}

# Error log - always to both file and stdout
log_error() {
    echo "[$(timestamp)] $*" | tee -a "$LOG_FILE" >&2
}

# Start update
log "=== Starting kicanvas.js update ==="
log "Download URL: $KICANVAS_URL"

# Remove any existing temp file
rm -f "$TEMP_FILE"

# Download to temp file with timeout
log "Downloading kicanvas.js..."
if timeout "$TIMEOUT_SECONDS" curl -fsSL "$KICANVAS_URL" -o "$TEMP_FILE" 2>&1 | tee -a "$LOG_FILE" >/dev/null; then
    # Check if download succeeded and file is not empty
    if [ ! -f "$TEMP_FILE" ]; then
        log_error "✗ Download failed: temp file not created"
        exit 1
    fi

    # Check file size (should be at least 10KB for a valid JS file)
    file_size=$(stat -f%z "$TEMP_FILE" 2>/dev/null || stat -c%s "$TEMP_FILE" 2>/dev/null)
    if [ -z "$file_size" ] || [ "$file_size" -lt 10240 ]; then
        log_error "✗ Download failed: file too small ($file_size bytes)"
        rm -f "$TEMP_FILE"
        exit 1
    fi

    # Basic validation: check if file starts with JavaScript-like content
    first_chars=$(head -c 100 "$TEMP_FILE")
    if ! echo "$first_chars" | grep -qE '(var |let |const |function |class |\(function|\/\*|\!)'; then
        log_error "✗ Downloaded file doesn't look like JavaScript"
        rm -f "$TEMP_FILE"
        exit 1
    fi

    # Create backup of existing file if it exists
    if [ -f "$TARGET_FILE" ]; then
        cp "$TARGET_FILE" "${TARGET_FILE}.backup"
        log "Created backup: ${TARGET_FILE}.backup"
    fi

    # Replace old file with new file
    mv "$TEMP_FILE" "$TARGET_FILE"

    log "✓ Successfully updated kicanvas.js ($file_size bytes)"
    log "=== Update complete ==="

    exit 0
else
    exit_code=$?
    if [ $exit_code -eq 124 ]; then
        log_error "✗ Download timeout (exceeded ${TIMEOUT_SECONDS}s)"
    else
        log_error "✗ Download failed (exit code: $exit_code)"
    fi

    # Clean up temp file
    rm -f "$TEMP_FILE"

    log_error "KiCanvas update failed!"
    log_error "See $LOG_FILE for details"
    exit 1
fi
