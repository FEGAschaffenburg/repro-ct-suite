<?php
/**
 * Modern Shortcode Manager Admin Page - Debug Enhanced Version
 * 
 * Saubere, moderne Admin-Oberfläche für Shortcode-Management
 * Version: 2024-11-07 16:45 - Mit umfassendem Debug-Support
 * 
 * @package Repro_CT_Suite
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Kalender laden (rcts_* Tabellen verwenden)
global $wpdb;
$table_calendars = $wpdb->prefix . 'rcts_calendars';
$calendars = $wpdb->get_results("SELECT calendar_id, name, color, is_selected FROM {$table_calendars} WHERE is_selected = 1 ORDER BY name ASC");

// Presets laden (rcts_* Tabellen verwenden)
$table_presets = $wpdb->prefix . 'rcts_shortcode_presets';
$presets = $wpdb->get_results("SELECT * FROM {$table_presets} ORDER BY name ASC");
?>

<div class="wrap" id="modern-shortcode-manager">
    
    <!-- Header Section -->
    <div class="sm-header">
        <h1 class="sm-page-title">
            <div class="sm-header-logo">
                <img src="<?php echo esc_url( plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/images/churchtools-suite-icon.svg' ); ?>" alt="ChurchTools Suite">
            </div>
            Shortcode Manager
        </h1>
    </div>

    <!-- Action Bar -->
    <div class="sm-action-bar">
        <button type="button" class="sm-btn sm-btn-primary" id="create-preset-btn">
            <span class="sm-btn-icon">✨</span>
            Neues Preset
        </button>
        
        <div class="sm-stats-inline">
            <span class="sm-stat-inline">
                <strong><?php echo count($presets); ?></strong> Eigene
            </span>
            <span class="sm-stat-inline">
                <strong><?php echo count($calendars); ?></strong> Kalender
            </span>
        </div>
    </div>

    <!-- Standard Shortcodes Section -->
    <div class="sm-section">
        <div class="sm-section-header sm-collapsible-header" data-target="standard-shortcodes">
            <h2>
                <span class="sm-collapse-icon">▼</span>
                📋 Standard Shortcodes
            </h2>
            <span class="sm-badge">6 Layouts</span>
        </div>
        
        <div class="sm-collapsible-content" id="standard-shortcodes">
            <div class="sm-shortcode-list">
                <!-- Compact View -->
                <div class="sm-shortcode-item">
                    <div class="sm-shortcode-info-wrapper">
                        <code class="sm-shortcode-code">[repro_ct_suite_events view="compact" limit="10"]</code>
                        <span class="sm-shortcode-desc">Ultra kompakte Liste • Datum, Zeit, Titel in einer Zeile • Perfekt für Footer</span>
                    </div>
                    <div class="sm-shortcode-actions">
                        <button type="button" class="sm-btn sm-btn-icon sm-btn-small preview-shortcode" data-shortcode="compact" title="Vorschau anzeigen">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                        <button type="button" class="sm-btn sm-btn-secondary sm-btn-small copy-shortcode" data-shortcode='[repro_ct_suite_events view="compact" limit="10"]'>
                            <span class="dashicons dashicons-clipboard"></span>
                        </button>
                    </div>
                </div>

                <!-- List Simple View -->
                <div class="sm-shortcode-item">
                    <div class="sm-shortcode-info-wrapper">
                        <code class="sm-shortcode-code">[repro_ct_suite_events view="list" limit="10"]</code>
                        <span class="sm-shortcode-desc">Moderne Liste • Große Datums-Box mit Details • Standard-Ansicht</span>
                    </div>
                    <div class="sm-shortcode-actions">
                        <button type="button" class="sm-btn sm-btn-icon sm-btn-small preview-shortcode" data-shortcode="list" title="Vorschau anzeigen">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                        <button type="button" class="sm-btn sm-btn-secondary sm-btn-small copy-shortcode" data-shortcode='[repro_ct_suite_events view="list" limit="10"]'>
                            <span class="dashicons dashicons-clipboard"></span>
                        </button>
                    </div>
                </div>

                <!-- Medium View -->
                <div class="sm-shortcode-item">
                    <div class="sm-shortcode-info-wrapper">
                        <code class="sm-shortcode-code">[repro_ct_suite_events view="medium" limit="10"]</code>
                        <span class="sm-shortcode-desc">Ausgewogene Liste • Datum, Zeit, Titel, Ort • Ideal für Hauptbereiche</span>
                    </div>
                    <div class="sm-shortcode-actions">
                        <button type="button" class="sm-btn sm-btn-icon sm-btn-small preview-shortcode" data-shortcode="medium" title="Vorschau anzeigen">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                        <button type="button" class="sm-btn sm-btn-secondary sm-btn-small copy-shortcode" data-shortcode='[repro_ct_suite_events view="medium" limit="10"]'>
                            <span class="dashicons dashicons-clipboard"></span>
                        </button>
                    </div>
                </div>

                <!-- List Grouped View -->
                <div class="sm-shortcode-item">
                    <div class="sm-shortcode-info-wrapper">
                        <code class="sm-shortcode-code">[repro_ct_suite_events view="list-grouped" limit="20"]</code>
                        <span class="sm-shortcode-desc">Timeline-Ansicht • Nach Datum gruppiert mit Zeitlinie • Übersichtlich</span>
                    </div>
                    <div class="sm-shortcode-actions">
                        <button type="button" class="sm-btn sm-btn-icon sm-btn-small preview-shortcode" data-shortcode="grouped" title="Vorschau anzeigen">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                        <button type="button" class="sm-btn sm-btn-secondary sm-btn-small copy-shortcode" data-shortcode='[repro_ct_suite_events view="list-grouped" limit="20"]'>
                            <span class="dashicons dashicons-clipboard"></span>
                        </button>
                    </div>
                </div>

                <!-- Cards View -->
                <div class="sm-shortcode-item">
                    <div class="sm-shortcode-info-wrapper">
                        <code class="sm-shortcode-code">[repro_ct_suite_events view="cards" limit="12"]</code>
                        <span class="sm-shortcode-desc">Karten-Grid • Responsive 3-Spalten-Layout • Modern & ansprechend</span>
                    </div>
                    <div class="sm-shortcode-actions">
                        <button type="button" class="sm-btn sm-btn-icon sm-btn-small preview-shortcode" data-shortcode="cards" title="Vorschau anzeigen">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                        <button type="button" class="sm-btn sm-btn-secondary sm-btn-small copy-shortcode" data-shortcode='[repro_ct_suite_events view="cards" limit="12"]'>
                            <span class="dashicons dashicons-clipboard"></span>
                        </button>
                    </div>
                </div>

                <!-- Sidebar View -->
                <div class="sm-shortcode-item">
                    <div class="sm-shortcode-info-wrapper">
                        <code class="sm-shortcode-code">[repro_ct_suite_events view="sidebar" limit="5"]</code>
                        <span class="sm-shortcode-desc">Sidebar Widget • Optimiert für schmale Bereiche • Kompakt mit Icon</span>
                    </div>
                    <div class="sm-shortcode-actions">
                        <button type="button" class="sm-btn sm-btn-icon sm-btn-small preview-shortcode" data-shortcode="sidebar" title="Vorschau anzeigen">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                        <button type="button" class="sm-btn sm-btn-secondary sm-btn-small copy-shortcode" data-shortcode='[repro_ct_suite_events view="sidebar" limit="5"]'>
                            <span class="dashicons dashicons-clipboard"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Eigene Presets Section -->
    <div class="sm-section">
        <div class="sm-section-header sm-collapsible-header" data-target="custom-presets">
            <h2>
                <span class="sm-collapse-icon">▼</span>
                ⭐ Eigene Presets
            </h2>
            <span class="sm-badge"><?php echo count($presets); ?> Presets</span>
        </div>
        
        <div class="sm-collapsible-content" id="custom-presets">
        <?php if (empty($presets)): ?>
            <div class="sm-empty-state-small">
                <p>Noch keine eigenen Presets erstellt.</p>
                <button type="button" class="sm-btn sm-btn-primary" id="create-first-preset-btn">
                    <span class="sm-btn-icon">✨</span>
                    Erstes Preset erstellen
                </button>
            </div>
        <?php else: ?>
            <div class="sm-preset-list">
                <?php foreach ($presets as $preset): ?>
                    <div class="sm-preset-list-item" data-preset-id="<?php echo esc_attr($preset->id); ?>">
                        <div class="sm-preset-list-info">
                            <div class="sm-preset-list-header">
                                <h3 class="sm-preset-list-name"><?php echo esc_html($preset->name); ?></h3>
                            </div>
                            
                            <?php if (!empty($preset->shortcode_tag)): ?>
                                <code class="sm-preset-list-code">[<?php echo esc_html($preset->shortcode_tag); ?>]</code>
                            <?php endif; ?>
                            
                            <div class="sm-preset-list-meta">
                                <?php if (!is_null($preset->calendar_ids) && !empty($preset->calendar_ids)): ?>
                                    <?php $calendar_count = count(explode(',', $preset->calendar_ids)); ?>
                                    <span><?php echo $calendar_count; ?> Kalender</span>
                                <?php else: ?>
                                    <span>Alle Kalender</span>
                                <?php endif; ?>
                                
                                <?php if (!empty($preset->view)): ?>
                                    <span>• <?php echo ucfirst($preset->view); ?></span>
                                <?php endif; ?>
                                
                                <?php if (!empty($preset->limit_count)): ?>
                                    <span>• Limit: <?php echo $preset->limit_count; ?></span>
                                <?php endif; ?>
                                
                                <?php if (!empty($preset->days_ahead)): ?>
                                    <span>• <?php echo $preset->days_ahead; ?> Tage</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="sm-preset-list-actions">
                            <?php if (!empty($preset->shortcode_tag)): ?>
                                <button type="button" class="sm-btn sm-btn-secondary sm-btn-small copy-shortcode" data-shortcode="[<?php echo esc_attr($preset->shortcode_tag); ?>]">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            <?php endif; ?>
                            <button type="button" class="sm-btn sm-btn-secondary sm-btn-small edit-preset" data-preset-id="<?php echo esc_attr($preset->id); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button type="button" class="sm-btn sm-btn-danger sm-btn-small delete-preset" data-preset-id="<?php echo esc_attr($preset->id); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>

</div>

<!-- Create/Edit Preset Modal -->
<div id="preset-modal" class="sm-modal">
    <div class="sm-modal-content">
        
        <!-- Modal Header -->
        <div class="sm-modal-header">
            <h2 class="sm-modal-title" id="modal-title">Neues Preset erstellen</h2>
            <button type="button" class="sm-modal-close" id="close-modal">×</button>
        </div>

        <!-- Modal Body -->
        <div class="sm-modal-body">
            <form id="preset-form">
                <input type="hidden" id="preset-id" name="preset_id" value="">
                
                <!-- Preset Name -->
                <div class="sm-form-group">
                    <label for="preset-name" class="sm-form-label">Preset Name *</label>
                    <input type="text" id="preset-name" name="preset_name" class="sm-form-input" 
                           placeholder="z.B. Gottesdienste" required>
                    <div class="sm-form-description">
                        Ein aussagekräftiger Name für dein Preset
                    </div>
                </div>

                <!-- Calendar Selection -->
                <div class="sm-form-group">
                    <label class="sm-form-label">Kalender auswählen *</label>
                    
                    <div class="sm-radio-group">
                        <label class="sm-radio-option">
                            <input type="radio" name="calendar_mode" value="all" checked>
                            Alle Kalender anzeigen
                        </label>
                        <label class="sm-radio-option">
                            <input type="radio" name="calendar_mode" value="specific">
                            Bestimmte Kalender auswählen
                        </label>
                    </div>

                    <div class="sm-calendar-selector">
                        <div id="calendar-list" class="sm-calendar-list">
                            <?php if (empty($calendars)): ?>
                                <div class="sm-no-calendars">
                                    <div class="sm-empty-icon">📅</div>
                                    <h4>Keine Kalender verfügbar</h4>
                                    <p>Führe zuerst eine Kalender-Synchronisation durch:</p>
                                    <p><strong>Repro CT-Suite → Dashboard → Synchronisation starten</strong></p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($calendars as $calendar): ?>
                                    <div class="sm-calendar-item" data-calendar-id="<?php echo esc_attr($calendar->calendar_id); ?>">
                                        <input type="checkbox" 
                                               name="calendar_ids[]" 
                                               value="<?php echo esc_attr($calendar->calendar_id); ?>"
                                               id="calendar-<?php echo esc_attr($calendar->calendar_id); ?>"
                                               class="sm-calendar-checkbox">
                                        <div class="sm-calendar-name"><?php echo esc_html($calendar->name); ?></div>
                                        <div class="sm-calendar-id">ID: <?php echo esc_html($calendar->calendar_id); ?></div>
                                        <?php if (!empty($calendar->color)): ?>
                                            <div class="sm-calendar-color" style="background-color: <?php echo esc_attr($calendar->color); ?>"></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Display Options -->
                <div class="sm-form-group">
                    <label class="sm-form-label">Anzeige-Modus</label>
                    
                    <div class="sm-options-grid">
                        <div class="sm-option-card" data-mode="list">
                            <div class="sm-option-icon">📋</div>
                            <input type="radio" name="display_mode" value="list" class="sm-option-checkbox" checked>
                            <div class="sm-option-label">Listen-Ansicht</div>
                        </div>
                        
                        <div class="sm-option-card" data-mode="grid">
                            <div class="sm-option-icon">📊</div>
                            <input type="radio" name="display_mode" value="grid" class="sm-option-checkbox">
                            <div class="sm-option-label">Raster-Ansicht</div>
                        </div>
                        
                        <div class="sm-option-card" data-mode="calendar">
                            <div class="sm-option-icon">📅</div>
                            <input type="radio" name="display_mode" value="calendar" class="sm-option-checkbox">
                            <div class="sm-option-label">Kalender-Ansicht</div>
                        </div>
                    </div>
                </div>

                <!-- Additional Settings -->
                <div class="sm-form-row">
                    <div class="sm-form-group">
                        <label for="events-limit" class="sm-form-label">Anzahl Events</label>
                        <input type="number" id="events-limit" name="events_limit" 
                               class="sm-form-input" value="10" min="1" max="100">
                    </div>
                    
                    <div class="sm-form-group">
                        <label for="days-ahead" class="sm-form-label">Tage voraus</label>
                        <input type="number" id="days-ahead" name="days_ahead" 
                               class="sm-form-input" value="30" min="1" max="365">
                    </div>
                </div>

                <!-- Features -->
                <div class="sm-form-group">
                    <label class="sm-form-label">Features</label>
                    
                    <div class="sm-checkbox-grid">
                        <label class="sm-checkbox-option">
                            <input type="checkbox" name="show_descriptions" value="1">
                            Beschreibungen anzeigen
                        </label>
                        
                        <label class="sm-checkbox-option">
                            <input type="checkbox" name="show_locations" value="1">
                            Orte anzeigen
                        </label>
                        
                        <label class="sm-checkbox-option">
                            <input type="checkbox" name="show_time" value="1" checked>
                            Uhrzeiten anzeigen
                        </label>
                        
                        <label class="sm-checkbox-option">
                            <input type="checkbox" name="show_organizer" value="1">
                            Veranstalter anzeigen
                        </label>
                    </div>
                </div>

            </form>
        </div>

        <!-- Modal Footer -->
        <div class="sm-modal-footer">
            <button type="button" class="sm-btn sm-btn-secondary" id="cancel-preset">
                Abbrechen
            </button>
            
            <div class="sm-footer-actions">
                <button type="button" class="sm-btn sm-btn-secondary" id="save-draft">
                    💾 Als Entwurf speichern
                </button>
                <button type="button" class="sm-btn sm-btn-primary" id="save-preset">
                    ✨ Preset erstellen
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Generated Shortcode Modal -->
<div id="shortcode-result-modal" class="sm-modal">
    <div class="sm-modal-content">
        <div class="sm-modal-header">
            <h2 class="sm-modal-title">🎉 Preset erfolgreich erstellt!</h2>
            <button type="button" class="sm-modal-close" onclick="closeShortcodeModal()">×</button>
        </div>
        <div class="sm-modal-body">
            <p>Dein Shortcode-Preset wurde erfolgreich erstellt. Du kannst den folgenden Shortcode in deine Beiträge und Seiten einfügen:</p>
            
            <div class="sm-shortcode-display">
                <span id="generated-shortcode">[rcts_events]</span>
                <button type="button" class="sm-shortcode-copy" onclick="copyGeneratedShortcode()">
                    📋 Kopieren
                </button>
            </div>
            
            <div class="sm-shortcode-info">
                <h4>📖 Verwendung:</h4>
                <ul>
                    <li>Kopiere den Shortcode und füge ihn in jeden Beitrag oder jede Seite ein</li>
                    <li>Der Shortcode wird automatisch durch deine Kalender-Events ersetzt</li>
                    <li>Du kannst das Preset jederzeit über diese Seite bearbeiten</li>
                </ul>
            </div>
        </div>
        <div class="sm-modal-footer">
            <button type="button" class="sm-btn sm-btn-secondary" onclick="closeShortcodeModal()">
                Schließen
            </button>
            <button type="button" class="sm-btn sm-btn-primary" onclick="copyAndClose()">
                📋 Kopieren & Schließen
            </button>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="preview-modal" class="sm-modal">
    <div class="sm-modal-content sm-modal-preview">
        <div class="sm-modal-header">
            <h2 class="sm-modal-title" id="preview-title">Shortcode Vorschau</h2>
            <button type="button" class="sm-modal-close" onclick="closePreviewModal()">×</button>
        </div>
        <div class="sm-modal-body">
            <div class="sm-preview-description" id="preview-description"></div>
            <div class="sm-preview-image-container">
                <img id="preview-image" src="" alt="Vorschau" class="sm-preview-image">
            </div>
        </div>
        <div class="sm-modal-footer">
            <button type="button" class="sm-btn sm-btn-secondary" onclick="closePreviewModal()">
                Schließen
            </button>
            <button type="button" class="sm-btn sm-btn-primary" id="copy-from-preview">
                <span class="dashicons dashicons-clipboard"></span>
                Shortcode kopieren
            </button>
        </div>
    </div>
</div>

<script>
// Globale Variablen für das Shortcode Manager Interface
window.shortcodeManager = {
    nonce: '<?php echo wp_create_nonce("sm_ajax_nonce"); ?>',
    ajaxUrl: '<?php echo admin_url("admin-ajax.php"); ?>'
};

// Preview Modal Daten
const previewData = {
    'standard': {
        title: 'Standard-Ansicht',
        description: '<strong>Verwendung:</strong> [repro_ct_suite_events]<br><strong>Einstellungen:</strong> Alle Kalender, Standard-Sortierung<br><strong>Beschreibung:</strong> Zeigt alle kommenden Termine in einer übersichtlichen Liste an.',
        image: '<?php echo plugin_dir_url( dirname( dirname( __FILE__ ) ) ); ?>assets/images/preview-standard.jpg'
    },
    'list': {
        title: 'Listen-Ansicht',
        description: '<strong>Verwendung:</strong> [repro_ct_suite_events view="list"]<br><strong>Parameter:</strong> view="list"<br><strong>Beschreibung:</strong> Kompakte Listendarstellung mit Datum, Uhrzeit und Veranstaltungsname.',
        image: '<?php echo plugin_dir_url( dirname( dirname( __FILE__ ) ) ); ?>assets/images/preview-list.jpg'
    },
    'grouped': {
        title: 'Gruppierte Ansicht',
        description: '<strong>Verwendung:</strong> [repro_ct_suite_events view="grouped"]<br><strong>Parameter:</strong> view="grouped"<br><strong>Beschreibung:</strong> Termine werden nach Datum gruppiert angezeigt. Ideal für eine übersichtliche Monatsansicht.',
        image: '<?php echo plugin_dir_url( dirname( dirname( __FILE__ ) ) ); ?>assets/images/preview-grouped.jpg'
    },
    'cards': {
        title: 'Karten-Layout',
        description: '<strong>Verwendung:</strong> [repro_ct_suite_events view="cards"]<br><strong>Parameter:</strong> view="cards"<br><strong>Beschreibung:</strong> Visuell ansprechende Karten mit detaillierten Informationen zu jedem Termin.',
        image: '<?php echo plugin_dir_url( dirname( dirname( __FILE__ ) ) ); ?>assets/images/preview-cards.jpg'
    },
    'limit': {
        title: 'Mit Limit-Parameter',
        description: '<strong>Verwendung:</strong> [repro_ct_suite_events limit="5"]<br><strong>Parameter:</strong> limit="5" (oder andere Zahl)<br><strong>Beschreibung:</strong> Begrenzt die Anzahl der angezeigten Termine. Hier werden maximal 5 Termine angezeigt.',
        image: '<?php echo plugin_dir_url( dirname( dirname( __FILE__ ) ) ); ?>assets/images/preview-limit.jpg'
    },
    'days': {
        title: 'Mit Zeitfilter',
        description: '<strong>Verwendung:</strong> [repro_ct_suite_events days="30"]<br><strong>Parameter:</strong> days="30" (oder andere Anzahl Tage)<br><strong>Beschreibung:</strong> Zeigt nur Termine innerhalb des angegebenen Zeitraums. Hier: Die nächsten 30 Tage.',
        image: '<?php echo plugin_dir_url( dirname( dirname( __FILE__ ) ) ); ?>assets/images/preview-days.jpg'
    }
};

// Preview Modal öffnen
function openPreviewModal(shortcodeType) {
    const data = previewData[shortcodeType];
    if (!data) {
        console.error('Preview data not found for:', shortcodeType);
        return;
    }
    
    console.log('Opening preview for:', shortcodeType, data);
    
    document.getElementById('preview-title').textContent = data.title;
    document.getElementById('preview-description').innerHTML = data.description;
    
    const imgElement = document.getElementById('preview-image');
    imgElement.src = data.image;
    imgElement.onerror = function() {
        console.error('Image failed to load:', data.image);
        this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iI2Y4ZmFmYyIvPjx0ZXh0IHg9IjQwMCIgeT0iMjAwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IiM2NDc0OGIiIHRleHQtYW5jaG9yPSJtaWRkbGUiPlZvcnNjaGF1IGJpbGQgbmljaHQgdmVyZsO8Z2JhcjwvdGV4dD48L3N2Zz4=';
    };
    
    // Speichere den Shortcode für den Kopier-Button
    const shortcodeMap = {
        'standard': '[repro_ct_suite_events]',
        'list': '[repro_ct_suite_events view="list"]',
        'grouped': '[repro_ct_suite_events view="grouped"]',
        'cards': '[repro_ct_suite_events view="cards"]',
        'limit': '[repro_ct_suite_events limit="5"]',
        'days': '[repro_ct_suite_events days="30"]'
    };
    
    document.getElementById('copy-from-preview').dataset.shortcode = shortcodeMap[shortcodeType];
    document.getElementById('preview-modal').style.display = 'block';
}

// Preview Modal schließen
function closePreviewModal() {
    document.getElementById('preview-modal').style.display = 'none';
}

// Kopier-Funktion (wird von modern-shortcode-manager.js überschrieben, falls geladen)
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            showCopyFeedback('✓ Shortcode kopiert!');
        }).catch(function(err) {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

// Fallback für ältere Browser
function fallbackCopy(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.select();
    try {
        document.execCommand('copy');
        showCopyFeedback('✓ Shortcode kopiert!');
    } catch (err) {
        showCopyFeedback('✗ Fehler beim Kopieren');
    }
    document.body.removeChild(textArea);
}

// Feedback anzeigen
function showCopyFeedback(message) {
    // Prüfe ob Toast-Funktion von main JS existiert
    if (window.shortcodeManager && typeof window.shortcodeManager.showToast === 'function') {
        window.shortcodeManager.showToast(message, 'success');
    } else {
        // Einfacher Fallback
        alert(message);
    }
}

// Event Listener für Preview-Buttons
document.addEventListener('DOMContentLoaded', function() {
    // Preview-Buttons
    document.querySelectorAll('.preview-shortcode').forEach(button => {
        if (!button.hasAttribute('data-js-bound')) {
            button.addEventListener('click', function() {
                openPreviewModal(this.dataset.shortcode);
            });
            button.setAttribute('data-js-bound', 'true');
        }
    });
    
    // Kopier-Button im Preview-Modal
    document.getElementById('copy-from-preview').addEventListener('click', function() {
        const shortcode = this.dataset.shortcode;
        copyToClipboard(shortcode);
        closePreviewModal();
    });
    
    // Copy-Buttons für Standard-Shortcodes (falls main JS nicht geladen)
    document.querySelectorAll('.copy-shortcode').forEach(button => {
        if (!button.hasAttribute('data-js-bound')) {
            button.addEventListener('click', function() {
                const shortcode = this.dataset.shortcode;
                copyToClipboard(shortcode);
            });
            button.setAttribute('data-js-bound', 'true');
        }
    });
    
    // Modal schließen bei Klick außerhalb
    document.getElementById('preview-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePreviewModal();
        }
    });
});

</script>
