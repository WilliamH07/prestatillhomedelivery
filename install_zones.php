<?php
/**
 * INSTALLATEUR RAPIDE DES ZONES DE LIVRAISON
 *
 * Ce script installe les tables nécessaires pour le système de zones
 * USAGE: Ouvrez ce fichier dans votre navigateur une seule fois
 * URL: https://votresite.com/modules/prestatillhomedelivery/install_zones.php
 */

// Sécurité basique
define('_PS_ADMIN_DIR_', getcwd());
include(dirname(__FILE__).'/../../config/config.inc.php');

// Vérification que l'utilisateur est admin
if (!Context::getContext()->employee || !Context::getContext()->employee->id) {
    die('Accès refusé. Vous devez être connecté en tant qu\'administrateur.');
}

echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Installation des Zones de Livraison</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #00aff0; padding-bottom: 10px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .warning { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; margin: 10px 0; border-radius: 4px; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #00aff0; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px 10px 0; }
        .btn:hover { background: #0099d6; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗺️ Installation du Système de Zones de Livraison</h1>';

// Vérifie si les tables existent déjà
$tables_exist = Db::getInstance()->executeS("SHOW TABLES LIKE '"._DB_PREFIX_."prestatill_homedelivery_delivery_zones'");

if (!empty($tables_exist)) {
    echo '<div class="warning">
        <strong>⚠️ Attention:</strong> Les tables de zones semblent déjà exister.
        L\'installation va être ignorée pour éviter la perte de données.
    </div>';
    echo '<p><a href="'.Context::getContext()->link->getAdminLink('AdminModules').'&configure=prestatillhomedelivery" class="btn">← Retour à la configuration</a></p>';
    echo '</div></body></html>';
    exit;
}

// Chargement du script SQL
include(dirname(__FILE__).'/sql/zones_install.php');

echo '<div class="info"><strong>ℹ️ Début de l\'installation...</strong></div>';

$errors = array();
$success_count = 0;

// Exécution des requêtes SQL
if (!empty($sql_requests)) {
    foreach ($sql_requests as $index => $request) {
        try {
            $result = Db::getInstance()->execute($request);

            if ($result) {
                $success_count++;
                echo '<div class="success">✓ Table créée avec succès ('.($index + 1).'/'.count($sql_requests).')</div>';
            } else {
                $error_msg = Db::getInstance()->getMsgError();
                $errors[] = 'Erreur lors de la création de la table: '.$error_msg;
                echo '<div class="error">✗ Erreur: '.$error_msg.'</div>';
            }
        } catch (Exception $e) {
            $errors[] = 'Exception: '.$e->getMessage();
            echo '<div class="error">✗ Exception: '.$e->getMessage().'</div>';
        }
    }
} else {
    echo '<div class="error">✗ Aucune requête SQL trouvée dans le fichier d\'installation.</div>';
}

// Initialisation des configurations
if (empty($errors)) {
    echo '<div class="info"><strong>Configuration des paramètres...</strong></div>';

    Configuration::updateValue('PRESTATILL_HD_GOOGLE_MAPS_API_KEY', '');
    Configuration::updateValue('PRESTATILL_HD_USE_ZONES', '0');

    echo '<div class="success">✓ Paramètres de configuration initialisés</div>';
}

// Résumé
echo '<hr>';
echo '<h2>📊 Résumé de l\'installation</h2>';

if (empty($errors)) {
    echo '<div class="success">
        <h3>✅ Installation réussie !</h3>
        <p><strong>'.$success_count.' tables créées avec succès.</strong></p>
        <p>Le système de zones de livraison est maintenant prêt à être utilisé.</p>
    </div>';

    echo '<h3>🎯 Prochaines étapes:</h3>
    <ol>
        <li>Configurez votre <strong>clé API Google Maps</strong> dans l\'onglet "Delivery Zones"</li>
        <li>Activez le mode "Use Zone-Based Delivery"</li>
        <li>Créez vos premières zones de livraison</li>
        <li>Associez chaque zone à un restaurant</li>
    </ol>';

    echo '<p><a href="'.Context::getContext()->link->getAdminLink('AdminModules').'&configure=prestatillhomedelivery&tab=6" class="btn">🚀 Configurer les zones maintenant</a></p>';

} else {
    echo '<div class="error">
        <h3>❌ L\'installation a rencontré des erreurs</h3>
        <p><strong>'.count($errors).' erreur(s) détectée(s).</strong></p>
        <ul>';

    foreach ($errors as $error) {
        echo '<li>'.$error.'</li>';
    }

    echo '</ul>
    </div>';

    echo '<h3>🔧 Comment résoudre:</h3>
    <ol>
        <li>Vérifiez que votre base de données est accessible</li>
        <li>Vérifiez que votre utilisateur MySQL a les droits CREATE TABLE</li>
        <li>Consultez les logs d\'erreur de votre serveur</li>
        <li>Contactez votre hébergeur si le problème persiste</li>
    </ol>';
}

echo '<hr>';
echo '<h3>📋 Tables créées:</h3>
<ul>
    <li><code>'._DB_PREFIX_.'prestatill_homedelivery_delivery_zones</code> - Stocke les zones de livraison</li>
    <li><code>'._DB_PREFIX_.'prestatill_homedelivery_zone_days</code> - Associe les zones aux jours de livraison</li>
    <li><code>'._DB_PREFIX_.'prestatill_homedelivery_address_cache</code> - Cache de géolocalisation des adresses</li>
</ul>';

echo '<div class="warning">
    <strong>⚠️ Important:</strong> Pour des raisons de sécurité, supprimez ce fichier (install_zones.php) après l\'installation.
</div>';

echo '<p><a href="'.Context::getContext()->link->getAdminLink('AdminModules').'&configure=prestatillhomedelivery" class="btn">← Retour à la configuration</a></p>';

echo '</div></body></html>';
