<?php

class ControllerModuleProductsOrders extends Controller
{
    public function index()
    {
        $this->language->load('module/products_orders');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/products_orders', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/products_orders/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function list()
    {
        $this->load->language('module/products_orders');

        $this->initializer([
            'productsOrders' => 'module/products_orders/order'
        ]);

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'] ?: 0;
        $length = $request['length'] ?: 10;

        $columns = [];
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->productsOrders->getOrders([
            'start' => $start,
            'limit' => $length,
            'search' => $search,
            'language_id' => $this->config->get('config_language_id')
        ]);
        
        
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = [
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records,
        ];

        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function listProducts()
    {
        $this->load->language('module/products_orders');

        $this->initializer([
            'productsOrders' => 'module/products_orders/order',
            'products' => 'module/products_orders/product',
        ]);

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'] ?: 0;
        $length = $request['length'] ?: 10;

        $columns = [];
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        if (isset($this->request->get['order_id']) == false) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => ['Order is is required']
            ]));

            return;
        }

        $id = $this->request->get['order_id'];

        if (preg_match('#^[0-9]+$#', $id) == false) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => ['Invalid order id']
            ]));

            return;
        }

        $order = $this->productsOrders->getOrderById($id);

        $allProducts = json_decode($order['products'], true);

        if (isset($request['type']) && $request['type'] == 'not-exists') {

            $total = count($allProducts['out']);

            $slice = array_slice($allProducts['out'], $start, $length);
            
            //here we check if products not found bracode key is numeric 
            //in case is numeric we replace it with word barcode nested of 0 as key

            if(count(array_column($slice,'0')) > 0)
            {
                $counter = 0;
                $data = [];
                foreach ($slice as $sliceData)
                {
                    $data[$counter]['barcode'] = $sliceData[0];  
                    $data[$counter]['quantity'] = $sliceData[1];  
                    $data[$counter]['product_id'] = $sliceData['product_id'];
                    $counter++;
                }
                
                $slice = $data; //overwirte $slice data with new data
            }
            

            $this->response->setOutput(json_encode([
                'draw' => intval($request['draw']),
                'data' => $slice,
                'recordsTotal' => $length,
                'recordsFiltered' => $total
            ]));
            return;

        }

        $inIds = array_column($allProducts['in'], 'product_id');

        $products = $this->products->getProductsByIds([
            'products' => $inIds,
            'language_id' => $this->config->get('config_language_id'),
            'start' => $start,
            'limit' => $length,
        ]);

        foreach ($products['data'] as &$product) {
            if (isset($allProducts['in'][$product['product_id']])) {
                $addedProduct = $allProducts['in'][$product['product_id']];
                $product['old_quantity'] = $addedProduct['old_quantity'];
                $product['added_quantity'] = $addedProduct['added_quantity'];
                $product['registered_quantity'] = $addedProduct['quantity'];
            }
        }

        $products['draw'] = intval($request['draw']);

        $this->response->setOutput(json_encode($products));
        return;
    }

    public function install()
    {
        $this->initializer(['module/products_orders/settings']);
        $this->settings->install();
    }

    public function uninstall()
    {
        $this->initializer(['module/products_orders/settings']);
        $this->settings->uninstall();
    }

    public function upload()
    {
        $data = $this->request->post;

        $files = $this->request->files;

        $this->initializer([
            'productsOrders' => 'module/products_orders/order',
            'settings' => 'module/products_orders/settings',
            'products' => 'module/products_orders/product',
        ]);

        if (!$this->settings->validate($data, $files)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->settings->getErrors()
            ]));

            return;
        }

        $uploadedFile = $this->settings->upload(
            $files['file']['tmp_name'],
            $files['file']['name']
        );

        if (!$uploadedFile) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->settings->getErrors()
            ]));

            return;
        }

        if (!($initData = $this->settings->getInitData($uploadedFile))) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_FILE',
                'errors' => $this->settings->getErrors()
            ]));

            return;
        }

        $barcodes = array_column($initData, 'barcode');
        $products = $this->products->getProductsByBarcodes($barcodes);

        $in = $out = [];
        foreach ($initData as $k => $p) {
            $barcode = $p['barcode'];
            $_p = null;
            foreach ($products as $pk => $product) {
                if (in_array($p['barcode'], $product)) {
                    $_p = $product;
                    $_p['added_quantity'] = $p['quantity'];
                    $_p['old_quantity'] = $_p['quantity'];
                    $_p['quantity'] = $_p['quantity'] + $p['quantity'];
                }
            }

            if ($_p) {
                $in[$_p['product_id']] = $_p;
            } else {
                $out[$k] = array_merge(['product_id' => null], $p);
            }
        }

        $finalProducts = ['in' => $in, 'out' => $out];

        $data = ['filename' => $data['filename'], 'products' => $finalProducts,'user_id' => $this->session->data['user_id']];

        if ($id = $this->productsOrders->insertProductsOrder($data)) {

            $this->products->updateQuantities($in);

            $this->response->setOutput(json_encode([
                'status' => 'OK',
                'id' => $id,
                'file_path' => $uploadedFile,
                'headers' => $initData
            ]));

            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }

    public function update()
    {
        if (
            isset($this->request->get['id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->get['id']) == false
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $data = $this->request->post['mp'];
        $id = $this->request->get['id'];

        $this->initializer([
            'msGateways' => 'module/products_orders/gateways',
            'ms' => 'module/products_orders/settings',
        ]);

        if (!$this->ms->validate($data)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->ms->getErrors()
            ]));

            return;
        }

        if (!$msg = $this->msGateways->getCompactShippingGatewayById($id)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROW',
                'errors' => ['Undefined gateway'],
            ]));

            return;
        }

        $msgs = array_column($msg, null, 'language_id');
        foreach ($data as $key => $value) {
            if (isset($msgs[$key])) {
                $_msg = $msgs[$key];
                $msgs[$key] = array_merge($msgs[$key], $value);
            }
        }

        if ($this->msGateways->updateShippingGatewayDescription($id, $data)) {
            $this->response->setOutput(json_encode([
                'status' => 'OK',
                'id' => $id,
                'data' => $msgs,
            ]));

            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }

    public function destroy()
    {
        if (
            isset($this->request->post['id']) == false ||
            preg_match('#^[0-9]+$#', $this->request->post['id']) == false
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['Invalid request'],
            ]));

            return;
        }

        $id = $this->request->post['id'];

        $this->initializer([
            'msGateways' => 'module/products_orders/gateways',
            'msgOrder' => 'module/products_orders/order',
        ]);

        if (!$msg = $this->msGateways->getCompactShippingGatewayById($id)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROW',
                'errors' => ['Undefined gateway'],
            ]));

            return;
        }

        $orders = $this->msgOrder->getOrdersByManualShippingGatewayId($id);

        if ($orders && (!isset($this->request->post['_h']) || $this->request->post['_h'] != 1)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CONSTRAINTS',
                'errors' => [''],
            ]));

            return;
        }

        if ($this->msGateways->deleteShippingGateway($id)) {
            if (isset($this->request->post['_h']) && (int)$this->request->post['_h'] == 1) {
                $this->msgOrder->nullOrderManualShippingGateways($id);
            }

            $this->response->setOutput(json_encode([
                'status' => 'OK',
            ]));

            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'error' => 'UNEXPECTED_ERROR',
            'errors' => ['Unexpected error'],
        ]));
    }
}