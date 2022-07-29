<?php

use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class ModelShippingDhl extends Model
{
    public function getQuote($address)
    {
        $this->language->load_json('shipping/dhl');

        $this->load->model('localisation/country');
        $this->load->model('localisation/zone');

        $settings = $this->config->get('dhl');

        $queryString = [];
        $queryString[] = "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone";
        $queryString[] = "WHERE geo_zone_id = '" . (int)$settings['geo_zone_id'] . "'";
        $queryString[] = "AND country_id = '" . (int)$address['country_id'] . "'";
        $queryString[] = "AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')";

        $query = $this->db->query(implode(' ', $queryString));

        $status = false;

        if (!$settings['geo_zone_id']) {
            $status = true;
        }

        if ($settings['status'] == 1) {
            $status = true;
        }

        if ($query->num_rows) {
            $status = true;
        }

        if ($this->hasEuro() == false) {
            $status = false;
        }

        $products = $this->cart->getProducts();

        if (count($products) > 10) {
            $status = false;
        }

        $method_data = [];

        if ($status) {

            $sender = [];
            $sender['country'] = $this->model_localisation_country->getCountry(
                $this->config->get('config_country_id')
            );
            $sender['city'] = $this->model_localisation_zone->getZone(
                $this->config->get('config_zone_id')
            );

            $qoutes = $this->calculateQoute($address, $products, $sender);

            if (isset($qoutes['quotationList']['quotation']) && count($qoutes['quotationList']['quotation']) > 0) {

                $currency = 'EUR';

                foreach ($qoutes['quotationList']['quotation'] as $key => $quotation) {

                    $prodName = strtolower(str_replace(' ', '_', $quotation['prodNm']));

                    if ($quotation['estTotPrice'] != 'n/a') {
                        preg_match('#([0-9\.]+)#', $quotation['estTotPrice'], $cost);

                        $cost = $cost[1];

                        $quote_data[$prodName] = [
                            'code' => 'dhl.' . $prodName,
                            'title' => $quotation['prodNm'],
                            'cost' => $this->currency->convert($cost, $currency, $this->config->get('config_currency')),
                            'tax_class_id' => 0,
                            'text' => $this->currency->format($this->currency->convert($cost, $currency, $this->config->get('config_currency')))
                        ];
                    } else {
                        $quote_data[$prodName] = [
                            'code' => 'dhl.' . $prodName,
                            'title' => $quotation['prodNm'],
                            'cost' => 'n\a',
                            'tax_class_id' => 0,
                            'text' => 'n\a'
                        ];
                    }
                }
            } else {
                $quote_data['dhl'] = [
                    'code' => 'dhl.dhl',
                    'title' => $this->language->get('text_description'),
                    'cost' => 'n\a',
                    'tax_class_id' => 0,
                    'text' => 'n\a'
                ];
            }


            $method_data = [
                'code' => 'dhl',
                'title' => $this->language->get('text_title'),
                'quote' => $quote_data,
                'sort_order' => 1,
                'error' => false
            ];
        }

        return $method_data;
    }

    public function calculateQoute($address, $products, $sender)
    {
        $queryParam['dtbl'] = 'N';
        $queryParam['declVal'] = '';
        $queryParam['declValCur'] = 'EUR';
        $queryParam['wgtUom'] = 'kg';
        $queryParam['dimUom'] = 'cm';
        $queryParam['noPce'] = count($products);
        $key = 0;
        foreach ($products as $product) {
            $queryParam['wgt' . $key] = $this->toKilogram($product['weight'], $product['weight_class_id']);
            $queryParam['w' . $key] = '';
            $queryParam['l' . $key] = '';
            $queryParam['h' . $key] = '';

            $key++;
        }
        $queryParam['shpDate'] = date('Y-m-d', strtotime('+ 2 days'));
        $queryParam['orgCtry'] = $sender['country']['iso_code_2'];
        $queryParam['orgCity'] = $sender['city']['code'];
        $queryParam['orgSub'] = '';
        $queryParam['orgZip'] = '';
        $queryParam['dstCtry'] = $address['iso_code_2'];
        $queryParam['dstCity'] = $address['zone_code'];
        $queryParam['dstSub'] = '';
        $queryParam['dstZip'] = $address['postcode'];

        $client = new GuzzleClient([
            'base_uri' => 'http://dct.dhl.com/'
        ]);

        try {
            $result = $client->get('data/quotation', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json;charset=utf-8',
                    'User-Agent' => 'integerating/expandcart',
                ],
                'query' => $queryParam
            ]);
            $response = json_decode($result->getBody(), true);

            return $response;
        } catch (RequestException $e) {
            echo $e->getMessage();
            exit;
        }

    }

    public function hasEuro()
    {
        $queryString = "SELECT 1 FROM currency WHERE code='EUR'";

        $query = $this->db->query($queryString);

        if ($query->num_rows > 0) {
            return true;
        }

        return false;
    }

    private function toKilogram($weight, $weight_class_id)
    {
        if ($weight == 0 || $weight_class_id == 1)
        {
            return $weight;
        }

        return $this->weight->convert($weight, $weight_class_id, 1);
    }
}
