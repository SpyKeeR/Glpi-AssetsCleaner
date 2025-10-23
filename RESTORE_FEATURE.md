# ♻️ Fonctionnalité de Restauration Automatique

## Vue d'ensemble

La version 1.0.4 du plugin Assets Cleaner introduit une nouvelle fonctionnalité majeure : **la restauration automatique depuis la corbeille**.

Cette fonctionnalité résout un problème courant : lorsqu'un équipement est temporairement hors ligne (panne réseau, maintenance, redémarrage), il est mis en corbeille après le délai configuré. Si l'équipement revient en ligne et est détecté à nouveau par l'inventaire, il sera automatiquement restauré !

---

## 🎯 Cas d'usage

### Scénario typique

**Sans la restauration automatique (avant v1.0.4) :**
```
Jour 0  : Imprimante HP-Office-201 fonctionne normalement
Jour 15 : Panne réseau temporaire (switch défaillant)
Jour 30 : Plugin met l'imprimante en corbeille (pas de réponse pendant 30 jours)
Jour 35 : Réseau réparé, imprimante détectée par inventaire
         ❌ PROBLÈME : L'imprimante reste en corbeille !
         → L'admin doit manuellement la restaurer
```

**Avec la restauration automatique (v1.0.4+) :**
```
Jour 0  : Imprimante HP-Office-201 fonctionne normalement
Jour 15 : Panne réseau temporaire (switch défaillant)
Jour 30 : Plugin met l'imprimante en corbeille (pas de réponse pendant 30 jours)
Jour 35 : Réseau réparé, imprimante détectée par inventaire
         ✅ SOLUTION : L'imprimante est automatiquement restaurée !
         → Aucune intervention manuelle nécessaire
```

---

## ⚙️ Configuration

### Accéder aux paramètres

1. Connectez-vous à GLPI en tant qu'administrateur
2. Allez dans **Configuration > Général**
3. Cliquez sur l'onglet **♻️ Nettoyage éléments**
4. Faites défiler jusqu'à la section **"Restauration automatique depuis la corbeille"**

### Options disponibles

| Paramètre | Description | Valeur par défaut | Recommandation |
|-----------|-------------|-------------------|----------------|
| **Activer la restauration automatique** | Active/désactive la fonctionnalité | ✅ Activé | Laisser activé |
| **Délai de restauration (jours)** | Nombre de jours pour vérifier les mises à jour d'inventaire | 7 jours | 7-14 jours selon votre fréquence d'inventaire |

### Valeurs recommandées selon les scénarios

#### Inventaire quotidien (recommandé)
```
Délai de restauration : 7 jours
```
→ Si un équipement a été inventorié dans les 7 derniers jours, il sera restauré

#### Inventaire hebdomadaire
```
Délai de restauration : 14 jours
```
→ Laisse plus de temps pour détecter les équipements qui reviennent

#### Environnement très dynamique
```
Délai de restauration : 3 jours
```
→ Restauration rapide, mais peut manquer certains équipements

---

## 🔧 Fonctionnement technique

### Critères de restauration

Un actif sera automatiquement restauré SI ET SEULEMENT SI :

1. ✅ L'actif est dans la corbeille (`is_deleted = 1`)
2. ✅ L'actif est géré par inventaire (`is_dynamic = 1`)
3. ✅ Le champ `last_inventory_update` est renseigné
4. ✅ La date `last_inventory_update` est dans les X derniers jours (selon configuration)

### Requête SQL utilisée

```sql
SELECT id, name, last_inventory_update
FROM glpi_printers  -- ou glpi_networkequipments, glpi_phones
WHERE is_deleted = 1
  AND is_dynamic = 1
  AND last_inventory_update IS NOT NULL
  AND last_inventory_update > DATE_SUB(NOW(), INTERVAL 7 DAY);
```

### Processus de restauration

1. **Détection** : La tâche cron `RestoreInventoriedAssets` s'exécute quotidiennement
2. **Analyse** : Elle parcourt tous les types d'actifs configurés (Imprimantes, Équipements réseau, Téléphones)
3. **Vérification** : Pour chaque actif en corbeille, elle vérifie si `last_inventory_update` est récent
4. **Restauration** : Si les critères sont remplis, elle appelle `$item->restore(['id' => $id])`
5. **Logging** : Chaque restauration est enregistrée dans les logs

---

## 📊 Tâche automatique

### Configuration de la tâche cron

1. Allez dans **Configuration > Actions automatiques**
2. Recherchez **"RestoreInventoriedAssets"**
3. Vérifiez les paramètres :
   - **État** : Activé
   - **Fréquence** : 86400 (1 jour)
   - **Mode** : CLI (recommandé)

### Exécution manuelle pour test

```bash
# Windows PowerShell
cd C:\inetpub\wwwroot\glpi
php bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronRestoreInventoriedAssets'

# Linux
cd /var/www/html/glpi
php bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronRestoreInventoriedAssets'
```

### Ordre d'exécution recommandé

Pour un fonctionnement optimal, exécutez les tâches dans cet ordre :

1. **CleanOldAssets** (2h00) - Met en corbeille les actifs obsolètes
2. **RestoreInventoriedAssets** (2h30) - Restaure ceux qui sont réapparus
3. **PurgeOldTrash** (3h00) - Purge définitivement les anciens actifs en corbeille

