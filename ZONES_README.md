# Système de Zones de Livraison - PrestaShop Home Delivery

## Vue d'ensemble

Ce module ajoute un système de gestion de zones de livraison basé sur Google Maps pour le module PrestaShop Home Delivery. Au lieu d'utiliser la proximité géographique simple (rayon), vous pouvez maintenant définir des zones de livraison précises et les associer à des restaurants spécifiques.

## Fonctionnalités

### 1. Gestion des Zones (Backoffice)

#### Accès
- Menu: **Commandes > Zones de livraison**

#### Création d'une Zone
1. Cliquez sur "Ajouter une zone"
2. Renseignez les informations:
   - **Nom de la zone**: Nom descriptif (ex: "Paris Centre")
   - **Restaurant**: Sélectionnez le restaurant responsable
   - **Transporteur**: Choisissez un transporteur spécifique ou "Tous les transporteurs"
   - **Couleur**: Couleur d'affichage sur la carte
   - **Priorité**: Ordre de vérification (plus élevé = vérifié en premier)
   - **Actif**: Active/désactive la zone

3. Dessiner la zone sur la carte:
   - **Polygone**: Pour des zones irrégulières (quartiers, arrondissements)
   - **Cercle**: Pour des zones circulaires (rayon autour d'un point)
   - **Rectangle**: Pour des zones rectangulaires

4. Sauvegardez

#### Édition d'une Zone
1. Cliquez sur l'icône d'édition
2. Modifiez les paramètres ou redessinez la zone
3. Sauvegardez

### 2. Affichage Frontend

Les zones de livraison s'affichent automatiquement sur la page de sélection du transporteur (carrier.tpl).

#### Ce que voit le client:
- **Carte interactive** avec toutes les zones de livraison
- **Légende** indiquant les restaurants pour chaque zone
- **Marqueur** sur leur adresse de livraison (s'ils sont connectés)
- **Notification** indiquant s'ils sont dans une zone de livraison

### 3. Logique de Sélection

#### Comment ça fonctionne:
1. Le client entre son adresse de livraison
2. Le système géolocalise l'adresse via Google Maps API
3. Le système vérifie si l'adresse est dans une zone de livraison active
4. Si oui, le restaurant associé à cette zone est automatiquement sélectionné
5. Les créneaux de livraison sont proposés en fonction du restaurant

#### Priorité des Zones:
- Si une adresse est dans plusieurs zones, la zone avec la **priorité la plus élevée** est sélectionnée
- Les zones sont vérifiées par ordre de priorité décroissant

## Installation

### Prérequis

1. **Clé API Google Maps**
   - Rendez-vous sur [Google Cloud Console](https://console.cloud.google.com/)
   - Créez un projet (ou utilisez-en un existant)
   - Activez les APIs suivantes:
     - Maps JavaScript API
     - Geocoding API
     - Geometry API (optionnel)
   - Créez une clé API et notez-la

2. **Configuration PrestaShop**
   - Assurez-vous que vos magasins (Stores) ont des coordonnées GPS valides
   - Menu: **Paramètres de la boutique > Contact > Magasins**

### Étapes d'Installation

1. **Exécuter le script d'installation des tables**
   ```php
   // Le script sera automatiquement exécuté lors de la mise à niveau du module vers la version 3.1.0
   // Ou manuellement via:
   include(_PS_MODULE_DIR_.'prestatillhomedelivery/sql/zones_install.php');
   ```

2. **Configurer la clé API Google Maps**
   - Allez dans la configuration du module
   - Section "Paramètres de livraison"
   - Collez votre clé API dans le champ "Clé API Google Maps"
   - Sauvegardez

3. **Activer le mode Zones**
   - Dans la configuration du module
   - Activez l'option "Utiliser les zones de livraison"
   - Sauvegardez

4. **(Optionnel) Migrer les données existantes**
   ```php
   // Si vous utilisiez l'ancien système de proximité
   require_once(_PS_MODULE_DIR_.'prestatillhomedelivery/sql/migrate_to_zones.php');
   $results = executeMigration();
   ```

## Migration depuis l'Ancien Système

### Zones de Proximité (Rayon)

Le script de migration `migrate_to_zones.php` convertit automatiquement:
- Les zones basées sur un **rayon** (distance en km) → Zones de type **cercle**
- Le centre du cercle = coordonnées GPS du magasin
- Le rayon = distance en mètres

### Codes Postaux

⚠️ **Attention**: La migration des codes postaux nécessite du travail manuel car:
- Les codes postaux ne correspondent pas à des zones géographiques précises
- Vous devrez dessiner manuellement les zones correspondantes dans l'interface d'administration

**Recommandation**:
1. Exportez la liste de vos codes postaux
2. Créez des zones polygonales pour regrouper les codes postaux par quartier/zone
3. Associez chaque zone au restaurant approprié

## Structure de la Base de Données

### Table: `prestatill_homedelivery_delivery_zones`

| Champ | Type | Description |
|-------|------|-------------|
| id_delivery_zone | INT | ID unique de la zone |
| zone_name | VARCHAR(255) | Nom de la zone |
| id_store | INT | ID du restaurant responsable |
| id_carrier | INT | ID du transporteur (0 = tous) |
| zone_type | ENUM | Type: polygon, circle, rectangle |
| zone_data | TEXT | Données JSON de la zone |
| zone_color | VARCHAR(7) | Couleur hex (#RRGGBB) |
| active | TINYINT | 1 = actif, 0 = inactif |
| priority | INT | Priorité (plus élevé = vérifié en premier) |
| date_add | DATETIME | Date de création |
| date_upd | DATETIME | Date de modification |

### Table: `prestatill_homedelivery_zone_days`

| Champ | Type | Description |
|-------|------|-------------|
| id_zone_day | INT | ID unique |
| id_delivery_zone | INT | ID de la zone |
| id_day | INT | ID du jour (1-7) |

### Table: `prestatill_homedelivery_address_cache`

| Champ | Type | Description |
|-------|------|-------------|
| id_address_cache | INT | ID unique |
| id_address | INT | ID de l'adresse PrestaShop |
| latitude | DECIMAL | Latitude |
| longitude | DECIMAL | Longitude |
| id_delivery_zone | INT | Zone associée |
| date_add | DATETIME | Date de création |
| date_upd | DATETIME | Date de mise à jour |

## Format des Données de Zone

### Polygone
```json
[
  {"lat": 48.8566, "lng": 2.3522},
  {"lat": 48.8606, "lng": 2.3376},
  {"lat": 48.8530, "lng": 2.3499}
]
```

### Cercle
```json
{
  "center": {"lat": 48.8566, "lng": 2.3522},
  "radius": 5000
}
```

### Rectangle
```json
{
  "bounds": {
    "north": 48.8700,
    "south": 48.8400,
    "east": 2.3800,
    "west": 2.3200
  }
}
```

## API et Méthodes Principales

### PrestatillHomeDeliveryZone

#### Méthodes Statiques

```php
// Récupère toutes les zones actives
PrestatillHomeDeliveryZone::getActiveZones($id_shop, $id_carrier);

// Récupère les zones d'un restaurant
PrestatillHomeDeliveryZone::getZonesByStore($id_store, $id_shop);

// Trouve le restaurant pour des coordonnées
PrestatillHomeDeliveryZone::getStoreByCoordinates($latitude, $longitude, $id_carrier, $id_shop);

// Cache les coordonnées d'une adresse
PrestatillHomeDeliveryZone::cacheAddressCoordinates($id_address, $latitude, $longitude);

// Récupère le cache pour une adresse
PrestatillHomeDeliveryZone::getCachedAddressCoordinates($id_address);
```

#### Méthodes d'Instance

```php
$zone = new PrestatillHomeDeliveryZone($id_delivery_zone);

// Associe des jours de livraison
$zone->setDeliveryDays([1, 2, 3, 4, 5]); // Lundi à Vendredi

// Récupère les jours
$days = $zone->getDeliveryDays();
```

## Bonnes Pratiques

### 1. Définition des Zones

- **Évitez les chevauchements**: Si deux zones se chevauchent, utilisez la priorité pour définir laquelle doit être vérifiée en premier
- **Zones cohérentes**: Dessinez des zones qui correspondent à la capacité de livraison du restaurant
- **Couleurs distinctes**: Utilisez des couleurs différentes pour faciliter la visualisation

### 2. Performance

- **Cache des coordonnées**: Le système met en cache les coordonnées des adresses pour éviter les appels répétés à l'API de géocodage
- **Optimisation des requêtes**: Les zones sont chargées une seule fois et vérifiées côté serveur

### 3. Sécurité

- **Limitation de la clé API**: Restreignez votre clé API Google Maps:
  - Par domaine (pour éviter l'utilisation non autorisée)
  - Par API (activez uniquement les APIs nécessaires)
  - Définissez des quotas si possible

## Dépannage

### La carte ne s'affiche pas

1. Vérifiez que la clé API Google Maps est correcte
2. Vérifiez que les APIs sont activées dans Google Cloud Console
3. Ouvrez la console du navigateur pour voir les erreurs JavaScript
4. Vérifiez que le domaine est autorisé dans les restrictions de la clé API

### Les zones ne sont pas détectées

1. Vérifiez que les zones sont **actives**
2. Vérifiez que le transporteur est correctement associé
3. Vérifiez que l'adresse a bien été géolocalisée (vérifiez le cache)
4. Vérifiez que les coordonnées GPS sont correctes

### Problèmes de géolocalisation

1. Vérifiez que l'API Geocoding est activée
2. Vérifiez que l'adresse est complète (rue, ville, code postal, pays)
3. Videz le cache des adresses si nécessaire:
   ```sql
   TRUNCATE TABLE `ps_prestatill_homedelivery_address_cache`;
   ```

## Support

Pour toute question ou problème:
- Email: contact@adametdev.fr
- Documentation: [Lien vers la documentation]

## Changelog

### Version 3.1.0
- Ajout du système de zones de livraison
- Intégration Google Maps
- Migration depuis l'ancien système de proximité
- Interface d'administration pour dessiner les zones
- Affichage des zones sur le frontend
- Cache de géolocalisation des adresses
