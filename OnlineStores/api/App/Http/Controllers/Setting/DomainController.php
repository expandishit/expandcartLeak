<?php

namespace Api\Http\Controllers\Setting;

use Api\Http\Controllers\Controller;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DomainController extends Controller
{
    /**
     * @var
     */
    private $domain;

    /**
     * DomainController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->domain = $this->container['domain'];
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function index(Request $request, Response $response)
    {
        $domains = $this->domain->getDomains();
        $domains_response = [];
        foreach ($domains as $key => $domain)
        {
            $domains_response[$key]['id'] = $domain['UNIQUEID'];
            $domains_response[$key]['name'] = strtolower($domain['DOMAINNAME']);
        }
        return $response->withJson([
            'status' => 'success',
            'status_code'  => 200,
            'data' => $domains_response
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function store(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        $domain = $parameters['domain'];
        if (!isset($domain) || empty($domain))
        {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[3],
                'error_description' => 'Domain name is required'
            ], 406);
        }
        $domainname = strtolower($domain);
        $domainname = str_ireplace("http://", "", $domainname);
        $domainname = str_ireplace("https://", "", $domainname);
        $domainname = str_ireplace("www.", "", $domainname);
        $domainname = 'http://' . $domainname;
        $domainname = strtoupper(parse_url($domainname, PHP_URL_HOST));

        $numberOfDomains = $this->domain->countDomain(); // Get number of registered domains

        $domains_limit = $this->domain->dedicatedDomain(); // get limit of domains

        if ($numberOfDomains >= $domains_limit)
        {
            return $response->withJson([
                'status' => 'error',
                'status_code' => 406,
                'error_description' => 'Exceeded domains limit'
            ], 400);
        }
        else
        {
            $domain_exists = $this->domain->countDomain($domainname);
            if ($domain_exists <= 0)
            {
                $data['uniqueID'] = $this->domain->addDomain($domainname);
                $data['domainname'] = strtolower($domainname);
                $this->domain->setMixpanel($data['domainname']);
                $this->domain->setAmplitude($data['domainname']);

                return $response->withJson([
                    'status' => 'success',
                    'status_code' => 200,
                    'data' => $data
                ], 200);

            }
            else
            {
                return $response->withJson([
                    'status' => 'error',
                    'status_code' => 403,
                    'error_description' => 'Domain Already Existed'
                ], 403);
            }
        }

        return $response->withJson([
            'status' => 'error',
            'status_code' => 407,
            'error_description' => 'Domain Not Saved'
        ], 407);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function delete(Request $request, Response $response, $args)
    {
        $parameters = $request->getParsedBody();
        $domain_id = $parameters['id'];

        // validate domain existence
        $domains = $this->domain->countDomain($parameters['name']);
        if (!$domains || $domains = 0)
        {
            return $response->withJson([
                'status' => 'failed',
                'status_code'  => 406,
                'data' => 'Domain not found'
            ], 406);
        }

        if ($this->domain->delete($domain_id))
        {
            return $response->withJson([
                'status' => 'success',
                'status_code'  => 200,
                'data' => 'Deleted successfully'
            ], 200);
        }

        return $response->withJson([
            'status' => 'success',
            'status_code'  => 407,
            'data' => 'Domain not deleted'
        ], 407);

    }

	
}