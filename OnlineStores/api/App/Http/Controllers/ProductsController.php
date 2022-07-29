<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

class ProductsController extends Controller
{
    private $product, $config, $option, $upload_dir, $setting;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->product = $this->container['product'];
        $this->config = $this->container['config'];
		$this->option = $container['option'];
        $this->container['upload_directory'] = DIR_IMAGE . 'data/products';
        $this->upload_dir = $this->container['upload_directory'];
        $this->setting = $this->container['setting'];
    }
	
	public function index(Request $request, Response $response)
    {
        
		$params = $_GET; 

        $page  = $params['page'] ?? 0;
        $limit = $params['limit'] ?? 10;
		$data=[];

        $data = [
            'filter_name' =>   $params['filter_name'] ?? null,
            'filter_model' =>   $params['filter_model'] ?? null,
            'filter_price' =>   $params['filter_price'] ?? null,
            'filter_quantity' =>   $params['filter_quantity'] ?? null,
            'filter_status' =>   $params['filter_status'] ?? null,
            'sort'  => $params['sort'] ?? null,
            'order' => $params['order'] ?? null,
            'start' => $page * $limit,
            'limit' => $limit ,
        ];


		$allowed_sort =  array(
            'pd2.name',
            'p.model',
            'p.price',
            'p.quantity',
            'p.status',
            'p.date_added',
            'p.sort_order'
        );

		if(isset($params['sort']) && !in_array($params['sort'],$allowed_sort)){
           return $response->withJson([
									'status' => 'failed', 
									'message' => 'sort value not accepted - allowed values "'.implode($allowed_sort).'"'
									]);
		}

		$filterData = [];
		if(isset($params['date_available'])){
			$filterData['date_available']=$params['date_available'];
		}
		if(isset($params['booleans'])){
			$filterData['booleans']=$params['booleans'];
		}
		if(isset($params['ranges'])){
			$filterData['ranges']=$params['ranges'];
		}
		if(isset($params['search'])){
			$filterData['search']=$params['search'];
		}
        if(isset($params['language_code'])){
            $filterData['language_code']=$params['language_code'];
        }
		//return $response->withJson($this->product->getAll());

        $result = $this->product->getProductsToFilter($data,$filterData);
        $products = $result['data'];
        foreach ($products as &$product){
            if ($product['image'] && strpos($product['image'], 'no_image.jpg') === false)
                $product['image'] = \Filesystem::getUrl('image/'.$product['image']);
            else
                $product['image'] = \Filesystem::getUrl('image/no_image.jpg');

            $product_images = $this->product->getProductImages($product['product_id']);
            $images = [];
            foreach ($product_images as $image) {
                $images[] = \Filesystem::getUrl('image/' . $image['image']); //HTTP_IMAGE . $image['image'];
            }
            $product['images'] = $images;
        }
        $payload = [
            'data' => $products,
            'totalFiltered' => $result['totalFiltered'],
            'total' => $result['total']
        ];

		return $response->withJson([
				'status' => 'success',
                'data' => $payload
        ]);

    }
	
    public function allProducts(Request $request, Response $response)
    {
        return $response->withJson($this->product->getAll());
    }

    public function show(Request $request, Response $response, $args)
    {
        $validator = $this->product->validator([
            'id' => $args['id']
        ], [
            'id' => 'int'
        ]);

        if ($validator) {

            $product = $this->product->getById($args['id']);
            $product_options = $this->product->getProductOptions($args['id']);

            // Get product options and option values
            if (!empty($product_options))
            {
                $product['product_option'] = array();
                foreach ($product_options as $option)
                {
                    $option_info = $this->option->getById($option['option_id']);
                    if (
                        $option_info['type'] == 'select' ||
                        $option_info['type'] == 'radio' ||
                        $option_info['type'] == 'checkbox' ||
                        $option_info['type'] == 'image' ||
                        $option_info['type'] == 'product'
                    ) {

                        //$option_values = $this->option->getOptionValues($option['option_id']);
                        $option_value_data = array();
                        foreach ($option['product_option_value'] as $option_value)
                        {
                            $option_value_data[] = array(
                                'option_value_id' => $option_value['option_value_id'],
                                'product_option_value_id' => $option_value['product_option_value_id'],
                                'quantity' => $option_value['quantity'],
                                'subtract' => $option_value['subtract'],
                                'price' => $option_value['price'],
                                'price_prefix' => $option_value['price_prefix'],
                                'points' => $option_value['points'],
                                'points_prefix' => $option_value['points_prefix'],
                                'weight' => $option_value['weight'],
                                'weight_prefix' => $option_value['weight_prefix'],
                                'sort_order' => $option_value['sort_order'],
                                'images' => (isset($option_value['images']) ? $option_value['images'] : []),
                                'option_value_description' => $this->option->getOptionValueDescription($option_value['option_value_id']),
                            );
                        }
                        $product['product_option'][] = array(
                            'product_option_id' => $option['product_option_id'],
                            'option_id' => $option_info['option_id'],
                            'name' => $option_info['name'],
                            'type' => $option_info['type'],
                            'required' => $option['required'],
                            'option_description' => $option_info['option_description'],
                            'product_option_value' => $option_value_data,
                        );
                    }
                    else {
                        $data['product_options'][] = array(
                            'product_option_id' => $option['product_option_id'],
                            'option_id' => $option['option_id'],
                            'name' => $option['name'],
                            'type' => $option['type'],
                            'option_value' => $option['option_value'],
                            'required' => $option['required']
                        );
                    }
                }
            }

            // Get product Categories
            $product_options = $this->product->getProductOptions($args['id']);

            if (!$product) {
                return $response->withJson([
                    'status' => 'error',
                    'status_code' => $this->status[0],
                    'error_description' => ''
                ]);
            }


            return $response->withJson([
                'status' => $this->status[1],
                'data' => $product
            ]);

        } else {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }

        return $response->withJson([
            'status' => 'error',
            'status_code' => $this->status[3],
            'error_description' => ''
        ]);
    }

    public function dropna(Request $request, Response $response, $args)
    {
        $validator = $this->product->validator([
            'id' => $args['id']
        ], [
            'id' => 'int'
        ]);

        if ($validator) {
            $productDropna = $this->product->getDropnaProByDropnaId($args['id']);

            if (!$productDropna) {
                return $response->withJson([
                    'status' => 'error',
                    'status_code' => $this->status[0],
                    'error_description' => ''
                ]);
            }

            return $response->withJson([
                'status' => $this->status[1],
                'data' => $productDropna
            ]);

        } else {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }

        return $response->withJson([
            'status' => 'error',
            'status_code' => $this->status[3],
            'error_description' => ''
        ]);
    }

    public function store(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();

        // Validate Add Product Inputs
        if (!empty($this->validateAddProduct($parameters)))
        {
            return $response->withJson($this->validateAddProduct($parameters)[0], 406);
        }

        if (isset($parameters['image']) && $this->product->isImage($parameters['image'])) {

            $path = $this->product->createDirectory(DIR_IMAGE . 'data/api/' . $this->config->get('config_template'));

            if ($path) {
                $parameters['image'] = $this->product->downloadImage(
                    $parameters['image'], $path, (substr(time(), -5) . '_' . basename($parameters['image']))
                );
            }
        }

        $createProduct = $this->product->createProduct($parameters);

        if($createProduct) {
            $guide = $this->setting->getGuideValue('GETTING_STARTED');
            if (!isset($guide['ADD_PRODUCTS']) || $guide['ADD_PRODUCTS'] !=1){
                $this->setting->editGuideValue('GETTING_STARTED', 'ADD_PRODUCTS', '1');
            }
            return $response->withJson([
                'status' => $this->status[1],'data' => $createProduct
            ], 201);
        }

        return $response->withJson([
            'status' => 'error',
            'status_code' => $this->status[3],
            'error_description' => 'Failed to add product'
        ], 400);
    }

    /**
     * @param $parameters
     * @return array
     */
    public function validateAddProduct($parameters)
    {
        $errors = array();
        // Get products count
        $currentProductsCount = $this->product->getTotalProductsCount();
        if($currentProductsCount + 1 > PRODUCTSLIMIT  && PRODUCTID == 3)
        {
            $errors[] = [
                'status' => 'error',
                'status_code' => "PACKAGE_UPGRADE",
                'error_description' => 'Products limit exceeded'];
        }
        if(!isset($parameters['product_description']) || empty($parameters['product_description']))
        {
            $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'Product description cannot be empty'];
        }
        elseif (!empty($parameters['product_description'])) // Validate Product Description
        {
            foreach ($parameters['product_description'] as $code => $description)
            {
                if (!isset($description['language_id']) || !isset($description['name']) || !isset($description['description']))
                {
                    $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'language_id, name and description in ' . $code . ' language are required'];
                }
            }
        }
        if (!isset($parameters['price']) || $parameters['price'] < 1) // Validate Model
        {
            $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'price filed is required and should be greater than 1'];
        }

        return $errors;
    }

    public function update(Request $request, Response $response, $args)
    {
        $parameters = $request->getParsedBody();

        // Validate Add Product Inputs
        if (!empty($this->validateUpdateProduct($parameters, $args)))
        {
            return $response->withJson($this->validateUpdateProduct($parameters, $args)[0], 406);
        }

        if (isset($parameters['image']) && $this->product->isImage($parameters['image'])) {

            $path = $this->product->createDirectory(DIR_IMAGE . 'data/api/' . $this->config->get('config_template'));

            if ($path) {
                $parameters['image'] = $this->product->downloadImage(
                    $parameters['image'], $path, (substr(time(), -5) . '_' . basename($parameters['image']))
                );
            }
        }

        if ($updatedProduct = $this->product->updateProduct($args['id'], $parameters)) {
            return $response->withJson([
                'status' => $this->status[1],'data' => $updatedProduct
            ], 202);
        }

        return $response->withJson([
            'status' => 'error',
            'status_code' => $this->status[3],
            'error_description' => 'Product not updated'
        ], 400);
    }

    /**
     * @param $parameters
     * @return array
     */
    public function validateUpdateProduct($parameters, $args)
    {
        $errors = array();
        // Get product
        if (!isset($args['id']) || empty($args['id']))
        {
            $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'Product id cannot be empty'];
        }
        if (!filter_var($args['id'], FILTER_VALIDATE_INT))
        {
            $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'Product id should be integer'];
        }
        $product = $this->product->getById($args['id']);
        if (!$product)
        {
            $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'Product id not found'];
        }
        if(!isset($parameters['product_description']) || empty($parameters['product_description']))
        {
            $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'Product description cannot be empty'];
        }
        elseif (!empty($parameters['product_description'])) // Validate Product Description
        {
            foreach ($parameters['product_description'] as $code => $description)
            {
                if (!isset($description['language_id']) || !isset($description['name']) || !isset($description['description']))
                {
                    $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'language_id, name and description in ' . $code . ' language are required'];
                }
            }
        }
        if (!isset($parameters['price']) || $parameters['price'] < 1) // Validate Model
        {
            $errors[] = ['status' => 'error', 'status_code' => 406, 'error_description' => 'price filed is required and should be greater than 1'];
        }

        return $errors;
    }

    public function delete(Request $request, Response $response, $args)
    {
        $validator = $this->product->validator([
            'id' => $args['id']
        ], [
            'id' => 'required|int'
        ]);

        if (!$validator) {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 406);
        }

        $this->product->deleteProduct($args['id']);

        return $response->withJson(['status' => $this->status[1]], 202);
    }

    public function updateValue(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();

        if(!$parameters['product_id'] || !isset($parameters['new_value']) || !$parameters['action_type'] || !$parameters['action_column']){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID, Value, Action or Column missing!'
            ], 406);
        }

        //If Dropna request, get mapped product id
        if(isset($parameters['is_dropna']) && $parameters['is_dropna']){
            $productDropna = $this->product->getDropnaProByDropnaId($parameters['product_id']);
            if($productDropna)
            {
                $product_id = $productDropna['product_id'];

                //Update product option values quantity
                if($parameters['opt_vals'] && count($parameters['opt_vals']) > 0){
                    foreach ($parameters['opt_vals'] as $opt_val) {
                        //get mapped product option value id
                        $productOptValDropna = $this->product->getDropnaProOptValtByDropnaId($opt_val['dropna_val_id']);
                        if($productOptValDropna)
                        {
                            $this->product->updateProductOptValQuantity($productOptValDropna['product_option_value_id'], $opt_val['quantity']);
                        }
                    }
                }

            }else{
                return $response->withJson([
                    'status' => 'error',
                    'status_code' => $this->status[2],
                    'error_description' => 'No Dropna Product Mapped!'
                ], 406);
            }
        }else{
            $product_id = $parameters['product_id'];
        }
        
        if($product_id){

            $updateData = [   
                            'column' => $parameters['action_column'], 
                            'value'  => $parameters['new_value'], 
                            'action' => $parameters['action_type']
                          ];
                          
            $updtQty = $this->product->api_model_updateProductValue($product_id, $updateData);
            if($updtQty)
                return $response->withJson([
                    'status' => $this->status[1],
                    'data' => ucfirst($parameters['action_column']).' Updated Successfully.'
                ], 201);
        }
        else{
            return $response->withJson([
                    'status' => 'error',
                    'status_code' => $this->status[2],
                    'error_description' => 'No Product Found!'
                ], 406);
        }

        return $response->withJson([
                    'status' => 'error',
                    'status_code' => $this->status[2],
                    'error_description' => 'Update Quantity Failed!'
                ], 406);
    }

    public function productsSchedule(Request $request, Response $response)
    {
       $productsData = $this->product->productsSchedule();
       return $response->withJson([
                'data' => $productsData
            ], 201);
    }

    public function productsMapp(Request $request, Response $response)
    {
       $parameters = $request->getParsedBody();
       $mapp = $this->product->productsMapp($parameters['mappData']);
       return $response->withJson([
                'status' => $mapp
            ], 201);
    }
	
	public function getProductRelated(Request $request, Response $response, $args)
    {
        $validator = $this->product->validator([
            'id' => $args['id']
        ], [
            'id' => 'int'
        ]);

        if ($validator) {

            $products = $this->product->getProductRelated($args['id']);
			$data['product_related'] = array();

			foreach ($products as $product_id) {
				$related_info = $this->product->getById($product_id);

				if ($related_info) {
					$data['product_related'][] = array(
						'product_id' => $related_info['product_id'],
						'name' => $related_info['name']
					);
				}
			}
			
			return $response->withJson([
                'status' => 'ok',
                'data' => $data
            ]);

        } 

       return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'validator' => $validator,
                'error_description' => ''
            ]);
	}


	public function getProductOptions(Request $request, Response $response, $args)
    {
        $validator = $this->product->validator([
            'id' => $args['id']
        ], [
            'id' => 'int'
        ]);


        if ($validator) {

            $product_options = $this->product->getProductOptions($args['id']);

            // var_dump($product_options);die;
			$data['product_options'] = array();
            foreach ($product_options as $product_option) {
				  $option_info = $this->option->getById($product_option['option_id']);
				if ($option_info) {
                if (
                    $option_info['type'] == 'select' ||
                    $option_info['type'] == 'radio' ||
                    $option_info['type'] == 'checkbox' ||
                    $option_info['type'] == 'image' ||
                    $option_info['type'] == 'product'
                ) {
                    $product_option_value_data = array();

                    foreach ($product_option['product_option_value'] as $product_option_value) {
                        $product_option_value_data[] = array(
                            // Product Option Image PRO module <<
                            'images' => (isset($product_option_value['images']) ? $product_option_value['images'] : []),
                            // >> Product Option Image PRO module
                            'name' => $product_option_value['name'],
                            'option_value_description' => $this->option->getOptionValueDescription($product_option_value['option_value_id']),
                            'product_option_value_id' => $product_option_value['product_option_value_id'],
                            'option_value_id' => $product_option_value['option_value_id'],
                            'quantity' => $product_option_value['quantity'],
                            'subtract' => $product_option_value['subtract'],
                            'price' => $product_option_value['price'],
                            'price_prefix' => $product_option_value['price_prefix'],
                            'points' => $product_option_value['points'],
                            'points_prefix' => $product_option_value['points_prefix'],
                            'weight' => $product_option_value['weight'],
                            'weight_prefix' => $product_option_value['weight_prefix'],
                            'sort_order' => $product_option_value['sort_order'],
                        );
                    }

                    $data['product_options'][] = array(
                        'product_option_id' => $product_option['product_option_id'],
                        'product_option_value' => $product_option_value_data,
                        'option_id' => $product_option['option_id'],
                        'name' => $option_info['name'],
                        'type' => $option_info['type'],
                        'required' => $product_option['required']
                    );
                } else {
                    $data['product_options'][] = array(
                        'product_option_id' => $product_option['product_option_id'],
                        'option_id' => $product_option['option_id'],
                        'name' => $option_info['name'],
                        'type' => $option_info['type'],
                        'option_value' => $product_option['option_value'],
                        'required' => $product_option['required']
                    );
                }
         
            }
            $data['not_selected_options'] = $product_options['not_selected'];
				  
			}
			
			foreach ($data['product_options'] as $product_option) {
            if (
                $product_option['type'] == 'select' ||
                $product_option['type'] == 'radio' ||
                $product_option['type'] == 'checkbox' ||
                $product_option['type'] == 'image' ||
                $product_option['type'] == 'product'
            ) {
                if (!isset($this->data['option_values'][$product_option['option_id']])) {
                    $data['option_values'][$product_option['option_id']] = array_column(
                        $this->option->getOptionValues($product_option['option_id']),
						null,
                        'option_value_id'
                    );
                }
            }
        }

			//options types 
			$data['optionTypes'] = [
				'text_choose' => [
					'select',
					'radio',
					'checkbox',
					'image',
				],
				'text_input' => [
					'text',
					'textarea',
				],
				'text_file' => [
					'file'
				],
				'text_date' => [
					'date',
					'time',
					'datetime',
				]
			];
			
            return $response->withJson([
                'status' => 'ok',
                'data' => $data
            ]);

        } 

       return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
	}

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function UpdateProductOptions(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        if(!isset($parameters['product_id'])){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID is required!'
            ], 406);
        }

        $update_response = $this->product->updateProductOptions($parameters['product_id'], $parameters);

        if($update_response){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Options Updated Successfully.'
            ], 200);
        }
        else
        {
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 400);
        }

    }

    public function UpdateProductOptionValues(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        if(!isset($parameters['product_option_id']) || !isset($parameters['product_id']) || !isset($parameters['required'])){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Option ID, Product ID or Required is missing!'
            ], 406);
        }

        $options_values = array();
        foreach ($parameters['option_value'] as $option_value)
        {
            array_push($options_values, $option_value);
        }


        $update_response = $this->product->updateProductOptionValues(
            $parameters['product_option_id'],
            $parameters['product_id'],
            $options_values,
            $parameters);

        if($update_response){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Option Values Updated Successfully.'
            ], 200);
        }
        else
        {
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 400);
        }
    }

	public function autocomplete(Request $request, Response $response)
    {
        $customerGroup = null;
        $params = $_GET;
		
        if (isset($params['customer_group_id'])) {
            if (preg_match('#^[0-9]+$#', $params['customer_group_id'])) {
                $customerGroup = $params['customer_group_id'];
            }
        }
		if (
            isset($params['filter_name']) ||
            isset($params['filter_model']) ||
            isset($params['filter_category_id'])
        ) {
			if (isset($params['filter_name'])) {
                $filter_name = $params['filter_name'];
            } else {
                $filter_name = '';
            }

            if (isset($params['filter_model'])) {
                $filter_model = $params['filter_model'];
            } else {
                $filter_model = '';
            }

            if (isset($params['filter_barcode'])) {
                $filter_barcode = $params['filter_barcode'];
            } else {
                $filter_barcode = '';
            }

            if (isset($params['limit'])) {
                $limit = $params['limit'];
            } else {
                $limit = 20;
            }

            $data = array(
                'filter_name' => $filter_name,
                'filter_model' => $filter_model,
                'filter_barcode' => $filter_barcode,
                'start' => 0,
                'limit' => $limit
            );

            if (isset($params['filter_status'])) {
                $data['filter_status'] = $params['filter_status'];
            }

        $final_data = $this->product->autocomplete($data,$customerGroup);
        return $response->withJson(['status' => 'ok', 'data' => $final_data]);
        }
       return $response->withJson(['status' => 'failed', 'message' => 'you should filter with   filter_name ,filter_model or filter_barcode ']);
    }
    public function UpdateProductAttributes(Request $request, Response $response){

        $parameters = $request->getParsedBody();
        if(!$parameters['product_id'] || !isset($parameters['product_attribute'])){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID, Value, Action or Column missing!'
            ], 406);
        }

            $upres = $this->product->api_model_updateProductAttributes($parameters['product_id'],$parameters['product_attribute']);

        if($upres){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Updated Successfully.'
            ], 201);
        }else{
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }
    }

    public function UpdateProductLinking(Request $request, Response $response){

        $parameters = $request->getParsedBody();

        if(!$parameters['product_id']){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID, Value, Action or Column missing!'
            ], 406);
        }

        $upres = $this->product->api_model_updateProductLinking($parameters['product_id'], $parameters);

        if($upres){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Updated Successfully.'
            ], 201);
        }else{
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }


    }
  
      public function UpdateProductInfo(Request $request, Response $response){

        $parameters = $request->getParsedBody();
        if(!$parameters['product_id'] ){

            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID, Value, Action or Column missing!'
            ], 406);
        }

        $upres = $this->product->api_model_updateProductInfo($parameters['product_id'], $parameters);

        if($upres){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Updated Successfully.'
            ], 201);
        }else{
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }

    }

        public function UpdateRewardPoints(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        if(!$parameters['points'] || !isset($parameters['customergroup']) || !isset($parameters['rewardpoint'])){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID, Value, Action or Column missing!'
            ], 406);
        }
        $upres = $this->product->api_model_updateProductrewardpoints($parameters['product_id'],$parameters);

        if($upres){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Updated Successfully.'
            ], 201);
        }else{
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }


    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function AddProductDiscount(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        if(!isset($parameters['product_id']) || !isset($parameters['quantity']) || !isset($parameters['priority']) || !isset($parameters['price']) || !isset($parameters['date_start']) || !isset($parameters['date_end']) || !isset($parameters['customer_group_id'])){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID, Value, Action or Column missing!'
            ], 406);
        }

        $discount_response = $this->product->addProductDiscount($parameters['product_id'], $parameters);

        if($discount_response){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Discount Created Successfully.'
            ], 201);
        }
        else
        {
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 400);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function UpdateProductDiscount(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        foreach ($parameters['product_discount'] as $key => $value)
        {
            if(!isset($value['product_discount_id']) || !isset($value['quantity']) || !isset($value['priority']) || !isset($value['price']) || !isset($value['date_start']) || !isset($value['date_end']) || !isset($value['customer_group_id'])){
                return $response->withJson([
                    'status' => 'error',
                    'status_code' => $this->status[2],
                    'error_description' => 'Product ID, Value, Action or Column is missing in object number ' . ($key+1)
                ], 406);
            }
        }

        $upres = $this->product->api_model_api_model_updateProductDiscount($parameters);

        if($upres){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Updated Successfully.'
            ], 201);
        }else{
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function DeleteProductDiscount(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        $id = $parameters['product_discount_id'];
        if(!isset($parameters['product_discount_id'])){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product Discount ID is missing!'
            ], 406);
        }

        $delete_response = $this->product->deleteProductDiscount($id);

        if($delete_response){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Deleted Successfully.'
            ], 201);
        }
        else
        {
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 400);
        }
    }

    public function updateSeo(Request $request, Response $response){

        $parameters = $request->getParsedBody();
        if(!$parameters['product_id'] || !isset($parameters['product_description'])){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID, Value, Action or Column missing!'
            ], 406);
        }

            $upres = $this->product->api_model_updateProductdescriptionMultipleValues($parameters['product_id'], $parameters['product_description']);


        if($upres){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Updated Successfully.'
            ], 201);
        }else{
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }

    }

    public function UpdateInventory(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();

        if(!$parameters['product_id'] || !isset($parameters['quantity']) || !isset($parameters['minimum']) || !isset($parameters['sku']) || !isset($parameters['barcode']) ){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID, Value, Action or Column missing!'
            ], 406);
        }

        $upres = $this->product->api_model_updateProductInventory($parameters['product_id'], $parameters);


        if($upres){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Updated Successfully.'
            ], 201);
        }else{
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }
    }


	public function getProductDiscounts(Request $request, Response $response, $args)
	{

	 $validator = $this->product->validator([
            'id' => $args['id']
        ], [
            'id' => 'int'
        ]);

        if ($validator) {
            $product_discounts = $this->product->getProductDiscounts($args['id']);

			return $response->withJson([
                'status' => 'ok',
                'data' => $product_discounts
            ]);

        }  else {

       return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
		}
	}

	public function getProductImages(Request $request, Response $response, $args)
    {
        $validator = $this->product->validator([
            'id' => $args['id']
        ], [
            'id' => 'int'
        ]);

        if ($validator) {

            $productImages = $this->product->getProductImages($args['id']);

            $images = [];
            $main_image =  $this->product->getProductMainImage($args['id']);
            $images[0]['product_image_id'] = "main_image";
            $images[0]['product_id'] = $args['id'];
            $images[0]['image'] = \Filesystem::getUrl('image/' . $main_image[0]['image']);
            $images[0]['sort_order'] = 0;

            if (!empty($productImages))
            {
                foreach ($productImages as $productImage)
                {
                    $productImage['image'] = \Filesystem::getUrl('image/'.$productImage['image']);
                    $images[] = $productImage;
                }
            }

            // Remove no_image.jpg from array
            $images_array = [];
            foreach ($images as $image)
            {
                if (strpos($image['image'], 'no_image.jpg') === false)
                {
                    $images_array[] = $image;
                }
            }

            return $response->withJson([
                'status' => $this->status[1],
                'data' => $images_array
            ]);

        } else {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }

    }
    
    
    ################ Get Product By Name Or SKU ################
    public function getProductByNameOrSku(Request $request, Response $response, $args)
    {
        $validator = $this->product->validator([
            'value' => $args['value']
        ], [
            'value' => 'string'
        ]);
        

        if (!$validator)
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);

        // get by sku or name
        $product = $this->product->getByNameOrSku($args['value']);

        if ($product)
            return $response->withJson([
                'status' => $this->status[1],
                'data' => $product
            ]);


        return $response->withJson([
            'status' => 'error',
            'status_code' => $this->status[0],
            'error_description' => ''
        ]);
    }


    ################ Upload Product Image From Url ################
    public function uploadImageFromUrl(Request $request, Response $response, $args)
    {
        $inputs         = $request->getParsedBody();
        $inputs['id']   = $args['id'];
        $rules          = [ 
            "id"        => "required|int",
            "image_url" => "required|url",
        ];
        $validator  = $this->validator($inputs, $rules);
        if (!$validator) {
            return $response->withJson([
                'status'            => 'error',
                'status_code'       => $this->status[2],
                'error_description' => ''
            ], 406);
        }

        if (!$this->product->isImage($inputs['image_url']))
            return $response->withJson([
                'status'            => 'error',
                'status_code'       => $this->status[2],
                'error_description' => ''
            ], 406);

        $path = $this->product->createDirectory(DIR_IMAGE . 'data/api/' . $this->config->get('config_template'));

        if ($path)
            $inputs['image'] = $this->product->downloadImage(
                $inputs['image_url'], $path, (substr(time(), -5) . '_' . basename($inputs['image_url']))
            );

        if ($this->product->updateProduct($args['id'], $inputs))
            return $response->withJson([
                'status'    => $this->status[1],
                'data'      => 'Uploaded Successfully.'
            ], 200);

        return $response->withJson([
            'status'            => 'error',
            'status_code'       => $this->status[3],
            'error_description' => ''
        ], 400);

    }


    public function UpdateProductShipping(Request $request, Response $response){

        $parameters = $request->getParsedBody();
        if(!$parameters['product_id']){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID, Value, Action or Column missing!'
            ], 406);
        }

        $upres = $this->product->api_model_updateProductShipping($parameters['product_id'], $parameters);

        if($upres){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Updated Successfully.'
            ], 201);
        }else{
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }
    }


    public function AddProductImages(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        $images = $request->getUploadedFiles();
        $product_id = $parameters['product_id'];
        if(!$product_id){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID is required!'
            ], 406);
        }

        // Start Uploading
        $uploaded_images = [];
        if (!empty($images))
        {
            foreach ($images['product_image'] as $image)
            {
                $tmp = $image->file;
                $filename = $this->moveUploadedFile($image);
                $uploaded_file = array();
                if (is_uploaded_file($tmp))
                {
                    $uploaded_file = \Filesystem::setPath('image/data/products/' . bin2hex(random_bytes(8)) . '_' . $filename)->upload($tmp);
                }
                array_push($uploaded_images, 'data/products/' . $uploaded_file['file_name']);
            }
        }
        else
        {
            $uploaded_images[] = 'image/no_image.jpg';
        }

        $upres = $this->product->api_model_api_model_addproductimages($product_id, $uploaded_images);

        if($upres){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Added Successfully.'
            ], 201);
        }else{
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }
    }

  
    public function UpdateProductImages(Request $request, Response $response){


        $parameters = $request->getParsedBody();
        $images = $request->getUploadedFiles();
        $product_id = $parameters['product_id'];
        if(!$product_id){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Product ID is required!'
            ], 406);
        }

        $data['product_image'] = array();
        if (!empty($images))
        {
            // Start Uploading
            foreach ($images['product_image'] as $image)
            {
                $tmp = $image->file;
                if ($tmp)
                {
                    $filename = $this->moveUploadedFile($image);
                    $uploaded_file = array();
                    if (is_uploaded_file($tmp))
                    {
                        $uploaded_file = \Filesystem::setPath('image/data/products/' . bin2hex(random_bytes(8)) . '_' . $filename)->upload($tmp);
                    }
                    $data['product_image'][]['image'] = 'data/products/' . $uploaded_file['file_name'];
                }
            }
        }

        // Images which related to product
        if (!empty($parameters['existed_images']))
        {
            $existed_images = $parameters['existed_images'];
            foreach ($existed_images as $existed_image)
            {
                $data['product_image'][]['image'] = $existed_image;
            }
        }

        // if empty product images
        else
        {
            $data['product_image'][]['image'] = 'image/no_image.jpg';
        }

        $upres = $this->product->api_model_api_model_updateproductimages($parameters['product_id'], $data);

        if($upres){
            return $response->withJson([
                'status' => $this->status[1],
                'data' => 'Updated Successfully.'
            ], 201);
        }else{
            return $response->withJson([
                'status' => 'failed',
                'status_code' => $this->status[2],
                'error_description' => ''
            ]);
        }


    }


    /**
     * Moves the uploaded file to the upload directory and assigns it a unique name
     * to avoid overwriting an existing uploaded file.
     *
     * @param string $directory directory to which the file is moved
     * @param UploadedFile $uploadedFile file uploaded file to move
     * @return string filename of moved file
     */
    function moveUploadedFile(UploadedFile $uploadedFile)
    {
        return $filename = basename(html_entity_decode($uploadedFile->getClientFilename(), ENT_QUOTES, 'UTF-8'));
    }

}
