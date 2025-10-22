-- Script de test pour vérifier les requêtes SQL du plugin Assets Cleaner
-- À exécuter dans phpMyAdmin ou mysql CLI pour comprendre pourquoi aucun actif n'est traité

-- ========================================
-- 1. VÉRIFICATION DE LA CONFIGURATION
-- ========================================
SELECT 'Configuration actuelle:' as info;
SELECT context, name, value 
FROM glpi_configs 
WHERE context = 'assetscleaner'
ORDER BY name;

-- ========================================
-- 2. VÉRIFICATION DES IMPRIMANTES
-- ========================================
SELECT '
Vérification des imprimantes:' as info;

-- 2.1 Structure de la table
SELECT 'Colonnes de la table glpi_printers:' as info;
SHOW COLUMNS FROM glpi_printers LIKE '%inventory%';
SHOW COLUMNS FROM glpi_printers LIKE 'is_%';

-- 2.2 Comptage général
SELECT 
    'Total imprimantes' as categorie,
    COUNT(*) as nombre
FROM glpi_printers
UNION ALL
SELECT 
    'Imprimantes dynamiques (is_dynamic=1)',
    COUNT(*)
FROM glpi_printers
WHERE is_dynamic = 1
UNION ALL
SELECT 
    'Imprimantes non supprimées (is_deleted=0)',
    COUNT(*)
FROM glpi_printers
WHERE is_deleted = 0
UNION ALL
SELECT 
    'Imprimantes en corbeille (is_deleted=1)',
    COUNT(*)
FROM glpi_printers
WHERE is_deleted = 1;

-- 2.3 Vérifier si last_inventory_update existe et contient des données
SELECT 
    'Imprimantes avec last_inventory_update renseigné' as info,
    COUNT(*) as nombre
FROM glpi_printers
WHERE last_inventory_update IS NOT NULL;

-- 2.4 Échantillon de données
SELECT 'Échantillon de 10 imprimantes:' as info;
SELECT 
    id,
    name,
    is_deleted,
    is_template,
    is_dynamic,
    last_inventory_update,
    DATE_SUB(NOW(), INTERVAL 30 DAY) as cutoff_30_days,
    CASE 
        WHEN last_inventory_update < DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 'OUI - Candidat'
        ELSE 'NON - Trop récent'
    END as serait_traite
FROM glpi_printers
LIMIT 10;

-- ========================================
-- 3. SIMULATION DE LA REQUÊTE DU PLUGIN
-- ========================================
SELECT '
Simulation requête plugin (30 jours):' as info;

-- Remplacer 30 par votre valeur de inactive_delay_days
SET @inactive_delay_days = 30;
SET @cutoff_date = DATE_SUB(NOW(), INTERVAL @inactive_delay_days DAY);

SELECT 
    id,
    name,
    is_deleted,
    is_dynamic,
    last_inventory_update,
    DATEDIFF(NOW(), last_inventory_update) as jours_depuis_maj
FROM glpi_printers
WHERE is_deleted = 0
  AND is_template = 0
  AND is_dynamic = 1
  AND last_inventory_update < @cutoff_date
ORDER BY last_inventory_update ASC
LIMIT 20;

-- ========================================
-- 4. STATISTIQUES DÉTAILLÉES
-- ========================================
SELECT '
Statistiques par âge (imprimantes dynamiques non supprimées):' as info;

SELECT 
    CASE 
        WHEN last_inventory_update IS NULL THEN 'Jamais mis à jour'
        WHEN DATEDIFF(NOW(), last_inventory_update) > 365 THEN '> 1 an'
        WHEN DATEDIFF(NOW(), last_inventory_update) > 180 THEN '6-12 mois'
        WHEN DATEDIFF(NOW(), last_inventory_update) > 90 THEN '3-6 mois'
        WHEN DATEDIFF(NOW(), last_inventory_update) > 60 THEN '2-3 mois'
        WHEN DATEDIFF(NOW(), last_inventory_update) > 30 THEN '1-2 mois'
        ELSE '< 1 mois'
    END as age_groupe,
    COUNT(*) as nombre
FROM glpi_printers
WHERE is_deleted = 0
  AND is_template = 0
  AND is_dynamic = 1
GROUP BY age_groupe
ORDER BY nombre DESC;

-- ========================================
-- 5. VÉRIFICATION POUR AUTRES TYPES
-- ========================================
SELECT '
Équipements réseau:' as info;
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_dynamic = 1 THEN 1 ELSE 0 END) as dynamiques,
    SUM(CASE WHEN is_deleted = 0 THEN 1 ELSE 0 END) as non_supprimes
FROM glpi_networkequipments;

SELECT '
Téléphones:' as info;
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_dynamic = 1 THEN 1 ELSE 0 END) as dynamiques,
    SUM(CASE WHEN is_deleted = 0 THEN 1 ELSE 0 END) as non_supprimes
FROM glpi_phones;

-- ========================================
-- 6. DIAGNOSTIC
-- ========================================
SELECT '
DIAGNOSTIC:' as info;

SELECT 
    CASE
        WHEN (SELECT COUNT(*) FROM glpi_printers WHERE is_dynamic = 1 AND is_deleted = 0 AND last_inventory_update < DATE_SUB(NOW(), INTERVAL 30 DAY)) = 0
        THEN '⚠️ AUCUNE imprimante dynamique non supprimée plus vieille que 30 jours'
        ELSE CONCAT('✓ ', 
            (SELECT COUNT(*) FROM glpi_printers WHERE is_dynamic = 1 AND is_deleted = 0 AND last_inventory_update < DATE_SUB(NOW(), INTERVAL 30 DAY)),
            ' imprimantes candidats au nettoyage')
    END as resultat
UNION ALL
SELECT 
    CASE
        WHEN (SELECT COUNT(*) FROM glpi_printers WHERE is_dynamic = 1) = 0
        THEN '❌ AUCUNE imprimante dynamique (is_dynamic=1) - Vérifier FusionInventory'
        ELSE CONCAT('✓ ', 
            (SELECT COUNT(*) FROM glpi_printers WHERE is_dynamic = 1),
            ' imprimantes dynamiques trouvées')
    END
UNION ALL
SELECT 
    CASE
        WHEN (SELECT COUNT(*) FROM glpi_configs WHERE context = 'assetscleaner' AND name = 'enabled' AND value = '1') = 0
        THEN '⚠️ Plugin désactivé dans la configuration'
        ELSE '✓ Plugin activé'
    END
UNION ALL
SELECT 
    CASE
        WHEN (SELECT value FROM glpi_configs WHERE context = 'assetscleaner' AND name = 'asset_types') NOT LIKE '%Printer%'
        THEN '⚠️ Type "Printer" non configuré dans asset_types'
        ELSE '✓ Type Printer configuré'
    END;
