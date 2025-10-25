# PCB Viewer

A web-based viewer for KiCad PCB and schematic files using KiCanvas.

## Directory Structure

```
pcbviewer/
├── htdocs/                  # Public files (serve this directory over HTTP)
│   ├── index.php           # Main viewer application
│   ├── kicanvas.js         # KiCanvas library
│   ├── fonts/              # Inter font files
│   └── projects/           # KiCad project repositories
│
├── update-repos.sh         # Script to update git repositories
├── update-kicanvas.sh      # Script to update kicanvas.js
├── CRON-SETUP.md          # Documentation for automated updates
├── *.log                   # Log files from update scripts
└── README.md              # This file
```

## Setup

### Web Server Configuration

Point your web server's document root to the `htdocs/` directory.

Example for Apache:
```apache
DocumentRoot "/Users/ch/Source/pcbviewer/htdocs"
```

Example for nginx:
```nginx
root /Users/ch/Source/pcbviewer/htdocs;
index index.php;
```

### Automated Updates

See [CRON-SETUP.md](CRON-SETUP.md) for setting up automated updates via cron.

## Features

- Browse multiple KiCad projects organized by category
- View PCB boards and schematics inline
- Theme support (KiCad, Witch Hazel)
- Anchor links to specific project categories
- MNT Reform aesthetic with Inter font
- Automated git repository updates
- Safe kicanvas.js updates

## Projects

Projects are stored in `htdocs/projects/` as git repositories. Add new projects by:

1. Cloning repositories into `htdocs/projects/`
2. Adding them to the `$topLevelProjects` array in `index.php`

The viewer will automatically discover all `.kicad_pro`, `.kicad_sch`, and `.kicad_pcb` files.
