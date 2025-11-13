# Configuration du système de géocodage avec fallback

## Vue d'ensemble

Le module dispose maintenant d'un système de géocodage avec **3 niveaux de fallback** pour garantir la disponibilité du service même si une API est indisponible.

**✅ Mise à jour 3.1.1 :** Configuration des clés API directement dans l'interface d'administration du module + respect complet des exigences d'OpenStreetMap Nominatim.

## Ordre des tentatives de géocodage

Le système essaie les APIs dans l'ordre suivant jusqu'à obtenir un résultat :

### 1️⃣ **OpenStreetMap Nominatim** (Gratuit)
- **URL** : `https://nominatim.openstreetmap.org/search`
- **Avantages** : Gratuit, pas de clé API nécessaire
- **Inconvénients** : Limites de taux strictes (max 1 requête/seconde)
- **Configuration** : Aucune (utilisé par défaut)
- **✅ Conformité** :
  - Rate limiting automatique (1 req/sec)
  - User-Agent personnalisé avec email de la boutique
  - HTTP Referer configuré
  - Attribution ODbL respectée

### 2️⃣ **Geocode.Maps.Co** (Freemium)
- **URL** : `https://geocode.maps.co/search`
- **Avantages** : Gratuit jusqu'à 1000 requêtes/jour
- **Inconvénients** : Nécessite une clé API
- **Configuration** :
  1. Aller dans **Modules → PrestatillHomeDelivery → Configurer**
  2. Section **"Geocoding API Configuration"**
  3. Entrer la clé API dans le champ **"Geocode.Maps.Co API Key"**
  4. Sauvegarder
- **Obtenir une clé** : https://geocode.maps.co/ (gratuit, inscription simple)

### 3️⃣ **Google Maps Geocoding API** (Payant avec crédit gratuit)
- **URL** : `https://maps.googleapis.com/maps/api/geocode/json`
- **Avantages** : Très fiable, précis, 200$/mois de crédit gratuit
- **Inconvénients** : Nécessite une clé API et un compte Google Cloud
- **Configuration** :
  1. Aller dans **Modules → PrestatillHomeDelivery → Configurer**
  2. Section **"Geocoding API Configuration"**
  3. Entrer la clé API dans le champ **"Google Maps Geocoding API Key"**
  4. Sauvegarder
- **Obtenir une clé** : https://console.cloud.google.com/ (nécessite une carte bancaire)

## Configuration recommandée

### Pour un site en production
1. **Obligatoire** : Configurer au minimum **Geocode.Maps.Co** (gratuit jusqu'à 1000 req/jour)
2. **Recommandé** : Configurer aussi **Google Maps** comme ultime fallback
3. Nominatim sera toujours essayé en premier (gratuit) avec rate limiting automatique

### Pour un site de développement/test
- Utiliser uniquement Nominatim (gratuit, avec rate limiting de 1 req/sec)
- Ou désactiver complètement le système de zones si non nécessaire

### OpenStreetMap Nominatim - Exigences respectées

Le module respecte **toutes les exigences** de la politique d'utilisation de Nominatim :

✅ **Rate Limiting** : Maximum 1 requête par seconde (géré automatiquement)
✅ **User-Agent** : Identifiant personnalisé avec nom de boutique et email
✅ **HTTP Referer** : URL de la boutique envoyée dans chaque requête
✅ **Attribution** : License ODbL respectée (données OpenStreetMap)

Le système attend automatiquement entre les requêtes pour respecter la limite de 1 req/sec.

## Désactiver le système de zones

Si vous n'avez pas besoin de la vérification géographique par zones :

### Option 1 : Via le back-office PrestaShop
1. Modules → Prestatill Home Delivery → Configuration
2. Onglet "Zones Management"
3. Désactiver "Use Zones"

### Option 2 : Via SQL
```sql
UPDATE `ps_configuration`
SET `value` = '0'
WHERE `name` = 'PRESTATILL_HD_USE_ZONES';
```

## Debugging

Le système inclut des logs détaillés dans la console du navigateur :

```javascript
=== PRESTATILL HOME DELIVERY DEBUG ===
--- GEOCODING ATTEMPTS ---
Attempts: ["Nominatim: FAILED", "Geocode.Maps.Co: SUCCESS"]
1️⃣ Nominatim URL: ...
   Nominatim Error: Access blocked
2️⃣ Geocode.Maps.Co URL: ...
   Geocode.Maps.Co Coordinates: {lat: 44.8789, lon: -0.6523}
--- FINAL RESULT ---
Final Coordinates: {latitude: 44.8789, longitude: -0.6523}
```

## Timeouts et performance

- Chaque API a un timeout de **5 secondes**
- Si les 3 APIs échouent, le temps maximum est de **15 secondes**
- L'utilisateur recevra le message : "Impossible de vérifier votre adresse"

## Résolution des problèmes

### Nominatim bloque mon IP
**Symptôme** : Message "Access blocked" dans les logs

**Solutions** :
1. Configurer Geocode.Maps.Co ou Google Maps (le système basculera automatiquement)
2. Attendre quelques heures/jours pour le déblocage automatique
3. Contacter nominatim@openstreetmap.org
4. Changer d'hébergeur/IP

### Toutes les APIs échouent
**Symptôme** : "Impossible de vérifier votre adresse" même avec les clés configurées

**Solutions** :
1. Vérifier les logs dans la console du navigateur
2. Vérifier que les clés API sont correctement configurées
3. Vérifier que le serveur peut faire des requêtes HTTP sortantes
4. Désactiver temporairement le système de zones

## Sécurité

- Les clés API ne sont jamais exposées dans les logs (remplacées par "HIDDEN_KEY")
- Les requêtes utilisent un User-Agent personnalisé conforme aux politiques
- Les timeouts empêchent les blocages prolongés

## Configuration SQL complète

```sql
-- Activer le système de zones
UPDATE `ps_configuration` SET `value` = '1' WHERE `name` = 'PRESTATILL_HD_USE_ZONES';

-- Configurer Geocode.Maps.Co (optionnel mais recommandé)
INSERT INTO `ps_configuration` (`name`, `value`)
VALUES ('GEOCODE_MAPS_API_KEY', 'VOTRE_CLE_GEOCODE_MAPS')
ON DUPLICATE KEY UPDATE `value` = 'VOTRE_CLE_GEOCODE_MAPS';

-- Configurer Google Maps (optionnel)
INSERT INTO `ps_configuration` (`name`, `value`)
VALUES ('PS_API_KEY', 'VOTRE_CLE_GOOGLE')
ON DUPLICATE KEY UPDATE `value` = 'VOTRE_CLE_GOOGLE';
```

## Support

Pour toute question ou problème :
- Consultez les logs dans la console du navigateur (F12)
- Vérifiez la configuration des clés API
- Testez avec le système de zones désactivé pour isoler le problème
