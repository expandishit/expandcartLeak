<?php 


class ModelPaymentRajhiBank extends Model
{

    /**
     * @var array
     */
    private $allowed_currencies_Codes = [
        'SAR'=> 286
    ];

    /**
     * @param string $address
     */
    public function getMethod($address)
    {

        if(!$this->AllowedCurrenciesAvailable()){
            return;
        }


        $this->language->load_json('payment/rajhi_bank');

        if ($this->config->get('rajhi_bank_status') )
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
                'code'       => 'rajhi_bank',
                'title'      => $this->language->get('text_title'),
                'sort_order' => $this->config->get('rajhi_bank_sort_order'),
            );
        }

        return $method_data;
    }

    /**
     * @return mixed
     */
    private function AllowedCurrenciesAvailable()
    {
        foreach ($this->allowed_currencies_Codes as $currency => $num_code){
            if ($this->currency->has($currency)) {
                return true;
            }
        }
        return false;
    }
}
