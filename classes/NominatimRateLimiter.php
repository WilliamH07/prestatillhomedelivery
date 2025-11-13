<?php
/**
* Nominatim Rate Limiter
*
* Manages OpenStreetMap Nominatim API rate limiting (max 1 request per second)
*
*  @author    Claude Code
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class NominatimRateLimiter
{
    const RATE_LIMIT_KEY = 'NOMINATIM_LAST_REQUEST_TIME';
    const MIN_INTERVAL_SECONDS = 1; // OpenStreetMap requirement: max 1 request per second

    /**
     * Wait if necessary to respect the rate limit (1 request per second)
     *
     * @return void
     */
    public static function waitIfNeeded()
    {
        $last_request_time = Configuration::get(self::RATE_LIMIT_KEY);

        if ($last_request_time) {
            $current_time = microtime(true);
            $time_since_last_request = $current_time - (float)$last_request_time;

            // If less than 1 second has passed, wait the remaining time
            if ($time_since_last_request < self::MIN_INTERVAL_SECONDS) {
                $wait_time = self::MIN_INTERVAL_SECONDS - $time_since_last_request;
                usleep((int)($wait_time * 1000000)); // Convert to microseconds
            }
        }

        // Update the last request time
        Configuration::updateValue(self::RATE_LIMIT_KEY, microtime(true));
    }

    /**
     * Get the recommended User-Agent for Nominatim
     *
     * @return string
     */
    public static function getUserAgent()
    {
        $shop_email = Configuration::get('PS_SHOP_EMAIL');
        $shop_name = Configuration::get('PS_SHOP_NAME');

        if (empty($shop_email)) {
            $shop_email = 'noreply@example.com';
        }

        if (empty($shop_name)) {
            $shop_name = 'PrestaShop';
        }

        return 'PrestaShop-HomeDelivery/3.1.1 (' . $shop_name . '; ' . $shop_email . ')';
    }

    /**
     * Get the HTTP Referer for Nominatim
     *
     * @return string
     */
    public static function getReferer()
    {
        $shop_url = Configuration::get('PS_SHOP_DOMAIN');

        if (empty($shop_url)) {
            $shop_url = $_SERVER['HTTP_HOST'] ?? 'localhost';
        }

        return 'https://' . $shop_url;
    }

    /**
     * Create stream context for Nominatim with proper headers
     *
     * @param int $timeout Timeout in seconds
     * @return resource Stream context
     */
    public static function createStreamContext($timeout = 5)
    {
        $options = array(
            'http' => array(
                'method' => 'GET',
                'header' => "User-Agent: " . self::getUserAgent() . "\r\n" .
                           "Referer: " . self::getReferer() . "\r\n",
                'timeout' => $timeout,
                'ignore_errors' => true
            )
        );

        return stream_context_create($options);
    }
}
