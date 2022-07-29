<?php
class ModelTotalShipping extends Model {
    public function getTotal(&$total_data, &$total, &$taxes) {
        if ($this->cart->hasShipping() && isset($this->session->data['shipping_method'])) {

            $sorting_order = $this->config->get('shipping_sort_order');
            $session_language = $this->session->data['language'];
            // grab shipping title with the correct language based on the session language and using the sorting order variable to dynamically grab it.
            try {
                $shipping_title = $this->config->get('category_product_based_charge_'.$sorting_order.'_title_'.$session_language);
            } catch(Exception $e) { }

            $total_data[] = array( 
                'code'       => 'shipping',
                'title'      => $this->session->data['shipping_method']['title'],
                'text'       => $this->currency->format($this->session->data['shipping_method']['cost']),
                'value'      => $this->session->data['shipping_method']['cost'],
                'sort_order' => $this->config->get('shipping_sort_order')
            );

            if ($this->session->data['shipping_method']['tax_class_id']) {
                $tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);
                
                foreach ($tax_rates as $tax_rate) {
                    if (!isset($taxes[$tax_rate['tax_rate_id']])) {
                        $taxes[$tax_rate['tax_rate_id']] = $tax_rate['amount'];
                    } else {
                        $taxes[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
                    }
                }
            }
            
            $total += $this->session->data['shipping_method']['cost'];
        }           
    }
}
?>