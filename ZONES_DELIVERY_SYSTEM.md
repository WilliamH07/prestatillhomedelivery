# Système de Zones de Livraison - PrestatillHomeDelivery

## Vue d'ensemble

Le module PrestatillHomeDelivery dispose désormais d'un système avancé de zones de livraison basé sur Google Maps, permettant de définir précisément les zones de livraison pour chaque restaurant/point de vente.

## Fonctionnalités

### 1. Gestion des zones dans l'administration

Le module offre une interface complète de gestion des zones accessible depuis :
**Modules > Gestionnaire des modules > PrestatillHomeDelivery > Onglet "Delivery Zones Management"**

#### Fonctionnalités disponibles :

- **Carte interactive Google Maps** : Visualisez et gérez vos zones de livraison directement sur une carte
- **Outils de dessin** :
  - Polygones (zones personnalisées)
  - Cercles (rayon autour d'un point)
  - Rectangles (zones rectangulaires)
- **Configuration par zone** :
  - Nom de la zone
  - Restaurant/point de vente assigné
  - Transporteur associé (optionnel)
  - Couleur de la zone
  - Jours de livraison
  - Priorité (en cas de zones qui se chevauchent)
  - Statut actif/inactif

### 2. Configuration requise

#### Clé API Google Maps

Le système nécessite une clé API Google Maps avec les APIs suivantes activées :
- Maps JavaScript API
- Geocoding API
- Drawing Library

**Configuration** :
1. Obtenir une clé API : https://developers.google.com/maps/documentation/javascript/get-api-key
2. Aller dans **Modules > PrestatillHomeDelivery > Onglet "Parameters"**
3. Entrer la clé dans le champ "Google Maps API KEY"

### 3. Activation du système de zones

1. Aller dans **Modules > PrestatillHomeDelivery > Onglet "Delivery Zones Management"**
2. Activer l'option "Use zone-based delivery system"
3. Sauvegarder la configuration

**Note** : Lorsque le système de zones est activé, il remplace le système de proximité géographique basé sur la distance.

## Architecture technique

### Tables de base de données

#### `ps_prestatill_homedelivery_delivery_zones`
Stocke les zones de livraison définies.

| Champ | Type | Description |
|-------|------|-------------|
| id_delivery_zone | INT | Identifiant unique de la zone |
| zone_name | VARCHAR(255) | Nom de la zone |
| id_store | INT | ID du restaurant assigné |
| id_carrier | INT | ID du transporteur (0 = tous) |
| zone_type | ENUM | Type : polygon, circle, rectangle |
| zone_data | TEXT | Données JSON de la géométrie |
| zone_color | VARCHAR(7) | Couleur hexadécimale |
| active | TINYINT | Statut actif/inactif |
| priority | INT | Priorité (plus élevé = prioritaire) |
| date_add | DATETIME | Date de création |
| date_upd | DATETIME | Date de modification |
| id_shop | INT | ID de la boutique |
| id_shop_group | INT | ID du groupe de boutiques |

#### `ps_prestatill_homedelivery_zone_days`
Associe les zones aux jours de livraison.

| Champ | Type | Description |
|-------|------|-------------|
| id_zone_day | INT | Identifiant unique |
| id_delivery_zone | INT | ID de la zone |
| id_day | INT | Jour de la semaine (1-7) |

#### `ps_prestatill_homedelivery_address_cache`
Cache de géolocalisation des adresses.

| Champ | Type | Description |
|-------|------|-------------|
| id_address_cache | INT | Identifiant unique |
| id_address | INT | ID de l'adresse PrestaShop |
| latitude | DECIMAL(10,8) | Latitude |
| longitude | DECIMAL(11,8) | Longitude |
| id_delivery_zone | INT | Zone trouvée pour cette adresse |
| date_add | DATETIME | Date de création |
| date_upd | DATETIME | Date de mise à jour |

### Fichiers modifiés/ajoutés

#### Fichiers de classe
- `classes/PrestatillHomeDeliveryZone.php` - Gestion des zones (existant, déjà implémenté)
- `classes/PrestatillHomeDeliveryConfiguration.php` - Modifié pour intégrer la logique de zones

#### Contrôleurs
- `controllers/front/validateordercarrier.php` - Ajout des actions AJAX pour gérer les zones

#### Templates Admin
- `views/templates/admin/configure.tpl` - Ajout de l'onglet "Delivery Zones Management"
- `views/templates/admin/tabs/zones_management.tpl` - Interface de gestion des zones
- `views/templates/admin/tabs/deliveryarea.tpl` - Ajout d'une alerte informant du nouveau système

#### JavaScript
- `views/js/zones_admin.js` - Gestion de la carte Google Maps et des zones (admin)

#### SQL
- `sql/zones_install.php` - Installation des tables de zones (existant)
- `sql/install.php` - Modifié pour inclure zones_install.php

#### Upgrade
- `upgrade/upgrade-3.1.0.php` - Script de migration (existant)

## Logique de fonctionnement

### 1. Lors de la commande (Frontend)

1. Le client saisit son adresse de livraison
2. Le module vérifie si le système de zones est activé (`PRESTATILL_HD_USE_ZONES`)
3. Si oui :
   - Géolocalisation de l'adresse (Google Maps ou Nominatim)
   - Vérification du cache de géolocalisation
   - Recherche de la zone correspondante via `PrestatillHomeDeliveryZone::getStoreByCoordinates()`
   - Attribution du restaurant associé à cette zone
   - Affichage des créneaux disponibles pour ce restaurant
4. Si non : utilisation de l'ancien système (proximité ou codes postaux)

### 2. Algorithmes de détection de zone

#### Polygones
Utilise l'algorithme **Ray Casting** pour déterminer si un point est à l'intérieur d'un polygone.

#### Cercles
Calcule la distance entre le point et le centre du cercle via la formule de **Haversine**, puis compare avec le rayon.

#### Rectangles
Vérifie si les coordonnées sont comprises dans les limites (nord, sud, est, ouest).

### 3. Gestion des priorités

Lorsque plusieurs zones se chevauchent pour une même adresse :
- La zone avec la priorité la plus élevée est sélectionnée
- En cas d'égalité, la première zone trouvée est utilisée

## Configuration recommandée

### Pour un restaurant unique
1. Créer une seule zone englobant toute la zone de livraison
2. Type : Polygone (pour une forme précise) ou Cercle (pour un rayon)
3. Assigner le restaurant unique
4. Définir les jours de livraison

### Pour plusieurs restaurants
1. Créer une zone par restaurant
2. Éviter les chevauchements (ou utiliser les priorités)
3. Assigner chaque zone à son restaurant correspondant
4. Définir les jours et transporteurs si nécessaires

### Optimisation des performances
- Le cache de géolocalisation réduit les appels API
- Les zones inactives ne sont pas prises en compte
- L'indexation SQL optimise les recherches

## Variables de configuration

| Variable | Type | Description | Défaut |
|----------|------|-------------|--------|
| PRESTATILL_HD_USE_ZONES | bool | Active le système de zones | 0 |
| PS_API_KEY | string | Clé API Google Maps | '' |

## API AJAX

### Actions disponibles

#### `getZones`
Récupère toutes les zones actives.
```
POST: action=getZones&id_shop={id_shop}
```

#### `saveZone`
Crée une nouvelle zone.
```
POST: action=saveZone&zone_name=...&id_store=...&zone_type=...&zone_data=...
```

#### `updateZone`
Met à jour une zone existante.
```
POST: action=updateZone&id_zone=...&zone_name=...&id_store=...
```

#### `deleteZone`
Supprime une zone.
```
POST: action=deleteZone&id_zone=...
```

#### `saveZonesConfiguration`
Sauvegarde la configuration globale des zones.
```
POST: action=saveZonesConfiguration&use_zones=...
```

## Dépannage

### La carte ne s'affiche pas
- Vérifiez que la clé API Google Maps est correctement configurée
- Vérifiez que les APIs requises sont activées
- Consultez la console JavaScript pour les erreurs

### Aucun restaurant trouvé
- Vérifiez que les zones sont bien activées
- Vérifiez que l'adresse du client est bien dans une zone définie
- Vérifiez les jours de livraison associés à la zone

### Problèmes de géolocalisation
- Le cache peut être vidé en supprimant les entrées dans `ps_prestatill_homedelivery_address_cache`
- Vérifiez les quotas de l'API Google Maps
- OpenStreetMap (Nominatim) est utilisé en fallback

## Migration depuis l'ancien système

Le système de zones **coexiste** avec l'ancien système :
- Activez `PRESTATILL_HD_USE_ZONES` pour utiliser les zones
- Désactivez pour revenir au système de proximité/codes postaux
- Les deux systèmes ne peuvent pas être actifs simultanément

## Support et documentation

Pour toute question ou problème :
- Documentation officielle : PrestaShop Addons
- Support : contact@adametdev.fr
- Module développé par : ADAM & DEV SAS

---

**Version** : 3.1.0
**Date** : 2025
**Auteur** : Laurent Baumgartner
