#!/bin/bash

# Git repository update script for cron
# Updates all git repositories in the projects/ directory
# Safe for automated execution - no prompts, has timeouts
# Silent on success (no cron emails), only outputs on errors

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECTS_DIR="$SCRIPT_DIR/htdocs/projects"
TIMEOUT_SECONDS=300  # 5 minutes per repo
LOG_FILE="$SCRIPT_DIR/update-repos.log"

# Parse arguments
VERBOSE=0
if [ "$1" = "-v" ] || [ "$1" = "--verbose" ]; then
    VERBOSE=1
fi

# Timestamp for logging
timestamp() {
    date '+%Y-%m-%d %H:%M:%S'
}

# Log function - always to file, to stdout only if verbose or error
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
log "=== Starting repository update ==="

# Check if projects directory exists
if [ ! -d "$PROJECTS_DIR" ]; then
    log_error "ERROR: Projects directory not found: $PROJECTS_DIR"
    exit 1
fi

# Find all git repositories
found_repos=0
updated_repos=0
failed_repos=0

# Iterate through top-level directories in projects/
for repo in "$PROJECTS_DIR"/*; do
    # Skip if not a directory
    [ ! -d "$repo" ] && continue

    # Check if it's a git repository
    if [ ! -d "$repo/.git" ]; then
        continue
    fi

    found_repos=$((found_repos + 1))
    repo_name=$(basename "$repo")
    log "Updating $repo_name..."

    # Change to repo directory and update
    if cd "$repo" 2>/dev/null; then
        # Use timeout and git flags to avoid hanging/prompting
        # Capture output to check for errors
        git_output=$(timeout "$TIMEOUT_SECONDS" git pull \
            --no-edit \
            --no-rebase \
            --no-stat \
            --quiet \
            2>&1)
        exit_code=$?

        if [ $exit_code -eq 0 ]; then
            log "✓ Successfully updated $repo_name"
            updated_repos=$((updated_repos + 1))
        else
            if [ $exit_code -eq 124 ]; then
                log_error "✗ Timeout updating $repo_name (exceeded ${TIMEOUT_SECONDS}s)"
            else
                log_error "✗ Failed to update $repo_name (exit code: $exit_code)"
                if [ -n "$git_output" ]; then
                    log_error "  Output: $git_output"
                fi
            fi
            failed_repos=$((failed_repos + 1))
        fi

        # Return to base directory
        cd "$SCRIPT_DIR" || exit 1
    else
        log_error "✗ Could not access $repo_name"
        failed_repos=$((failed_repos + 1))
    fi
done

# Summary
log "=== Update complete ==="
log "Found: $found_repos repos"
log "Updated: $updated_repos repos"
log "Failed: $failed_repos repos"
log ""

# If there were failures, output summary to stderr for cron email
if [ $failed_repos -gt 0 ]; then
    echo "Repository update failed!" >&2
    echo "Failed: $failed_repos out of $found_repos repos" >&2
    echo "See $LOG_FILE for details" >&2
    exit 1
fi

exit 0
