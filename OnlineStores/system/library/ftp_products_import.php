<?php


class Caller
{
    private $url;

    private $cookie;

    public function __construct($args)
    {
        $this->url    = $args[1];
        $this->cookie = explode(',', $args[2] ?? []);
    }

    public static function call($args)
    {
        return (new static($args))->callUrl();
    }

    private function callUrl()
    {
        $ch = curl_init();

        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
        );

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        # sending manually set cookie
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: " . implode(';', $this->cookie)));

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }
}

$output = Caller::call($_SERVER['argv']);

echo $output;

exit;
