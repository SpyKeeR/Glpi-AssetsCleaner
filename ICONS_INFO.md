# Icônes pour Assets Cleaner

## Icône actuelle de l'onglet

L'onglet de configuration utilise actuellement l'icône **Tabler Icons** : `ti ti-trash-x`

Cette icône fait partie du framework Tabler Icons intégré dans GLPI 11.

## Sources d'icônes recommandées

### 1. Tabler Icons (Recommandé - déjà inclus dans GLPI 11)
- Site web : https://tabler.io/icons
- Format : Classes CSS
- Utilisation : `<i class='ti ti-nom-icone'></i>`
- Icônes pertinentes pour Assets Cleaner :
  - `ti-trash-x` (actuelle) - Corbeille avec X
  - `ti-trash` - Corbeille simple
  - `ti-recycle` - Recyclage
  - `ti-broom` - Balai/nettoyage
  - `ti-eraser` - Gomme
  - `ti-calendar-time` - Calendrier avec temps (pour les délais)

### 2. Font Awesome (Si disponible dans votre version de GLPI)
- Site web : https://fontawesome.com/icons
- Format : Classes CSS
- Utilisation : `<i class='fas fa-nom-icone'></i>`

### 3. Créer une icône personnalisée
Si vous souhaitez une icône SVG personnalisée :

1. **Créer ou trouver un SVG**
   - https://www.flaticon.com/
   - https://www.iconfinder.com/
   - https://iconmonstr.com/

2. **Ajouter l'icône au plugin**
   - Créer un dossier : `pics/`
   - Placer le fichier SVG : `pics/assetscleaner.svg`
   - Dimensions recommandées : 24x24px ou 32x32px

3. **Utiliser l'icône dans le code**
   ```php
   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
       if ($item->getType() == 'Config') {
           $icon = Plugin::getWebDir('assetscleaner') . '/pics/assetscleaner.svg';
           return [
               1 => "<img src='$icon' alt='' style='width: 16px; height: 16px; margin-right: 5px;'>" 
                    . __('Assets Cleaner', 'assetscleaner')
           ];
       }
       return '';
   }
   ```

## Modification de l'icône actuelle

Pour changer l'icône de l'onglet, modifier le fichier `src/ConfigAssetsCleaner.php` :

```php
public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
{
    if ($item->getType() == 'Config') {
        return [
            1 => "<i class='ti ti-VOTRE-ICONE'></i> " . __('Assets Cleaner', 'assetscleaner')
        ];
    }
    return '';
}
```

Remplacez `VOTRE-ICONE` par le nom de l'icône Tabler souhaitée.

## Icône dans la liste des plugins

L'icône affichée dans la liste des plugins (Configuration > Plugins) est gérée automatiquement par GLPI.
Pour ajouter une icône personnalisée :

1. Créer le fichier : `pics/assetscleaner.png` (64x64px recommandé)
2. GLPI utilisera automatiquement cette image dans la liste des plugins
