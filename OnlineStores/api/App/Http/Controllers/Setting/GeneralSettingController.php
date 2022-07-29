<?php

namespace Api\Http\Controllers\Setting;

use Api\Http\Controllers\Controller;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class GeneralSettingController extends Controller
{
    private $setting, $general;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->setting = $this->container['setting'];
        $this->general = $this->container['general'];
    }

    public function getGeneralStoreSetting(Request $request, Response $response)
    {
        return $response->withJson([
            'status' => 'success',
            'status_code' => 200,
            'data' => $this->setting->getGeneralStoreSetting()
        ], 200);
    }

    public function update(Request $request, Response $response, $args)
    {
        $parameters = $request->getParsedBody();

        //return $response->withJson($parameters);
        // Validate Add Product Inputs
        if (!empty($this->validateUpdateStoreSetting($parameters)))
        {
            return $response->withJson($this->validateUpdateStoreSetting($parameters), 406);
        }

        // start preparing data
        $prepared_data = array();
        $prepared_data['config_email'] = $parameters['email'];
        $prepared_data['config_telephone'] = $parameters['phone'];
        //prepare name
        foreach ( $parameters['name'] as $code => $value )
        {
            $prepared_data['config_name'][$code] = $value;
        }
        // prepare address
        if (isset($parameters['address']) && !empty($parameters['address']))
        {
            foreach ( $parameters['address'] as $code => $value )
            {
                $prepared_data['config_address'][$code] = $value;
            }
        }
        // prepare meta_title
        if (isset($parameters['meta_title']) && !empty($parameters['meta_title']))
        {
            foreach ( $parameters['meta_title'] as $code => $value )
            {
                $prepared_data['config_title'][$code] = $value;
            }
        }
        // prepare meta_description
        if (isset($parameters['meta_description']) && !empty($parameters['meta_description']))
        {
            foreach ( $parameters['meta_description'] as $code => $value )
            {
                $prepared_data['config_meta_description'][$code] = $value;
            }
        }

        $saved_setting = $this->setting->updateGeneralSetting($prepared_data);
        $guideValueUpdate = $this->setting->editGuideValue('config', 'EDIT_SETTINGS', '1');
        if ($saved_setting && $guideValueUpdate)
        {
            return $response->withJson([
                'status' => 'success',
                'status_code' => 200,
            ], 200);
        }

        return $response->withJson([
            'status' => 'failed',
            'status_code' => 405,
            'data' => $saved_setting
        ], 200);
    }

    public function validateUpdateStoreSetting($parameters)
    {
        $errors = array();
        if ((utf8_strlen($parameters['email']) > 96) || !filter_var($parameters['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'email not valid'];
        }

        if(!isset($parameters['email']) || empty($parameters['email'])) // validate Email
        {
            $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'email cannot be empty'];
        }
        if(!isset($parameters['phone']) || empty($parameters['phone']) || strlen($parameters['phone']) < 10) // validate phone
        {
            $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'phone cannot be empty and should be greater than 10 digits'];
        }

        if (!isset($parameters['name']) || empty($parameters['name']))  // Validate store name
        {
            $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'name cannot be required'];
        }

        foreach ( $parameters['name'] as $code => $value ) // Validate store name localization
        {
            if ( (utf8_strlen($value) < 1) || (utf8_strlen($value) > 255) )
            {
                $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'name in ' . $code . ' language is required'];
            }
        }

        return $errors;
    }
	
}