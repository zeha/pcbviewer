# Cron Setup for Updates

This document covers automated updates for both git repositories and kicanvas.js.

## Repository Updates

To update repositories every day at 3 AM:

```bash
crontab -e
```

Add this line (replace `/path/to/pcbviewer` with your actual path):
```
0 3 * * * /path/to/pcbviewer/update-repos.sh
```

## KiCanvas.js Updates

To update kicanvas.js weekly on Sundays at 4 AM:

```bash
crontab -e
```

Add this line (replace `/path/to/pcbviewer` with your actual path):
```
0 4 * * 0 /path/to/pcbviewer/update-kicanvas.sh
```

**Note:** Both scripts are silent on success, so you'll only get emails if updates fail!

## Other Schedule Examples

Every 6 hours:
```
0 */6 * * * /path/to/pcbviewer/update-repos.sh
```

Every day at midnight:
```
0 0 * * * /path/to/pcbviewer/update-repos.sh
```

Every Monday at 9 AM:
```
0 9 * * 1 /path/to/pcbviewer/update-repos.sh
```

## Features

### Repository Updates

- **Silent on success**: No output when all updates succeed (no cron emails!)
- **Errors only mode**: Only sends cron email if updates fail
- **Timeout protection**: Each repo update times out after 5 minutes
- **No prompts**: Uses `--no-edit` and `--quiet` flags
- **Logging**: All activity logged to update-repos.log with timestamps
- **Error handling**: Continues even if one repo fails
- **Non-interactive**: Safe for cron execution
- **Verbose mode**: Use `-v` flag for manual testing to see all output

### KiCanvas.js Updates

- **Safe download**: Never destroys existing file if download fails
- **Atomic replacement**: Downloads to temp file first, validates, then replaces
- **Automatic backup**: Creates .backup file before replacing
- **File validation**: Checks file size and content before accepting download
- **Silent on success**: No output when update succeeds (no cron emails!)
- **Errors only mode**: Only sends cron email if download fails
- **Timeout protection**: 60 second download timeout
- **Configurable URL**: Set via environment variable or edit script
- **Verbose mode**: Use `-v` flag for manual testing

## Configuration

### Repository Updates

Edit `update-repos.sh` to change:
- `TIMEOUT_SECONDS`: Default 300 (5 minutes)
- `LOG_FILE`: Default ./update-repos.log

### KiCanvas.js Updates

Edit `update-kicanvas.sh` to change:
- `KICANVAS_URL`: Download URL for kicanvas.js (default: unpkg.com)
- `TIMEOUT_SECONDS`: Default 60 (1 minute)
- `LOG_FILE`: Default ./update-kicanvas.log

You can also set the URL via environment variable:
```bash
KICANVAS_URL=https://cdn.example.com/kicanvas.js ./update-kicanvas.sh
```

## Testing the Scripts

### Test Repository Updates

Verbose mode (shows all output):
```bash
cd /path/to/pcbviewer
./update-repos.sh -v
```

Silent mode (like cron):
```bash
./update-repos.sh
```

### Test KiCanvas.js Updates

Verbose mode:
```bash
cd /path/to/pcbviewer
./update-kicanvas.sh -v
```

Silent mode (like cron):
```bash
./update-kicanvas.sh
```

Check the logs:
```bash
tail -f /path/to/pcbviewer/update-repos.log
tail -f /path/to/pcbviewer/update-kicanvas.log
```
