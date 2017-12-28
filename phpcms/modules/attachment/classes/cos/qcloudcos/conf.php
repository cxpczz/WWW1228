<?php

namespace qcloudcos;

class Conf {
    // Cos php sdk version number.
    const VERSION = 'v4.2.3';
   # const API_COSAPI_END_POINT = 'http://region.file.myqcloud.com/files/v2/';
	//define('VERSION',1);
    // Please refer to http://console.qcloud.com/cos to fetch your app_id, secret_id and secret_key.
    #const APP_ID = '1253791122';
    #const SECRET_ID = 'AKIDVwDKj2N8uTMAnEQw2MaeWHrLfCmD4mdO';
    #const SECRET_KEY = 'jLm385HVVQErIY3RefVE4ijdfaKsc3rc';

    /**
     * Get the User-Agent string to send to COS server.
     */
    public static function getUserAgent() {
        return 'cos-php-sdk-' . self::VERSION;
    }
}
