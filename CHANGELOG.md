# Changelog

All notable changes to the **Assets Cleaner** plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## 🇫🇷 Version Française

### [1.0.4] - 2025-10-23 - Restauration automatique depuis la corbeille ♻️ 🚀

**Nouvelle fonctionnalité majeure :**
- ♻️ **NOUVEAU** : Restauration automatique depuis la corbeille !
  - Nouvelle tâche cron : `RestoreInventoriedAssets`
  - Si un actif en corbeille est détecté à nouveau par l'inventaire (dans les 7 jours par défaut), il est automatiquement restauré
  - Évite les faux positifs lors de pannes réseau temporaires ou maintenances
  - Entièrement configurable depuis l'interface
- ⚙️ **CONFIGURATION** : Nouvelles options ajoutées
  - "Activer la restauration automatique" (activé par défaut)
  - "Délai de restauration" : nombre de jours pour vérifier les mises à jour d'inventaire récentes (défaut: 7)
- 📝 **LOGS** : Logs détaillés de restauration
  - Affiche le nom, ID et date du dernier inventaire pour chaque actif restauré
  - Logs d'échec de restauration si problème
- 🎨 **INTERFACE** : Nouvelle section dans la configuration
  - Section "Restauration automatique depuis la corbeille" avec explication
  - Icône de l'onglet changée en `ti-recycle` ♻️
  - Nom de l'onglet : "Nettoyage éléments"

**Scénario d'utilisation :**
```
Jour 0  : Imprimante répond à l'inventaire
Jour 30 : Plus de réponse → Mise en corbeille
Jour 35 : Imprimante rallumée → Inventaire la détecte
        → ✅ Restauration automatique !
```

**Documentation :**
- 📚 README mis à jour avec le nouveau workflow en 3 étapes
- 📋 INSTALL.md mis à jour avec la nouvelle configuration
- 🌍 Toutes les traductions ajoutées (FR et POT)

**⚠️ Migration depuis v1.0.3** :
- La restauration automatique est **activée par défaut**
- Aucune action requise, mais vous pouvez la désactiver dans la configuration si nécessaire

### [1.0.3] - 2025-10-23 - Simplification logique et logging amélioré 🚀

**Améliorations majeures :**
- 🔄 **REFONTE** : Simplification complète de la logique des tâches cron
  - Suppression de la dépendance au statut "Décommissionné (Auto)"
  - Logique simplifiée en 2 étapes : mise en corbeille puis purge
  - Plus besoin de créer un statut spécifique !
- 📝 **LOGS** : Amélioration significative du système de logs
  - Tous les logs sont maintenant écrits dans `files/_log/assetscleaner.log`
  - Logs détaillés avec noms d'actifs, IDs et dates de dernière mise à jour
  - Messages de résumé par type d'actif (succès/échecs)
- 🐛 **FIX** : Correction des requêtes SQL
  - Suppression de la condition `states_id > 0` qui excluait tous les actifs
  - Suppression de la clause OR complexe sur `last_inventory_update`
  - Requêtes SQL simplifiées et plus performantes
- 🧹 **CLEANUP** : Suppression du code obsolète
  - Méthode `getOutOfOrderStateId()` supprimée
  - Configuration simplifiée (suppression des champs first_action/second_action)
  - Interface utilisateur épurée

**Documentation :**
- 📚 README mis à jour avec les nouveaux workflows
- 🔧 Script de diagnostic SQL ajouté (`tools/debug_sql_queries.sql`)
- 🌍 Traductions mises à jour
- 📦 Compilateur .mo en PHP pur (plus de dépendance à msgfmt)

**⚠️ Migration depuis v1.0.2** :
Aucune action requise ! Le plugin fonctionne immédiatement sans configuration de statut.

### [1.0.2] - 2025-10-23 - Corrections contexte et nomenclature 🔧

**Corrections :**
- 🐛 **FIX** : Correction du contexte de configuration
  - Changement de `plugin:assetscleaner` vers `assetscleaner` (convention GLPI standard)
  - Amélioration de la gestion du retour de `saveConfig()` pour éviter les faux négatifs
- 🔗 **IMPORTANT** : Mise à jour de toutes les URLs et noms
  - Repository GitHub : `SpyKeeR/assetscleaner` (minuscules obligatoires)
  - Package Composer : `spykeer/assetscleaner`
  - Toutes les URLs mises à jour en conséquence
- 📝 Mise à jour de la documentation complète

