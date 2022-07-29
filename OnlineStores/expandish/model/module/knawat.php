<?php

class ModelModuleKnawat extends Model
{
    private $base_url = "https://mp.knawat.io/api/";

    public function getSettings(): array
    {
        return $this->config->get('knawat') ?? [];
    }

    public function isInstalled(): bool
    {

        return Extension::isInstalled('knawat');
    }


    /***************** get Token ***************/
    public function getToken($data = []): string
    {
        return $this->getSettings()['token'];
    }

    //send knawat request
    function sendKnawatRequest($type, $url, $body = [], $token = '', $json_body = false)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_POSTFIELDS => ($json_body ? json_encode($body) : http_build_query($body)),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: ' . ($json_body ? 'application/json' : 'application/x-www-form-urlencoded'),
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($http_status != 200)
            return null;
        return json_decode($response);
    }



    /////////////////////////////////////////////////////////////
    ////////////////////// Order Functions ///////////////////
    /////////////////////////////////////////////////////////////

    /***************** check if order status is equal to chosen status and order not inserted to knawat before ***************/
    public function checkOrderStatusForInsertToKnawat($order_id): bool
    {
        if (!$this->getToken())
            return false;

        //get order data
        $order = $this->db->query("SELECT  payment_code, order_status_id  FROM `" . DB_PREFIX . "order`  WHERE order_id = '" . $order_id . "' LIMIT 1");;
        if (!isset($order->row['order_status_id']))
            return false;

        //check if order not paid or status now allow to add to knawat
        if ($this->getSettings()['push_order_status_id'] != $order->row['order_status_id'] || $order->row['payment_code'] == 'cod')
            return false;

        //check if order inserted before
        $exist_orders = $this->db->query("SELECT  knawat_order_id FROM `" . DB_PREFIX . "knawat_orders`  WHERE order_id = '" . $order_id . "' LIMIT 1");
        if ($exist_orders->num_rows)
            return false;

        return true;
    }

    /***************** check if order has knawat products ***************/
    public function checkIfKnawatOrder($order_id): bool
    {
        $order_products = $this->db->query("SELECT COUNT(*) AS order_products_count  FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . $order_id . "'");
        $knawat_products = $this->db->query("SELECT COUNT(*) AS knawat_products_count  FROM `" . DB_PREFIX . "knawat_products` WHERE product_id IN (SELECT product_id  FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . $order_id . "')");
        return $order_products->row['order_products_count'] == $knawat_products->row['knawat_products_count'] && $order_products->row['order_products_count'] != 0;
    }


    /***************** Insert order into knawat ***************/
    public function insertOrderIntoKnawat($order_id)
    {

        //get order and shipping country code
        $order = $this->db->query("SELECT  *  FROM `" . DB_PREFIX . "order`  LEFT JOIN country ON order.shipping_country_id = country.country_id WHERE order_id = '" . $order_id . "' LIMIT 1");;

        if ($order->row) {
            $body = $this->formatKnawatOrder($order->row);
            $url = $this->base_url . "orders";
            $order = $this->sendKnawatRequest('POST', $url, $body, $this->getToken(), true);
            if ($order->status && $order->status == 'success')
                $this->db->query("INSERT INTO " . DB_PREFIX . "knawat_orders SET order_id = '" . (int)$order_id . "', knawat_order_id = '" . $this->db->escape($order->data->id) . "'");

        }
        return null;
    }

    /***************** format knawat order ***************/
    public function formatKnawatOrder($order): array
    {
        $products = $this->knawatOrderProducts($order['order_id']);

        $formatted_order = [
            'total' => (float)$order['total'],
            'externalId' => $order['order_id'],
            'notes' => $order['comment'],
            'taxTotal' => (float)$order['total'],
            'status' => 'pending',
            'financialStatus' => "paid",
            'fulfillmentStatus' => 'pending',
//            'invoice_url' => "pending",
        ];
        $formatted_order['items'] = $this->formatKnawatOrderProducts($products);
        $formatted_order['shipping'] = array(
            'first_name' => $order['shipping_firstname'] ?:  ($order['firstname'] ?: '___'),
            'last_name' =>$order['shipping_lastname'] ?: ($order['lastname'] ?: '___'),
            'company' => $order['shipping_company'] ?: $order['shipping_method'],
            'address_1' => $order['shipping_address_1'] ?: ($order['shipping_city'] ?: '___'),
            'address_2' => $order['shipping_address_2'] ?: ($order['shipping_city'] ?: '___'),
            'city' => $order['shipping_city'] ?: '___',
            'state' => $order['shipping_zone'] ?: '___',
            'postcode' => $order['shipping_postcode'] ?: '',
            'country' => $order['iso_code_2'] ?: substr($order['shipping_country'], 0, 2),
            'email' => $order['email'] ?: '',
            'phone' => $order['telephone'] ?: ''
        );

        return $formatted_order;
    }


    /***************** format knawat order  products***************/
    public function formatKnawatOrderProducts($products): array
    {
        $items = [];
        foreach ($products as $product) {
            $items[] = [
                'sku' => $product['knawat_sku'] ?? '',
                'name' => $product['name'],
                'description' => $product['product_description'],
                'quantity' => (int)$product['quantity'],
                'total' => (float)$product['total'],
                'tax' => $product['tax'],
            ];
        }
        return $items;
    }

    /***************** get knawat order products ***************/
    private function knawatOrderProducts($order_id)
    {

        //get products with knawat sku
        $query = $fields = [];
        $fields[] = 'order_product.*';
        $fields[] = 'product_description.name as product_name';
        $fields[] = 'product_description.description as product_description';
        $fields[] = 'knawat_products.sku as knawat_sku';

        $fields = implode(',', $fields);

        $query[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'order_product';
        $query[] = 'LEFT JOIN knawat_products ON order_product.product_id = knawat_products.product_id';
        $query[] = 'LEFT JOIN product_description ON order_product.product_id = product_description.product_id';
        $query[] = 'AND product_description.language_id="' . (int)$this->config->get('config_language_id') . '"';
        $query[] = 'WHERE order_id = "' . (int)$order_id . '"';

        $data = $this->db->query(implode(' ', $query));

        return $data->rows ?? [];
    }


    /***************** count knawat product in order***************/
    public function countKnawatProducts($products_id = [])
    {
        $result = $this->db->query("SELECT COUNT(*) AS total  FROM `" . DB_PREFIX . "knawat_products` WHERE product_id IN (".implode(",", $products_id).")");
        return $result->row['total'];
    }


}
