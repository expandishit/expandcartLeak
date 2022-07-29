<?php

namespace ExpandCart\Foundation\Providers;

class DedicatedDomains
{
    /**
     * The server name string.
     *
     * @var string
     */
    private $serverName;

    /**
     * The domains list.
     *
     * @var array
     */
    private $domains = null;

    /**
     * The dedicated domains table name.
     *
     * @var string
     */
    private $table = DB_PREFIX . 'dedicated_domains';

    /**
     * The registry object.
     *
     * @var Registry
     */
    private $registry;

    /**
     * The dedcated domains options list.
     *
     * @var array
     */
    public $options;

    /**
     * Variable to check the application status.
     *
     * @var bool
     */
    public $appInstalled = false;

    /**
     * Static insance to be used as a singleton object.
     *
     * @var bool|array
     */
    public static $instance;

    /**
     * Set the server name.
     *
     * @param string $serverName
     *
     * @return \ExpandCart\Foundation\Providers\DedicatedDomains
     */
    public function setServerName($serverName)
    {
        $this->serverName = $serverName;

        return $this;
    }

    /**
     * Get the server name.
     *
     * @return string
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * Set registry object.
     *
     * @param Rigestry $registry
     *
     * @return \ExpandCart\Foundation\Providers\DedicatedDomains
     */
    public function setRegistry($registry)
    {
        $this->registry = $registry;

        return $this;
    }

    /**
     * Get the rigestry object instance.
     *
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Return the domain list.
     *
     * @return array
     */
    public function getDomain()
    {
        if (!self::$instance) {
            self::$instance = $this->domainsList();
        }

        return self::$instance;
    }

    /**
     * Return the domain id from the domain list.
     *
     * @return int
     */
    public function getDomainId()
    {
        return $this->getDomain()['domain_id'];
    }

    /**
     * Get domains by country iso code.
     *
     * @return array
     */
    public function getDomainByCountryCode($countryCode)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->table;
        $queryString[] = 'WHERE country="' . $countryCode . '"';
        $queryString[] = 'AND domain_status=1';

        $data = $this->getRegistry()->get('db')->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        }
        else{
            $queryString = [];
            $queryString[] = 'SELECT * FROM ' . $this->table;
            $queryString[] = 'WHERE country="WWW"';
            $queryString[] = 'AND domain_status=1';

            $data = $this->getRegistry()->get('db')->query(implode(' ', $queryString));
            if ($data->num_rows) {
                return $data->row;
            }
        }
        return false;
    }

    /**
     * Get domains by server name.
     *
     * @return array
     */
    public function domainsList()
    {
        $server_name = $this->getServerName();

        if ( strpos( $server_name, "www" ) !== false ) {
            $server_name = str_replace( "www.", "", $server_name );
        }

        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->table;
        $queryString[] = "WHERE domain='{$server_name}' OR domain='www.{$server_name}'";
        $queryString[] = 'AND domain_status=1';
        $data = $this->getRegistry()->get('db')->query(implode(' ', $queryString));

        if ($data->num_rows == 1) {
            return $data->row;
        }

        return false;
    }

    /**
     * Check the application status.
     *
     * @return bool
     */
    public function isActive()
    {
        if (empty($this->options)) {
            $this->getOptions();
        }

        if ($this->appInstalled == true) {

            if (isset($this->options['status']) && $this->options['status'] == 1) {
                return true;
            }

        }

        return false;
    }

    /**
     * Get application options.
     *
     * @return array
     */
    public function getOptions()
    {
        if (!$this->appInstalled) {
            $queryString = [];
            /*$queryString[] = "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'dedicated_domains'";
            $data = $this->getRegistry()->get('db')->query(implode(' ', $queryString));*/

            if (\Extension::isInstalled('dedicated_domains')) {

                if (empty($this->options)) {
                    $this->options = $this->getRegistry()->get('config')->get('dedicated_domains');
                }
                $this->appInstalled = true;
            }
        }
        return $this->options;
    }

    /**
     * Compine the protocol with the user submitted url.
     *
     * @param string $protocol
     * @param string $url
     * @param string $requestURI
     *
     * @return void
     */
    public function redirect($protocol, $url, $requestURI)
    {
        if (isset($requestURI) && $requestURI !== '/') {
            $url = $url . $requestURI;
        }
        return $this->getRegistry()->get('response')->redirect($protocol . $url);
    }

    /**
     * Checks for the user protocol.
     *
     * @return string
     */
    public function getProtocol()
    {
        $request = $this->getRegistry()->get('request');

        if (isset($request->server['HTTPS']) && (
                ($request->server['HTTPS'] == 'on') || ($request->server['HTTPS'] == '1'))
        ) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }

        return $protocol;
    }

    /**
     * This method is totally out of the object context and MUST be seperated
     *
     * @see https://stackoverflow.com/a/13600004/2359679
     */
    public function ipInfo($ip = NULL, $purpose = "location", $deep_detect = true)
    {
        $output = NULL;

       if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
        $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));

        $support    = array("country", "countrycode", "state", "region", "city", "location", "address");

        $continents = array(
            "AF" => "Africa",
            "AN" => "Antarctica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America"
        );

        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            /*$ipdat = @json_decode(
                file_get_contents(
                    "http://api.ipstack.com/" . $ip . "?access_key=298934a62a0d7887c0fd9cd5724623c4&format=1"
                )
            );*/

            $ipdata = json_decode(
                file_get_contents('http://extreme-ip-lookup.com/json/' . $ip)
            );

            if (@strlen(trim($ipdata->countryCode)) == 2) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city"           => @$ipdat->city,
                            "state"          => @$ipdat->region_name,
                            "country"        => @$ipdat->country,
                            "country_code"   => @$ipdat->countryCode,
                            "continent"      => @$ipdat->continent,
                            "continent_code" => @$ipdat->continent
                        );
                        break;
                    case "address":
                        $address = array($ipdat->country_name);
                        if (@strlen($ipdat->region_name) >= 1)
                            $address[] = $ipdat->region_name;
                        if (@strlen($ipdat->city) >= 1)
                            $address[] = $ipdat->city;
                        $output = implode(", ", array_reverse($address));
                        break;
                    case "city":
                        $output = @$ipdat->city;
                        break;
                    case "state":
                        $output = @$ipdat->region_name;
                        break;
                    case "region":
                        $output = @$ipdat->region_name;
                        break;
                    case "country":
                        $output = @$ipdat->country_name;
                        break;
                    case "countrycode":
                        $output = @$ipdata->countryCode;
                        break;
                }
            }

        }
        return $output;
    }

    /**
     * This method is totally out of the object context and MUST be seperated
     *
     * @see https://stackoverflow.com/a/15047834/2359679
     */
    public function isBot()
    {
        return (
            isset($_SERVER['HTTP_USER_AGENT'])
            && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])
        );
    }
}
