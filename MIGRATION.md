# 🔄 Migration Guide v1.0.1 → v1.0.2

## ⚠️ Important Changes

La version **1.0.2** corrige deux problèmes majeurs :

1. **Contexte de configuration** : `plugin:assetscleaner` → `assetscleaner`
2. **Nomenclature GitHub** : URLs uniformisées en minuscules

---

## 📋 Checklist de migration

### Avant de mettre à jour

- [ ] ✅ Faire une sauvegarde complète de la base de données
- [ ] 📝 Noter vos paramètres actuels (Configuration > Général > Assets Cleaner)
- [ ] 📸 Faire une capture d'écran de la configuration

```bash
# Sauvegarde de la base
mysqldump -u root -p glpi > glpi_backup_before_assetscleaner_v1.0.2.sql
```

---

## 🚀 Méthode 1 : Installation propre (recommandée)

### Étape 1 : Désinstaller la version actuelle

1. **GLPI Web Interface** :
   - Configuration > Plugins
   - Cliquer sur "Désinstaller" pour Assets Cleaner
   - Puis "Désactiver"

2. **Supprimer les fichiers** :
```bash
cd /path/to/glpi/plugins
rm -rf assetscleaner
```

### Étape 2 : Installer la nouvelle version

```bash
cd /path/to/glpi/plugins
git clone https://github.com/SpyKeeR/assetscleaner.git assetscleaner
```

### Étape 3 : Activer et reconfigurer

1. Configuration > Plugins
2. Cliquer "Installer" puis "Activer"
3. **Reconfigurer tous vos paramètres** (Configuration > Général > Assets Cleaner)

---

## 🔧 Méthode 2 : Mise à jour avec migration SQL

### Étape 1 : Mettre à jour les fichiers

```bash
cd /path/to/glpi/plugins/assetscleaner
git pull origin main
```

### Étape 2 : Migrer la configuration

**Option A : Via SQL** (si vous êtes à l'aise avec SQL)

```bash
mysql -u root -p glpi < tools/migrate_v1.0.1_to_v1.0.2.sql
```

**Option B : Manuellement via phpMyAdmin**

```sql
-- Copier l'ancienne configuration vers la nouvelle
INSERT INTO glpi_configs (context, name, value)
SELECT 'assetscleaner', name, value
FROM glpi_configs
WHERE context = 'plugin:assetscleaner'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Vérifier
SELECT * FROM glpi_configs WHERE context = 'assetscleaner';

-- Supprimer l'ancienne (optionnel)
DELETE FROM glpi_configs WHERE context = 'plugin:assetscleaner';
```

### Étape 3 : Mettre à jour le plugin dans GLPI

1. Configuration > Plugins
2. Trouver "Assets Cleaner"
3. Cliquer sur "Mettre à niveau" si disponible

---

## ✅ Vérifications post-migration

### 1. Vérifier la configuration

```sql
SELECT context, name, value 
FROM glpi_configs 
WHERE context = 'assetscleaner'
ORDER BY name;
```

**Résultat attendu : 8 lignes**
- `enabled`
- `inactive_delay_days`
- `trash_delay_days`
- `first_action`
- `second_action_enabled`
- `second_action`
- `asset_types`
- `delete_related_items`

### 2. Tester l'interface web

1. Aller dans **Configuration > Général > Onglet "Assets Cleaner"**
2. Vérifier que tous les paramètres sont présents
3. Modifier une valeur et cliquer "Enregistrer"
4. ✅ Devrait afficher **"Configuration sauvegardée avec succès"** (en vert)
5. ❌ Plus d'erreur "Error saving configuration"

### 3. Tester les tâches cron

```bash
cd /path/to/glpi
php bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronCleanOldAssets'
```

---

## 🆘 Dépannage

### Problème : "Configuration sauvegardée" mais valeurs non appliquées

**Solution** : Vérifier que le contexte est bien `assetscleaner`

```sql
-- Vérifier le contexte
SELECT DISTINCT context FROM glpi_configs WHERE name LIKE '%asset%';

-- Si vous voyez 'plugin:assetscleaner', migrer :
UPDATE glpi_configs 
SET context = 'assetscleaner' 
WHERE context = 'plugin:assetscleaner';
```

### Problème : Configuration vide après migration

**Solution** : Reconfigurer manuellement ou réinstaller

```bash
# Option 1 : Réinstaller
cd /path/to/glpi/plugins
rm -rf assetscleaner
git clone https://github.com/SpyKeeR/assetscleaner.git assetscleaner
# Puis réinstaller via GLPI

# Option 2 : Reconfigurer via l'interface web
# Configuration > Général > Assets Cleaner
```

### Problème : Plugin n'apparaît plus dans la liste

**Vérification** :

```bash
# Le dossier doit s'appeler 'assetscleaner' en minuscules
ls -la /path/to/glpi/plugins/ | grep asset

# Si le dossier a un autre nom, le renommer :
cd /path/to/glpi/plugins
mv Glpi-AssetsCleaner assetscleaner
# ou
mv glpi-assetscleaner assetscleaner
```

---

## 📊 Tableau de correspondance

| Élément | v1.0.1 (❌ ancien) | v1.0.2 (✅ nouveau) |
|---------|-------------------|---------------------|
| **Config Context** | `plugin:assetscleaner` | `assetscleaner` |
| **GitHub Repo** | `SpyKeeR/Glpi-AssetsCleaner` | `SpyKeeR/assetscleaner` |
| **Composer** | `spykeer/glpi-assetscleaner` | `spykeer/assetscleaner` |
| **Folder** | `assetscleaner` | `assetscleaner` (inchangé) |

---

## 📝 Notes importantes

### Pourquoi ce changement ?

**Contexte de configuration** : 
- ✅ GLPI utilise des contextes simples pour les plugins : `fusioninventory`, `webservices`, etc.
- ❌ Le préfixe `plugin:` n'est pas standard et peut causer des problèmes

**URLs GitHub** :
- ✅ Les noms de dépôts pour plugins GLPI doivent être en minuscules
- ✅ Le dossier du plugin DOIT être en minuscules sans caractères spéciaux
- ❌ `Glpi-AssetsCleaner` avec majuscules et tirets causait de la confusion

### Impact sur les utilisateurs existants

- **v1.0.0** : Probablement très peu d'utilisateurs (version initiale bugguée)
- **v1.0.1** : Configuration ne se sauvegardait pas correctement, donc peu d'impact
- **v1.0.2** : Version stable recommandée pour tous

---

## 🎯 Résumé rapide

**Si vous installez pour la première fois** :
```bash
cd /path/to/glpi/plugins
git clone https://github.com/SpyKeeR/assetscleaner.git assetscleaner
# Installer via GLPI > Configuration > Plugins
```

**Si vous mettez à jour depuis v1.0.1** :
```bash
cd /path/to/glpi/plugins/assetscleaner
git pull
# Reconfigurer via GLPI > Configuration > Général > Assets Cleaner
```

---

**Date** : 23 octobre 2025  
**Version cible** : 1.0.2  
**Status** : ✅ Testé et validé
