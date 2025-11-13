<?php
/**
* Prestatill Home Delivery Zones
*
* Home Delivery Module with slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestatillHomeDeliveryZone extends ObjectModel
{
    public $id_delivery_zone;
    public $zone_name;
    public $id_store;
    public $id_carrier;
    public $zone_type;
    public $zone_data;
    public $zone_color;
    public $active;
    public $priority;
    public $date_add;
    public $date_upd;
    public $id_shop;
    public $id_shop_group;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'prestatill_homedelivery_delivery_zones',
        'primary' => 'id_delivery_zone',
        'fields' => array(
            'zone_name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
            'id_store' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_carrier' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'zone_type' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'zone_data' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'zone_color' => array('type' => self::TYPE_STRING, 'validate' => 'isColor', 'size' => 7),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'priority' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_shop_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
        ),
    );

    /**
     * Récupère toutes les zones actives
     *
     * @param int $id_shop
     * @param int $id_carrier
     * @return array
     */
    public static function getActiveZones($id_shop = null, $id_carrier = null)
    {
        if ($id_shop === null) {
            $id_shop = (int)Context::getContext()->shop->id;
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.'prestatill_homedelivery_delivery_zones`
                WHERE `active` = 1 AND `id_shop` = '.(int)$id_shop;

        if ($id_carrier !== null) {
            $sql .= ' AND (`id_carrier` = '.(int)$id_carrier.' OR `id_carrier` = 0)';
        }

        $sql .= ' ORDER BY `priority` DESC';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Récupère les zones pour un restaurant spécifique
     *
     * @param int $id_store
     * @param int $id_shop
     * @return array
     */
    public static function getZonesByStore($id_store, $id_shop = null)
    {
        if ($id_shop === null) {
            $id_shop = (int)Context::getContext()->shop->id;
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.'prestatill_homedelivery_delivery_zones`
                WHERE `id_store` = '.(int)$id_store.'
                AND `id_shop` = '.(int)$id_shop.'
                ORDER BY `priority` DESC';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Vérifie si un point (lat, lng) est dans une zone
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $id_carrier
     * @param int $id_shop
     * @return int|false ID du magasin si trouvé, false sinon
     */
    public static function getStoreByCoordinates($latitude, $longitude, $id_carrier = null, $id_shop = null)
    {
        $zones = self::getActiveZones($id_shop, $id_carrier);

        foreach ($zones as $zone) {
            $zone_data = json_decode($zone['zone_data'], true);

            if ($zone['zone_type'] === 'polygon') {
                if (self::isPointInPolygon($latitude, $longitude, $zone_data)) {
                    return (int)$zone['id_store'];
                }
            } elseif ($zone['zone_type'] === 'circle') {
                if (self::isPointInCircle($latitude, $longitude, $zone_data)) {
                    return (int)$zone['id_store'];
                }
            } elseif ($zone['zone_type'] === 'rectangle') {
                if (self::isPointInRectangle($latitude, $longitude, $zone_data)) {
                    return (int)$zone['id_store'];
                }
            }
        }

        return false;
    }

    /**
     * Algorithme Ray Casting pour vérifier si un point est dans un polygone
     *
     * @param float $latitude
     * @param float $longitude
     * @param array $polygon Array de points [{lat: x, lng: y}, ...]
     * @return bool
     */
    private static function isPointInPolygon($latitude, $longitude, $polygon)
    {
        $vertices_count = count($polygon);
        $is_inside = false;

        for ($i = 0, $j = $vertices_count - 1; $i < $vertices_count; $j = $i++) {
            $xi = $polygon[$i]['lat'];
            $yi = $polygon[$i]['lng'];
            $xj = $polygon[$j]['lat'];
            $yj = $polygon[$j]['lng'];

            $intersect = (($yi > $longitude) != ($yj > $longitude))
                && ($latitude < ($xj - $xi) * ($longitude - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $is_inside = !$is_inside;
            }
        }

        return $is_inside;
    }

    /**
     * Vérifie si un point est dans un cercle
     *
     * @param float $latitude
     * @param float $longitude
     * @param array $circle {center: {lat: x, lng: y}, radius: r}
     * @return bool
     */
    private static function isPointInCircle($latitude, $longitude, $circle)
    {
        $center_lat = $circle['center']['lat'];
        $center_lng = $circle['center']['lng'];
        $radius = $circle['radius']; // en mètres

        $distance = self::calculateDistance($latitude, $longitude, $center_lat, $center_lng);

        return $distance <= $radius;
    }

    /**
     * Vérifie si un point est dans un rectangle
     *
     * @param float $latitude
     * @param float $longitude
     * @param array $rectangle {bounds: {north: n, south: s, east: e, west: w}}
     * @return bool
     */
    private static function isPointInRectangle($latitude, $longitude, $rectangle)
    {
        $bounds = $rectangle['bounds'];

        return $latitude >= $bounds['south']
            && $latitude <= $bounds['north']
            && $longitude >= $bounds['west']
            && $longitude <= $bounds['east'];
    }

    /**
     * Calcule la distance entre deux points (formule de Haversine)
     *
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float Distance en mètres
     */
    private static function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earth_radius = 6371000; // Rayon de la Terre en mètres

        $d_lat = deg2rad($lat2 - $lat1);
        $d_lng = deg2rad($lng2 - $lng1);

        $a = sin($d_lat / 2) * sin($d_lat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($d_lng / 2) * sin($d_lng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth_radius * $c;
    }

    /**
     * Récupère ou crée le cache de géolocalisation pour une adresse
     *
     * @param int $id_address
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    public static function cacheAddressCoordinates($id_address, $latitude, $longitude)
    {
        // Cherche la zone correspondante
        $id_delivery_zone = self::getStoreByCoordinates($latitude, $longitude);

        $existing = Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'prestatill_homedelivery_address_cache`
            WHERE `id_address` = '.(int)$id_address
        );

        if ($existing) {
            // Mise à jour
            return Db::getInstance()->update(
                'prestatill_homedelivery_address_cache',
                array(
                    'latitude' => (float)$latitude,
                    'longitude' => (float)$longitude,
                    'id_delivery_zone' => $id_delivery_zone ? (int)$id_delivery_zone : null,
                    'date_upd' => date('Y-m-d H:i:s'),
                ),
                '`id_address` = '.(int)$id_address
            );
        } else {
            // Insertion
            return Db::getInstance()->insert(
                'prestatill_homedelivery_address_cache',
                array(
                    'id_address' => (int)$id_address,
                    'latitude' => (float)$latitude,
                    'longitude' => (float)$longitude,
                    'id_delivery_zone' => $id_delivery_zone ? (int)$id_delivery_zone : null,
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                )
            );
        }
    }

    /**
     * Récupère les coordonnées en cache pour une adresse
     *
     * @param int $id_address
     * @return array|false
     */
    public static function getCachedAddressCoordinates($id_address)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'prestatill_homedelivery_address_cache`
            WHERE `id_address` = '.(int)$id_address
        );
    }

    /**
     * Associe des jours de livraison à une zone
     *
     * @param int $id_delivery_zone
     * @param array $days Array des id_day
     * @return bool
     */
    public function setDeliveryDays($days)
    {
        if (!$this->id) {
            return false;
        }

        // Supprime les associations existantes
        Db::getInstance()->delete(
            'prestatill_homedelivery_zone_days',
            '`id_delivery_zone` = '.(int)$this->id
        );

        // Ajoute les nouvelles associations
        if (!empty($days)) {
            foreach ($days as $id_day) {
                Db::getInstance()->insert(
                    'prestatill_homedelivery_zone_days',
                    array(
                        'id_delivery_zone' => (int)$this->id,
                        'id_day' => (int)$id_day,
                    )
                );
            }
        }

        return true;
    }

    /**
     * Récupère les jours de livraison associés à une zone
     *
     * @return array
     */
    public function getDeliveryDays()
    {
        if (!$this->id) {
            return array();
        }

        $result = Db::getInstance()->executeS(
            'SELECT `id_day` FROM `'._DB_PREFIX_.'prestatill_homedelivery_zone_days`
            WHERE `id_delivery_zone` = '.(int)$this->id
        );

        $days = array();
        if ($result) {
            foreach ($result as $row) {
                $days[] = (int)$row['id_day'];
            }
        }

        return $days;
    }
}
