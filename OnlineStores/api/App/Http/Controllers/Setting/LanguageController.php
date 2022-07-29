<?php

namespace Api\Http\Controllers\Setting;

use Api\Http\Controllers\Controller;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class LanguageController extends Controller
{
    /**
     * @var
     */
    private $setting, $config;

    /**
     * DomainController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->setting = $this->container['setting'];
        $this->config = $container['config'];
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function update(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        $language = $parameters['language'];
        // Validate Language code
        if (!isset($language) || empty($language))
        {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[3],
                'error_description' => 'Language code is required'
            ], 406);
        }

        // check if language existed
        $general = $this->container['general'];
        $supported_languages = $general->getStoreLanguages();
        if (!in_array($language, $supported_languages))
        {
            return $response->withJson([
                'status' => 'error',
                'status_code' => 408,
                'error_description' => 'Not supported language'
            ], 408);
        }

        $data = ['config_admin_language' => $language];
        // Update Language
        $this->setting->updateGeneralSetting($data);
        // Edit Guide Value
        $this->setting->editGuideValue('GETTING_STARTED', 'LANGUAGE', '1');

        return $response->withJson([
            'status' => 'success',
            'status_code' => 200,
            'data' => ['language' => $this->config->get('config_admin_language')]
        ], 200);

    }

	
}