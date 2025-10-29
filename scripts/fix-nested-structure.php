<?php
/**
 * Fix: Verschachtelte Plugin-Ordnerstruktur bereinigen
 * 
 * Wenn WordPress das Plugin mit doppelt verschachtelter Struktur installiert hat
 * (repro-ct-suite/repro-ct-suite/repro-ct-suite.php), wird es hier korrigiert.
 * 
 * VERWENDUNG:
 * 1. Diese Datei per FTP in wp-content/plugins/ hochladen
 * 2. Im Browser aufrufen: https://deine-domain.de/wp-content/plugins/fix-nested-structure.php
 * 3. Nach Erfolg: Datei wieder vom Server löschen
 * 
 * @package Repro_CT_Suite
 */

// Sicherheit: nur direkt aufrufen
if (php_sapi_name() === 'cli' || isset($_SERVER['REQUEST_METHOD'])) {
    // OK
} else {
    die('Direct access only');
}

// Plugins-Verzeichnis
$pluginsDir = dirname(__FILE__);
$targetSlug = 'repro-ct-suite';
$targetPath = $pluginsDir . '/' . $targetSlug;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Fix verschachtelte Plugin-Struktur</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
        .success { color: green; background: #f0fff0; padding: 15px; border-left: 4px solid green; }
        .error { color: red; background: #fff0f0; padding: 15px; border-left: 4px solid red; }
        .info { color: #333; background: #f0f0f0; padding: 15px; border-left: 4px solid #0073aa; }
        code { background: #eee; padding: 2px 6px; border-radius: 3px; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Fix: Verschachtelte Plugin-Struktur</h1>

<?php

// Prüfe ob verschachtelt
$nestedPath = $targetPath . '/' . $targetSlug;
$mainFile = $targetPath . '/' . $targetSlug . '.php';
$nestedMainFile = $nestedPath . '/' . $targetSlug . '.php';

echo '<div class="info"><h2>Diagnose:</h2>';
echo '<p><strong>Plugins-Verzeichnis:</strong> <code>' . $pluginsDir . '</code></p>';
echo '<p><strong>Erwartet:</strong> <code>' . $targetPath . '/repro-ct-suite.php</code></p>';

if (is_dir($targetPath)) {
    echo '<p>✓ Ordner existiert: <code>' . $targetSlug . '/</code></p>';
    
    if (file_exists($mainFile)) {
        echo '<p style="color: green;">✓✓ Struktur ist KORREKT!</p>';
        echo '<p>Hauptdatei gefunden: <code>repro-ct-suite/repro-ct-suite.php</code></p>';
        echo '</div>';
        echo '<div class="success"><strong>Keine Aktion nötig!</strong><br>';
        echo 'Das Plugin kann aktiviert werden: <code>repro-ct-suite/repro-ct-suite.php</code></div>';
        exit;
    } elseif (is_dir($nestedPath) && file_exists($nestedMainFile)) {
        echo '<p style="color: red;">✗ Verschachtelte Struktur gefunden!</p>';
        echo '<p>Aktuell: <code>repro-ct-suite/repro-ct-suite/repro-ct-suite.php</code></p>';
        echo '</div>';
        
        // FIX durchführen wenn ?fix=1
        if (isset($_GET['fix']) && $_GET['fix'] === '1') {
            echo '<div class="info"><h2>Reparatur läuft...</h2>';
            
            // 1. Temporären Ordner erstellen
            $tempDir = $pluginsDir . '/repro-ct-suite-temp-' . time();
            if (!mkdir($tempDir, 0755, true)) {
                echo '<p class="error">Fehler: Temporärer Ordner konnte nicht erstellt werden.</p></div>';
                exit;
            }
            echo '<p>✓ Temp-Ordner erstellt</p>';
            
            // 2. Dateien aus verschachteltem Ordner in Temp kopieren
            $success = copyDirectory($nestedPath, $tempDir);
            if (!$success) {
                echo '<p class="error">Fehler beim Kopieren der Dateien.</p></div>';
                deleteDirectory($tempDir);
                exit;
            }
            echo '<p>✓ Dateien kopiert</p>';
            
            // 3. Alten Ordner löschen
            if (!deleteDirectory($targetPath)) {
                echo '<p class="error">Fehler: Alter Ordner konnte nicht gelöscht werden.</p></div>';
                deleteDirectory($tempDir);
                exit;
            }
            echo '<p>✓ Alter Ordner gelöscht</p>';
            
            // 4. Temp umbenennen
            if (!rename($tempDir, $targetPath)) {
                echo '<p class="error">Fehler: Umbenennen fehlgeschlagen.</p></div>';
                exit;
            }
            echo '<p>✓ Ordner umbenannt</p>';
            
            echo '</div>';
            echo '<div class="success"><h2>✓ Erfolgreich repariert!</h2>';
            echo '<p>Die Struktur ist jetzt korrekt:</p>';
            echo '<p><code>repro-ct-suite/repro-ct-suite.php</code></p>';
            echo '<p><strong>Nächste Schritte:</strong></p>';
            echo '<ol>';
            echo '<li>Diese Datei vom Server löschen</li>';
            echo '<li>In WordPress: Plugins → Plugin aktivieren</li>';
            echo '</ol>';
            echo '</div>';
            
        } else {
            // Zeige Reparatur-Button
            echo '<div class="info">';
            echo '<h2>Reparatur verfügbar</h2>';
            echo '<p>Klicke auf den Button um die verschachtelte Struktur zu korrigieren:</p>';
            echo '<p><a href="?fix=1" style="display: inline-block; padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 3px;">Jetzt reparieren</a></p>';
            echo '</div>';
        }
    } else {
        echo '<p style="color: red;">✗ Hauptdatei nicht gefunden!</p>';
        echo '</div>';
        echo '<div class="error">';
        echo '<h2>Unbekannte Struktur</h2>';
        echo '<p>Der Ordner <code>repro-ct-suite</code> existiert, aber die Hauptdatei wurde nicht gefunden.</p>';
        echo '<p><strong>Manuelle Prüfung erforderlich:</strong></p>';
        echo '<ul>';
        $files = scandir($targetPath);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $fullPath = $targetPath . '/' . $file;
                $type = is_dir($fullPath) ? '[DIR]' : '[FILE]';
                echo '<li>' . $type . ' ' . htmlspecialchars($file) . '</li>';
            }
        }
        echo '</ul>';
        echo '</div>';
    }
} else {
    echo '<p style="color: red;">✗ Plugin-Ordner nicht gefunden!</p>';
    echo '</div>';
    echo '<div class="error">';
    echo '<p>Der Ordner <code>repro-ct-suite</code> existiert nicht in <code>wp-content/plugins/</code></p>';
    echo '<p><strong>Bitte Plugin zuerst installieren!</strong></p>';
    echo '</div>';
}

/**
 * Kopiert Verzeichnis rekursiv
 */
function copyDirectory($src, $dst) {
    if (!is_dir($src)) return false;
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    
    $dir = opendir($src);
    if (!$dir) return false;
    
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        
        $srcFile = $src . '/' . $file;
        $dstFile = $dst . '/' . $file;
        
        if (is_dir($srcFile)) {
            if (!copyDirectory($srcFile, $dstFile)) {
                closedir($dir);
                return false;
            }
        } else {
            if (!copy($srcFile, $dstFile)) {
                closedir($dir);
                return false;
            }
        }
    }
    
    closedir($dir);
    return true;
}

/**
 * Löscht Verzeichnis rekursiv
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) return false;
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}

?>

    <hr>
    <p style="color: #666; font-size: 12px;">
        <strong>Hinweis:</strong> Diese Datei nach erfolgreicher Reparatur vom Server löschen!
    </p>
</body>
</html>