**⚠️ Migration depuis v1.0.1** :
Si vous avez installé v1.0.1, la configuration utilisait `plugin:assetscleaner`. Après la mise à jour :
1. Allez dans Configuration > Général > Assets Cleaner
2. Reconfigurez vos paramètres (l'ancien contexte ne sera pas migré automatiquement)
3. Vos anciennes valeurs peuvent être supprimées manuellement de la table `glpi_configs`

### [1.0.1] - 2025-10-22 - Correctifs critiques 🔧

**Corrections :**
- 🐛 **CRITIQUE** : Correction de l'erreur "Error saving configuration"
  - La fonction `Config::setConfigurationValues()` est maintenant appelée correctement (une seule fois avec toutes les valeurs)
  - Suppression de la logique de boucle qui causait les échecs de sauvegarde
- 🧹 Suppression de tous les `error_log()` qui causaient des problèmes de token CRLF
- 🔗 Uniformisation des URLs GitHub dans tous les fichiers d'en-tête
- 📦 Correction du nom du package Composer : `spykeer/glpi-assetscleaner`
- 🌍 Mise à jour des fichiers de traduction (.po et .pot)
- 📝 Ajout de documentation développeur (DEVELOPMENT.md)

**Améliorations :**
- Code plus propre et maintenable
- Gestion d'erreurs simplifiée
- Documentation technique complète

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

### [1.0.4] - 2025-10-23 - Automatic restoration from trash ♻️ 🚀

#### Added
- ♻️ **NEW**: Automatic restoration from trash!
  - New cron task: `RestoreInventoriedAssets`
  - If a trashed asset is detected again by inventory (within 7 days by default), it's automatically restored
  - Prevents false positives during temporary network outages or maintenance
  - Fully configurable from the interface
- ⚙️ **CONFIGURATION**: New settings added
  - "Enable automatic restoration" (enabled by default)
  - "Restore threshold": number of days to check for recent inventory updates (default: 7)
- 📝 **LOGS**: Detailed restoration logs
  - Shows name, ID, and last inventory date for each restored asset
  - Logs restoration failures if issues occur
- 🎨 **INTERFACE**: New configuration section
  - "Automatic restoration from trash" section with explanation
  - Tab icon changed to `ti-recycle` ♻️
  - Tab name: "Assets cleanup"

#### Use Case
```
Day 0:  Printer responds to inventory
Day 30: No response → Moved to trash
Day 35: Printer turned back on → Inventory detects it
        → ✅ Automatically restored!
```

#### Documentation
- 📚 README updated with new 3-stage workflow
- 📋 INSTALL.md updated with new configuration
- 🌍 All translations added (FR and POT)

**⚠️ Migration from v1.0.3**:
- Automatic restoration is **enabled by default**
- No action required, but you can disable it in configuration if needed

### [1.0.2] - 2025-10-23 - Context and naming fixes 🔧

#### Fixed
- 🐛 **FIX**: Configuration context correction
  - Changed from `plugin:assetscleaner` to `assetscleaner` (GLPI standard convention)
  - Improved `saveConfig()` return handling to avoid false negatives
- 🔗 **IMPORTANT**: All URLs and names updated
  - GitHub repository: `SpyKeeR/assetscleaner` (lowercase required)
  - Composer package: `spykeer/assetscleaner`
  - All URLs updated accordingly
- 📝 Complete documentation update

**⚠️ Migration from v1.0.1**:
If you installed v1.0.1, configuration used `plugin:assetscleaner`. After update:
1. Go to Setup > General > Assets Cleaner tab
2. Reconfigure your settings (old context won't migrate automatically)
3. Old values can be manually deleted from `glpi_configs` table

### [1.0.1] - 2025-10-22 - Critical Fixes 🔧

#### Fixed
- 🐛 **CRITICAL**: Fixed "Error saving configuration" issue
  - `Config::setConfigurationValues()` now called correctly (once with all values)
  - Removed loop logic that was causing save failures
- 🧹 Removed all `error_log()` calls causing CRLF token issues
- 🔗 Standardized GitHub URLs across all header files
- 📦 Fixed Composer package name: `spykeer/glpi-assetscleaner`
- 🌍 Updated translation files (.po and .pot)
- 📝 Added developer documentation (DEVELOPMENT.md)

#### Improved
- Cleaner and more maintainable code
- Simplified error handling
- Comprehensive technical documentation

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
| 1.0.4 | 2025-10-23 | 11.0.x | 8.2+ |
| 1.0.3 | 2025-10-23 | 11.0.x | 8.2+ |
| 1.0.2 | 2025-10-23 | 11.0.x | 8.2+ |
| 1.0.1 | 2025-10-22 | 11.0.x | 8.2+ |
| 1.0.0 | 2025-10-20 | 11.0.x | 8.2+ |


## Support

For questions, bug reports, or feature requests, please [open an issue](https://github.com/SpyKeeR/glpi-assetscleaner/issues).
