<?php
/**
* Prestatill Home Delivery Slots - Migration vers les zones
*
* Script de migration pour convertir les zones de proximité existantes en zones géographiques
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__).'/../classes/PrestatillHomeDeliveryZone.php');

/**
 * Migre les zones de proximité (rayon) vers des zones géographiques (cercles)
 *
 * Cette fonction prend les anciennes configurations basées sur la distance
 * et les convertit en zones de type "cercle" sur la carte
 */
function migrateProximityToZones()
{
    $results = array(
        'success' => 0,
        'errors' => 0,
        'messages' => array(),
    );

    // Récupère toutes les configurations de distance par magasin
    // Table: prestatill_homedelivery_delivery_by_distance ou configuration similaire
    $sql = 'SELECT DISTINCT id_store, dbd_supp, id_carrier, id_day
            FROM `'._DB_PREFIX_.'prestatill_homedelivery_dbd_supp`
            WHERE dbd_supp > 0';

    $proximityZones = Db::getInstance()->executeS($sql);

    if (!$proximityZones) {
        $results['messages'][] = 'Aucune zone de proximité à migrer.';
        return $results;
    }

    foreach ($proximityZones as $proximity) {
        $id_store = (int)$proximity['id_store'];
        $distance_km = (float)$proximity['dbd_supp'];
        $id_carrier = (int)$proximity['id_carrier'];
        $id_day = (int)$proximity['id_day'];

        // Récupère les informations du magasin
        $store = new Store($id_store);
        if (!Validate::isLoadedObject($store)) {
            $results['errors']++;
            $results['messages'][] = 'Magasin ID '.$id_store.' introuvable.';
            continue;
        }

        // Vérifie que le magasin a des coordonnées
        if (empty($store->latitude) || empty($store->longitude)) {
            $results['errors']++;
            $results['messages'][] = 'Le magasin "'.$store->name.'" n\'a pas de coordonnées GPS.';
            continue;
        }

        // Crée une zone de type cercle
        $zone = new PrestatillHomeDeliveryZone();
        $zone->zone_name = 'Zone '.$store->name.' ('.($distance_km).' km)';
        $zone->id_store = $id_store;
        $zone->id_carrier = $id_carrier;
        $zone->zone_type = 'circle';

        // Convertit la distance en mètres
        $radius_meters = $distance_km * 1000;

        // Crée les données de la zone (cercle)
        $zone_data = array(
            'center' => array(
                'lat' => (float)$store->latitude,
                'lng' => (float)$store->longitude,
            ),
            'radius' => $radius_meters,
        );

        $zone->zone_data = json_encode($zone_data);
        $zone->zone_color = '#'.substr(md5($store->name), 0, 6); // Génère une couleur aléatoire
        $zone->active = 1;
        $zone->priority = 1;
        $zone->date_add = date('Y-m-d H:i:s');
        $zone->date_upd = date('Y-m-d H:i:s');
        $zone->id_shop = (int)Context::getContext()->shop->id;
        $zone->id_shop_group = (int)Context::getContext()->shop->id_shop_group;

        if ($zone->save()) {
            // Associe les jours de livraison si spécifié
            if ($id_day > 0) {
                $zone->setDeliveryDays(array($id_day));
            }

            $results['success']++;
            $results['messages'][] = 'Zone créée pour le magasin "'.$store->name.'" (rayon: '.$distance_km.' km).';
        } else {
            $results['errors']++;
            $results['messages'][] = 'Erreur lors de la création de la zone pour le magasin "'.$store->name.'".';
        }
    }

    return $results;
}

/**
 * Migre les codes postaux vers des zones personnalisées
 *
 * Cette fonction aide à créer des zones basées sur les codes postaux existants
 * Note: La conversion exacte nécessite l'utilisation de l'API de géocodage
 */
function migratePostalCodesToZones()
{
    $results = array(
        'success' => 0,
        'errors' => 0,
        'messages' => array(),
    );

    // Récupère toutes les configurations de codes postaux
    $sql = 'SELECT DISTINCT zip_supp, id_carrier, id_day, id_zone
            FROM `'._DB_PREFIX_.'prestatill_homedelivery_zip_supp`
            WHERE zip_supp IS NOT NULL AND zip_supp != ""';

    $postalZones = Db::getInstance()->executeS($sql);

    if (!$postalZones) {
        $results['messages'][] = 'Aucun code postal à migrer.';
        return $results;
    }

    $results['messages'][] = 'ATTENTION: La migration des codes postaux vers des zones géographiques nécessite l\'utilisation de l\'API de géocodage Google Maps.';
    $results['messages'][] = 'Nombre de configurations de codes postaux trouvées: '.count($postalZones);
    $results['messages'][] = 'Veuillez créer manuellement les zones pour ces codes postaux dans l\'interface d\'administration.';

    foreach ($postalZones as $postal) {
        $results['messages'][] = '- Codes postaux: '.$postal['zip_supp'].' (Transporteur: '.$postal['id_carrier'].', Jour: '.$postal['id_day'].')';
    }

    return $results;
}

/**
 * Exécute la migration complète
 */
function executeMigration()
{
    $results = array(
        'proximity' => migrateProximityToZones(),
        'postal' => migratePostalCodesToZones(),
    );

    return $results;
}
