<?php
/**
 * WordPress Plugin Folder Cleanup Script
 * 
 * Prüft und bereinigt verschachtelte oder alte Plugin-Installationen
 * vor der Installation eines neuen ZIP.
 * 
 * VERWENDUNG:
 * 1. Diese Datei auf den Server hochladen (wp-content/plugins/ oder temporär im Root)
 * 2. Im Browser aufrufen: https://deine-domain.de/cleanup-server-installation.php
 * 3. Nach erfolgreichem Cleanup: Datei vom Server löschen (Sicherheit!)
 * 
 * @package Repro_CT_Suite
 */

// Sicherheitscheck: nur ausführen wenn direkt aufgerufen
if (php_sapi_name() === 'cli' || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET')) {
    // OK
} else {
    die('Direct access only');
}

// WordPress Bootstrap (falls im wp-content oder plugins ausgeführt)
$wpLoad = false;
$searchPaths = [
    dirname(dirname(dirname(__DIR__))) . '/wp-load.php',  // wenn in plugins/repro-ct-suite/scripts/
    dirname(dirname(__DIR__)) . '/wp-load.php',           // wenn in plugins/
    dirname(__DIR__) . '/wp-load.php',                     // wenn im Root
];

foreach ($searchPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $wpLoad = true;
        break;
    }
}

if (!$wpLoad) {
    die('WordPress nicht gefunden. Bitte Skript in wp-content/plugins/ oder WordPress-Root hochladen.');
}

