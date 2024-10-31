<?php

class Nettle_API_Handler {
    /** @var string Nettle Pay API key ID. */
    public static $api_key_id;

    /** @var string Nettle Pay API key secret. */
    public static $api_key_secret;

    /** @var string Nettle Pay API url. */
    public static $api_url;

    /** @var string/array Log variable function. */
    public static $log;

    public static function createPayment($requestParams) {
        $headers = array(
            'Authorization' => 'Basic '.base64_encode(self::$api_key_id.':'.self::$api_key_secret),
            'Content-Type' => 'application/json'
        );

        self::log("[INFO] creating a payment request");

        $data_to_post = json_encode($requestParams);
        self::log("[INFO] " . $data_to_post);

        $args = array(
            'body' => $data_to_post,
            'headers' => $headers,
            'timeout' => 120
        );
        $response = wp_remote_post(self::$api_url.'/apps/public/checkout/payment', $args);

        if (is_wp_error($response)) {
            self::log("[INFO] HTTP Failed: " . $response->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $http_code = wp_remote_retrieve_response_code($response);

        self::log('[INFO] HTTP status: ' . $http_code);
        self::log('[INFO] HTTP body: ' . $body);

        if ($http_code === 201) {
            return json_decode($body, true);
        }
    }

    public static function findCurrencyByCode($currencyCode) {
        $headers = array(
            'Authorization' => 'Basic '.base64_encode(self::$api_key_id.':'.self::$api_key_secret),
            'Content-Type' => 'application/json'
        );

        self::log("[INFO] getting currency info for code " . $currencyCode);

        $args = array(
            'headers' => $headers,
            'timeout' => 120
        );
        $response = wp_remote_get(self::$api_url . '/apps/public/currency/' . $currencyCode, $args);

        if (is_wp_error($response)) {
            self::log("[INFO] HTTP failed: " . $response->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $http_code = wp_remote_retrieve_response_code($response);

        self::log('[INFO] HTTP status: ' . $http_code);
        self::log('[INFO] HTTP body: ' . $body);

        if ($http_code === 200) {
            return json_decode($body, true);
        }
    }

    /**
     * Call the $log variable function.
     *
     * @param string $message Log message.
     * @param string $level   Optional. Default 'info'.
     *     emergency|alert|critical|error|warning|notice|info|debug
     */
    public static function log($message, $level = 'info') {
        return call_user_func(self::$log, $message, $level);
    }
}
