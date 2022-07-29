<?php

namespace ModulesGarden\Geolocation\Submodules;

use GeoIp2\Database\Reader;
/**
 * Submodule GeoIP2
 */
class GeoIP2 {
    /**
     * getCountry return ISO 3166-1 Country Code (Alpha-2) by IP
     */
    public function getCountry($ip = null) {    
        if(!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."GeoIP2".DIRECTORY_SEPARATOR."geoip2.phar"))
        {
            throw new \Exception('geoip2.phar file not found.');
        }
        if(!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."GeoIP2".DIRECTORY_SEPARATOR."GeoLite2-Country.mmdb"))
        {
            throw new \Exception('GeoLite2-Country.mmdb file not found.');
        }
        require_once dirname(__FILE__).DIRECTORY_SEPARATOR."GeoIP2".DIRECTORY_SEPARATOR."geoip2.phar";

        $reader = new Reader(dirname(__FILE__).DIRECTORY_SEPARATOR.'GeoIP2'.DIRECTORY_SEPARATOR.'GeoLite2-Country.mmdb');
        $record = $reader->country(filter_var($ip, FILTER_VALIDATE_IP) ? $ip : $this->getRealIp());
        
        return $record->country->isoCode;
    }

    private function getRealIp()
    {
        $ip = $tmpip = $_SERVER['REMOTE_ADDR'];

        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }

        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = $tmpip;
        }

        return $ip;
    }
}