// Admin-Check
if (!current_user_can('manage_options')) {
    die('Keine Berechtigung. Bitte als Administrator einloggen und erneut aufrufen.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plugin Cleanup - Repro CT-Suite</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background: #f0f0f1; }
        .container { background: white; border: 1px solid #c3c4c7; border-radius: 4px; padding: 24px; }
        h1 { color: #1d2327; margin-top: 0; }
        .status { padding: 12px; margin: 16px 0; border-left: 4px solid #2271b1; background: #f0f6fc; }
        .status.success { border-color: #00a32a; background: #f0f6f0; }
        .status.warning { border-color: #dba617; background: #fcf9e8; }
        .status.error { border-color: #d63638; background: #fcf0f1; }
        .code { background: #f6f7f7; padding: 8px 12px; border-radius: 3px; font-family: Consolas, Monaco, monospace; font-size: 13px; }
        .actions { margin-top: 24px; }
        .button { display: inline-block; padding: 10px 20px; background: #2271b1; color: white; text-decoration: none; border-radius: 3px; border: none; cursor: pointer; }
        .button:hover { background: #135e96; }
        .button.secondary { background: #dcdcde; color: #2c3338; }
        .button.secondary:hover { background: #c3c4c7; }
        ul { line-height: 1.8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Plugin Installation Cleanup</h1>
        <p>Dieses Skript prüft und bereinigt alte/verschachtelte Installationen von <strong>Repro CT-Suite</strong>.</p>

<?php

$pluginsDir = WP_PLUGIN_DIR;
$targetSlug = 'repro-ct-suite';
$issues = [];
$fixed = [];

// Prüfung 1: Verschachtelte Ordnerstrukturen
$targetPath = $pluginsDir . '/' . $targetSlug;
if (is_dir($targetPath)) {
    $nestedPath = $targetPath . '/' . $targetSlug;
    if (is_dir($nestedPath)) {
        $issues[] = "Verschachtelte Struktur gefunden: <code>$targetSlug/$targetSlug/</code>";
        
        if (isset($_GET['fix']) && $_GET['fix'] === 'nested') {
            // Move files one level up
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($nestedPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            $tempDir = $pluginsDir . '/' . $targetSlug . '_temp_' . time();
            mkdir($tempDir, 0755, true);
            
            foreach ($files as $file) {
                $relative = str_replace($nestedPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $dest = $tempDir . DIRECTORY_SEPARATOR . $relative;
                
                if ($file->isDir()) {
                    mkdir($dest, 0755, true);
                } else {
                    $destDir = dirname($dest);
                    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                    copy($file->getPathname(), $dest);
                }
            }
            
            // Remove old structure
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($targetPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $file) {
                if ($file->isDir()) rmdir($file->getPathname());
                else unlink($file->getPathname());
            }
            rmdir($targetPath);
            
            // Rename temp to target
            rename($tempDir, $targetPath);
            
            $fixed[] = "Verschachtelte Struktur bereinigt: Dateien nach <code>$targetSlug/</code> verschoben";
        }
    }
}

// Prüfung 2: Alte Ordner (z.B. repro-ct-suite-clean)
$oldFolders = ['repro-ct-suite-clean', 'repro-ct-suite-v0.2.4', 'repro-ct-suite-old'];
foreach ($oldFolders as $old) {
    $oldPath = $pluginsDir . '/' . $old;
    if (is_dir($oldPath)) {
        $issues[] = "Alter Ordner gefunden: <code>$old/</code>";
        
        if (isset($_GET['fix']) && $_GET['fix'] === 'old-' . $old) {
            // Check if plugin is active
            $pluginFile = $old . '/' . $targetSlug . '.php';
            if (is_plugin_active($pluginFile)) {
                deactivate_plugins($pluginFile);
            }
            
            // Remove directory
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($oldPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $file) {
                if ($file->isDir()) rmdir($file->getPathname());
                else unlink($file->getPathname());
            }
            rmdir($oldPath);
            
            $fixed[] = "Alter Ordner gelöscht: <code>$old/</code>";
        }
    }
}

// Prüfung 3: Plugin-Hauptdatei am richtigen Ort?
$mainFile = $targetPath . '/repro-ct-suite.php';
if (is_dir($targetPath) && !file_exists($mainFile)) {
    $issues[] = "Plugin-Hauptdatei nicht gefunden: <code>repro-ct-suite/repro-ct-suite.php</code> fehlt";
}

// Ausgabe
if (!empty($fixed)) {
    echo '<div class="status success">';
    echo '<strong>✓ Erfolgreich bereinigt:</strong><ul>';
    foreach ($fixed as $f) echo "<li>$f</li>";
    echo '</ul></div>';
}

if (!empty($issues)) {
    echo '<div class="status warning">';
    echo '<strong>⚠ Gefundene Probleme:</strong><ul>';
    foreach ($issues as $i) echo "<li>$i</li>";
    echo '</ul></div>';
    
    echo '<div class="actions">';
    echo '<p><strong>Nächste Schritte:</strong></p>';
    
    // Verschachtelt?
    if (is_dir($targetPath . '/' . $targetSlug)) {
        echo '<p><a href="?fix=nested" class="button">Verschachtelte Struktur jetzt bereinigen</a></p>';
    }
    
    // Alte Ordner?
    foreach ($oldFolders as $old) {
        if (is_dir($pluginsDir . '/' . $old)) {
            echo '<p><a href="?fix=old-' . $old . '" class="button secondary">Alten Ordner "' . $old . '" löschen</a></p>';
        }
    }
    
    echo '</div>';
} else {
    echo '<div class="status success">';
    echo '<strong>✓ Keine Probleme gefunden!</strong><br>';
    echo 'Die Plugin-Installation ist sauber. Du kannst jetzt das neue ZIP installieren.';
    echo '</div>';
}

?>

        <hr style="margin: 32px 0; border: none; border-top: 1px solid #c3c4c7;">
        
        <h2>Anleitung: Neues ZIP installieren</h2>
        <ol>
            <li>Falls oben Probleme angezeigt werden: auf "Bereinigen" klicken</li>
            <li>ZIP herunterladen von: <a href="https://github.com/FEGAschaffenburg/repro-ct-suite/releases/latest" target="_blank">GitHub Release</a></li>
            <li>In WordPress: <strong>Plugins → Installieren → Plugin hochladen</strong></li>
            <li>ZIP auswählen und installieren</li>
            <li>Plugin aktivieren</li>
            <li><strong>Wichtig:</strong> Diese Cleanup-Datei vom Server löschen!</li>
        </ol>
        
        <p style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #c3c4c7; color: #646970; font-size: 13px;">
            <strong>Hinweis:</strong> Nach erfolgreicher Installation bitte diese Datei (<code><?php echo basename(__FILE__); ?></code>) 
            vom Server löschen – aus Sicherheitsgründen sollte sie nicht dauerhaft öffentlich erreichbar sein.
        </p>
    </div>
</body>
</html>
