# 🎉 Assets Cleaner v1.0.3 - Release Notes

**Date de sortie** : 23 octobre 2025

---

## 🌟 Résumé

Cette version apporte une **refonte majeure de la logique interne** du plugin avec une **simplification significative** et un **système de logging complet**. Plus besoin de créer un statut spécifique !

---

## 🚀 Nouveautés principales

### 1️⃣ Simplification de la logique ✨

**Avant v1.0.3 :**
- Dépendait d'un statut "Décommissionné (Auto)" à créer manuellement
- Double action : changement de statut + mise en corbeille
- Logique complexe avec recherche de statuts

**Après v1.0.3 :**
- ✅ Plus de dépendance au statut !
- ✅ Workflow simplifié en 2 étapes :
  1. **Étape 1** : Mise en corbeille (soft delete)
  2. **Étape 2** : Purge définitive (hard delete)
- ✅ Aucune configuration de statut requise

### 2️⃣ Système de logging complet 📝

**Nouveau fichier de log :** `files/_log/assetscleaner.log`

**Exemple de logs :**
```log
[2025-10-23 10:00:00] Cutoff date for inactive assets: 2025-09-23 10:00:00 (older than 30 days)
[2025-10-23 10:00:00] Found 12 Printers to process
[2025-10-23 10:00:00] ✓ Moved to trash: Printer "HP-Office-201" (ID: 456, last update: 2025-08-15 14:30:00)
[2025-10-23 10:00:00] ✓ Moved to trash: Printer "Canon-Lab-102" (ID: 457, last update: 2025-08-10 09:15:00)
[2025-10-23 10:00:00] Summary for Printers: 12 moved to trash, 0 failed
```

**Avantages :**
- 🔍 Traçabilité complète de toutes les actions
- 📊 Résumés par type d'actif
- 🐛 Facilite le débogage
- 📈 Audit des suppressions

**Consulter les logs (PowerShell) :**
```powershell
Get-Content F:\GLPI\files\_log\assetscleaner.log -Tail 50
```

### 3️⃣ Corrections SQL critiques 🐛

**Problèmes corrigés :**
- ❌ Requête SQL avec `states_id > 0` excluait tous les actifs
- ❌ Clause OR complexe créait des résultats imprévisibles
- ❌ Dépendance inutile à la table `glpi_states`

**Améliorations :**
- ✅ Requête simplifiée : `last_inventory_update < cutoff_date`
- ✅ Filtrage correct : `is_deleted = 0` et `is_dynamic = 1`
- ✅ Performance améliorée

---

## 🛠️ Améliorations techniques

### Code nettoyé
- Suppression de la méthode `getOutOfOrderStateId()` (29 lignes)
- Suppression du code de gestion des statuts (≈50 lignes)
- Code plus maintenable et lisible

### Configuration simplifiée
- Suppression des champs obsolètes :
  - ❌ `first_action` (Set as Decommissioned / Move to Trash)
  - ❌ `second_action` (Enable second action)
- Nouveaux noms de champs plus clairs :
  - ✅ `inactive_delay_days` → Jours avant mise en corbeille
  - ✅ `trash_delay_days` → Jours avant purge
  - ✅ `second_action_enabled` → Activer la purge

### Documentation enrichie
- 📚 README mis à jour avec nouveaux workflows
- 🔧 Script de diagnostic SQL (`tools/debug_sql_queries.sql`)
- 📦 Compilateur .mo en PHP pur (plus besoin de msgfmt !)
- 🌍 Traductions françaises mises à jour

---

## 📥 Installation / Mise à jour

### Nouvelle installation

```bash
cd /path/to/glpi/plugins
git clone https://github.com/SpyKeeR/assetscleaner.git assetscleaner
```

Puis : **Configuration > Plugins > Installer > Activer**

### Mise à jour depuis v1.0.2

```bash
cd /path/to/glpi/plugins/assetscleaner
git pull origin main
```

**⚠️ Important :** Aucune migration nécessaire ! Le plugin fonctionne immédiatement.

---

## 🧪 Tests et validation

### Vérifier que le plugin fonctionne

1. **Activer les logs dans GLPI** (si pas déjà fait) :
   ```
   Configuration > Général > Logs
   Niveau : 4 ou 5
   ```

2. **Exécuter la tâche cron manuellement** :
   ```bash
   php bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronCleanOldAssets'
   ```

3. **Consulter les logs** :
   ```powershell
   # Windows PowerShell
   Get-Content F:\GLPI\files\_log\assetscleaner.log -Tail 20

   # Linux/Mac
   tail -n 20 /path/to/glpi/files/_log/assetscleaner.log
   ```

### Script de diagnostic SQL

Un nouveau script SQL est disponible pour diagnostiquer les problèmes :

```bash
mysql -u root -p glpi < tools/debug_sql_queries.sql
```

Ce script affiche :
- ✅ Configuration actuelle
- 📊 Nombre d'actifs éligibles au nettoyage
- 📈 Statistiques par âge d'actif
- 🔍 Simulation de la requête du plugin

---

## 🐛 Bugs corrigés

| Bug | Description | Solution |
|-----|-------------|----------|
| #1 | Aucun actif traité malgré configuration correcte | Requête SQL corrigée |
| #2 | Logs invisibles lors de l'exécution cron | Ajout de `Toolbox::logInFile()` |
| #3 | Erreur si statut "Décommissionné" absent | Suppression de la dépendance |
| #4 | Clause OR sur `last_inventory_update` trop permissive | Simplification de la condition |

---

## 📋 Compatibilité

- ✅ GLPI ≥ 11.0.0 < 11.0.99
- ✅ PHP ≥ 8.2
- ✅ GLPI Agent ou NetInventory/NetDiscovery

---

## 🙏 Remerciements

Merci aux utilisateurs qui ont remonté les problèmes de configuration et de logs !

---

## 📞 Support

- 🐛 **Issues GitHub** : https://github.com/SpyKeeR/assetscleaner/issues
- 📧 **Email** : Voir profil GitHub
- 📚 **Documentation** : [README.md](README.md)

---

## 🔜 Prochaines versions

**v1.1.0** (à venir) :
- Support de types d'actifs additionnels (Computers, Monitors)
- Interface de visualisation des assets en attente de suppression
- Notifications par email des suppressions
- Export CSV des opérations

---

**Bon nettoyage ! 🧹**
