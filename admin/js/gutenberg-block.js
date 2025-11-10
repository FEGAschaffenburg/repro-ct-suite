/**
 * Gutenberg Block für Repro CT Suite Events
 * Ermöglicht die Auswahl verschiedener Event-Ansichten im Block-Editor
 */

(function (blocks, element, blockEditor, components) {
	const el = element.createElement;
	const { registerBlockType } = blocks;
	const { InspectorControls, useBlockProps } = blockEditor;
	const { PanelBody, SelectControl, TextControl, ToggleControl, RangeControl } = components;

	registerBlockType('repro-ct-suite/events', {
		title: 'ChurchTools Termine',
		icon: 'calendar-alt',
		category: 'widgets',
		attributes: {
			view: {
				type: 'string',
				default: 'list'
			},
			limit: {
				type: 'number',
				default: 10
			},
			calendarIds: {
				type: 'string',
				default: ''
			},
			fromDays: {
				type: 'number',
				default: 0
			},
			toDays: {
				type: 'number',
				default: 90
			},
			showPast: {
				type: 'boolean',
				default: false
			},
			showFields: {
				type: 'string',
				default: 'date,time,location,description'
			}
		},

		edit: function (props) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps();

			const viewOptions = [
				{ label: '📋 Kompakte Liste', value: 'compact' },
				{ label: '📄 Einfache Liste', value: 'list' },
				{ label: '📊 Mittlere Liste', value: 'medium' },
				{ label: '📅 Timeline (gruppiert)', value: 'list-grouped' },
				{ label: '🎴 Karten-Grid', value: 'cards' },
				{ label: '📌 Sidebar Widget', value: 'sidebar' }
			];

			const viewDescriptions = {
				compact: 'Ultra kompakt: Datum, Zeit, Titel in einer Zeile',
				list: 'Modern: Große Datums-Box mit Details',
				medium: 'Ausgewogen: Datum, Zeit, Titel, Ort',
				'list-grouped': 'Timeline: Nach Datum gruppiert mit Zeitlinie',
				cards: 'Grid: Karten-Layout mit Hover-Effekten',
				sidebar: 'Widget: Optimiert für schmale Bereiche'
			};

			return el(
				'div',
				blockProps,
				[
					el(
						InspectorControls,
						null,
						el(
							PanelBody,
							{ title: 'Ansicht & Layout', initialOpen: true },
							[
								el(SelectControl, {
									label: 'Ansicht wählen',
									value: attributes.view,
									options: viewOptions,
									onChange: (value) => setAttributes({ view: value }),
									help: viewDescriptions[attributes.view]
								}),
								el(RangeControl, {
									label: 'Anzahl Termine',
									value: attributes.limit,
									onChange: (value) => setAttributes({ limit: value }),
									min: 1,
									max: 50
								})
							]
						),
						el(
							PanelBody,
							{ title: 'Filter-Einstellungen', initialOpen: false },
							[
								el(TextControl, {
									label: 'Kalender-IDs',
									value: attributes.calendarIds,
									onChange: (value) => setAttributes({ calendarIds: value }),
									help: 'Komma-getrennt, z.B. "1,2,3" (leer = alle)'
								}),
								el(RangeControl, {
									label: 'Von (Tage ab heute)',
									value: attributes.fromDays,
									onChange: (value) => setAttributes({ fromDays: value }),
									min: -365,
									max: 365
								}),
								el(RangeControl, {
									label: 'Bis (Tage ab heute)',
									value: attributes.toDays,
									onChange: (value) => setAttributes({ toDays: value }),
									min: 0,
									max: 365
								}),
								el(ToggleControl, {
									label: 'Vergangene Termine anzeigen',
									checked: attributes.showPast,
									onChange: (value) => setAttributes({ showPast: value })
								})
							]
						),
						el(
							PanelBody,
							{ title: 'Anzuzeigende Felder', initialOpen: false },
							el(TextControl, {
								label: 'Felder',
								value: attributes.showFields,
								onChange: (value) => setAttributes({ showFields: value }),
								help: 'Komma-getrennt: date, time, location, description'
							})
						)
					),
					el(
						'div',
						{
							style: {
								padding: '30px',
								background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
								borderRadius: '12px',
								color: '#ffffff',
								textAlign: 'center'
							}
						},
						[
							el('div', {
								style: {
									fontSize: '48px',
									marginBottom: '16px'
								}
							}, '📅'),
							el('h3', {
								style: {
									margin: '0 0 8px 0',
									fontSize: '20px',
									fontWeight: '600'
								}
							}, 'ChurchTools Termine'),
							el('p', {
								style: {
									margin: '0',
									opacity: '0.9',
									fontSize: '14px'
								}
							}, `Ansicht: ${viewOptions.find(opt => opt.value === attributes.view)?.label || 'Standard'}`),
							el('p', {
								style: {
									margin: '8px 0 0 0',
									opacity: '0.8',
									fontSize: '13px'
								}
							}, `${attributes.limit} Termine · ${attributes.showPast ? 'inkl. vergangene' : 'nur zukünftige'}`)
						]
					)
				]
			);
		},

		save: function (props) {
			const { attributes } = props;
			
			// Shortcode-String erstellen
			let shortcode = '[repro_ct_suite_events';
			shortcode += ` view="${attributes.view}"`;
			shortcode += ` limit="${attributes.limit}"`;
			
			if (attributes.calendarIds) {
				shortcode += ` calendar_ids="${attributes.calendarIds}"`;
			}
			
			if (attributes.fromDays !== 0) {
				shortcode += ` from_days="${attributes.fromDays}"`;
			}
			
			if (attributes.toDays !== 90) {
				shortcode += ` to_days="${attributes.toDays}"`;
			}
			
			if (attributes.showPast) {
				shortcode += ' show_past="true"';
			}
			
			if (attributes.showFields !== 'date,time,location,description') {
				shortcode += ` show_fields="${attributes.showFields}"`;
			}
			
			shortcode += ']';
			
			// Shortcode als HTML-Kommentar speichern, damit WordPress es verarbeitet
			return el('div', null, shortcode);
		}
	});
})(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components
);
