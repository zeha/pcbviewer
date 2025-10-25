# Projects Directory

This directory contains KiCad project repositories that are displayed by the PCB viewer.

## Adding Projects

Clone KiCad project repositories into this directory:

```bash
cd htdocs/projects
git clone https://source.mnt.re/reform/pocket-reform.git
```

## Configuration

After adding a project repository, add it to the `$topLevelProjects` array in `../index.php`:

```php
$topLevelProjects = [
    'pocket-reform' => 'MNT Pocket Reform - Portable modular computer',
    'your-project' => 'Your Project Description'
];
```

## Notes

- Project repositories are excluded from this repository via `.gitignore`
- Each project should contain `.kicad_pro`, `.kicad_pcb`, and `.kicad_sch` files
- The viewer automatically discovers all KiCad files in subdirectories
