# Guide de développement - Assets Cleaner

## Structure du projet

```
assetscleaner/
├── front/              # Pages frontend
│   └── config.php     # Page de configuration
├── locales/           # Fichiers de traduction
│   ├── assetscleaner.pot  # Template de traduction
│   ├── fr_FR.po          # Traductions françaises
│   └── fr_FR.mo          # Traductions compilées
├── src/               # Classes PHP
│   ├── AssetsCleaner.php         # Logique principale + cron
│   ├── ConfigAssetsCleaner.php   # Configuration
│   └── ProfileAssetsCleaner.php  # Gestion des droits
├── tools/             # Scripts utilitaires
│   └── compile_mo.php # Compilation des traductions
├── composer.json      # Dépendances Composer
├── hook.php          # Hooks d'installation/désinstallation
└── setup.php         # Configuration du plugin

```

## Problèmes résolus (22/10/2025)

### 1. Erreur de sauvegarde de configuration ❌ → ✅
**Problème** : `Config::setConfigurationValues()` échouait silencieusement

**Cause** : Appel multiple en boucle au lieu d'un seul appel avec toutes les valeurs

**Solution** : 
```php
// AVANT (❌ incorrect)
foreach ($values as $key => $value) {
    Config::setConfigurationValues('plugin:assetscleaner', [$key => $value]);
}

// APRÈS (✅ correct)
Config::setConfigurationValues('plugin:assetscleaner', $values);
```

### 2. Problèmes de token CRLF ❌ → ✅
**Problème** : `error_log()` causait des fins de lignes incorrectes

**Solution** : Suppression de tous les `error_log()` du code

### 3. Incohérence des noms/URLs ❌ → ✅
**Problème** : Références mixtes à `AssetsCleaner` et `assetscleaner`, URLs GitHub incorrectes

**Solution** :
- Nom du dossier plugin : `assetscleaner` (minuscules)
- Namespace PHP : `GlpiPlugin\Assetscleaner\`
- Composer package : `spykeer/glpi-assetscleaner`
- URL GitHub : `https://github.com/SpyKeeR/Glpi-AssetsCleaner`

## Compilation des traductions

### Méthode 1 : Avec gettext (recommandé)

```bash
cd locales
msgfmt fr_FR.po -o fr_FR.mo
```

### Méthode 2 : Avec le script PHP

```bash
php tools/compile_mo.php
```

### Méthode 3 : Laisser GLPI le faire
GLPI peut compiler automatiquement les fichiers .mo au chargement du plugin.

## Mise à jour des traductions

1. **Extraire les nouvelles chaînes** (après modification du code) :
   ```bash
   # Utiliser l'outil extract de GLPI
   php ../../tools/extract_template.php assetscleaner
   ```

2. **Mettre à jour le .po français** :
   ```bash
   msgmerge --update locales/fr_FR.po locales/assetscleaner.pot
   ```

3. **Éditer manuellement** `locales/fr_FR.po` pour ajouter les traductions

4. **Compiler** :
   ```bash
   msgfmt locales/fr_FR.po -o locales/fr_FR.mo
   ```

## Conventions de nommage GLPI

### Identifiants de configuration
Préfixe : `plugin:assetscleaner`

Exemple :
```php
Config::setConfigurationValues('plugin:assetscleaner', [
    'enabled' => 1,
    'inactive_delay_days' => 30,
]);
```

### Tâches cron
Format : `GlpiPlugin\Assetscleaner\AssetsCleaner::cronTaskName`

Exemples :
- `GlpiPlugin\Assetscleaner\AssetsCleaner::cronCleanOldAssets`
- `GlpiPlugin\Assetscleaner\AssetsCleaner::cronPurgeOldTrash`

### Chemins
- Page de config : `front/config.php`
- URL complète : `/plugins/assetscleaner/front/config.php`

## Tests

### Tester la sauvegarde de configuration

1. Aller dans **Configuration > Général > Onglet Assets Cleaner**
2. Modifier les paramètres
3. Cliquer sur **Enregistrer**
4. Vérifier dans la base de données :

```sql
SELECT * FROM glpi_configs 
WHERE context = 'plugin:assetscleaner';
```

### Tester les tâches cron

```bash
# Nettoyer les anciens actifs
php bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronCleanOldAssets'

# Purger la corbeille
php bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronPurgeOldTrash'
```

## Checklist avant commit

- [ ] Code sans `error_log()` ou `var_dump()`
- [ ] URLs GitHub correctes
- [ ] Traductions à jour (.po et .mo synchronisés)
- [ ] Pas d'erreurs dans les logs GLPI
- [ ] Tests manuels de la configuration
- [ ] Version mise à jour dans `setup.php`

## Dépannage

### "Error saving configuration"
- Vérifier les droits SQL sur `glpi_configs`
- Vérifier les logs PHP : `/var/log/php/error.log` ou `/var/www/html/glpi/files/_log/`
- Activer le mode debug GLPI : `$CFG_GLPI['debug_mode'] = true;`

### Traductions non appliquées
- Vider le cache GLPI : `Configuration > Général > Système > Vider le cache`
- Recompiler le .mo : `msgfmt locales/fr_FR.po -o locales/fr_FR.mo`
- Vérifier les permissions du fichier .mo

### Cron ne s'exécute pas
- Vérifier que les tâches sont activées : `Configuration > Actions automatiques`
- Lancer manuellement pour voir les erreurs
- Vérifier `is_dynamic = 1` sur les actifs à nettoyer
