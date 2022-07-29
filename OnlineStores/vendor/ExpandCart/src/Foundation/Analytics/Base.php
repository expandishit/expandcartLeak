<?php

namespace ExpandCart\Foundation\Analytics;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

abstract class Base
{
    protected $client = null;

    protected $module = null;

    protected $method = null;

    protected $tokenAuth = null;

    protected $period = 'month';

    protected $segment = null;

    protected $filterLimit = 20;

    protected $format = 'json';

    protected $availableFormats = [
        'json',
        'xml'
    ];

    protected $queryString = [];

    protected $date = 'yesterday';

    protected $idSite = null;

    private function boot()
    {
        $this->method = $this->getMethod();

        return $this;
    }

    abstract public function setMethod($method);

    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    public function setTokenAuth($tokenAuth)
    {
        $this->tokenAuth = $tokenAuth;

        return $this;
    }

    public function setPeriod($period)
    {
        $this->period = $period;

        return $this;
    }

    public function setFilterLimit($limit)
    {
        $this->filterLimit = $limit;

        return $this;
    }

    public function setFilter($limit)
    {
        return $this->setFilterLimit($limit);
    }

    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    public function setSiteId($idSite)
    {
        $this->idSite = $idSite;

        return $this;
    }

    public function getIdSite()
    {
        return $this->idSite ?: ANALYTICS_SITE_ID;
    }

    public function setSegment($segment)
    {
        $this->segment = $segment;

        return $this;
    }

    public function getSiteUrl()
    {
        return substr($this->getBaseUri(), 5) . '/';
    }

    private function getBaseUri()
    {
        return 'http://analytics.expandcart.com';
    }

    private function getTokenAuth()
    {
        return $this->tokenAuth ?: ANALYTICS_TOKEN_AUTH;
    }

    protected function send($parameters = [])
    {
        if (!$this->tokenAuth) {
            exit;
            throw new \Exception('unauthorized action');
        }

        if (!$this->client) {
            $this->setClient(new GuzzleClient([
                'base_uri' => $this->getBaseUri()
            ]));
        }

        if (!$this->module) {
            throw new \Exception('bad module entry');
        }

        if (!$this->method) {
            throw new \Exception('temp message');
        }

        if (isset($parameters) && is_array($parameters)) {
            $this->queryString = array_merge($this->queryString, $parameters);
        }

        $this->queryString['token_auth'] = $this->getTokenAuth();
        $this->queryString['module'] = 'API';
        $this->queryString['method'] = implode('.', [$this->module, $this->method]);
        $this->queryString['format'] = $this->format;

        try {
            $result = $this->client->get($this->getBaseUri(), [
                'query' => $this->queryString
            ]);
        } catch (RequestException $e) {
            // TODO handle this to preview an error page instead just halting the application.
            /*echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse()) . "\n";
            }*/
            exit;
        }

        return $result;
    }

    public function fetch()
    {
        if (!$this->module) {
            exit;
            throw new \Exception('bad module entry');
        }

        if (!$this->method) {
            exit;
            throw new \Exception('temp message');
        }

        $http = new \GuzzleHttp\Client([
            'base_uri' => $this->getBaseUri(),
            'verify' => false
        ]);

        try {
            $result = $http->get('', [
                'query' => array_merge([
                    'module' => 'API',
                    'method' => implode('.', [$this->module, $this->method]),
                    'idSite' => $this->getIdSite(),
                    'period' => $this->period,
                    'date' => $this->date,
                    'format' => $this->format,
                    'filter_limit' => $this->filterLimit,
                    'token_auth' => $this->getTokenAuth(),
                    'segment' => $this->segment,
                ], (isset($this->queryString[$this->method]) ? $this->queryString[$this->method] : []))
            ]);
        } catch (RequestException $e) {
            // TODO handle this to preview an error page instead just halting the application.
            /*echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse()) . "\n";
            }*/
            exit;
        }

        return json_decode($result->getBody(), true);
    }
}
