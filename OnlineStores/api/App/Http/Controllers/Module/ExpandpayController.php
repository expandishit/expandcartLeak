<?php

namespace Api\Http\Controllers\Module;

use Api\Http\Controllers\Controller;
use Api\Models\Token;
use Api\Models\User;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;



class ExpandpayController extends Controller{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->config 	= $this->container['config'];
        $this->setting 	= $this->container['setting'];
		$this->registry = $this->container['registry'];
        $this->expandpay = $this->container['expandpay'];
        $this->order = $this->container['order'];
        $this->domain = $this->container['domain'];
    }

    public function callback(Request $request, Response $response){
			
			
		 $callback_path  = '/index.php?route=payment/expandpay/callback&invoice_id=' . $_GET['invoice_id'];
         
		 $url = sprintf(
                "%s://%s%s",
                isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
               STORECODE . '.expandcart.com',
                $callback_path
            );
			
			$domains = $this->domain->getDomains();

			if(is_array($domains) && !empty($domains)){
				 $url = sprintf(
						"%s://%s%s",
						isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
					   rtrim(array_values($domains)[0]['DOMAINNAME'],"/"),
						$callback_path
					);
			
			}
					
        return $response->withHeader('Location',$url);
    }
	
    public function updatePaymentRegister(Request $request, Response $response){

        $request_header = getallheaders();

		//more aaccurate at signature validation than encoding the barsed body
		$body 	   = file_get_contents('php://input'); 
		
		//for some reason it received as X-Expandpay-Signature not as sended X-ExpandPay-Signature
		$signature 		= $request_header['X-Expandpay-Signature'] ?? $request_header['X-ExpandPay-Signature'] ?? "";
		$request_body 	= $request->getParsedBody();
        
	   $contents_logs = [
            'header' => $request_header,
            'body' 	 => $body
        ]; // used for logs
		
        $this->log('webhook',$contents_logs);
		
        if($this->validateSignature($body,$this->config->get('expandpay_token'),$signature)){
            switch($request_body['type']){
                case 'register':
                    $this->expandpay->editSettingValue('expandpay','expandpay_merchant_status',$request_body['status']);
                    
					if($request_body['status'] == 'active'){
						$this->expandpay->editSettingValue('expandpay','expandpay_file_upload',1);
					}
					else if($request_body['status'] == 'rejected'){
                        $this->expandpay->editSettingValue('expandpay','expandpay_status',0);
                        $this->expandpay->editSettingValue('expandpay','expandpay_file_upload',0);
                    }
                    return $response->withJson([
                        'status' => 'OK',
                        'message' => 'merchant status updated successfully'
                    ]);
                    break;
                case 'payment':
					return $this->updateOrderStatus($response,$request_body);
                    break;
            }

        }else{               
            return $response->withJson([
                'status' => "ERR",
                'error' => 'authorization failed'
            ]);
		}
    }

    private function updateOrderStatus($response,$request_body){
        $request_body['custom_fields'] = json_decode($request_body['custom_fields'],true);
        
        $order_id = $request_body['custom_fields']['customer_refrence'];
        
        if(!isset($order_id))
            return $response->withJson([
                'status' => 'ERR',
                'message' => 'customer refrence is missing'
            ]);
        $order_info = $this->expandpay->getOrder($order_id);

        if($order_info['payment_trackId'] != $request_body['key'])
            return $response->withJson([
                'status' => 'ERR',
                'message' => 'invoice id not match any records'
            ]);
        
        if($request_body['status'] == 'paid')
            $this->expandpay->confirm($order_id, $this->config->get('expandpay_order_status_id'));
        else
            $this->expandpay->confirm($order_id, $this->config->get('expandpay_denied_order_status_id'));

        return $response->withJson([
                            'status' => 'OK',
                            'message' => 'order status updated successfully'
                        ]);
    }
    
	private function validateSignature($payload ,$key,$header_signature =''){

        // Signature matching
        $expected_signature = hash_hmac('sha1', $payload, $key);
      
        $signature = '';
        if(
          strlen($header_signature) == 45 &&
          substr($header_signature, 0, 5) == 'sha1='
          ) {
          $signature = substr($header_signature, 5);
        }

        //validate 
        if (hash_equals($signature, $expected_signature)) {
			return true;
        }
      
        return false;
    }

    private function log($type, $contents , $fileName=false)
    {
        if (!$fileName || empty($fileName))
            $fileName='expandpay.log';

        $log = new \Log($fileName);
        $log->write('[' . $type . '] ' . json_encode($contents));

    }
}



?>