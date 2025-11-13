# 🗺️ Installation du Système de Zones de Livraison

## Installation Rapide (3 étapes)

### Étape 1: Installer les tables en base de données

**Option A - Installation automatique (RECOMMANDÉ)**
1. Ouvrez votre navigateur
2. Allez sur: `https://votresite.com/modules/prestatillhomedelivery/install_zones.php`
3. Suivez les instructions à l'écran
4. **Supprimez le fichier `install_zones.php` après l'installation**

**Option B - Installation manuelle**
1. Ouvrez phpMyAdmin
2. Sélectionnez votre base de données PrestaShop
3. Copiez le contenu de `sql/zones_install.php`
4. Collez et exécutez le SQL

### Étape 2: Intégrer le code dans le module

Suivez le guide détaillé dans le fichier `INTEGRATION_GUIDE.php`.

Les principaux changements à faire:

1. **Dans `prestatillhomedelivery.php`** - Ajouter le require de la classe
2. **Dans `getContent()`** - Ajouter la gestion de l'onglet zones
3. **Dans `assignConfiguration()`** - Passer les variables au template
4. **Dans `loadAsset()`** - Charger Google Maps API et les JS/CSS
5. **Dans `controllers/front/validateordercarrier.php`** - Ajouter les méthodes AJAX

### Étape 3: Configuration

1. Allez dans **Modules > Gestionnaire des modules > PrestatillHomeDelivery**
2. Cliquez sur l'onglet **"Delivery Zones"**
3. Entrez votre **clé API Google Maps**
   - Obtenez-la sur: https://console.cloud.google.com/
   - Activez les APIs: Maps JavaScript, Geocoding, Drawing
4. Activez **"Use Zone-Based Delivery"**
5. Sauvegardez

## Utilisation

### Créer une zone de livraison

1. Dans l'onglet "Delivery Zones", cliquez sur **"Add New Zone"**
2. Remplissez les informations:
   - **Nom** de la zone
   - **Restaurant** responsable
   - **Transporteur** (ou "Tous")
   - **Couleur** d'affichage
   - **Priorité** (plus haute = vérifiée en premier)
3. **Dessinez** la zone sur la carte:
   - Cliquez sur "Polygon" / "Circle" / "Rectangle"
   - Dessinez sur la carte
4. Cliquez sur **"Save Zone"**

### Éditer / Supprimer une zone

- Cliquez sur le bouton ✏️ pour **éditer**
- Cliquez sur le bouton 🗑️ pour **supprimer**

## Fichiers créés

### Templates
- `views/templates/admin/configure.tpl` - ✅ Modifié (ajout onglet)
- `views/templates/admin/tabs/zones.tpl` - ✅ Créé
- `views/templates/admin/zone_map.tpl` - ✅ Créé (ancien, peut être supprimé)
- `views/templates/front/zones_map.tpl` - ✅ Créé

### JavaScript
- `views/js/zones_tab.js` - ✅ Créé (interface admin)
- `views/js/admin_zones.js` - ✅ Créé (ancien, peut être supprimé)
- `views/js/front_zones.js` - ✅ Créé (affichage client)

### CSS
- `views/css/admin_zones.css` - ✅ Créé
- `views/css/front_zones.css` - ✅ Créé

### PHP
- `classes/PrestatillHomeDeliveryZone.php` - ✅ Créé
- `controllers/admin/AdminPrestatillHomeDeliveryZones.php` - ✅ Créé (peut être supprimé)
- `sql/zones_install.php` - ✅ Créé
- `sql/migrate_to_zones.php` - ✅ Créé
- `upgrade/upgrade-3.1.0.php` - ✅ Créé

### Utilitaires
- `install_zones.php` - ⚠️ À SUPPRIMER après installation
- `INTEGRATION_GUIDE.php` - 📖 Guide d'intégration
- `ZONES_README.md` - 📖 Documentation complète
- `README_INSTALLATION_ZONES.md` - 📖 Ce fichier

## Vérification de l'installation

### 1. Tables créées
Vérifiez dans phpMyAdmin que ces tables existent:
- `ps_prestatill_homedelivery_delivery_zones`
- `ps_prestatill_homedelivery_zone_days`
- `ps_prestatill_homedelivery_address_cache`

### 2. Onglet visible
Allez dans la configuration du module, vous devez voir l'onglet "Delivery Zones" avec une icône 📍

### 3. Carte affichée
Après avoir configuré la clé API Google Maps, la carte doit s'afficher dans la modale "Add New Zone"

## Dépannage

### ❌ L'onglet "Delivery Zones" n'apparaît pas
- Vérifiez que vous avez bien modifié `configure.tpl`
- Videz le cache PrestaShop: **Paramètres avancés > Performances > Vider le cache**

### ❌ La carte ne s'affiche pas
- Vérifiez votre clé API Google Maps
- Vérifiez que les APIs sont activées (Maps JavaScript, Geocoding, Drawing)
- Ouvrez la console du navigateur (F12) pour voir les erreurs
- Vérifiez que le JS est chargé: `views/js/zones_tab.js`

### ❌ Erreur lors de la sauvegarde des zones
- Vérifiez les logs d'erreur PHP
- Vérifiez que les tables sont bien créées
- Vérifiez que le contrôleur `validateordercarrier.php` a bien les méthodes AJAX

### ❌ Les zones ne se chargent pas
- Ouvrez la console réseau (F12 > Network)
- Vérifiez les appels AJAX vers `validateordercarrier`
- Vérifiez que les méthodes AJAX renvoient bien du JSON

## Migration depuis l'ancien système

Si vous utilisiez le système basé sur la proximité (rayon):

1. Exécutez le script de migration:
```php
include('sql/migrate_to_zones.php');
$results = executeMigration();
var_dump($results);
```

2. Les zones basées sur un **rayon** seront converties en **cercles**
3. Les **codes postaux** devront être recréés manuellement (dessinez les zones)

## Support

- 📧 Email: contact@adametdev.fr
- 📚 Documentation complète: `ZONES_README.md`
- 🐛 Bugs: Vérifiez les logs et la console du navigateur

## Checklist complète

- [ ] Tables SQL installées
- [ ] Classe `PrestatillHomeDeliveryZone.php` incluse dans le module
- [ ] Méthode `getContent()` modifiée
- [ ] Méthode `assignConfiguration()` modifiée
- [ ] Méthode `loadAsset()` créée/modifiée
- [ ] Méthodes AJAX ajoutées dans `validateordercarrier.php`
- [ ] Clé API Google Maps configurée
- [ ] Mode "Use Zones" activé
- [ ] Première zone créée avec succès
- [ ] Zone affichée dans la liste
- [ ] Fichier `install_zones.php` supprimé

---

**🎉 Une fois terminé, vous pourrez gérer vos zones de livraison directement depuis la configuration du module !**
