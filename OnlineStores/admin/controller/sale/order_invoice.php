<?php

require_once(DIR_SYSTEM . 'library/phpqrcode/qrlib.php');

class ControllerSaleOrderInvoice extends Controller
{
	/**
	 *
	 *
	 */
	public function generateQR()
	{
        $this->load->language('sale/order');

        $order_id = $this->request->get['order_id'];
        $language_id        = $this->request->get['language_id'];
        $language_code      = $this->request->get['language_code'];
        $language_directory = $this->request->get['language_directory'];

        $config_qrcode_settings = $this->config->get('config_qrcode_settings');

        //Generate QR Code Image
        if( $config_qrcode_settings == 'pdf' ){
            QRcode::png($this->url->frontUrl('order/invoice/PDF', "language_id=$language_id&order_id=$order_id" , 'SSL', false));
        }
        elseif( $config_qrcode_settings == 'text_pdf' ){
            QRcode::png($this->getInvoiceDataAsText($order_id, $language_code, $language_directory) .'

'.$this->language->get('text_generate_pdf') .'
            	'.
            	$this->url->frontUrl('order/invoice/PDF', "language_id=$language_id&order_id=$order_id", 'SSL', false));
        }
        else{
            //if $config_qrcode_settings == 'text' or other
            QRcode::png($this->getInvoiceDataAsText($order_id, $language_code, $language_directory));
        }
    }

    public function getInvoiceDataAsText($order_id, $language_code, $language_directory)
    {
        //get order info
        $this->load->model('sale/order');
        $order_info = $this->model_sale_order->getOrder($order_id);

        //Get total data
        $total_data = $this->model_sale_order->getOrderTotals($order_id);

        //Get selected taxe names to be displayed
        $config_qrcode_selected_taxes = $this->config->get('config_qrcode_selected_taxes');

        $total_data_str = '';
        $taxes = '';
        foreach($total_data as $record){
            if($record['code'] == 'tax' && in_array($this->getTaxRateIdByName($record['title']), $config_qrcode_selected_taxes)){
                $taxes .= strip_tags($record['title']) . ' : ' . $record['text'] . '
';
            }

            $total_data_str .= $record['title'] . ': ' . $record['text'] . '
            ';
        }
        //Load language files 
        $this->language->setDirectory($language_directory)->load('setting/setting');

        $config_qrcode_text_fields = $this->config->get('config_qrcode_text_fields');
        $text = '';

        if( in_array('order_id', $config_qrcode_text_fields) )
            $text .= $this->language->get('qrcode_text_fields_order_id') . ": #$order_id" . "
"; 

        if( in_array('invoice_no', $config_qrcode_text_fields) )
            $text .= $this->language->get('qrcode_text_fields_invoice_number') . ": " . ($order_info['invoice_no'] ? $order_info['invoice_prefix'] . $order_info['invoice_no'] : '');

        if( in_array('store_name', $config_qrcode_text_fields) )
            $text .= "
" . $this->language->get('qrcode_text_fields_store_name') . ": " . $this->config->get('config_name')[$language_code];

        if( in_array('invoice_date', $config_qrcode_text_fields) )
            $text .= "
" . $this->language->get('qrcode_text_fields_invoice_date') . ": " . $order_info['date_added'] . $order_info['time_added'];

        if( in_array('payment_method', $config_qrcode_text_fields) )
            $text .= "
" . $this->language->get('qrcode_text_fields_payment_method') . ": " . $order_info['payment_method'];

        if( in_array('tax_no', $config_qrcode_text_fields) )
            $text .= "
" . $this->language->get('qrcode_text_fields_tax_no') . ": " . $this->config->get('config_tax_number');

        if( in_array('customer_name', $config_qrcode_text_fields) ||
            in_array('customer_email', $config_qrcode_text_fields) ||
            in_array('customer_phone', $config_qrcode_text_fields) )
            $text .="
" . $this->language->get('qrcode_text_fields_invoice_to') . ":";

        if( in_array('taxes', $config_qrcode_text_fields) )
            $text .="
" . strip_tags($taxes);
   
        if( in_array('customer_name', $config_qrcode_text_fields) )
            $text .="
        " . $this->language->get('qrcode_text_fields_customer_name') . ": ". $order_info['firstname'] . ' ' . $order_info['lastname'];

        if( in_array('customer_email', $config_qrcode_text_fields) )
            $text .="
        " . $this->language->get('qrcode_text_fields_customer_email') . ": ". $order_info['email'];

        if( in_array('customer_phone', $config_qrcode_text_fields) )
            $text .="
        " . $this->language->get('qrcode_text_fields_customer_phone') . ": ". $order_info['telephone'];

        if( in_array('totals', $config_qrcode_text_fields) )
            $text .="
        " . strip_tags($total_data_str);
// " . $this->language->get('qrcode_text_fields_totals') . ":     

        return $text;
    }

    private function getTaxRateIdByName($name = ''){
        $this->load->model('localisation/tax_rate');
        return $this->model_localisation_tax_rate->getTaxRateIdByName($name);
    }
}
