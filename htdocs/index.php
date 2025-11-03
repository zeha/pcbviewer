<style>
@import url('fonts/inter.css');

body {
    margin: 0;
    background: #000;
    color: #fff;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-feature-settings: 'cv08' 1, 'cv11' 1;
}

body.viewer-mode {
    height: 100vh;
    overflow: hidden;
}

#sources {
    padding: 12px 20px;
    border-bottom: 1px solid #333;
    background: #0a0a0a;
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 50px;
    box-sizing: border-box;
}

kicanvas-embed {
    height: calc(100vh - 50px);
    display: block;
}

#sources .links-left,
#sources .links-right {
    display: flex;
    gap: 15px;
    align-items: center;
}

#sources a {
    color: #b794f6;
    text-decoration: none;
    padding: 6px 10px;
    border: 1px solid transparent;
    display: inline-block;
    transition: all 0.2s ease;
    font-size: 14px;
}

#sources a:hover {
    border-color: #b794f6;
    background: rgba(183, 148, 246, 0.1);
}

#file-selector,
#theme-selector {
    background: #111;
    color: #b794f6;
    border: 1px solid #333;
    padding: 6px 10px;
    font-size: 14px;
    font-family: inherit;
    cursor: pointer;
    border-radius: 0;
    outline: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12"><path fill="%23b794f6" d="M6 9L1 4h10z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 8px center;
    padding-right: 28px;
}

#theme-selector {
    display: none; /* Hidden until kicanvas upstream is ready */
}

#file-selector:hover,
#theme-selector:hover {
    border-color: #b794f6;
    background-color: #1a1a1a;
}

#file-selector:focus,
#theme-selector:focus {
    border-color: #b794f6;
    box-shadow: 0 0 0 2px rgba(183, 148, 246, 0.1);
}

#file-selector option,
#theme-selector option {
    background: #111;
    color: #fff;
}

#project-list {
    max-width: 900px;
    margin: 0 auto;
    padding: 60px 40px;
}

#project-list h1 {
    color: #fff;
    font-weight: 700;
    font-size: 42px;
    letter-spacing: -0.02em;
    margin-bottom: 40px;
}

#project-list > ul {
    list-style: none;
    padding: 0;
}

#project-list > ul > li {
    margin-bottom: 50px;
}

#project-list ul ul {
    list-style: none;
    padding-left: 0;
    margin-top: 20px;
}

#project-list ul ul li {
    margin: 12px 0;
}

#project-list a {
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    display: block;
    padding: 16px 24px;
    background: #111;
    border: 1px solid #222;
    border-left: 3px solid #b794f6;
    transition: all 0.2s ease;
}

#project-list a:hover {
    background: #1a1a1a;
    border-left-color: #d4bbff;
    border-color: #333;
    transform: translateX(4px);
}

#project-list strong {
    color: #b794f6;
    font-size: 20px;
    font-weight: 600;
    letter-spacing: -0.01em;
}

#project-list li:target > strong {
    color: #d4bbff;
}

#project-list li:target {
    scroll-margin-top: 20px;
}
</style>
<script type="module" src="kicanvas.js"></script>
<?php
// Define toplevel projects with descriptions
$topLevelProjects = [
    'desktop-reform' => 'MNT Desktop Reform',
    'mnt-halo-90' => 'MNT HALO-90',
    'reform' => 'MNT Reform 2',
    'reform-next' => 'MNT Reform Next',
    'pocket-reform' => 'MNT Pocket Reform',
    'mnt-reform-layerscape-ls1028a-som' => 'LS1028A System-on-Module',
    'mnt-reform-rk3588-som' => 'RCORE RK3588 System-on-Module',
    'mnt-reform-raspberry-pi-cm4-som' => 'RCM4 RPi CM4 System-on-Module',
    'reform-kintex-som' => 'RKX7 Kintex-7 FPGA System-on-Module',
    'reform-qcs6490' => 'Quisar QCS6490 System-on-Module',
];

