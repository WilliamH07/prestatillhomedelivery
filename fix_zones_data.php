<?php
/**
 * Script pour nettoyer les données de zones corrompues par pSQL()
 *
 * Exécuter ce script UNE SEULE FOIS depuis le navigateur :
 * https://votre-site.com/modules/prestatillhomedelivery/fix_zones_data.php
 *
 * Ce script va :
 * 1. Récupérer toutes les zones
 * 2. Essayer de décoder le zone_data
 * 3. Si échec, supprimer la zone (car données corrompues)
 */

// Inclure PrestaShop
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/classes/PrestatillHomeDeliveryZone.php');

// Sécurité : autoriser seulement en mode debug ou avec un token
$token = Tools::getValue('token');
$expected_token = md5(_COOKIE_KEY_ . 'fix_zones');

if ($token !== $expected_token && !_PS_MODE_DEV_) {
    die('Access denied. Use: ?token=' . $expected_token);
}

echo '<h1>Fix Zones Data</h1>';
echo '<p>Cleaning corrupted zone data...</p>';

// Récupérer toutes les zones
$sql = 'SELECT * FROM `'._DB_PREFIX_.'prestatill_homedelivery_delivery_zones`';
$zones = Db::getInstance()->executeS($sql);

if (empty($zones)) {
    echo '<p style="color: orange;">No zones found.</p>';
    exit;
}

$fixed = 0;
$deleted = 0;
$errors = 0;

echo '<table border="1" cellpadding="5">';
echo '<tr><th>ID</th><th>Name</th><th>Type</th><th>Status</th><th>Action</th></tr>';

foreach ($zones as $zone) {
    $id = $zone['id_delivery_zone'];
    $name = $zone['zone_name'];
    $type = $zone['zone_type'];
    $data = $zone['zone_data'];

    echo '<tr>';
    echo '<td>' . $id . '</td>';
    echo '<td>' . htmlspecialchars($name) . '</td>';
    echo '<td>' . $type . '</td>';

    // Essayer de décoder
    $decoded = json_decode($data, true);

    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        // Données corrompues
        echo '<td style="color: red;">CORRUPTED</td>';

        // Essayer de corriger
        // pSQL() remplace les guillemets par \'
        $fixed_data = str_replace("\\'", "'", $data);
        $fixed_data = str_replace("\\\"", "\"", $fixed_data);

        $decoded_fixed = json_decode($fixed_data, true);

        if ($decoded_fixed !== null) {
            // Correction réussie
            $update_sql = 'UPDATE `'._DB_PREFIX_.'prestatill_homedelivery_delivery_zones`
                          SET `zone_data` = "'.pSQL($fixed_data, true).'"
                          WHERE `id_delivery_zone` = '.(int)$id;

            if (Db::getInstance()->execute($update_sql)) {
                echo '<td style="color: green;">FIXED</td>';
                $fixed++;
            } else {
                echo '<td style="color: red;">FIX FAILED</td>';
                $errors++;
            }
        } else {
            // Impossible de corriger - supprimer
            $delete_sql = 'DELETE FROM `'._DB_PREFIX_.'prestatill_homedelivery_delivery_zones`
                          WHERE `id_delivery_zone` = '.(int)$id;

            // Supprimer aussi les jours associés
            $delete_days_sql = 'DELETE FROM `'._DB_PREFIX_.'prestatill_homedelivery_zone_days`
                               WHERE `id_delivery_zone` = '.(int)$id;

            Db::getInstance()->execute($delete_days_sql);

            if (Db::getInstance()->execute($delete_sql)) {
                echo '<td style="color: orange;">DELETED</td>';
                $deleted++;
            } else {
                echo '<td style="color: red;">DELETE FAILED</td>';
                $errors++;
            }
        }
    } else {
        // Données OK
        echo '<td style="color: green;">OK</td>';
        echo '<td>-</td>';
    }

    echo '</tr>';
}

echo '</table>';

echo '<h2>Summary</h2>';
echo '<ul>';
echo '<li>Total zones: ' . count($zones) . '</li>';
echo '<li style="color: green;">Fixed: ' . $fixed . '</li>';
echo '<li style="color: orange;">Deleted: ' . $deleted . '</li>';
echo '<li style="color: red;">Errors: ' . $errors . '</li>';
echo '</ul>';

echo '<p><strong>Done!</strong> You can now reload the zones management page.</p>';
echo '<p><a href="' . Context::getContext()->link->getAdminLink('AdminModules') . '&configure=prestatillhomedelivery">Go to module configuration</a></p>';
