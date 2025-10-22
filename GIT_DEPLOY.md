# 🚀 Guide de mise à jour Git - v1.0.1

## Statut actuel

Vous avez maintenant **13 fichiers modifiés** et **3 nouveaux fichiers** prêts à être commités.

---

## 📦 Étapes de déploiement

### 1️⃣ Vérifier les modifications

```powershell
cd f:\Git-Repositories\GitHub\SpyKeeR\Glpi-AssetsCleaner
git status
```

**Vous devriez voir** :
- Modified: 13 fichiers
- Untracked: 3 nouveaux fichiers (DEVELOPMENT.md, FIXES_SUMMARY.md, tools/compile_mo.php)

---

### 2️⃣ Recompiler les traductions (IMPORTANT)

```powershell
# Si msgfmt est installé
cd locales
msgfmt fr_FR.po -o fr_FR.mo
cd ..

# Sinon, GLPI le fera automatiquement au prochain chargement
```

---

### 3️⃣ Ajouter tous les fichiers

```powershell
# Ajouter les fichiers modifiés
git add -u

# Ajouter les nouveaux fichiers
git add DEVELOPMENT.md
git add FIXES_SUMMARY.md
git add GIT_DEPLOY.md
git add tools/compile_mo.php

# Vérifier que tout est staged
git status
```

---

### 4️⃣ Créer le commit

```powershell
git commit -m "fix: critical configuration save error and project cleanup

BREAKING FIXES:
- Fix Config::setConfigurationValues() call (was called in loop, now once)
- Remove all error_log() causing CRLF issues
- This fixes 'Error saving configuration' that prevented config save

IMPROVEMENTS:
- Standardize GitHub URLs across all files
- Fix Composer package name (spykeer/glpi-assetscleaner)
- Update copyright headers (SpyKeeR)
- Clean up translation files (.po/.pot)
- Bump version to 1.0.1

NEW FILES:
- DEVELOPMENT.md: Complete developer documentation
- FIXES_SUMMARY.md: Detailed explanation of all fixes
- tools/compile_mo.php: Translation compilation script

MODIFIED FILES:
- src/ConfigAssetsCleaner.php (CRITICAL FIX)
- src/AssetsCleaner.php (URLs/copyright)
- src/ProfileAssetsCleaner.php (URLs/copyright)
- front/config.php (URLs/copyright)
- setup.php (version bump + URLs)
- hook.php (URLs/copyright)
- composer.json (package name + homepage)
- locales/assetscleaner.pot (updated)
- locales/fr_FR.po (cleaned)
- CHANGELOG.md (v1.0.1 entry)

Fixes #1 (if you have an issue opened)"
```

---

### 5️⃣ Vérifier le commit

```powershell
# Voir le dernier commit
git log -1 --stat

# Voir les différences
git show HEAD
```

---

### 6️⃣ Pousser vers GitHub

```powershell
# Pousser vers la branche main
git push origin main

# Ou si c'est votre première push
git push -u origin main
```

---

### 7️⃣ Créer une release GitHub (optionnel mais recommandé)

#### Via interface web :
1. Aller sur https://github.com/SpyKeeR/Glpi-AssetsCleaner/releases
2. Cliquer "Draft a new release"
3. Tag version : `v1.0.1`
4. Release title : `v1.0.1 - Critical Configuration Fix`
5. Description :

```markdown
## 🔧 Critical Bug Fix Release

This release fixes a critical bug that prevented configuration from being saved.

### 🐛 Critical Fix
- **Fixed**: Configuration could not be saved due to incorrect API usage
  - `Config::setConfigurationValues()` was called multiple times in a loop
  - Now called once with all values, as intended by GLPI API

### ✨ Improvements
- Removed debug logging causing CRLF issues
- Standardized project naming across all files
- Fixed Composer package name
- Updated all GitHub URLs
- Cleaned translation files

### 📝 Documentation
- Added comprehensive developer guide (DEVELOPMENT.md)
- Added detailed fixes summary (FIXES_SUMMARY.md)
- Updated CHANGELOG with all changes

### ⚠️ Upgrade Notes
If you installed v1.0.0:
1. Update plugin files
2. Go to Setup > Plugins > Upgrade
3. Reconfigure your settings (previous config may not have been saved correctly)

### 📦 Installation
Download `assetscleaner-1.0.1.zip` and extract to `/glpi/plugins/assetscleaner/`

See [INSTALL.md](INSTALL.md) for complete installation instructions.
```