function findKicadProFiles($dir, $baseDir = '') {
    $results = [];
    if (!is_dir($dir)) {
        return $results;
    }

    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $fullPath = $dir . '/' . $item;
        $relativePath = $baseDir ? $baseDir . '/' . $item : $item;

        if (is_dir($fullPath)) {
            $results = array_merge($results, findKicadProFiles($fullPath, $relativePath));
        } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'kicad_pro') {
            $results[] = $relativePath;
        }
    }

    return $results;
}

$projects = [];
$projectDescriptions = [];

foreach ($topLevelProjects as $topLevel => $description) {
    $topLevelPath = 'projects/' . $topLevel;
    if (is_dir($topLevelPath)) {
        $foundFiles = findKicadProFiles($topLevelPath, $topLevel);
        foreach ($foundFiles as $projectPath) {
            $projects[] = $projectPath;
            $projectDescriptions[$projectPath] = $description;
        }
    }
}

// Get selected project from URL parameter
$selectedProject = $_GET['project'] ?? null;
$isViewerMode = $selectedProject !== null && in_array($selectedProject, $projects);

// Validate theme parameter (allowlist only)
$allowedThemes = ['kicad', 'witchhazel'];
$selectedTheme = 'kicad'; // default
if (isset($_GET['theme']) && in_array($_GET['theme'], $allowedThemes, true)) {
    $selectedTheme = $_GET['theme'];
}
?>
<body<?= $isViewerMode ? ' class="viewer-mode"' : '' ?>>
<?php
// Function to get abbreviated project name
function getAbbreviatedName($projectPath) {
    $name = pathinfo($projectPath, PATHINFO_FILENAME);
    // Remove "pocket-reform-" prefix for cleaner display
    $name = str_replace('pocket-reform-', '', $name);
    return $name;
}

// If no project is selected, show project list
if ($selectedProject === null || !in_array($selectedProject, $projects)) {
?>
    <div id="project-list">
        <h1>Select a Project</h1>
        <ul>
<?php
$currentTopLevel = null;
foreach ($projects as $project):
    $topLevel = explode('/', $project)[0];
    if ($topLevel !== $currentTopLevel) {
        if ($currentTopLevel !== null) echo "</ul></li>";
        $currentTopLevel = $topLevel;
        echo "<li style='margin-bottom: 20px;' id='" . htmlspecialchars($topLevel) . "'><strong>" . htmlspecialchars($projectDescriptions[$project]) . "</strong><ul style='margin-top: 10px;'>";
    }
?>
            <li><a href="?project=<?= urlencode($project) ?>"><?= htmlspecialchars(getAbbreviatedName($project)) ?></a></li>
<?php endforeach;
if ($currentTopLevel !== null) echo "</ul></li>";
?>
        </ul>
    </div>
<?php
} else {
    // If a project is selected, find all related files
    $allFiles = [];
    $projectDir = 'projects/' . dirname($selectedProject);
    $projectName = pathinfo($selectedProject, PATHINFO_FILENAME);

    // Find all kicad files in the project directory
    if (is_dir($projectDir)) {
        $files = scandir($projectDir);
        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array($ext, ['kicad_pro', 'kicad_pcb', 'kicad_sch'])) {
                $allFiles[] = $projectDir . '/' . $file;
            }
        }
    }

    // Sort files: kicad_pro first, then kicad_sch (alphabetically), then kicad_pcb
    usort($allFiles, function($a, $b) {
        $extA = pathinfo($a, PATHINFO_EXTENSION);
        $extB = pathinfo($b, PATHINFO_EXTENSION);

        // Define order priority
        $priority = ['kicad_pro' => 0, 'kicad_sch' => 1, 'kicad_pcb' => 2];
        $prioA = $priority[$extA] ?? 999;
        $prioB = $priority[$extB] ?? 999;

        // If different types, sort by priority
        if ($prioA !== $prioB) {
            return $prioA - $prioB;
        }

        // Same type, sort alphabetically
        return strcmp($a, $b);
    });

    // Validate and handle file selection parameter (allowlist only)
    $selectedFile = null;
    if (isset($_GET['file']) && $_GET['file'] !== 'all') {
        // Check if the requested file is in our list
        $requestedFile = $_GET['file'];
        foreach ($allFiles as $file) {
            if (basename($file) === $requestedFile) {
                $selectedFile = $requestedFile;
                break;
            }
        }
    }

    // Filter files based on selection
    $filesToLoad = [];
    if ($selectedFile === null) {
        // Show all files
        $filesToLoad = $allFiles;
    } else {
        // Show only .kicad_pro and the selected file
        foreach ($allFiles as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $basename = basename($file);
            if ($ext === 'kicad_pro' || $basename === $selectedFile) {
                $filesToLoad[] = $file;
            }
        }
    }

    // Build list of selectable files (non-pro files)
    $selectableFiles = [];
    foreach ($allFiles as $file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if ($ext !== 'kicad_pro') {
            $selectableFiles[] = basename($file);
        }
    }
