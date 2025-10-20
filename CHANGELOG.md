# Changelog

All notable changes to the **Assets Cleaner** plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## 🇫🇷 Version Française

### [1.0.0] - 2025-10-20 - Version Initiale 🎉

**Nouveautés :**
- ✨ Nettoyage automatique des actifs obsolètes (Imprimantes, Équipements réseau, Téléphones)
- 🔄 Processus en 2 étapes : Changement de statut + corbeille, puis purge
- ⚙️ Interface de configuration flexible dans GLPI
- 📊 Deux tâches automatiques (CleanOldAssets, PurgeOldTrash)
- 🗑️ Suppression optionnelle des éléments liés (ports, infocom, associations)
- 🌍 Traductions françaises complètes
- 📝 Journalisation détaillée de toutes les actions
- 🔍 Détection intelligente des états (priorité "Hors Parc (Auto)")
- ⏱️ Délais configurables (période d'inactivité, rétention corbeille)

**Compatibilité :**
- GLPI 11.0.0 - 11.0.99
- PHP 8.2+

**Fonctionnalités prévues :**
- [ ] Notifications email
- [ ] Widget tableau de bord
- [ ] Export des rapports en CSV
- [ ] Support d'autres types (Ordinateurs, Moniteurs)
- [ ] Liste blanche/noire
- [ ] Mode simulation (dry-run)
- [ ] Traductions anglaises

---

## 🇬🇧 English Version

## [1.0.0] - 2025-10-20

### 🎉 Initial Release

#### Added
- ✨ Automated cleanup of obsolete assets (Printers, Network Equipment, Phones)
- 🔄 Two-stage deletion process (status change + trash, then purge)
- ⚙️ Flexible configuration interface in GLPI
- 📊 Two automated tasks (CleanOldAssets, PurgeOldTrash)
- 🗑️ Optional deletion of related items (ports, infocom, associations)
- 🌍 French translations (fr_FR)
- 📝 Detailed logging of all actions
- 🔍 Smart state detection (prioritizes "Hors Parc (Auto)")
- ⏱️ Configurable delays (inactive period, trash retention)
- 🎛️ Asset type selection (user-configurable)

#### Technical Features
- Compatible with GLPI 11.0.0 - 11.0.99
- Requires PHP 8.2+
- PSR-4 autoloading
- Secure database queries via GLPI API
- Comprehensive error handling
- CLI and web-based cron execution

#### Documentation
- 📖 Complete README with use cases
- 📋 Installation guide (INSTALL.md)
- 📝 SQL monitoring queries
- 🔒 Security warnings and best practices

---

## [Unreleased]

### Planned Features
- [ ] Email notifications for cleanup actions
- [ ] Dashboard widget with statistics
- [ ] Export cleanup reports to CSV
- [ ] Support for additional asset types (Computers, Monitors)
- [ ] Whitelist/blacklist functionality
- [ ] Dry-run mode for testing
- [ ] English translations

---

## Version History

| Version | Date | GLPI Version | PHP Version |
|---------|------|--------------|-------------|
| 1.0.0 | 2025-10-20 | 11.0.x | 8.2+ |


## Support

For questions, bug reports, or feature requests, please [open an issue](https://github.com/SpyKeeR/glpi-assetscleaner/issues).