6. Joindre le fichier ZIP si vous en créez un :
```powershell
# Créer une archive pour la release
cd f:\Git-Repositories\GitHub\SpyKeeR
Compress-Archive -Path Glpi-AssetsCleaner -DestinationPath assetscleaner-1.0.1.zip -CompressionLevel Optimal
```

7. Cliquer "Publish release"

---

### 8️⃣ Créer une archive de distribution (optionnel)

```powershell
# Retour au répertoire parent
cd f:\Git-Repositories\GitHub\SpyKeeR

# Créer une archive propre (sans .git)
$source = "Glpi-AssetsCleaner"
$destination = "assetscleaner"
$exclude = @(".git", ".gitignore", "*.md", "tools")

# Copier dans un dossier temporaire
Copy-Item -Path $source -Destination $destination -Recurse -Exclude $exclude

# Créer le ZIP
Compress-Archive -Path $destination -DestinationPath "assetscleaner-1.0.1.zip"

# Nettoyer
Remove-Item -Path $destination -Recurse -Force
```

---

## ✅ Checklist finale

Avant de pousser, vérifiez :

- [ ] Tous les fichiers sont staged (`git status` montre tout en vert)
- [ ] Le message de commit est descriptif
- [ ] La version est bien 1.0.1 dans `setup.php`
- [ ] Le CHANGELOG est à jour
- [ ] Les traductions .mo sont compilées (ou seront compilées par GLPI)
- [ ] Aucun fichier sensible n'est inclus (.env, credentials, etc.)

---

## 🔄 Si vous devez annuler

```powershell
# Annuler les modifications non commitées
git reset --hard HEAD

# Supprimer les fichiers non trackés
git clean -fd

# Annuler le dernier commit (sans perdre les modifications)
git reset --soft HEAD~1

# Annuler le dernier commit (en perdant les modifications) ⚠️
git reset --hard HEAD~1
```

---

## 📊 Résumé des modifications

```
13 fichiers modifiés :
✅ src/ConfigAssetsCleaner.php (FIX CRITIQUE)
✅ src/AssetsCleaner.php
✅ src/ProfileAssetsCleaner.php
✅ front/config.php
✅ setup.php
✅ hook.php
✅ composer.json
✅ locales/assetscleaner.pot
✅ locales/fr_FR.po
✅ locales/fr_FR.mo (à recompiler)
✅ CHANGELOG.md
✅ README.md (si modifié)

3 nouveaux fichiers :
✨ DEVELOPMENT.md
✨ FIXES_SUMMARY.md
✨ tools/compile_mo.php
```

---

## 🎉 Après le push

1. Vérifier sur GitHub que tout est bien monté
2. Tester l'installation depuis GitHub :
```bash
cd /path/to/glpi/plugins
git clone https://github.com/SpyKeeR/Glpi-AssetsCleaner.git assetscleaner
cd assetscleaner
composer install --no-dev
```

3. Mettre à jour la description du dépôt :
```
Nettoie automatiquement les actifs GLPI obsolètes (imprimantes, équipements réseau, téléphones IP) non mis à jour par l'inventaire
```

4. Ajouter des topics au dépôt :
- `glpi`
- `glpi-plugin`
- `inventory`
- `assets-management`
- `automation`
- `cleanup`

---

**Date de création** : 22 octobre 2025  
**Version du plugin** : 1.0.1
