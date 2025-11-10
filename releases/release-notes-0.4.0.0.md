# Release 0.4.0.0 - Unified Sync System

## 🚀 MAJOR UPDATE: Neues einheitliches Sync-System

Diese Version bringt eine grundlegende Überarbeitung der Synchronisations-Architektur mit intelligenter 2-Phasen-Synchronisation.

### ⚡ Wichtigste Verbesserungen

- **Einheitliches Sync-System**: Ersetzt die komplexe Dual-Sync-Architektur durch einen intelligenten 2-Phasen-Ansatz
- **Code-Vereinfachung**: Reduzierung von 600+ Zeilen auf 304 Zeilen sauberen Code
- **Intelligente Duplikats-Vermeidung**: appointment_id-Tracking verhindert Dopplungen zwischen Events und Appointments APIs
- **Automatische Migration**: DB V6 mit sicherer Orphaned-Appointment-Migration

### 📋 Neue Features

- **2-Phasen-Workflow**: 
  - Phase 1: Events API sammelt appointment_ids
  - Phase 2: Appointments API importiert zusätzliche Termine
- **Admin-UI-Aufräumung**: Konsolidierte Menü-Struktur ohne redundante Handler
- **Umfassendes Logging**: Detaillierte Statistiken für beide Sync-Phasen
- **Automatische Schema-Updates**: Sichere DB-Migration mit Rollback-Funktionalität

### 🔧 Breaking Changes

- Alte separate Events- und Appointments-Sync-Services wurden durch einheitlichen Service ersetzt
- Admin-Handler für Appointments wurden konsolidiert

### ⚠️ Nach dem Update

**WICHTIG**: Nach der Installation ersten Sync ausführen, um das neue einheitliche System zu aktivieren.

### 📦 Installation

1. Plugin deaktivieren
2. Alte Version löschen
3. Neue Version hochladen
4. Plugin aktivieren
5. Ersten Sync durchführen

### 🛠️ Technische Details

- **Architecture**: Repository-Pattern mit Service-Layer
- **Migration**: Automatische DB V6 Migration mit bis zu 50 automatisch migrierten Orphaned-Appointments
- **Safety**: Umfangreiche Migrations-Sicherheitsprüfungen
- **Performance**: Verbesserte Wartbarkeit und reduzierte Komplexität

---

**Unterstützung**: Bei Fragen zur Migration erstelle bitte ein Issue im Repository.