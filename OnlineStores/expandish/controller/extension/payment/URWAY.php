<?php
class ControllerExtensionPaymentURWAY extends Controller {

    public function response()
    {
        $this->load->language('payment/urway');
        $this->load->model('checkout/order');
        $this->load->model('localisation/country');
        $this->load->model('payment/urway');
        // response code start=======
        $merchantKey=$this->config->get('payment_urway_client_key');

        if($_GET !== NULL)
        {

            $requestHash ="".$_GET['TranId']."|".$merchantKey."|".$_GET['ResponseCode']."|".$_GET['amount']."";
            $hash=hash('sha256', $requestHash);
            if($hash == $_GET['responseHash'] && $_GET['Result']== "Successful")
            {
                $this->model_checkout_order->confirm((int)$_GET['TrackId'], $this->config->get('config_order_status_id'), 'Processing', true);
//                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '5', date_modified = NOW() WHERE order_id = '" . (int)$_GET['TrackId'] . "'");
                $this->response->redirect($this->url->link('checkout/success', '', true));

            }
            else
            {
                $this->model_checkout_order->confirm((int)$_GET['TrackId'],  $this->config->get('config_order_status_id'), 'Failed', true);
//                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '1', date_modified = NOW() WHERE order_id = '" . (int)$_GET['TrackId'] . "'");
                $this->response->redirect($this->url->link('checkout/error', '', true));
            }

        }
        // response code end==========
    }



}