Exemple de crontab :
```cron
0 2 * * * php /var/www/html/glpi/bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronCleanOldAssets'
30 2 * * * php /var/www/html/glpi/bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronRestoreInventoriedAssets'
0 3 * * * php /var/www/html/glpi/bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronPurgeOldTrash'
```

---

## 📝 Logs et monitoring

### Consulter les logs

Tous les événements de restauration sont enregistrés dans les logs GLPI.

**Via l'interface :**
1. Configuration > Actions automatiques
2. Cliquez sur **RestoreInventoriedAssets**
3. Onglet **Historique**

**Exemple de logs :**
```
[2025-10-23 02:30:00] Looking for assets in trash with inventory update after: 2025-10-16 02:30:00 (within 7 days)
[2025-10-23 02:30:00] Found 3 Printer in trash with recent inventory update
[2025-10-23 02:30:00] Restored Printer "HP LaserJet 4050" (ID: 156) - Last inventory: 2025-10-22 18:45:00
[2025-10-23 02:30:00] Restored Printer "Canon iR2525" (ID: 203) - Last inventory: 2025-10-21 09:30:00
[2025-10-23 02:30:00] Restored Printer "Epson XP-440" (ID: 89) - Last inventory: 2025-10-22 15:20:00
[2025-10-23 02:30:00] Restored 3 Printer from trash
```

### Requête SQL de monitoring

Pour vérifier les actifs qui pourraient être restaurés :

```sql
-- Imprimantes éligibles à la restauration
SELECT 
    p.id,
    p.name,
    p.last_inventory_update,
    DATEDIFF(NOW(), p.last_inventory_update) AS days_since_update
FROM glpi_printers p
WHERE p.is_deleted = 1
  AND p.is_dynamic = 1
  AND p.last_inventory_update IS NOT NULL
  AND p.last_inventory_update > DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY p.last_inventory_update DESC;
```

---

## ⚠️ Considérations importantes

### Quand désactiver la restauration automatique ?

Vous devriez désactiver cette fonctionnalité si :

1. ❌ Vous souhaitez **valider manuellement** chaque restauration
2. ❌ Votre inventaire est **peu fiable** (nombreuses fausses détections)
3. ❌ Vous voulez **forcer la purge** d'équipements même s'ils réapparaissent

### Interaction avec les autres paramètres

| Paramètre | Impact sur la restauration |
|-----------|---------------------------|
| **Délai inactif (30j)** | Ne change rien - la restauration fonctionne indépendamment |
| **Délai corbeille (60j)** | Si un actif est restauré, le compteur de 60j repart de zéro |
| **Types d'actifs** | Seuls les types cochés seront restaurés automatiquement |

### Cas particuliers

**Actifs restaurés puis remis en corbeille :**
```
Jour 0  : Actif actif
Jour 30 : Mis en corbeille (inactif)
Jour 35 : Restauré automatiquement (inventaire OK)
Jour 40 : Ne répond plus
Jour 70 : Remis en corbeille (30j d'inactivité)
```
→ C'est normal ! L'actif peut passer plusieurs fois par ce cycle si son statut change.

**Actifs créés manuellement :**
```
is_dynamic = 0
```
→ Ne seront JAMAIS restaurés automatiquement (uniquement les actifs gérés par inventaire)

---

## 🎓 Exemples d'utilisation

### Exemple 1 : Maintenance réseau planifiée

**Contexte :** Vous effectuez une maintenance réseau de 2 semaines

**Configuration recommandée :**
```
Délai inactif         : 30 jours
Restauration auto     : Activée
Délai de restauration : 14 jours
```

**Résultat :**
- Les équipements hors ligne > 30j seront mis en corbeille
- Quand la maintenance sera terminée et que les équipements répondront à nouveau, ils seront restaurés automatiquement
- Pas d'intervention manuelle nécessaire !

### Exemple 2 : Changement d'infrastructure

**Contexte :** Vous déménagez des équipements vers un nouveau bâtiment progressivement

**Configuration recommandée :**
```
Délai inactif         : 45 jours (plus long pour le déménagement)
Restauration auto     : Activée
Délai de restauration : 7 jours
```

**Résultat :**
- Les équipements non déménagés restent en corbeille
- Dès qu'un équipement est réinstallé et détecté par inventaire, il est restauré

### Exemple 3 : Test de la fonctionnalité

**Pour tester :**

1. Choisissez une imprimante de test
2. Mettez-la manuellement en corbeille
3. Assurez-vous qu'elle répond à l'inventaire (vérifiez `last_inventory_update`)
4. Lancez manuellement la tâche :
   ```bash
   php bin/console glpi:cron:run -d 'GlpiPlugin\Assetscleaner\AssetsCleaner::cronRestoreInventoriedAssets'
   ```
5. Vérifiez que l'imprimante a été restaurée

---

## 📞 Support et questions

Si vous rencontrez des problèmes :

1. Vérifiez les logs de la tâche `RestoreInventoriedAssets`
2. Vérifiez que `last_inventory_update` est bien rempli pour vos actifs
3. Vérifiez que vos actifs ont bien `is_dynamic = 1`
4. Consultez le CHANGELOG.md pour les notes de version

---

## 🔗 Liens utiles

- [README complet](README.md)
- [Guide d'installation](INSTALL.md)
- [Changelog](CHANGELOG.md)
- [Issues GitHub](https://github.com/SpyKeeR/assetscleaner/issues)

---

**Fait avec ❤️ pour faciliter la gestion de votre inventaire GLPI**
