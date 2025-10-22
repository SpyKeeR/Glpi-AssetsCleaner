<div align="center">

# 🧹 Assets Cleaner for GLPI

### Automated cleanup of obsolete inventory assets

[![GLPI Version](https://img.shields.io/badge/GLPI-%E2%89%A5%2011.0.0-blue)](https://glpi-project.org/)
[![PHP Version](https://img.shields.io/badge/PHP-%E2%89%A5%208.2-purple)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPLv2-green)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.0.0-orange)](https://github.com/pluginsGLPI/assetscleaner/releases)

</div>

---

## 🇫🇷 Version Française

**Assets Cleaner** nettoie automatiquement les actifs obsolètes (imprimantes, équipements réseau, téléphones IP) qui ne sont plus détectés par les outils d'inventaire.

### ✨ Fonctionnalités principales
- 🔄 **Nettoyage en deux étapes** : Changement de statut + mise en corbeille, puis purge définitive
- ⚙️ **Configuration flexible** : Délais personnalisables (défaut: 30j + 60j)
- 🎯 **Types d'actifs** : Imprimantes, téléphones IP, équipements réseau
- 🧹 **Suppression complète** : Éléments liés (ports, contrats, documents) optionnelle
- 📊 **Actions automatiques** : 2 tâches cron intégrées
- 🌍 **Traductions** : Interface française complète

### 🚀 Installation rapide
```bash
cd /path/to/glpi/plugins
git clone https://github.com/SpyKeeR/assetscleaner.git assetscleaner
```
Puis : **Configuration > Plugins > Installer > Activer**

**💡 Important** : Créez l'état "Hors Parc (Auto)" dans Configuration > Intitulés > État

[📖 Documentation complète ci-dessous](#-about) • [🇬🇧 English version below](#-about)

---


---

## 🇬🇧 English Version

<div align="center">

*Keep your GLPI inventory clean by automatically removing assets that haven't been updated by inventory tools*

[Features](#-features) • [Installation](#-installation) • [Configuration](#-configuration) • [Documentation](#-documentation)

---

</div>

## 📖 About

**Assets Cleaner** is a GLPI plugin that automatically manages obsolete assets (printers, network equipment, IP phones) that are no longer detected by inventory tools like GLPI Agent or NetDiscovery.

### 🎯 Why use this plugin?

When using automated inventory tools (NetInventory, NetDiscovery), assets can become obsolete:
- 🖨️ Printers moved or unplugged
- 🔌 Network equipment decommissioned
- 📞 IP phones replaced

Without maintenance, your GLPI database accumulates **ghost assets** that clutter your inventory and impact performance.

**Assets Cleaner solves this automatically!**

---

## ✨ Features

### 🔄 Two-Stage Automated Cleanup

#### Stage 1: Identification & Isolation
After **X days** without inventory update, the plugin automatically:
- 📍 Changes asset status to "Out of Service (Auto)"
- 🗑️ Moves asset to trash
- 📝 Logs all actions for audit

#### Stage 2: Permanent Deletion (Optional)
After **Y additional days** in trash, the plugin:
- 💥 Permanently deletes the asset
- 🧹 Removes related items (network ports, financial data, associations)
- 📊 Maintains database integrity

### ⚙️ Flexible Configuration

| Setting | Description | Default |
|---------|-------------|---------|
| **Inactive Delay** | Days before marking as obsolete | 30 days |
| **Trash Delay** | Days in trash before purge | 60 days |
| **Asset Types** | Select which types to clean | Printers only |
| **Related Items** | Also delete ports, contracts, etc. | Enabled |

### 🎛️ Supported Asset Types

- 🖨️ **Printers**
- 📞 **IP Phones**
- 🌐 **Network Equipment**

---

## 🚀 Installation

### Prerequisites

- GLPI ≥ 11.0.0
- PHP ≥ 8.2
- GLPI Agent or NetInventory/NetDiscovery configured

### Quick Install

```bash
# Navigate to GLPI plugins directory
cd /path/to/glpi/plugins

# Clone the repository
git clone https://github.com/SpyKeeR/assetscleaner.git assetscleaner

# Or download and extract manually
# wget https://github.com/SpyKeeR/assetscleaner/archive/refs/heads/main.zip
# unzip main.zip
# mv assetscleaner-main assetscleaner
```

### Activation

1. 🔐 Log in to GLPI with an **administrator** account
2. 🔧 Navigate to **Setup > Plugins**
3. 🔍 Find **Assets Cleaner** in the list
4. ⚡ Click **Install** then **Activate**

### Post-Installation

Create the "Out of Service (Auto)" state:
1. Go to **Setup > Dropdowns > Status**
2. Add new status: **"Hors Parc (Auto)"** or **"Out of Service (Auto)"**
3. Save

> 💡 **Tip:** The plugin will fallback to existing "out of service" or "hors service" states if the specific one doesn't exist.

---

## ⚙️ Configuration

### Basic Setup

1. Navigate to **Setup > General > Assets Cleaner tab**
2. Configure your preferences:

```
✓ Enable automatic cleaning
📅 Inactive delay: 30 days (recommended)
🗑️ Trash delay: 60 days (recommended)
📦 Asset types: [✓] Printers [✓] Network Equipment [✓] Phones
🔗 Delete related items: Enabled
```

3. Click **Save**

### Automated Tasks (Cron)

The plugin registers two automatic tasks. **You must enable them!**

#### Configure Cron Tasks

1. Go to **Setup > Automatic actions**
2. Search for **"AssetsCleaner"**
3. Enable both tasks:

| Task | Frequency | Description |
|------|-----------|-------------|
| **CleanOldAssets** | Daily | Marks obsolete assets and moves to trash |
| **PurgeOldTrash** | Daily | Permanently deletes old trashed items |

#### CLI Execution (Recommended)

For better performance, run via command line:

```bash
# Run both tasks
php bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronCleanOldAssets'
php bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronPurgeOldTrash'
```

Add to your system crontab:
```cron
# Daily at 2 AM
0 2 * * * php /path/to/glpi/bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronCleanOldAssets'
0 3 * * * php /path/to/glpi/bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronPurgeOldTrash'
```

---

## 🔧 How It Works

### Detection Mechanism

The plugin uses the **`last_inventory_update`** field from each asset to determine if it's obsolete.

```sql
-- Assets are considered obsolete when:
WHERE last_inventory_update < (NOW() - configured_delay)
   OR last_inventory_update IS NULL
```

### Stage 1: CleanOldAssets Task

**When an asset is detected as obsolete:**

1. 🔍 **Status Change**
   - Searches for state "Hors Parc (Auto)" (priority)
   - Fallback to "hors service" or "out of order"
   - Updates `states_id` field

2. 🗑️ **Move to Trash**
   - Sets `is_deleted = 1`
   - Keeps asset data intact
   - Asset disappears from normal views

> ⚡ **Both actions happen simultaneously** to ensure proper asset isolation!

### Stage 2: PurgeOldTrash Task (Optional)

**When trash retention period expires:**

1. 💥 **Permanent Deletion**
   - Deletes the asset record from database

2. 🧹 **Cleanup Related Items** (if enabled)
   - Network ports (`glpi_networkports`)
   - Financial data (`glpi_infocoms`)
   - Contract associations (`glpi_contracts_items`)
   - Document links (`glpi_documents_items`)
   - Ticket history (`glpi_items_tickets`)

### Database Impact

| Action | Database Operations | Reversible? |
|--------|---------------------|-------------|
| **Stage 1** | UPDATE (status + is_deleted) | ✅ Yes (restore from trash) |
| **Stage 2** | DELETE (permanent) | ❌ No (backup required) |

---

## 💡 Use Cases

### 🖨️ Case 1: Network Printers with NetInventory

**Scenario:** You use GLPI Agent with NetInventory to discover network printers. Some printers get unplugged or moved without notice.

**Recommended Configuration:**
```
Inactive delay:  30 days
Trash delay:     60 days
Asset types:     [✓] Printers
Related items:   Enabled
```

**Timeline:**
- **Day 0**: Printer stops responding to inventory
- **Day 30**: ⚠️ Status changed to "Out of Service" + moved to trash
- **Day 90**: 💥 Printer permanently deleted with all related data

### 🌐 Case 2: Network Equipment (Conservative)

**Scenario:** You want to identify obsolete equipment but manually review before deletion.

**Recommended Configuration:**
```
Inactive delay:  45 days
Trash delay:     Disabled
Asset types:     [✓] Network Equipment
Related items:   Disabled
```

**Timeline:**
- **Day 45**: ⚠️ Equipment marked obsolete and moved to trash
- **Manual**: 👤 Admin reviews and decides to restore or delete

### 📞 Case 3: IP Phones (Aggressive)

**Scenario:** IP phones are frequently replaced, you want fast cleanup.

**Recommended Configuration:**
```
Inactive delay:  15 days
Trash delay:     30 days
Asset types:     [✓] Phones
Related items:   Enabled
```

**Timeline:**
- **Day 15**: ⚠️ Phone trashed
- **Day 45**: 💥 Phone purged

---

## 📊 Monitoring & Logs

### View Plugin Activity

All actions are logged in GLPI. To view them:

1. **Setup > Automatic actions**
2. Click on **CleanOldAssets** or **PurgeOldTrash**
3. View **Logs** tab

### Example Log Entries

```
✓ Processed 12 Printer
  - Printer "HP LaserJet 4050" (ID: 156) marked out of order and moved to trash
  - Printer "Canon iR2525" (ID: 203) marked out of order and moved to trash
  ...

✓ Purged 3 Printer
  - Purged Printer "Epson XP-440" (ID: 89)
    - Deleted 2 network ports
    - Deleted infocom data
  ...
```

### SQL Monitoring Queries

Check obsolete assets before cleanup:

```sql
-- Count obsolete printers
SELECT COUNT(*) 
FROM glpi_printers 
WHERE is_deleted = 0 
  AND is_template = 0
  AND (last_inventory_update < DATE_SUB(NOW(), INTERVAL 30 DAY) 
       OR last_inventory_update IS NULL);

-- List assets in trash
SELECT name, date_mod 
FROM glpi_printers 
WHERE is_deleted = 1 
  AND date_mod < DATE_SUB(NOW(), INTERVAL 60 DAY);
```

---

## ⚠️ Important Warnings

### 🔴 Critical Safety Information

> **This plugin can PERMANENTLY delete data!**

Before using in production:

1. ✅ **Backup your database** regularly
2. ✅ **Test in a development environment** first
3. ✅ **Start with long delays** (60-90 days minimum)
4. ✅ **Monitor logs** after first execution
5. ✅ **Verify asset states** are correctly configured

### 🛡️ Best Practices

| Practice | Recommendation |
|----------|----------------|
| **Initial Testing** | Use 90+ day delays first |
| **Backup Frequency** | Daily before cron runs |
| **Monitoring** | Check logs weekly |
| **State Creation** | Create "Out of Service (Auto)" state |
| **Dry Run** | Manually review trash before enabling Stage 2 |

### 🚨 What Could Go Wrong?

| Risk | Prevention |
|------|------------|
| **Deleting active assets** | Ensure inventory runs regularly |
| **Loss of history** | Keep long trash delays (60+ days) |
| **Database corruption** | Always backup before changes |
| **False positives** | Review logs and adjust delays |

---

## 📚 Documentation

- 📖 [Full Installation Guide](INSTALL.md)
- 🔍 [SQL Monitoring Queries](SQL_QUERIES.md) *(if you created this file)*
- 📝 [Changelog](CHANGELOG.md) *(if you created this file)*

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### How to Contribute

1. 🍴 Fork the repository
2. 🌿 Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. ✅ Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. 📤 Push to the branch (`git push origin feature/AmazingFeature`)
5. 🎉 Open a Pull Request

---

## 📄 License

This project is licensed under the **GNU General Public License v2.0** - see the [LICENSE](LICENSE) file for details.

```
AssetsCleaner is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

---

## 🙏 Acknowledgments

- GLPI Project team for the excellent ITSM platform
- Community contributors for feedback and testing

---

## 📞 Support

- 🐛 **Bug Reports:** [Open an issue](https://github.com/SpyKeeR/assetscleaner/issues)
- 💬 **Questions:** [Discussions](https://github.com/SpyKeeR/assetscleaner/discussions)
- 📧 **Contact:** Your contact info here

---

## 🔗 Links

- [GLPI Official Website](https://glpi-project.org/)
- [GLPI Documentation](https://glpi-user-documentation.readthedocs.io/)
- [GLPI Plugins Directory](https://plugins.glpi-project.org/)

---

<div align="center">

**Made with ❤️ for the GLPI community**

⭐ **Star this repo if you find it useful!**

</div>
