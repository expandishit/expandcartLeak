<?php

namespace ExpandCart\Foundation\Support;

class Hubspot
{
    private static $packages = [
        1 => 'ec_sp_basic_plan',
        2 => 'ec_sp_standard_plan',
        52 => 'ec_sp_knawat_standard_plan',
        53 => 'ec_sp_professional_plan',
        4 => 'ec_sp_business_plan',
        6 => 'ec_sp_ultimate_plan',
        8 => 'ec_sp_enterprise_plan',
        50 => 'ec_sp_enterprise_plus_plan',
    ];

    public static function tracking($event_name, $properties = [])
    {
        $server = EC_MONITORING['server'];
        $port = EC_MONITORING['port'];

        if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
            throw new \Exception(socket_strerror($errorcode));
        }

        $data = [
            'email' => $properties['base_email'] ?? BILLING_DETAILS_EMAIL, // Billing system email
            'eventName' => $event_name, // internal Hubspot event id, for example : pe25199511_added_a_product,
        ];

        if (count($properties) > 0) {
            $data['properties'] = $properties;
        }

        $body = bin2hex(gzencode(json_encode($data)));

        $inputs = [
            'headers' => [
                'auth' => time(), // @TODO
                'method' => 'POST', // currently meaningless
                'action' => 'hubspot/events/store', // HTTP-like request uri
                'content-encoding' => 'gzip',
                'udp-version' => '1.0'
            ],
            'body' => $body
        ];

        $inputs = json_encode($inputs);

        socket_sendto($sock, $inputs, strlen($inputs), 0 , $server , $port);
    }

    public static function addContact($data)
    {
        if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
            throw new \Exception(socket_strerror($errorcode));
        }

        if (isset($data['packageid'])) {
            $data['ec_subscription_plan'] = self::detectPackage($data['packageid']);

            unset($data['packageid']);
        }

        if (isset($data['pid'])) {
            $data['ec_subscription_plan'] = self::detectPackage($data['pid']);

            unset($data['pid']);
        }

        $body = bin2hex(gzencode(json_encode($data)));

        $inputs = [
            'headers' => [
                'auth' => time(),
                'method' => 'POST',
                'action' => 'hubspot/contact/create',
                'content-encoding' => 'gzip',
                'udp-version' => '1.0'
            ],
            'body' => $body
        ];

        $inputs = json_encode($inputs);

        socket_sendto($sock, $inputs, strlen($inputs), 0, EC_MONITORING['server'], EC_MONITORING['port']);
    }

    public static function updateContact($data)
    {
        if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
            throw new \Exception(socket_strerror($errorcode));
        }

        if (isset($data['packageid'])) {
            $data['ec_subscription_plan'] = self::detectPackage($data['packageid']);

            unset($data['packageid']);
        }

        if (isset($data['pid'])) {
            $data['ec_subscription_plan'] = self::detectPackage($data['pid']);

            unset($data['pid']);
        }

        $body = bin2hex(gzencode(json_encode($data)));

        $inputs = [
            'headers' => [
                'auth' => time(),
                'method' => 'POST',
                'action' => 'hubspot/contact/update',
                'content-encoding' => 'gzip',
                'udp-version' => '1.0'
            ],
            'body' => $body
        ];

        $inputs = json_encode($inputs);

        socket_sendto($sock, $inputs, strlen($inputs), 0, EC_MONITORING['server'], EC_MONITORING['port']);
    }

    protected static function detectPackage($pid)
    {
        return self::$packages[$pid] ?? 'ec_sp_15_day_trial';
    }
}
