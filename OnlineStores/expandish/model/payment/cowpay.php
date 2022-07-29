<?php 


class ModelPaymentCowpay extends Model
{

    /**
     * @param string $address
     *
     * @return array
     */
    public function getMethod($address)
    {

        if (!$this->currency->has('EGP')) {
            return;
        }

        if ($this->currency->getCurrencies()['EGP']['status'] == 0) {
            return;
        }

        $this->language->load_json('payment/cowpay');

        if ($this->config->get('cowpay_status') )
        {
            $status = TRUE;
        }
        else
        {
            $status = FALSE;
        }

        $method_data = array();

        if ($status)
        {

            $method_data = array( 
                'code'       => 'cowpay',
                'title'      => $this->language->get('text_title'),
                'sort_order' => $this->config->get('cowpay_sort_order'),
            );
        }

        return $method_data;
    }
}
