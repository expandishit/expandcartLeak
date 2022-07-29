<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ExpandCart\Foundation\Support\Hubspot;

class InstallationController extends Controller
{
    /**
     * @var
     */
    private $installation, $config;


    /**
     * InstallationController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->installation = $this->container['installation'];
        $this->config = $this->container['config'];
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request, Response $response)
    {
        return $response->withJson([
            'status' => 'success',
            'data' => $this->installation->getInstallationInfo()
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
        $data = array();
        if ($parameters['products_types'] == null || $parameters['products_types'] == "")
        {
            $data['products_types'] = "others";
        }

        $data['products_types'] = json_encode($parameters['products_types']);
        $data['selling_channel'] = $parameters['selling_channel'];
        $data['product_source'] = $parameters['product_source'];
        $data['previous_website'] = $parameters['previous_website'];
        $data['registered_business'] = $parameters['registered_business'];

        if(isset($parameters['previous_website']) && $parameters['previous_website'] == "on"){
            $data['previous_website'] = "Yes";
        }else{
            $data['previous_website'] ="No";
        }

        //$saved_data = $this->container->installation->store($data);

        // todo Lines are commented for testing api but will un comment after finishing
        // todo update the client phone from whmcs api
        $whmcs= new \whmcs();
        $userId= WHMCS_USER_ID;
        $phoneNumber = $whmcs->getClientPhone($userId);

        if ($phoneNumber != null && $phoneNumber != ""){
            $data['config_telephone']= $phoneNumber;
        }

        // Save installation info
        $saved_data = $this->container->installation->store($data);

        // update mixapanel && Amplitude
        $this->container->installation->updateUserMixpanel([
            '$selling channel'       => $data['selling_channel'],
            '$product source'        => $data['product_source'],
            '$registered business'   => $data['registered_business'],
            '$previous website'      => $data['previous_website'],
            '$products types'        => $data['products_types'],
            '$store code'            => STORECODE,
            '$subscription plan'     => PRODUCTID,
        ]);

        $this->container->installation->updateUserAmplitude([
            'selling channel'       => $data['selling_channel'],
            'product source'        => $data['product_source'],
            'registered business'   => $data['registered_business'],
            'previous website'      => $data['previous_website'],
            'products types'        => $data['products_types'],
            'store code'            => STORECODE,
            'subscription plan'     => PRODUCTID,
        ]);

        //################### AutoPilot Start #####################################
        try {
            $fields = array();

            $fields["string--Selling--Channel"] = $data['selling_channel'];
            $fields["string--Product--Source"] = $data['product_source'];
            $fields["string--Previous--Website"] = $data['previous_website'];
            $fields["string--Registered--Company"] = $data['registered_business'];

            //new custom fields
            //$fields["string--Store--Name"] = $store_name;
            $fields["string--Product--Types"] = $data['products_types'];

            autopilot_UpdateContactCustomFields(BILLING_DETAILS_EMAIL, $fields);
        }
        catch (\Exception $e) {
            return $response->withJson([
                'status' => 'error',
                'status_code' => 402,
                'error_description' => 'Auto Pilot Error'
            ], 402);
        }



        //################### AutoPilot End  #####################################

        //################### Fresh Sales Start  #####################################
        try {

            \FreshsalesAnalytics::init(array('domain' => 'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io', 'app_token' => FRESHSALES_TOKEN));

            $leadData = array(
                'identifier' => BILLING_DETAILS_EMAIL,
                'Selling Channel' => $data['selling_channel'],
                'Product Source' => $data['product_source'],
                'Previous Website' => $data['previous_website'],
                'Product Types' => $data['products_types'],
                'Registered Company' => $data['registered_business']
            );
            \FreshsalesAnalytics::identify(
                $leadData
            );
        }
        catch (\Exception $e) {
            return $response->withJson([
                'status' => 'error',
                'status_code' => 405,
                'error_description' => 'Fresh Analytics Error'
            ], 405);
        }
        //################### Intercom Start #####################################
        try {
            $url = 'https://api.intercom.io/events';
            $authid = INTERCOM_AUTH_ID;

            $cURL = curl_init();
            curl_setopt($cURL, CURLOPT_URL, $url);
            curl_setopt($cURL, CURLOPT_USERPWD, $authid);
            curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($cURL, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($cURL, CURLOPT_POST, true);
            curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json'
            ));
            $intercomData['event_name'] = 'application-installation';
            $intercomData['created_at'] = time();
            $intercomData['user_id'] = STORECODE;
            curl_setopt($cURL, CURLOPT_POSTFIELDS, json_encode($intercomData));
            $result = curl_exec($cURL);
            curl_close($cURL);
        }
        catch (\Exception $e) {  }
        //################### Intercom END #####################################

        //################### Hubspot Start #####################################
            $productSource_MAP = [
                'own_products' => 'ec_os_qui_ps_own_products',
                'retail'=>'ec_os_qui_ps_retail',
                'dropshipping' =>'ec_os_qui_ps_dropshipping',
                'multi_merchant'=>'ec_os_qui_ps_multi_merchant',
                'do_not_know'=>'ec_os_qui_ps_do_not_know'
            ];
            $selling_channel_MAP = [
                'website'=>'ec_os_qui_sc_website',
                'social_media' =>'ec_os_qui_sc_social_media',
                'marketplaces'=>'ec_os_qui_sc_marketplaces',
                'retail_store'=>'ec_os_qui_sc_retail_store',
                'all_channels'=>'ec_os_qui_sc_all_channels',
                'not_selling'=>'ec_os_qui_sc_not_selling',
                'building_for_client'=>'ec_os_qui_sc_building_for_client',
                'research_purposes'=>'ec_os_qui_sc_research_purposes',
            ];
            $registeredCompany_MAP = [
                'Yes' => 'ec_os_qui_yes',
                'No'=>'ec_os_qui_no',
                'Not Yet' =>'ec_os_qui_not_yet ',
            ];
            Hubspot::tracking('pe25199511_os_questioneer_updated', [
                "ec_os_qui_product_source"    => $data['product_source'],
                "ec_os_qui_registered_company" => $data['registered_business'],
                "ec_os_qui_selling_channel"   => $data['selling_channel'],
                "ec_os_qui_products_types" => $parameters['products_types'][0] ?? 'Other'
            ]);

       //################### Hubspot End #####################################

        $this->container->installation->applyDefaultTemplate();

        return $response->withJson([
            'status' => 'success',
            'status_code' => 200,
        ], 200);
    }
}
