<?php

class ModelPaymentFaturah extends Model
{
    public static $environmentUrl = "https://gateway.faturah.com/TransactionRequestHandler.aspx";

    private $soapURL = 'https://Services.faturah.com/TokenGeneratorWS.asmx?wsdl';

    public function getMethod($address, $total)
    {
        $this->load->language('payment/faturah');

        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX .
            "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('faturah_geo_zone_id') .
            "' AND country_id = '" . (int)$address['country_id'] .
            "' AND (zone_id = '" . (int)$address['zone_id'] .
            "' OR zone_id = '0')"
        );

        //if ($this->config->get('faturah_total') > $total) {
        //	$status = false;
        //} else
        if (! $this->config->get('faturah_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $currencies = array(
            'SAR'
        );

        //Remove currency restrict
        //if (!in_array(strtoupper($this->currency->getCode()), $currencies)) {
        //	$status = false;
        //}

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'faturah',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('faturah_sort_order')
            );
        }

        return $method_data;
    }

    public function generatePaymentURL($merchantCode, $tokenGUID, $orderDate, $orderTotal,  $products, $customerInfo, $deliveryAmount = 0, $rate, $currency)
    {
//        $environmentUrl = self::$environmentUrl;
        $environmentUrl = 'https://gateway.faturah.com/TransactionRequestHandler.aspx';

        $products = $this->getImplodedProducts($products, $rate, $currency);

        return $environmentUrl
            . '?mc=' . html_entity_decode($merchantCode, ENT_QUOTES, 'UTF-8')
            . '&mt=' . html_entity_decode($tokenGUID, ENT_QUOTES, 'UTF-8')
            . '&dt=' . $orderDate
            . '&a='  . $this->currency->format($orderTotal * $rate, $currency, false, false)
            . '&ProductID=' . html_entity_decode($products['productIds'], ENT_QUOTES, 'UTF-8')
            . '&ProductName=' . html_entity_decode($products['productNames'], ENT_QUOTES, 'UTF-8')
            . '&ProductDescription=' .  html_entity_decode($products['productDescriptions'], ENT_QUOTES, 'UTF-8')
            . '&ProductQuantity=' . $products['productQuantities']
            . '&ProductPrice=' . $products['productPrices']
            . '&DeliveryCharge=' . round($deliveryAmount * $rate, 2)
            . '&CustomerName=' . html_entity_decode(isset($customerInfo['fullName']) ? $customerInfo['fullName'] : '' , ENT_QUOTES, 'UTF-8')
            . '&EMail=' . $customerInfo['email']
            . '&PhoneNumber=' .  html_entity_decode(isset($customerInfo['PhoneNumber']) ? $customerInfo['PhoneNumber'] : '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Implode products info for faturah payment URL
     *
     * @param array $products Array of products returned by $this->cart->getProducts()
     * @return array
     */
    public function getImplodedProducts($products, $rate = 1, $currency)
    {
        $imploded = array();

        foreach ($products as $product) {
            $imploded['productIds'][] = (int) $product['product_id'];
            $imploded['productNames'][] = $product['name'];
            $imploded['productDescriptions'][] = isset($product['description']) ? $product['description'] : $product['name'];
            $imploded['productPrices'][] = $this->currency->format((float)$product['price'] * $rate, $currency, false, false);
            $imploded['productQuantities'][] = $product['quantity'];
        }
        $imploded['productIds'] = implode('|', $imploded['productIds']);
        $imploded['productNames'] = implode('|', $imploded['productNames']);
        $imploded['productDescriptions'] = implode('|', $imploded['productDescriptions']);
        $imploded['productPrices'] = implode('|', $imploded['productPrices']);
        $imploded['productQuantities'] = implode('|', $imploded['productQuantities']);

        return $imploded;
    }


    /**
     * Convert currency for Faturah
     *
     * @param string $from_Currency currency on ISO format
     * @param string $to_Currency
     * @return float
     */
    public static function convertCurrency($from_Currency, $to_Currency = 'SAR')
    {
        $amount = 1;
        $amount = urlencode($amount);
        $from_Currency = urlencode($from_Currency);
        $to_Currency = urlencode($to_Currency);
        $url = 'http://www.google.com/finance/converter?a=' . $amount . '&from=' . $from_Currency .'&to=' .$to_Currency;
        $ch = curl_init();
        $timeout = 0;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $rawdata = curl_exec($ch);
        curl_close($ch);
        $data = explode('bld>', $rawdata);
        $data = explode($to_Currency, $data[1]);
        return round($data[0], 2);
    }


    /**
     * Generate New Merchant Token Service
     *
     * @param string $merchantCode
     * @return mixed
     */
    public function generateFaturahMerchantToken($merchantCode)
    {
        $client = new SoapClient($this->soapURL);
        $parameters = array(
            'GenerateNewMerchantToken' => array(
                'merchantCode' => $merchantCode
            )
        );
        $result = $client->__soapCall('GenerateNewMerchantToken', $parameters);

        return $result->GenerateNewMerchantTokenResult;
    }

    public function isSecureMerchant($merchantCode)
    {
        $client = new SoapClient($this->soapURL);
        $parameters = array(
            'IsSecuredMerchant' => array(
                'merchantCode' => $merchantCode
            )
        );
        $result = $client->__soapCall('IsSecuredMerchant', $parameters);

        return $result->IsSecuredMerchantResult;
    }

    public function generateSecureHashMessage($merchantKey, $merchantCode, $merchantToken, $orderTotal, $rate, $currency)
    {
        return $this->generateSecureHash($merchantKey
            . $merchantCode
            . $merchantToken
            . $this->currency->format($orderTotal * $rate, $currency, false, false)
        );
    }

    public function generateResponseHash($merchantKey, $response, $status, $code, $token, $lang, $ignore)
    {
        $message = $merchantKey
            . '&Response=' . $response
            . '&status=' . $status
            . '&code=' . $code
            . '&token=' . $token
            . '&lang=' . $lang
            . '&ignore=' . $ignore;
        return $this->generateSecureHash($message);
    }

    public function generateSecureHash($message)
    {
        $client = new SoapClient($this->soapURL);

        $result = $client->__soapCall('generateSecureHash', array(
            'generateSecureHash' => array(
                'message' => $message
            )
        ));

        return $result->GenerateSecureHashResult;
    }
}

