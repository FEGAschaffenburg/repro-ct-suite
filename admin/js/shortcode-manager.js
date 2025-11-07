/**
 * Shortcode Manager JavaScript
 * Moderne UI für Shortcode-Verwaltung mit Live-Vorschau
 */

class ShortcodeManager {
    constructor() {
        this.currentView = 'grid';
        this.currentEditId = null;
        this.previewTimer = null;
        this.isLoading = false;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadShortcodes();
        this.setupAutoPreview();
    }

    bindEvents() {
        // Header-Buttons
        document.getElementById('create-shortcode-btn')?.addEventListener('click', () => {
            this.openEditor();
        });

        document.getElementById('refresh-list-btn')?.addEventListener('click', () => {
            this.loadShortcodes();
        });

        // Search
        document.getElementById('shortcode-search')?.addEventListener('input', (e) => {
            this.filterShortcodes(e.target.value);
        });

        // View Toggle
        document.querySelectorAll('.view-toggle').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchView(e.target.closest('.view-toggle').dataset.view);
            });
        });

        // Modal Events
        document.getElementById('close-modal-btn')?.addEventListener('click', () => {
            this.closeEditor();
        });

        document.getElementById('cancel-edit-btn')?.addEventListener('click', () => {
            this.closeEditor();
        });

        document.getElementById('save-shortcode-btn')?.addEventListener('click', () => {
            this.saveShortcode();
        });

        document.getElementById('delete-current-shortcode-btn')?.addEventListener('click', () => {
            this.deleteCurrentShortcode();
        });

        // Preview Controls
        document.getElementById('refresh-preview-btn')?.addEventListener('click', () => {
            this.updatePreview();
        });

        document.getElementById('copy-shortcode-btn')?.addEventListener('click', () => {
            this.copyShortcode();
        });

        // Form Events
        document.getElementById('edit-all-calendars')?.addEventListener('change', (e) => {
            this.toggleAllCalendars(e.target.checked);
        });

        // Calendar checkboxes
        document.querySelectorAll('.calendar-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                this.updateAllCalendarsState();
                this.schedulePreviewUpdate();
            });
        });

        // Modal overlay close
        document.getElementById('shortcode-editor-modal')?.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                this.closeEditor();
            }
        });

        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isModalOpen()) {
                this.closeEditor();
            }
        });
    }

    setupAutoPreview() {
        // Auto-preview bei Formular-Änderungen
        const formInputs = document.querySelectorAll('#shortcode-editor-form input, #shortcode-editor-form select');
        formInputs.forEach(input => {
            const events = input.type === 'checkbox' ? ['change'] : ['input', 'change'];
            events.forEach(event => {
                input.addEventListener(event, () => {
                    this.schedulePreviewUpdate();
                });
            });
        });

        // Field checkboxes
        document.querySelectorAll('.field-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                this.schedulePreviewUpdate();
            });
        });
    }

    schedulePreviewUpdate() {
        if (this.previewTimer) {
            clearTimeout(this.previewTimer);
        }
        this.previewTimer = setTimeout(() => {
            this.updatePreview();
        }, 500);
    }

    async loadShortcodes() {
        this.showLoading();

        try {
            const response = await this.apiRequest('get_presets');
            
            if (response.success && response.data) {
                this.renderShortcodeList(response.data);
            } else {
                this.showEmptyState();
            }
        } catch (error) {
            console.error('Error loading shortcodes:', error);
            this.showToast('error', 'Fehler', 'Shortcodes konnten nicht geladen werden.');
            this.showEmptyState();
        }
    }

    renderShortcodeList(shortcodes) {
        const container = document.getElementById('shortcode-list');
        const loading = document.getElementById('shortcode-list-loading');
        const empty = document.getElementById('shortcode-list-empty');

        loading.style.display = 'none';

        if (!shortcodes || shortcodes.length === 0) {
            this.showEmptyState();
            return;
        }

        empty.style.display = 'none';
        container.style.display = 'block';

        if (this.currentView === 'grid') {
            container.className = 'shortcode-grid';
            container.innerHTML = shortcodes.map(shortcode => this.renderShortcodeCard(shortcode)).join('');
        } else {
            container.className = 'shortcode-list';
            container.innerHTML = shortcodes.map(shortcode => this.renderShortcodeListItem(shortcode)).join('');
        }

        // Event-Listener für Shortcode-Aktionen
        this.bindShortcodeEvents();
    }

    renderShortcodeCard(shortcode) {
        const calendars = this.parseCalendarIds(shortcode.calendar_ids);
        const fields = this.parseShowFields(shortcode.show_fields);
        const generatedShortcode = this.generateShortcodeString(shortcode);

        return `
            <div class="shortcode-card" data-id="${shortcode.id}">
                <div class="shortcode-card-header">
                    <h3 class="shortcode-card-title">${this.escapeHtml(shortcode.name)}</h3>
                    <div class="shortcode-card-actions">
                        <button class="button button-small edit-shortcode" data-id="${shortcode.id}">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button class="button button-small copy-shortcode" data-shortcode="${this.escapeHtml(generatedShortcode)}">
                            <span class="dashicons dashicons-admin-page"></span>
                        </button>
                        <button class="button button-small button-link-delete delete-shortcode" data-id="${shortcode.id}" data-name="${this.escapeHtml(shortcode.name)}">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
                <div class="shortcode-card-body">
                    <div class="shortcode-meta">
                        <div class="meta-item">
                            <span class="dashicons dashicons-visibility"></span>
                            ${this.getViewLabel(shortcode.view)}
                        </div>
                        <div class="meta-item">
                            <span class="dashicons dashicons-list-view"></span>
                            ${shortcode.limit_count} Termine
                        </div>
                        <div class="meta-item">
                            <span class="dashicons dashicons-calendar"></span>
                            ${calendars.length > 0 ? calendars.length + ' Kalender' : 'Alle Kalender'}
                        </div>
                        <div class="meta-item">
                            <span class="dashicons dashicons-admin-generic"></span>
                            ${fields.length} Felder
                        </div>
                    </div>
                    <div class="shortcode-preview-code">
                        ${this.escapeHtml(generatedShortcode)}
                    </div>
                </div>
            </div>
        `;
    }

    renderShortcodeListItem(shortcode) {
        const calendars = this.parseCalendarIds(shortcode.calendar_ids);
        const fields = this.parseShowFields(shortcode.show_fields);
        const generatedShortcode = this.generateShortcodeString(shortcode);

        return `
            <div class="shortcode-list-item" data-id="${shortcode.id}">
                <div class="list-item-content">
                    <div class="list-item-title">
                        <strong>${this.escapeHtml(shortcode.name)}</strong>
                        <div class="shortcode-preview-code" style="margin-top: 5px;">
                            ${this.escapeHtml(generatedShortcode)}
                        </div>
                    </div>
                    <div class="list-item-meta">
                        <div class="list-item-meta-row">
                            <strong>Ansicht:</strong> ${this.getViewLabel(shortcode.view)}
                        </div>
                        <div class="list-item-meta-row">
                            <strong>Termine:</strong> ${shortcode.limit_count}
                        </div>
                    </div>
                    <div class="list-item-meta">
                        <div class="list-item-meta-row">
                            <strong>Kalender:</strong> ${calendars.length > 0 ? calendars.length : 'Alle'}
                        </div>
                        <div class="list-item-meta-row">
                            <strong>Felder:</strong> ${fields.length}
                        </div>
                    </div>
                    <div class="list-item-meta">
                        <div class="list-item-meta-row">
                            <strong>Erstellt:</strong> ${this.formatDate(shortcode.created_at)}
                        </div>
                        <div class="list-item-meta-row">
                            <strong>Geändert:</strong> ${this.formatDate(shortcode.updated_at)}
                        </div>
                    </div>
                    <div class="shortcode-card-actions">
                        <button class="button button-small edit-shortcode" data-id="${shortcode.id}">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button class="button button-small copy-shortcode" data-shortcode="${this.escapeHtml(generatedShortcode)}">
                            <span class="dashicons dashicons-admin-page"></span>
                        </button>
                        <button class="button button-small button-link-delete delete-shortcode" data-id="${shortcode.id}" data-name="${this.escapeHtml(shortcode.name)}">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    bindShortcodeEvents() {
        // Edit buttons
        document.querySelectorAll('.edit-shortcode').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const id = btn.dataset.id;
                this.openEditor(id);
            });
        });

        // Copy buttons
        document.querySelectorAll('.copy-shortcode').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const shortcode = btn.dataset.shortcode;
                this.copyToClipboard(shortcode);
            });
        });

        // Delete buttons
        document.querySelectorAll('.delete-shortcode').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const id = btn.dataset.id;
                const name = btn.dataset.name;
                this.deleteShortcode(id, name);
            });
        });

        // Card click to edit
        document.querySelectorAll('.shortcode-card, .shortcode-list-item').forEach(card => {
            card.addEventListener('click', (e) => {
                if (!e.target.closest('button')) {
                    const id = card.dataset.id;
                    this.openEditor(id);
                }
            });
        });
    }

    switchView(view) {
        this.currentView = view;
        
        // Update UI
        document.querySelectorAll('.view-toggle').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === view);
        });

        // Re-render list
        const container = document.getElementById('shortcode-list');
        if (container.children.length > 0) {
            // Re-render with current data
            const shortcodes = this.extractShortcodesFromDOM();
            this.renderShortcodeList(shortcodes);
        }
    }

    showLoading() {
        document.getElementById('shortcode-list-loading').style.display = 'block';
        document.getElementById('shortcode-list').style.display = 'none';
        document.getElementById('shortcode-list-empty').style.display = 'none';
    }

    showEmptyState() {
        document.getElementById('shortcode-list-loading').style.display = 'none';
        document.getElementById('shortcode-list').style.display = 'none';
        document.getElementById('shortcode-list-empty').style.display = 'block';
    }

    async openEditor(id = null) {
        this.currentEditId = id;
        const modal = document.getElementById('shortcode-editor-modal');
        const title = document.getElementById('modal-title-text');
        const deleteBtn = document.getElementById('delete-current-shortcode-btn');
        const saveBtn = document.getElementById('save-btn-text');

        if (id) {
            title.textContent = 'Shortcode bearbeiten';
            deleteBtn.style.display = 'block';
            saveBtn.textContent = 'Aktualisieren';
            
            try {
                const response = await this.apiRequest('load_preset', { id });
                if (response.success && response.data) {
                    this.populateForm(response.data);
                }
            } catch (error) {
                console.error('Error loading preset:', error);
                this.showToast('error', 'Fehler', 'Shortcode konnte nicht geladen werden.');
                return;
            }
        } else {
            title.textContent = 'Neuen Shortcode erstellen';
            deleteBtn.style.display = 'none';
            saveBtn.textContent = 'Erstellen';
            this.resetForm();
        }

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Initial preview
        setTimeout(() => {
            this.updatePreview();
        }, 100);
    }

    closeEditor() {
        const modal = document.getElementById('shortcode-editor-modal');
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        this.currentEditId = null;
        if (this.previewTimer) {
            clearTimeout(this.previewTimer);
        }
    }

    isModalOpen() {
        const modal = document.getElementById('shortcode-editor-modal');
        return modal && modal.style.display === 'flex';
    }

    populateForm(data) {
        document.getElementById('edit-name').value = data.name || '';
        document.getElementById('edit-view').value = data.view || 'cards';
        document.getElementById('edit-limit').value = data.limit_count || 10;
        document.getElementById('edit-from-days').value = data.from_days || -7;
        document.getElementById('edit-to-days').value = data.to_days || 90;
        document.getElementById('edit-show-past').checked = data.show_past === 'true';
        document.getElementById('edit-order-dir').value = data.order_dir || 'asc';

        // Calendar selection
        const calendarIds = this.parseCalendarIds(data.calendar_ids);
        const allCalendarsCheckbox = document.getElementById('edit-all-calendars');
        
        if (calendarIds.length === 0) {
            allCalendarsCheckbox.checked = true;
            document.querySelectorAll('.calendar-checkbox').forEach(cb => {
                cb.checked = false;
            });
        } else {
            allCalendarsCheckbox.checked = false;
            document.querySelectorAll('.calendar-checkbox').forEach(cb => {
                cb.checked = calendarIds.includes(cb.value);
            });
        }

        // Show fields
        const showFields = this.parseShowFields(data.show_fields);
        document.querySelectorAll('.field-checkbox').forEach(cb => {
            cb.checked = showFields.includes(cb.value);
        });
    }

    resetForm() {
        document.getElementById('shortcode-editor-form').reset();
        document.getElementById('edit-view').value = 'cards';
        document.getElementById('edit-limit').value = 10;
        document.getElementById('edit-from-days').value = -7;
        document.getElementById('edit-to-days').value = 90;
        document.getElementById('edit-order-dir').value = 'asc';
        document.getElementById('edit-all-calendars').checked = true;
        
        // Reset checkboxes
        document.querySelectorAll('.calendar-checkbox').forEach(cb => {
            cb.checked = false;
        });
        
        document.querySelectorAll('.field-checkbox').forEach(cb => {
            cb.checked = ['date', 'time', 'calendar'].includes(cb.value);
        });
    }

    async saveShortcode() {
        const formData = this.getFormData();
        
        if (!this.validateForm(formData)) {
            return;
        }

        const saveBtn = document.getElementById('save-shortcode-btn');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<span class="spinner is-active" style="float: none; margin: 0 5px 0 0;"></span>Speichern...';
        saveBtn.disabled = true;

        try {
            const action = this.currentEditId ? 'update_preset' : 'save_preset';
            const requestData = this.currentEditId ? 
                { id: this.currentEditId, ...formData } : 
                formData;

            const response = await this.apiRequest(action, requestData);
            
            if (response.success) {
                this.showToast('success', 'Erfolg', 
                    this.currentEditId ? 'Shortcode wurde aktualisiert.' : 'Shortcode wurde erstellt.');
                this.closeEditor();
                this.loadShortcodes();
            } else {
                this.showToast('error', 'Fehler', response.data || 'Shortcode konnte nicht gespeichert werden.');
            }
        } catch (error) {
            console.error('Error saving shortcode:', error);
            this.showToast('error', 'Fehler', 'Ein unerwarteter Fehler ist aufgetreten.');
        } finally {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    }

    async deleteCurrentShortcode() {
        if (!this.currentEditId) return;

        const name = document.getElementById('edit-name').value;
        if (!confirm(`Möchten Sie den Shortcode "${name}" wirklich löschen?`)) {
            return;
        }

        try {
            const response = await this.apiRequest('delete_preset', { id: this.currentEditId });
            
            if (response.success) {
                this.showToast('success', 'Erfolg', 'Shortcode wurde gelöscht.');
                this.closeEditor();
                this.loadShortcodes();
            } else {
                this.showToast('error', 'Fehler', 'Shortcode konnte nicht gelöscht werden.');
            }
        } catch (error) {
            console.error('Error deleting shortcode:', error);
            this.showToast('error', 'Fehler', 'Ein unerwarteter Fehler ist aufgetreten.');
        }
    }

    async deleteShortcode(id, name) {
        if (!confirm(`Möchten Sie den Shortcode "${name}" wirklich löschen?`)) {
            return;
        }

        try {
            const response = await this.apiRequest('delete_preset', { id });
            
            if (response.success) {
                this.showToast('success', 'Erfolg', 'Shortcode wurde gelöscht.');
                this.loadShortcodes();
            } else {
                this.showToast('error', 'Fehler', 'Shortcode konnte nicht gelöscht werden.');
            }
        } catch (error) {
            console.error('Error deleting shortcode:', error);
            this.showToast('error', 'Fehler', 'Ein unerwarteter Fehler ist aufgetreten.');
        }
    }

    async updatePreview() {
        const formData = this.getFormData();
        const previewContainer = document.getElementById('preview-content');
        const previewLoading = document.getElementById('preview-loading');
        const shortcodeInput = document.getElementById('generated-shortcode');

        // Update generated shortcode
        const generatedShortcode = this.generateShortcodeString(formData);
        shortcodeInput.value = generatedShortcode;

        // Show loading
        previewLoading.style.display = 'flex';

        try {
            // Simulate preview API call (you'll need to implement this endpoint)
            const response = await this.apiRequest('preview_shortcode', formData);
            
            if (response.success && response.data) {
                previewContainer.innerHTML = response.data;
            } else {
                previewContainer.innerHTML = '<p style="text-align: center; color: #646970; padding: 40px;">Vorschau nicht verfügbar</p>';
            }
        } catch (error) {
            console.error('Error updating preview:', error);
            previewContainer.innerHTML = '<p style="text-align: center; color: #d63638; padding: 40px;">Fehler beim Laden der Vorschau</p>';
        } finally {
            previewLoading.style.display = 'none';
        }
    }

    getFormData() {
        const allCalendars = document.getElementById('edit-all-calendars').checked;
        const selectedCalendars = allCalendars ? [] : 
            Array.from(document.querySelectorAll('.calendar-checkbox:checked')).map(cb => cb.value);
        
        const selectedFields = Array.from(document.querySelectorAll('.field-checkbox:checked')).map(cb => cb.value);
        // Always include title
        if (!selectedFields.includes('title')) {
            selectedFields.unshift('title');
        }

        return {
            name: document.getElementById('edit-name').value.trim(),
            view: document.getElementById('edit-view').value,
            limit_count: parseInt(document.getElementById('edit-limit').value) || 10,
            calendar_ids: selectedCalendars.join(','),
            from_days: parseInt(document.getElementById('edit-from-days').value) || -7,
            to_days: parseInt(document.getElementById('edit-to-days').value) || 90,
            show_past: document.getElementById('edit-show-past').checked ? 'true' : 'false',
            order_dir: document.getElementById('edit-order-dir').value,
            show_fields: selectedFields.join(',')
        };
    }

    validateForm(formData) {
        if (!formData.name) {
            this.showToast('error', 'Validierung', 'Bitte geben Sie einen Namen ein.');
            document.getElementById('edit-name').focus();
            return false;
        }

        if (formData.limit_count < 1 || formData.limit_count > 100) {
            this.showToast('error', 'Validierung', 'Die Anzahl der Termine muss zwischen 1 und 100 liegen.');
            document.getElementById('edit-limit').focus();
            return false;
        }

        return true;
    }

    generateShortcodeString(data) {
        const params = [];
        
        if (data.view && data.view !== 'cards') {
            params.push(`view="${data.view}"`);
        }
        
        if (data.limit_count && data.limit_count !== 10) {
            params.push(`limit="${data.limit_count}"`);
        }
        
        if (data.calendar_ids) {
            params.push(`calendar_ids="${data.calendar_ids}"`);
        }
        
        if (data.from_days && data.from_days !== -7) {
            params.push(`from_days="${data.from_days}"`);
        }
        
        if (data.to_days && data.to_days !== 90) {
            params.push(`to_days="${data.to_days}"`);
        }
        
        if (data.show_past === 'true') {
            params.push('show_past="true"');
        }
        
        if (data.order_dir && data.order_dir !== 'asc') {
            params.push(`order="${data.order_dir}"`);
        }
        
        if (data.show_fields && data.show_fields !== 'title,date,time,calendar') {
            params.push(`show_fields="${data.show_fields}"`);
        }

        return `[rcts_events${params.length > 0 ? ' ' + params.join(' ') : ''}]`;
    }

    toggleAllCalendars(checked) {
        document.querySelectorAll('.calendar-checkbox').forEach(cb => {
            cb.checked = !checked;
        });
        this.schedulePreviewUpdate();
    }

    updateAllCalendarsState() {
        const checkedCalendars = document.querySelectorAll('.calendar-checkbox:checked').length;
        const allCalendarsCheckbox = document.getElementById('edit-all-calendars');
        allCalendarsCheckbox.checked = checkedCalendars === 0;
    }

    copyShortcode() {
        const input = document.getElementById('generated-shortcode');
        this.copyToClipboard(input.value);
    }

    copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                this.showToast('success', 'Kopiert', 'Shortcode wurde in die Zwischenablage kopiert.');
            });
        } else {
            // Fallback
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            this.showToast('success', 'Kopiert', 'Shortcode wurde in die Zwischenablage kopiert.');
        }
    }

    filterShortcodes(searchTerm) {
        const cards = document.querySelectorAll('.shortcode-card, .shortcode-list-item');
        const term = searchTerm.toLowerCase();

        cards.forEach(card => {
            const title = card.querySelector('.shortcode-card-title, .list-item-title');
            const code = card.querySelector('.shortcode-preview-code');
            
            const titleText = title ? title.textContent.toLowerCase() : '';
            const codeText = code ? code.textContent.toLowerCase() : '';
            
            const matches = titleText.includes(term) || codeText.includes(term);
            card.style.display = matches ? '' : 'none';
        });
    }

    async apiRequest(action, data = {}) {
        const response = await fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: `repro_ct_suite_${action}`,
                nonce: repro_ct_suite_ajax.nonce,
                ...data
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    showToast(type, title, message) {
        const container = document.getElementById('toast-container');
        const id = Date.now();
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon">
                <span class="dashicons dashicons-${type === 'success' ? 'yes-alt' : type === 'error' ? 'dismiss' : 'warning'}"></span>
            </div>
            <div class="toast-content">
                <div class="toast-title">${this.escapeHtml(title)}</div>
                <div class="toast-message">${this.escapeHtml(message)}</div>
            </div>
            <button class="toast-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        `;

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.removeToast(toast);
        });

        container.appendChild(toast);

        // Show animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            this.removeToast(toast);
        }, 5000);
    }

    removeToast(toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    // Helper functions
    parseCalendarIds(calendarIds) {
        return calendarIds ? calendarIds.split(',').filter(id => id.trim()) : [];
    }

    parseShowFields(showFields) {
        return showFields ? showFields.split(',').filter(field => field.trim()) : [];
    }

    getViewLabel(view) {
        const labels = {
            'list': 'Liste (einfach)',
            'list-grouped': 'Liste (gruppiert)',
            'cards': 'Kacheln'
        };
        return labels[view] || view;
    }

    formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('de-DE');
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    extractShortcodesFromDOM() {
        // Helper to extract current shortcode data from DOM for re-rendering
        // This is a simplified version - in real app you'd store the data
        return [];
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.shortcodeManager = new ShortcodeManager();
});