?>
    <div id="sources">
        <div class="links-left">
            <a href="./">← Back to Project List</a>
            <select id="file-selector">
                <option value="all"<?= $selectedFile === null ? ' selected' : '' ?>>All Files</option>
<?php foreach ($selectableFiles as $file): ?>
                <option value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>"<?= $selectedFile === $file ? ' selected' : '' ?>><?= htmlspecialchars($file) ?></option>
<?php endforeach; ?>
            </select>
        </div>
        <div class="links-right">
            <select id="theme-selector">
                <option value="kicad"<?= $selectedTheme === 'kicad' ? ' selected' : '' ?>>KiCad Theme</option>
                <option value="witchhazel"<?= $selectedTheme === 'witchhazel' ? ' selected' : '' ?>>Witch Hazel Theme</option>
            </select>
            <a href="https://mntre.com" target="_blank">MNT</a>
            <a href="https://source.mnt.re" target="_blank">Sources</a>
            <a href="https://kicanvas.org" target="_blank">Kicanvas ♥</a>
        </div>
    </div>
<kicanvas-embed
    id="viewer"
    controls="full"
    controlslist="nodownload nooverlay"
    theme="<?= htmlspecialchars($selectedTheme, ENT_QUOTES, 'UTF-8') ?>"
    >
<?php foreach ($filesToLoad as $file): ?>
    <kicanvas-source src="./<?= htmlspecialchars($file) ?>"></kicanvas-source>
<?php endforeach; ?>
</kicanvas-embed>
<script>
(function() {
    'use strict';

    // File selector handling
    const fileSelector = document.getElementById('file-selector');
    if (fileSelector) {
        fileSelector.addEventListener('change', function(e) {
            const file = e.target.value;
            const url = new URL(window.location);

            if (file === 'all') {
                url.searchParams.delete('file');
            } else {
                url.searchParams.set('file', file);
            }

            // Reload page with new file selection
            window.location.href = url.toString();
        });
    }

    // Theme selector handling
    const themeSelector = document.getElementById('theme-selector');
    const viewer = document.getElementById('viewer');

    if (!themeSelector || !viewer) return;

    // Allowlist of permitted themes for security
    const ALLOWED_THEMES = ['kicad', 'witchhazel'];

    function isValidTheme(theme) {
        return ALLOWED_THEMES.includes(theme);
    }

    function updateTheme(theme) {
        // Validate theme against allowlist
        if (!isValidTheme(theme)) {
            console.error('Invalid theme:', theme);
            return;
        }

        // Update kicanvas-embed theme attribute
        viewer.setAttribute('theme', theme);

        // Store preference in localStorage
        try {
            localStorage.setItem('kicanvas-theme', theme);
        } catch (e) {
            console.warn('Could not save theme preference:', e);
        }

        // Update URL without page reload
        const url = new URL(window.location);
        url.searchParams.set('theme', theme);
        window.history.replaceState({}, '', url);
    }

    // Handle theme change
    themeSelector.addEventListener('change', function(e) {
        const theme = e.target.value;
        updateTheme(theme);
    });

    // On page load, check localStorage if no URL theme param
    if (!new URL(window.location).searchParams.has('theme')) {
        try {
            const savedTheme = localStorage.getItem('kicanvas-theme');
            if (savedTheme && isValidTheme(savedTheme)) {
                themeSelector.value = savedTheme;
                viewer.setAttribute('theme', savedTheme);
            }
        } catch (e) {
            console.warn('Could not load theme preference:', e);
        }
    }
})();
</script>
<?php
}
?>
</body>
