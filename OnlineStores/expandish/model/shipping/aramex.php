<?php
class ModelShippingAramex extends Model
{
    function getQuote($address)
    {
        if (!$this->currency->has('USD')) {
            return;
        }
        
        if ($this->currency->getCurrencies()['USD']['status'] == 0) {
            return;
        }

        $this->language->load_json('shipping/fedex');
        $this->load->model('aramex/aramex');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('aramex_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if (!$this->config->get('aramex_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $error = '';

        $quote_data = array();

        if ($status) {

            $chk_live_rate = ($this->config->get('aramex_live_rate_calculation')) ? $this->config->get('aramex_live_rate_calculation') : '';

            if ($chk_live_rate == 1) {
                $weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->config->get('aramex_weight_class_id'));
                $weight_code = strtoupper($this->weight->getUnit($this->config->get('aramex_weight_class_id')));

                $this->load->model('localisation/country');

                $country_info = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
                $this->load->model('localisation/zone');

                //Get EN language ID
                $lang_id = null;
                if($this->config->get('config_language') == 'ar'){
                    $this->load->model('localisation/language');
                    $languages = $this->model_localisation_language->getLanguages();
                    if($languages['en']['language_id'])
                        $lang_id = $languages['en']['language_id'];
                }
                ///////////////////
                if($this->config->get('aramex_cities_table') && $this->config->get('aramex_cities_table') == 1 && !empty($address['area_id'])){
                    $this->load->model('localisation/area');
                    $zone_info = $this->model_localisation_area->getArea($address['area_id'], $lang_id);
                }else{
                    $zone_info = $this->model_localisation_zone->getZone($address['zone_id'], $lang_id);
                }
                $clientInfo = $this->model_aramex_aramex->getClientInfo();
                ##################### config shipper details ################
                $origin_country = ($this->config->get('aramex_shipper_country_code')) ? $this->config->get('aramex_shipper_country_code') : '';
                $origin_city = ($this->config->get('aramex_shipper_city')) ? $this->config->get('aramex_shipper_city') : '';
                $origin_zipcode = ($this->config->get('aramex_shipper_postal_code')) ? $this->config->get('aramex_shipper_postal_code') : '';
                $origin_state = ($this->config->get('aramex_shipper_state')) ? $this->config->get('aramex_shipper_state') : '';
                // $destination_city = ($address['city'])?$address['city']:'';
                if($zone_info)
                    $destination_city = $zone_info['name'];
                else
                    $destination_city = ($address['zone']) ? $address['zone'] : '';
                ##################### config default service type ################

                if (strtolower($address['iso_code_2']) == strtolower($origin_country)) {
                    $ProductGroup = 'DOM';
                    $ProductType = ($this->config->get('aramex_default_allowed_domestic_methods')) ? [$this->config->get('aramex_default_allowed_domestic_methods')] : [];
                    //$aramex_default_allowed_domestic_additional_services = ($this->config->get('aramex_default_allowed_domestic_additional_services'))?$this->config->get('aramex_default_allowed_domestic_additional_services'):'';
                } else {
                    $ProductGroup = 'EXP';
                    $ProductType = ($this->config->get('aramex_allowed_international_methods')) ? $this->config->get('aramex_allowed_international_methods') : [];
                    //$aramex_default_allowed_international_additional_services = ($this->config->get('aramex_default_allowed_international_additional_services'))?$this->config->get('aramex_default_allowed_international_additional_services'):'';
                }


                $cart_count = ($this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0));

                if ($ProductGroup == 'EXP') {
                    $internationalMethods = $this->internationalMethods();
                }

                try {

                    foreach($ProductType as $type) {

                        $params = array(
                            'ClientInfo' => $clientInfo,
    
                            'Transaction' => array(
                                'Reference1' => '001',
                            ),
    
                            'OriginAddress' => array(
                                'StateOrProvinceCode' => $origin_state,
                                'City' => $origin_city,
                                'PostCode' => $origin_zipcode,
                                'CountryCode' => $origin_country
                            ),
    
                            'DestinationAddress' => array(
                                'StateOrProvinceCode' => ($address['zone']) ? $address['zone'] : '',
                                'City' => $destination_city,
                                'PostCode' => ($address['postcode']) ? $address['postcode'] : '',
                                'CountryCode' => ($address['iso_code_2']) ? $address['iso_code_2'] : ''
    
                            ),
                            'ShipmentDetails' => array(
                                'PaymentType' => 'P',
                                'ProductGroup' => $ProductGroup,
                                'ProductType' => $type,
                                'ActualWeight' => array('Value' => $weight, 'Unit' => $weight_code),
                                'ChargeableWeight' => array('Value' => $weight, 'Unit' => $weight_code),
                                'NumberOfPieces' => $cart_count,
                            )
                        );


                        $baseUrl = $this->model_aramex_aramex->getWsdlPath();
                        $soapClient = new SoapClient($baseUrl . '/aramex-rates-calculator-wsdl.wsdl', array('trace' => 1));
    
                        $results = $soapClient->CalculateRate($params);
                        //print_r($results);
                        $error = "";
                        if ($results->HasErrors) {
                            if (count($results->Notifications->Notification) > 1) {
    
                                foreach ($results->Notifications->Notification as $notify_error) {
                                    $error .= 'Aramex: ' . $notify_error->Code . ' - ' . $notify_error->Message . "<br>";
                                }
                            } else {
                                $error .= 'Aramex: ' . $results->Notifications->Notification->Code . ' - ' . $results->Notifications->Notification->Message;
                            }
    
                        } else {
                            $cost = $results->TotalAmount->Value;
                            $currency = $results->TotalAmount->CurrencyCode;
                            //echo $this->currency->convert($cost, $currency, $this->config->get('config_currency'));
                            $quote_data['aramex-' . $type] = array(
                                'code' => 'aramex.aramex-' . $type,
                                'title' => 'Aramex shipping charges' . ($ProductGroup == 'EXP' ? ' - ' . $internationalMethods[$type] : '') . ' - ',
                                'cost' => $this->currency->convert($cost, $currency, $this->config->get('config_currency')),
                                'tax_class_id' => $this->config->get('aramex_tax_class_id'),
                                'text' => $this->currency->format($this->tax->calculate($this->currency->convert($cost, $currency, $this->currency->getCode()), $this->config->get('aramex_tax_class_id'), $this->config->get('config_tax')), $this->currency->getCode(), 1.0000000)
                            );
                        }

                    }

                } catch (Exception $e) {

                    $error .= $e->getMessage();
                }
            } else {

                $cost = ($this->config->get('aramex_default_rate')) ? $this->config->get('aramex_default_rate') : '';
                $currency = $this->config->get('config_currency');
                $quote_data['aramex'] = array(
                    'code' => 'aramex.aramex',
                    'title' => 'Aramex shipping charges',
                    'cost' => $this->tax->calculate($this->currency->convert($cost, $currency, $this->currency->getCode()), $this->config->get('aramex_tax_class_id'), $this->config->get('config_tax')),
                    'tax_class_id' => $this->config->get('aramex_tax_class_id'),
                    'text' => $this->currency->format($this->tax->calculate($this->currency->convert($cost, $currency, $this->currency->getCode()), $this->config->get('aramex_tax_class_id'), $this->config->get('config_tax')), $this->currency->getCode(), 1.0000000)
                );

            }
        } // end of status if

        $method_data = array();

        if ($quote_data || $error) {
            $title = 'Aramex';

            if ($this->config->get('fedex_display_weight')) {
                $title .= ' (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $this->config->get('fedex_weight_class_id')) . ')';
            }

            $method_data = array(
                'code' => 'fedex',
                'title' => $title,
                'quote' => $quote_data,
                'sort_order' => $this->config->get('fedex_sort_order'),
                'error' => $error
            );
        }

        return $method_data;
    }

    private function internationalMethods()
	{
        return [
            'DPX' => 'Value Express Parcels',
            'EDX' => 'Economy Document Express',
            'EPX' => 'Economy Parcel Express',
            'GDX' => 'Ground Document Express',
            'GPX' => 'Ground Parcel Express',
            'IBD' => 'International defered',
            'PDX' => 'Priority Document Express',
            'PLX' => 'Priority Letter Express <.5 kg Docs',
            'PPX' => 'Priority Parcel Express'
        ];
    }
    
}
?>