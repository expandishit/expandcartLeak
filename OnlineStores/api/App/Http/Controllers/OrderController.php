<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Respect\Validation\Validator as vld;

class OrderController extends Controller
{
    private $order, $product ,$registry;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->order = $this->container['order'];
		$this->registry = $container['registry'];
    }

    public function index(Request $request, Response $response)
    {
        $params = $_GET; //$request->getQueryParams();

        $page  = $params['page'] ?? 0;
        $limit = $params['limit'] ?? 20;

        $data = [
            'order_status_id' =>   $params['order_status_id'] ?? null,
            'customer_id' =>   $params['customer_id'] ?? null,
            'product_id' =>   $params['product_id'] ?? null,
            'country_id' =>   $params['country_id'] ?? null,
            'zone_id' =>   $params['zone_id'] ?? null,
            'date_added' =>   $params['date_added'] ?? null,
            'archived_orders' => (isset($params['archived_orders'] ) && $params['archived_orders'] == 1 ) ? 'on' : '',
            'unhandled_orders' => ['status' => (isset($params['unhandled']) && $params['archived_orders'] == 1 ) ? 'unhandled' : ''],
            'total' => $params['total'] ?? null,
            'sort'  => $params['sort'] ?? null,
            'order' => $params['order'] ?? null,
            'start' => $page * $limit,
            'limit' => $limit ,
			'search'=>  $params['search'] ?? false,
        ];
		

        return $response->withJson($this->order->getAll($data));
    }

    public function show(Request $request, Response $response, $args)
    {

        $order_id = $request->getQueryParam('order_id');
        if(!$order_id){
            return $response->withJson(['status' => 'failed', 'message' => 'Missing order_id']);
        }

        $order_info = $this->order->getOrderForApi($order_id);

        $products = [];
        foreach ($order_info['products'] as $key => $product)
        {
            $product['image'] = \Filesystem::getUrl('image/'.$product['image']);;
            array_push($products , $product);
        }
        $order_info['products'] = $products;
        return $response->withJson($order_info);
    }

    public function store(Request $request, Response $response)
    {
    }

    public function update(Request $request, Response $response, $args)
    {
    }

    public function delete(Request $request, Response $response, $args)
    {
		$parameters = $request->getParsedBody();
		 $order_id  = $parameters['order_id'];
		 $action 	= $parameters['action'];
		 
		  if((!$this->registry->get('user')->hasPermission('modify', 'sale/order')) && !$this->registry->get('user')->hasPermission('custom', 'deleteOrder'))
        {
          
           return $response->withJson(['status' => 'failed', 'message' => 'unauthorized action']);
        }
		
		 if(!$order_id || !$action){
            return $response->withJson(['status' => 'failed', 'message' => 'Missing order_id or action']);
        }

        return $response->withJson(['status' => 'ok', 'data' => $this->order->deleteOrder($order_id,$action)]);
    }

    public function change_status(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        /*var_dump(vld::intVal()->validate(''));
        exit();*/
        return $response->withJson(['status' => 'ok', 'data' => $this->order->changeStatus($data)]);
    }

    public function getStatuses(Request $request, Response $response)
    {
        return $response->withJson(['status' => 'ok', 'data' => $this->order->getStatuses()]);
    }
	
    public function latestOrders(Request $request, Response $response)
    {
        $params = $_GET; //$request->getQueryParams();

        $page  = $params['page'] ?? 0;
        $limit = $params['limit'] ?? 5;

        $data = [
            'order_status_id' =>   $params['order_status_id'] ?? null,
            'customer_id' =>   $params['customer_id'] ?? null,
            'product_id' =>   $params['product_id'] ?? null,
            'country_id' =>   $params['country_id'] ?? null,
            'zone_id' =>   $params['zone_id'] ?? null,
            'date_added' =>   $params['date_added'] ?? null,
            'archived_orders' => (isset($params['archived_orders'] ) && $params['archived_orders'] == 1 ) ? 'on' : '',
            'unhandled_orders' => ['status' => (isset($params['unhandled']) && $params['archived_orders'] == 1 ) ? 'unhandled' : ''],
            'total' =>   $params['total'] ?? null,
            'sort'  => $params['sort'] ?? 'date_added',
            'order' => $params['order'] ?? 'desc',
            'start' => $page * $limit,
            'limit' => $limit,
			'search'=> $params['search'] ?? false,
        ];
		
		$orders = $this->order->getAll($data);

		$orders_data = [];
		if(array_key_exists('data',$orders)){
			
			foreach ($orders['data'] as $order){
			
				$orderProducts = $this->order->getOrderProducts($order['order_id']);
				
				if(!$orderProducts){
					$orderProducts=[];
				}

                foreach ($orderProducts as &$product){
                    if (strpos($product['image'], 'no_image.jpg'))
                        $product['image'] = \Filesystem::getUrl($product['image']);
                    else
                        $product['image'] = \Filesystem::getUrl('image/'.$product['image']);
                }
					
				$order['orderProducts']=$orderProducts;
				$orders_data[]=$order;
			}
			
			$orders["data"]=$orders_data;
		}
		
        return $response->withJson($orders);
       
    }

	public function updateCustomerInfo(Request $request, Response $response)
    {
		
		
        $parameters = $request->getParsedBody();
		$order_id  = $parameters['order_id'];
		$customer  = $parameters['customer'];
		
		if((!$this->registry->get('user')->hasPermission('modify', 'sale/order')))
        {
           return $response->withJson(['status' => 'failed', 'message' => 'unauthorized action']);
        }
		
		if(!$order_id || !$customer){
            return $response->withJson(['status' => 'failed', 'message' => 'Missing order_id or customer info Array']);
        }
				
		if (preg_match('#^[0-9]+$#', $order_id) == false) {
			return $response->withJson(['status' => 'failed', 'message' => 'Unauthorized action - order id should be number']);
        }
		
		
		$order=$this->order->getOrderForApi($order_id);
		if (!$order['data']) {
           return $response->withJson(['status' => 'failed', 'message' => 'something went wrong - this order not found']);
        }
		
		
		
		$update = $this->order->updateCustomerInfo($order_id,$customer);
		
		if (!$update) {
           return $response->withJson(['status' => 'failed', 'message' => 'something went wrong - cant update customer info ']);
        }
        return $response->withJson(['status' => 'ok', 'data' => $update]);
    }
	
	public function updateCustomerAddresses(Request $request, Response $response)
    {
		
		
        $parameters = $request->getParsedBody();
		$order_id  = $parameters['order_id'];
		$customer  = $parameters['customer'];
		
		if((!$this->registry->get('user')->hasPermission('modify', 'sale/order')))
        {
           return $response->withJson(['status' => 'failed', 'message' => 'unauthorized action']);
        }
		
		if(!$order_id || !$customer){
            return $response->withJson(['status' => 'failed', 'message' => 'Missing order_id or customer  Array']);
        }
				
		if (preg_match('#^[0-9]+$#', $order_id) == false) {
			return $response->withJson(['status' => 'failed', 'message' => 'Unauthorized action - order id should be number']);
        }
		
		
		$order=$this->order->getOrderForApi($order_id);
		if (!$order['data']) {
           return $response->withJson(['status' => 'failed', 'message' => 'something went wrong - this order not found']);
        }
		
		
		
		$update = $this->order->updateCustomerAddresses($order_id,$customer);
		
		if (!$update) {
           return $response->withJson(['status' => 'failed', 'message' => 'something went wrong - cant update customer address ']);
        }
        return $response->withJson(['status' => 'ok', 'data' => $update]);
    }

	public function updateOrderGateway(Request $request, Response $response)
    {
		
		
        $parameters 		= $request->getParsedBody();
		$order_id   		= $parameters['order_id'];
		$gatway_id    		= $parameters['gatway_id'];
		$isBundled    		= $parameters['bundled'];
		$url    			= $parameters['url'];
		
		if((!$this->registry->get('user')->hasPermission('modify', 'sale/order')))
        {
           return $response->withJson(['status' => 'failed', 'message' => 'unauthorized action']);
        }
		
		if(!$order_id || !$gatway_id || !$isBundled || !$url ){
            return $response->withJson(['status' => 'failed', 'message' => 'Missing order_id ,gatway_id ,bundled  or url ']);
        }
				
		if (preg_match('#^[0-9]+$#', $order_id) == false) {
			return $response->withJson(['status' => 'failed', 'message' => 'Unauthorized action - order id should be number']);
        }
		
			
		$order=$this->order->getOrderForApi($order_id);
		if (!$order['data']) {
           return $response->withJson(['status' => 'failed', 'message' => 'something went wrong - this order not found']);
        }
		
		
		$update = $this->order->updateOrderManualGateway($order_id, $gatway_id, $isBundled);
		
		if (!$update) {
           return $response->withJson(['status' => 'failed', 'message' => 'something went wrong - cant update customer address ']);
        }
		
		$update2 =  $this->order->updateShippingTrackingURL($order_id, $url);
		
        return $response->withJson(['status' => 'ok', 'data' => $update]);
    }
	
	/**
	//parameters 
	{
	"order_id" : int,
	"order_status_id" :int ,
	"affiliate_id" : int ,
	"products" : [
			//for deleted products 
			{
				"order_product_id" : int,
				"delete_status" : text <hard>
			},
			
			//for added & edited products 
			{
				"order_product_id" : int, // 0 in case of new 
				"product_id" : int,
				"delete_status" : text <not_deleted>,
				"name" : text,
				"product_status" : int,
				"soft_delete_status" : int,
				"total" : float,
				"tax"   : float,
				"reward": float,
				"price": float,
				"quantity": int,
				"model"   : text,
				"added_by_user_type"   : text<admin | customer>,
				"order_option" : [    //optional in case of exists
						{
						"order_option_id" : int ,
						"product_option_id" : int ,
						"product_option_value_id" : int ,
						"name" : text ,
						"value" : text ,
						"type" : text
						}
					]
			}
		],
	"order_total" : [
		{
			"order_total_id" : int ,
			"code" : text ,
			"title" : text ,
			"text" : text ,
			"value" : float ,
			"sort_order" : int
		}
	]
	}
*/
	public function updateOrderProducts(Request $request, Response $response)
    {


        $parameters 		= $request->getParsedBody();
		$order_id   		= $parameters['order_id'];
		$order_products   	= $parameters['products'];
		$order_total    	= $parameters['order_total'];



		if(!$order_id || empty($order_products) || empty($order_total)  ){
            return $response->withJson(['status' => 'failed', 'message' => 'Missing order_id , products , order_status_id OR order_total ']);
        }

		if (preg_match('#^[0-9]+$#', $order_id) == false) {
			return $response->withJson(['status' => 'failed', 'message' => 'Unauthorized action - order id should be number']);
        }


		$order=$this->order->getOrderForApi($order_id);
		if (!$order['data']) {
           return $response->withJson(['status' => 'failed', 'message' => 'something went wrong - this order not found']);
        }

		$data = [
				"order_products" => $order_products ,
				"order_id" => $order_id ,
				"order_status_id" =>$order_status_id,
				"order_total" => $order_total
			];

		$update = $this->order->updateOrderProducts($order_id, $data);
		//$update = true;
		if (!$update) {
           return $response->withJson(['status' => 'failed', 'message' => 'something went wrong - cant update order products ']);
        }


        return $response->withJson(['status' => 'ok', 'data' => $update]);
    }
	
	public function get_shipping_methods(Request $request, Response $response)
	{
		$parameters 		= $_GET;
		$order_id 			= false;
		
		if(isset($parameters["order_id"])){
		$order_id   		= $parameters['order_id'];
		}
		
		if(!$order_id ){
            return $response->withJson(['status' => 'failed', 'message' => 'Missing order_id ']);
        }
        
		$shipping_methods =  $this->order->get_shipping_methods($order_id);
        return $response->withJson(['status' => 'ok', 'data' => $shipping_methods]);
    }
    
    public function generate_invoice(Request $request, Response $response){
        
        $parameters = $request->getParsedBody();
        if(!$parameters['order_id']){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID, Value, Action or Column missing!'
            ], 406);
        }

        $upres = $this->order->createInvoiceNoAdminApi($parameters['order_id']);

        if($upres){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'generate Successfully.'
            ], 201);
        }else{
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }

    }
    public function view_invoice(Request $request, Response $response){
        $parameters = $request->getParsedBody();
        if(!$parameters['order_id']){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID, Value, Action or Column missing!'
            ], 406);
        }
        $invoice_url = $this->order->getinvoice($parameters['order_id']);

        return $response->withJson([
            'status' => $this->status[1],
            'invoice_url' => $invoice_url 
        ], 201);
    }


    public function update_provider_orders(Request $request, Response $response)
    {
        $parameters 		= $request->getParsedBody();
        $order_id   		= $parameters['order_id'];
        $paid_to_merchant   = $parameters['paid_to_merchant'] ? date('Y-m-d H:i:s',strtotime($parameters['paid_to_merchant'])) : '';
        $courier_name    	= $parameters['courier_name'];

        if(!$order_id || !$courier_name )
            return $response->withJson(['status' => 'failed', 'message' => 'Missing order_id or courier_name']);

        $order=$this->order->getOrderForApi($order_id);
        if (!$order['data'])
            return $response->withJson(['status' => 'failed', 'message' => 'something went wrong - this order not found']);

        //get provider info data
        $provider_order = $this->order->getProviderOrder($order_id);
        if($provider_order){
            $this->order->updateProviderOrder($provider_order['id'],['paid_to_merchant'=>$paid_to_merchant,'courier_name'=>$courier_name]);
        }else{
            $this->order->createProviderOrder(['order_id'=>$order_id,'provider_name'=>'expandship','paid_to_merchant'=>$paid_to_merchant,'courier_name'=>$courier_name]);
        }

        return $response->withJson(['status' => 'ok']);
    }
}