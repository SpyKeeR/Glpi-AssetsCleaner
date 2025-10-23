# Guide d'installation et de déploiement - Assets Cleaner

## Structure du plugin

Le plugin AssetsCleaner est **prêt à être déployé** tel quel dans GLPI. Voici sa structure :

```
assetscleaner/
    ├── setup.php
    ├── hook.php
    ├── composer.json
    ├── README.md
    ├── LICENSE
    ├── front/
    │   └── config.php
    ├── src/
    │   ├── AssetsCleaner.php
    │   ├── ConfigAssetsCleaner.php
    │   └── ProfileAssetsCleaner.php
    └── locales/
        └── fr_FR.po
```

## Étapes d'installation

### 1. Copie dans GLPI

Copiez le dossier complet dans votre installation GLPI :

```powershell
# Windows PowerShell
$glpiPath = "C:\inetpub\wwwroot\glpi"  # Ajustez selon votre installation
$pluginSource = "f:\Git-Repositories\GitHub\glpi\AssetsCleaner\example"
$pluginDest = "$glpiPath\plugins\assetscleaner"

Copy-Item -Path $pluginSource -Destination $pluginDest -Recurse
```

Ou sous Linux :
```bash
# Linux
cp -r /chemin/vers/AssetsCleaner/example /var/www/html/glpi/plugins/assetscleaner
```

### 2. Installation via l'interface GLPI

1. Connectez-vous à GLPI en tant qu'administrateur
2. Allez dans **Configuration > Plugins**
3. Trouvez "Assets Cleaner" dans la liste
4. Cliquez sur **Installer**
5. Puis cliquez sur **Activer**

### 3. Configuration initiale

1. Allez dans **Configuration > Général**
2. Cliquez sur l'onglet **♻️ Nettoyage éléments**
3. Configurez les paramètres :
   
   **Section : Nettoyage automatique**
   - ✅ Activer le nettoyage automatique
   - Jours avant mise en corbeille : 30 (ou selon vos besoins)
   - ✅ Activer la suppression définitive (purge)
   - Jours en corbeille avant purge : 60
   - Types d'actifs : ☑ Imprimantes (et autres si besoin)
   - ✅ Supprimer les éléments liés
   
   **Section : Restauration automatique depuis la corbeille** ✨ Nouveau !
   - ✅ Activer la restauration automatique
   - Délai de restauration : 7 jours
   
4. Cliquez sur **Enregistrer**

**Note importante** : Le plugin met automatiquement les actifs en corbeille quand ils ne répondent plus à l'inventaire. Si un actif est à nouveau détecté par l'inventaire dans les 7 jours suivant sa mise en corbeille, il sera automatiquement restauré !

### 4. Configuration des tâches automatiques

1. Allez dans **Configuration > Actions automatiques**
2. Recherchez "AssetsCleaner"
3. Pour chaque tâche (CleanOldAssets, RestoreInventoriedAssets et PurgeOldTrash) :
   - Cliquez sur le nom de la tâche
   - État : **Activé**
   - Mode d'exécution : **CLI** (recommandé pour les grosses bases)
   - Fréquence d'exécution : **86400** (1 jour = 86400 secondes)
   - Cliquez sur **Enregistrer**

**Nouvelles tâches disponibles** :
- **CleanOldAssets** : Met en corbeille les actifs obsolètes
- **RestoreInventoriedAssets** ✨ : Restaure les actifs depuis la corbeille s'ils sont à nouveau inventoriés
- **PurgeOldTrash** : Purge définitivement les anciens actifs en corbeille

### 5. Test manuel

Pour tester manuellement les tâches sans attendre le cron :

```powershell
# Depuis le dossier de GLPI
cd C:\inetpub\wwwroot\glpi

# Tester la première tâche (nettoyage)
php bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronCleanOldAssets'

# Tester la deuxième tâche (restauration) ✨ Nouveau !
php bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronRestoreInventoriedAssets'

# Tester la troisième tâche (purge)
php bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronPurgeOldTrash'
```

Ou via l'interface :
1. **Configuration > Actions automatiques**
2. Cliquez sur la tâche souhaitée
3. Cliquez sur **Exécuter**

## Configuration avancée

### Personnalisation des états "Hors Parc"

Le plugin recherche automatiquement un état contenant "hors service", "out of order" ou "hors parc". 

Pour créer un état personnalisé :
1. **Configuration > Intitulés > État**
2. Ajouter un nouvel état, par exemple : "Hors Parc - Auto"
3. Le plugin l'utilisera automatiquement

### Planification du cron système (optionnel)

Si vous utilisez le mode CLI pour les actions automatiques, configurez un cron système :

**Windows (Planificateur de tâches)** :
- Programme : `php.exe`
- Arguments : `C:\inetpub\wwwroot\glpi\front\cron.php`
- Planification : Toutes les 5 minutes

**Linux (crontab)** :
```bash
*/5 * * * * php /var/www/html/glpi/front/cron.php &>/dev/null
```

## Vérification

### Logs des actions

Les actions du plugin sont enregistrées dans :
1. **Configuration > Actions automatiques** > Cliquez sur la tâche > Onglet **Historique**
2. Les fichiers de log GLPI (si activés)

### Surveillance

Vérifiez régulièrement :
- Le nombre d'actifs marqués comme inactifs
- Les éléments dans la corbeille
- Les logs d'exécution des tâches automatiques

## Dépannage

### Le plugin n'apparaît pas dans la liste

- Vérifiez que les fichiers sont dans `plugins/assetscleaner/`
- Vérifiez les permissions des fichiers
- Vérifiez les logs PHP pour des erreurs de syntaxe

### Les tâches automatiques ne s'exécutent pas

- Vérifiez que les tâches sont activées
- Vérifiez que le cron système est configuré
- Exécutez manuellement pour voir les erreurs

### Aucun actif n'est nettoyé

- Vérifiez que le plugin est activé dans la configuration
- Vérifiez que des types d'actifs sont sélectionnés
- Vérifiez que le champ `last_inventory_update` est rempli pour vos actifs
- Vérifiez les délais configurés

## Désinstallation

1. **Configuration > Plugins**
2. Cliquez sur "Assets Cleaner"
3. **Désactiver**
4. **Désinstaller** (cela supprimera toute la configuration)

## Support

Pour toute question ou problème, consultez :
- Le README.md du plugin
- Les issues GitHub du projet
- La documentation GLPI officielle
