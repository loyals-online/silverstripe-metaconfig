<?php

namespace Loyals\MetaConfig\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Core\Config\Config;

/*
 * @TODO Move this to it's own extension, and require this via composer
 */
/**
 * Google Geocoding model that
 *
 * @Author Martijn Schenk
 * @Alias  Chibby
 * @Email  martijnschenk@loyals.nl
 */
class GoogleGeocoding extends DataObject
{
    private static $table_name = 'GoogleGeocoding';

    /**
     * The endpoint for the Google URL Shortener API
     *
     * @var string
     */
    protected static $url = 'https://maps.googleapis.com/maps/api/geocode/json';

    /**
     * The API key for the Google URL Shortener API
     *
     * @var string
     */
    protected $key;

    /**
     * @inheriteddocs
     *
     * @var array
     */
    private static $db = [
        'AddressString' => 'Varchar',
        'Longitude'     => 'Decimal(14,10)',
        'Latitude'      => 'Decimal(14,10)',
    ];

    /**
     * Retrieve or create a short URL
     *
     * @param $longURL
     *
     * @return DataObject|static
     */
    public static function getOrCreateGeocode($addressString)
    {
        $item = static::get()
            ->filter([
                'AddressString' => $addressString,
            ])
            ->first();

        if (!$item) {
            $item = static::create()
                ->request($addressString);
        }

        return $item;
    }

    /**
     * Request the short URL
     *
     * @param $longURL
     *
     * @return static
     */
    protected function request($addressString)
    {
        $this->key = $this->getConfig('Google', 'Api', 'key');

        $item = static::create();

        if ($response = $this->performCurlRequest($addressString)) {
            $item->AddressString  = $addressString;
            $item->Latitude = (float) $response->lat;
            $item->Longitude = (float) $response->lng;
            $item->write();
        }

        return $item;
    }

    /**
     * Perform the actual cURL request to the Google URL Shortener API
     *
     * @param string $addressString
     *
     * @return string|bool
     */
    protected function performCurlRequest($addressString)
    {

        $url = static::$url . '?key=' . $this->key . '&' . http_build_query(['address' => $addressString]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        $result = json_decode($response);

        if (isset($result->results, $result->results[0]) && $result->results[0]) {
            return $result->results[0]->geometry->location;
        }

        return false;
    }

    /**
     * Retrieve something from the environment config
     *
     * @param string $item1 [optional]
     * @param string $itemN [optional]
     *
     * @return mixed
     */
    protected function getConfig()
    {
        if (!$config = Config::inst()
            ->get('Environment', BASE_PATH)
        ) {
            $config = Config::inst()
                ->get('Environment', 'default');
        }

        foreach (func_get_args() as $item) {
            if (isset($config[$item])) {
                $config = $config[$item];
            } else {
                return null;
            }
        }

        return $config;
    }
}
