<?php

namespace ExpandCart\Foundation\WebServices\Soap;

ini_set('display_errors', 1);
error_reporting(E_ALL);

class Client extends \SoapClient
{
    private $client;

    private $functionName;

    private $options;

    private $arguments;

    private $inputHeaders;

    private $wsdl;

    public function __construct($wsdl = null, $options = [])
    {
        if ($wsdl) {
            $this->setWsdl($wsdl);
        }

        if (empty($options) === false) {
            $this->setOptions($options);
        }

        if ($this->wsdl || (
                in_array('location', $this->options) === false ||
                in_array('uri', $this->options) === false
            )
        ) {
            $this->boot();
        }
    }

    private function boot()
    {
        if (
            !$this->wsdl && (
                in_array('location', $this->options) === false ||
                in_array('uri', $this->options) === false
            )
        ) {
            throw new \Expecetion('Invalid WSDL');
        }

        $options = [];

        if ($this->options) {
            $options = $this->options;
        }

        $this->client = true;

        return parent::__construct($this->wsdl, $options);
    }

    public function setWsdl($wsdl)
    {
        $this->wsdl = $wsdl;

        return $this;
    }

    public function getWsdl()
    {
        return $this->wsdl;
    }

    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setFunction($functionName)
    {
        $this->functionName = $functionName;

        return $this;
    }

    public function getFunction()
    {
        return $this->functionName;
    }

    public function setFunctionWithArgs($functionName, $arguments = [])
    {
        $this->functionName = $functionName;

        $this->arguments = $arguments;

        return $this;
    }

    public function setArguments($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getArguments($arguments)
    {
        return $this->arguments;
    }

    public function setInputHeaders($inputHeaders)
    {
        $this->inputHeaders = $inputHeaders;

        return $this;
    }

    public function getInputHeaders()
    {
        return $this->inputHeaders;
    }

    public function call($functionName = null, $arguments = [], $options = [], $inputHeaders = [])
    {
        if ($functionName) {
            $this->functionName = $functionName;
        }

        if (!$this->functionName) {
            throw new \Expecetion('');
        }

        if (empty($arguments) === false) {
            $this->arguments = $arguments;
        }

        if (empty($options) === false) {
            $this->options = $options;
        }

        if (empty($inputHeaders) === false) {
            $this->inputHeaders = $inputHeaders;
        }

        if (!$this->client) {
            $this->boot();
        }

        return $this->__soapCall($this->functionName, [$this->arguments], $this->options);
    }

    public function getFunctions()
    {
        return $this->__getFunctions();
    }

    public function getTypes()
    {
        return $this->__getTypes();
    }

    public function getLastResponse()
    {
        return $this->__getLastResponse();
    }

    public function getLastResponseHeaders()
    {
        return $this->__getLastResponseHeaders();
    }

    public function getLastRequestHeaders()
    {
        return $this->__getLastRequestHeaders();
    }

    public function getLastRequest()
    {
        return $this->__getLastRequest();
    }

    public function getCookies()
    {
        return $this->__getCookies();
    }
}
