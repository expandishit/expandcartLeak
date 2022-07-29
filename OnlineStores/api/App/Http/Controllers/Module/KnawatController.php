<?php

namespace Api\Http\Controllers\Module;

use Api\Http\Controllers\Controller;
use Api\Models\Token;
use Api\Models\User;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class KnawatController extends Controller
{

    private $knawat;
    private $config;
    private $settings;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->knawat = $this->container['knawat'];
        $this->config = $container['config'];
        $this->settings = $container['setting'];
    }

    private function validate($input, $headerToken)
    {
        $consumerSecret = $this->knawat->getSettings();

        if (!isset($consumerSecret['store']) || !isset($consumerSecret['store']->consumer_secret)) {
            return false;
        }

        $consumerSecret = $consumerSecret['store']->consumer_secret;
        $generatedHash = base64_encode(hash_hmac('sha256', json_encode($input), $consumerSecret, true));

        return $generatedHash === $headerToken[0];
    }

    /***************** Get Access Token ***************/
    public function getToken(Request $request, Response $response)
    {
        $this->logData(['name'=>'oauth second step (get token)','request body' => $request->getParsedBody()]);

        $input = $request->getParsedBody();

        if ($this->validate($input, $request->getHeader('X-Knawat-Hmac-Sha256')) == false) {
            return $response->withJson(['error' => "Invalid request"], 503);
        }

        //add product to store if not exist
        if (!isset($input['code']) || !$input['code'])
            return $response->withJson(['error' => 'code required'], 422);

        //get client secret
        $client_secret = $this->knawat->getClientSecretByClientId($input['code']);

        if (!isset($client_secret['client_secret']) || !$client_secret['client_secret']) {
            $this->logData(['name'=>'invalid code','request body' => $request->getParsedBody(),'type'=>'error']);
            return $response->withJson(['error' => 'invalid code'], 422);
        }

        //create jwt token
        $user = (new User)->setClient($this->container->db);
        $user = $user->getUser($client_secret['client_secret'], $input['code']);
        $token = (new Token())->setClient($this->container->db);

        //delete old token
        $token->deleteTokenIfExist($user['id']);

        //store new token
        $access_token = Token::generateToken(25);
        $token->storeToken($access_token, $user['id'], 99999999999);

        //save access token to database
        $this->knawat->updateSettings(['access_token' => $access_token]);


        $this->logData(['name'=>'create access token','request body' => ["access_token"=> $access_token, "token_type"=>"bearer"]]);

        return $response->withJson(["access_token"=> $access_token, "token_type"=>"bearer"], 200);
    }


    /***************** Listen to pushed product webhook ***************/
    public function pushProductListener(Request $request, Response $response)
    {
        $this->logData(['name'=>'push product','request body' => $request->getParsedBody()]);
        //make validation
        if (!$this->knawat->isInstalled()) {
            $this->logData(['name'=>'App not installed on ExpandCart','type'=>'error']);
            return $response->withJson(['error' => "App not installed on ExpandCart"], 503);
        }

        $input = $request->getParsedBody();

        if ($this->validate($input, $request->getHeader('X-Knawat-Hmac-Sha256')) == false) {
            return $response->withJson(['error' => "Invalid request"], 503);
        }

        if(!$input['product']) {
            $this->logData(['name'=>'Product not found. try again later','type'=>'error']);
            return $response->withJson(['error' => "Product not found. try again later"], 422);
        }

        //get product details and convert it to json to deal with all functionality in the model
        $product = json_decode(json_encode($input['product']));

        //store webhook to logs
        $topic = implode(",",$request->getHeader('X-Knawat-Topic'));
        $log_id = $this->knawat->logWebhookCall($topic,0,$product->sku);

        //add product to store if not exist
        $product_id = $this->knawat->getProductIdBySku($product->sku);

        //set product format to  save in our store or update if exist
        if ($product_id)
            $this->knawat->updateKnawatProduct($product_id, $product);
        else
            $product_id = $this->knawat->saveKnawatProduct($product);

        //update log to success
        $this->knawat->updateLogStatus($log_id);
        $this->logData(['name'=>'product pushed successfully','request body' => ['externalId'=>$product_id,'externalUrl'=>$this->knawat->getProductUrl($product_id)]]);
        return $response->withJson(['product' =>['externalId'=>$product_id,'externalUrl'=>$this->knawat->getProductUrl($product_id)]], 200);
    }

    /***************** Listen to delete product webhook ***************/
    public function deleteProductListener(Request $request, Response $response)
    {

        $this->logData(['name'=>'delete product','request body' => $request->getParsedBody()]);

        //make validation
        if (!$this->knawat->isInstalled())
            return $response->withJson(['error' => "App not installed on ExpandCart"], 503);

        $input = $request->getParsedBody();

        if ($this->validate($input, $request->getHeader('X-Knawat-Hmac-Sha256')) == false) {
            return $response->withJson(['error' => "Invalid request"], 503);
        }

        //store webhook to logs
        $topic = implode(",",$request->getHeader('X-Knawat-Topic'));
        $log_id = $this->knawat->logWebhookCall($topic,0,$input['sku']);

        //add product to store if not exist
        if (isset($input['sku'])) {
            if (isset($input['ec_internal_sync'])) {
                $this->knawat->deleteKnawatProductByKnawatSku($input['sku']);
            } else {
                $this->knawat->deleteKnawatProductBySku($input['sku']);
                $this->knawat->updateLogStatus($log_id);
                $this->logData(['name'=>'product deleted successfully','request body' => ['sku'=>$input['sku']]]);
            }
            return $response->withJson(['status' => 'success'], 200);
        }
        return $response->withJson(['error' => 'failed to delete product'], 422);
    }


    /***************** Listen to update order webhook ***************/
    public function updateOrderListener(Request $request, Response $response)
    {

        $this->logData(['name'=>'update order','request body' => $request->getParsedBody()]);
        //make validation
        if (!$this->knawat->isInstalled()) {
            return $response->withJson(['error' => "App not installed on ExpandCart"], 503);
        }

        $input = $request->getParsedBody();

        if ($this->validate($input, $request->getHeader('X-Knawat-Hmac-Sha256')) == false) {
            return $response->withJson(['error' => "Invalid request"], 503);
        }

        //store webhook to logs
        $topic = implode(",",$request->getHeader('X-Knawat-Topic'));
        $log_id = $this->knawat->logWebhookCall($topic,0,$input['id']);

        //get order id if exist
        $order_id = $this->knawat->getKnawatOrderId($input['id']);

        if ($order_id) {
            $statuses = [
                'pending'       => $this->config->get('config_order_status_id'),
                'processing'    => $this->config->get('config_order_cod_status_id'),
                'packed'        => $this->config->get('config_order_cod_status_id'),
                'shipped'       => $this->config->get('config_order_shipped_status_id'),
                'delivered'     => $this->config->get('config_complete_status_id'),
                'voided'        => $this->config->get('config_cancelled_order_status_id')
            ];
            if (isset($input['fulfillmentStatus'])) {
                $this->knawat->updateOrderStatus($order_id, ['order_status_id' => $statuses[$input['fulfillmentStatus']]]);
                $this->knawat->updateLogStatus($log_id);
                $this->logData(['name'=>'order updated successfully','request body' => ['id'=>$input['id']]]);

                return $response->withJson(['status' => 'success'], 200);
            }
        }

        return $response->withJson(['error' => 'failed to update order'], 422);
    }


    /***************** Listen to cancel order webhook ***************/
    public function cancelOrderListener(Request $request, Response $response)
    {

        $this->logData(['name'=>'cancel order','request body' => $request->getParsedBody()]);
        //make validation
        if (!$this->knawat->isInstalled())
            return $response->withJson(['error' => "App not installed on ExpandCart"], 503);

        $input = $request->getParsedBody();

        if ($this->validate($input, $request->getHeader('X-Knawat-Hmac-Sha256')) == false) {
            return $response->withJson(['error' => "Invalid request"], 503);
        }

        //store webhook to logs
        $topic = implode(",",$request->getHeader('X-Knawat-Topic'));
        $log_id = $this->knawat->logWebhookCall($topic,0,$input['id']);

        //get order id if exist
        $order_id = $this->knawat->getKnawatOrderId($input['id']);

        if ($order_id) {
            $status_id = $this->config->get('config_cancelled_order_status_id');
            $this->knawat->updateOrderStatus($order_id, ['order_status_id' => $status_id]);
            $this->knawat->updateLogStatus($log_id);
            $this->logData(['name'=>'order canceled successfully','request body' => ['id'=>$input['id']]]);

            return $response->withJson(['status' => 'success'], 200);
        }

        return $response->withJson(['status' => 'failed to cancel order'], 422);
    }

    public function updateAppConfigListener(Request $request, Response $response)
    {
        //make validation
        if (!$this->knawat->isInstalled())
            return $response->withJson(['error' => "App not installed on ExpandCart"], 503);

        $input = $request->getParsedBody();

        if (!$request->getHeader('X-EC-UDP')) {
            return $response->withJson(['error' => "Invalid request"], 503);
        }

        if (!defined('EC_UDP_TOKEN') || $request->getHeader('X-EC-UDP')[0]  != EC_UDP_TOKEN) {
            return $response->withJson(['error' => "Invalid request 2"], 503);
        }

        if ($input['status'] == 'OK' && isset($input['total_synced'])) {

            $settings = $this->knawat->getSettings();

            $data = ['sync' => [
                'total_count' => $settings['sync']['total_count'],
                'total_synced' => $input['total_synced'],
                'sync_status' => (isset($input['last_request']) && $input['last_request'] == 1) ? 1 : 0,
            ]];

            if (isset($input['last_request']) && $input['last_request'] == 1) {
                $data['total_imported_products'] = $input['total_synced'];
            }

            $this->knawat->updateSettings($data);

            return $response->withJson(['status' => 'success'], 200);
        }

        return $response->withJson(['failed to update store data'], 422);
    }

    public function getProductsSkusListener(Request $request, Response $response)
    {
        //make validation
        if (!$this->knawat->isInstalled())
            return $response->withJson(['error' => "App not installed on ExpandCart"], 503);

        $input = $request->getParsedBody();

        if (!$request->getHeader('X-EC-UDP')) {
            return $response->withJson(['error' => "Invalid request"], 503);
        }

        if (!defined('EC_UDP_TOKEN') || $request->getHeader('X-EC-UDP')[0]  != EC_UDP_TOKEN) {
            return $response->withJson(['error' => "Invalid request 2"], 503);
        }

        $data = $this->knawat->getKnawatProductsSku();

        return $response->withJson(['status' => 'OK', 'data' => $data], 200);
    }


    /***************** Listen to update store webhook ***************/
    public function updateStoreListener(Request $request, Response $response)
    {

        $this->logData(['name'=>'update store /(oauth third step)','request body' => $request->getParsedBody()]);

        //make validation
        if (!$this->knawat->isInstalled())
            return $response->withJson(['error' => "App not installed on ExpandCart"], 503);

        $input = $request->getParsedBody();

        if ($this->validate($input, $request->getHeader('X-Knawat-Hmac-Sha256')) == false) {
            return $response->withJson(['error' => "Invalid request"], 503);
        }

        //store webhook to logs
        $topic = implode(",",$request->getHeader('X-Knawat-Topic'));
        $log_id = $this->knawat->logWebhookCall($topic);

        if ($input['store']) {
            $store = json_decode(json_encode($input['store']));
            //generate new token
            $token = $this->knawat->generateToken(['consumer_key'=>$store->consumer_key,'consumer_secret'=>$store->consumer_secret]);
            $this->knawat->updateSettings(['store' => $store,'token'=>$token, 'install_completed'=>true]);
            $this->knawat->updateLogStatus($log_id);
            $this->logData(['name'=>'store updated successfully','request body' => $request->getParsedBody()]);

            return $response->withJson(['status' => 'success'], 200);
        }

        if (isset($input['currency'])) {
            $this->knawat->updateSettings(['knawat_currency' => $input['currency']]);
            if ($this->container['currency']->has($input['currency'])) {
                $this->settings->updateGeneralSetting(['config_currency' => $input['currency']]);
            }
        }

        return $response->withJson(['failed to update store data'], 422);
    }

    /***************** Listen to update profit webhook ***************/
    public function updateProfitListener(Request $request, Response $response)
    {

        $this->logData(['name'=>'update profit','request body' => $request->getParsedBody()]);

        //make validation
        if (!$this->knawat->isInstalled())
            return $response->withJson(['error' => "App not installed on ExpandCart"], 503);

        $input = $request->getParsedBody();

        if ($this->validate($input, $request->getHeader('X-Knawat-Hmac-Sha256')) == false) {
            return $response->withJson(['error' => "Invalid request"], 503);
        }

        //store webhook to logs
        $topic = implode(",",$request->getHeader('X-Knawat-Topic'));
        $log_id = $this->knawat->logWebhookCall($topic);

        if ($input['store']) {

            $old_settings = $this->knawat->getSettings();
            $store = json_decode(json_encode($input['store']));
            //generate new token
            $token = $this->knawat->generateToken(['consumer_key'=>$store->consumer_key,'consumer_secret'=>$store->consumer_secret]);
            $this->knawat->updateSettings(['store' => $store,'token'=>$token]);
            $this->knawat->updateLogStatus($log_id);

            //update products with new prices
            $this->knawat->updateKnawatProductsPriceWithNewProfit($old_settings['store']->sale_price, $store->sale_price);
            $this->logData(['name'=>'profit updated successfully','request body' => $request->getParsedBody()]);

            return $response->withJson(['status' => 'success'], 200);
        }

        return $response->withJson(['error'=>"failed to update profit"], 422);
    }


    public function logData($body)
    {
        $this->container['logger']->info(json_encode($body));
    }

}