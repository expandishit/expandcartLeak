<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

abstract class Controller
{
    /**
     * The container instance
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * The response statuses array
     *
     * @var array
     */
    protected $status = [
        0 => 'ZERO_RESULTS',
        1 => 'OK',
        2 => 'INVALID',
        3 => 'UNKNOWN',
    ];

    /**
     * The Controller constructor
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * validate given inputs against some rules
     *
     * TODO this should be seperated library, write it from scratch or use a ready tool from packagiest
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function validator($inputs, $rules)
    {

        $required   = function ($value) { return isset($value) && empty($value) === false; };
        $string     = function ($value) { return is_string($value); };
        $email      = function ($value) { return filter_var($value, FILTER_VALIDATE_EMAIL); };
        $url        = function ($value) { return filter_var($value, FILTER_VALIDATE_URL); };
        $int        = $integer = $id = function ($value) { return preg_match('#^\d+$#', $value);};

        foreach ($rules as $key => $value) {
            $rules = explode('|', $value);
            foreach ($rules as $rule) {
                if (!$$rule($inputs[$key])) {
                    return false;
                }
            }
        }

        return true;
    }
}