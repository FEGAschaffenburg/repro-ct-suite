<?php
if (!defined('ABSPATH')) {
    exit;
}

// Dokumente-Definition
$docs = array(
    'overview' => array('file' => 'overview.md', 'title' => 'Dokumentations-Übersicht', 'icon' => '📚'),
    'quick-start' => array('file' => 'quick-start.md', 'title' => 'Quick-Start Guide', 'icon' => '⚡'),
    'features' => array('file' => 'features.md', 'title' => 'Version 0.9.0 Features', 'icon' => '🚀'),
    'roadmap' => array('file' => 'roadmap.md', 'title' => 'Roadmap & Ausblick', 'icon' => '🔮'),
    'backend' => array('file' => 'backend.md', 'title' => 'Backend-Bedienungsanleitung', 'icon' => '⚙️'),
    'frontend' => array('file' => 'frontend.md', 'title' => 'Frontend-Bedienungsanleitung', 'icon' => '👥'),
    'screenshots' => array('file' => 'screenshots.md', 'title' => 'Screenshot-Anleitung', 'icon' => '📸'),
    'changelog' => array('file' => 'changelog.md', 'title' => 'Changelog', 'icon' => '📋'),
    'dbstruktur' => array('file' => 'dbstruktur.md', 'title' => 'Datenbank-Struktur', 'icon' => '🗄️'),
    'pluginstruktur' => array('file' => 'pluginstruktur.md', 'title' => 'Plugin-Struktur', 'icon' => '🏗️'),
    'css' => array('file' => 'css.md', 'title' => 'CSS-Komponenten', 'icon' => '🎨'),
    'testplan' => array('file' => 'testplan.md', 'title' => 'Test-Plan', 'icon' => '✅'),
);
$docs_path = plugin_dir_path(__FILE__) . 'docs/';
$active = isset($_GET['doc']) && isset($docs[$_GET['doc']]) ? $_GET['doc'] : 'overview';

function rcts_parse_markdown($content) {
    $content = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $content);
    $content = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $content);
    $content = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $content);
    $content = preg_replace('/^---$/m', '<hr>', $content);
    $content = preg_replace_callback('/(?:^|\n)(?:[\-\*\+] .+\n?)+/m', function($matches) {
        $list = $matches[0];
        $items = preg_replace('/^[\-\*\+] (.+)$/m', '<li>$1</li>', $list);
        return '<ul>' . $items . '</ul>';
    }, $content);
    $content = preg_replace('/```([a-z]*)\n(.*?)\n```/s', '<pre><code class="language-$1">$2</code></pre>', $content);
    $content = preg_replace('/`([^`]+)`/', '<code>$1</code>', $content);
    $content = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $content);
    $content = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $content);
    $content = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2" target="_blank">$1</a>', $content);
    $content = preg_replace('/\n\n/', '</p><p>', $content);
    $content = '<p>' . $content . '</p>';
    $content = str_replace('<p></p>', '', $content);
    $content = str_replace('<p><h', '<h', $content);
    $content = str_replace('</h1></p>', '</h1>', $content);
    $content = str_replace('</h2></p>', '</h2>', $content);
    $content = str_replace('</h3></p>', '</h3>', $content);
    $content = str_replace('<p><ul>', '<ul>', $content);
    $content = str_replace('</ul></p>', '</ul>', $content);
    $content = str_replace('<p><pre>', '<pre>', $content);
    $content = str_replace('</pre></p>', '</pre>', $content);
    $content = str_replace('<p><hr></p>', '<hr>', $content);
    return $content;
}
?>
<div class="wrap">
    <h1><span style="font-size: 32px; margin-right: 10px; vertical-align: middle;"><?php echo $docs[$active]['icon']; ?></span> Dokumentation</h1>
    <div style="display:flex;gap:32px;">
        <div style="min-width:220px;max-width:260px;background:#fff;border-radius:8px;padding:24px 18px;box-shadow:0 2px 8px #eee;">
            <h2 style="font-size:1.1em;margin-bottom:16px;">Menü</h2>
            <?php foreach ($docs as $key => $doc): ?>
                <a href="?page=repro-ct-suite-dokumentation&doc=<?php echo esc_attr($key); ?>" style="display:block;padding:10px 8px;margin-bottom:6px;text-decoration:none;color:#2271b1;border-left:3px solid transparent;transition:all 0.2s;<?php echo ($active === $key) ? 'background:#f0f6fc;border-left-color:#2271b1;font-weight:600;' : ''; ?>">
                    <span style="margin-right:8px;"><?php echo $doc['icon']; ?></span>
                    <?php echo esc_html($doc['title']); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div style="flex:1;background:#fff;border-radius:8px;padding:32px;box-shadow:0 2px 8px #eee;">
            <?php
            $file = $docs_path . $docs[$active]['file'];
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $content = preg_replace('/^# .+\n/', '', $content);
                echo '<div style="line-height:1.8;font-size:15px;">';
                echo rcts_parse_markdown($content);
                echo '</div>';
            } else {
                echo '<div class="notice notice-error" style="padding:20px;">';
                echo '<p><strong>Fehler:</strong> Dokumentations-Datei nicht gefunden.</p>';
                echo '<p>Datei: <code>' . esc_html($docs[$active]['file']) . '</code></p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>
<style>
    .wrap h1 { margin-bottom: 24px; }
    .wrap h2 { margin-top: 40px; padding-top: 20px; border-top: 1px solid #f0f0f1; color: #1d2327; }
    .wrap h2:first-child { margin-top: 0; padding-top: 0; border-top: none; }
    .wrap h3 { margin-top: 30px; color: #2271b1; }
    .wrap ul, .wrap ol { margin: 15px 0; padding-left: 30px; }
    .wrap li { margin: 8px 0; }
    .wrap a { color: #2271b1; text-decoration: none; }
    .wrap a:hover { text-decoration: underline; }
    .wrap code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
    .wrap pre { background: #282c34; color: #abb2bf; padding: 15px; border-radius: 5px; overflow-x: auto; line-height: 1.5; }
    .wrap pre code { background: transparent; color: inherit; padding: 0; }
</style>
