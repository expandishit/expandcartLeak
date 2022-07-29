<?php
class ControllerPaymentBankTransfer extends Controller {
	protected function index() {
		$this->language->load_json('payment/bank_transfer');

        $additionalAccounts = [];

		if (
		    isset($this->session->data['ms_independent_payments']) &&
            $this->session->data['ms_independent_payments']['status'] == true
        ) {
            $this->data['bank'] = nl2br($this->session->data['ms_independent_payments']['bank_transfer']);
        } else {
            $this->data['bank'] = nl2br($this->config->get('bank_transfer_bank_' . $this->config->get('config_language_id')));

            if (is_array($this->config->get('bank_transfer_addiontal_accounts'))) {
                $additionalAccounts = $this->config->get('bank_transfer_addiontal_accounts');
            }
        }
        $this->data['order_id'] = $this->session->data['order_id'];
		$this->data['continue'] = $this->url->link('checkout/success');
        $this->data['isMobile'] = false;

        if ((isset($this->request->get['ismobile']) && $this->request->get['ismobile'] == 1)
            ||
            (strpos($_SERVER['HTTP_REFERER'], 'ismobile=1') !== false))
        {
            $this->data['isMobile'] = true;
        }



		
        // $this->template = $this->checkTemplate('payment/bank_transfer.expand');
        $this->template = 'default/template/payment/bank_transfer.expand';

        $this->data['additional_accounts'] = $additionalAccounts;

		$this->render_ecwig();
	}
	
	public function confirm() {
		$this->language->load_json('payment/bank_transfer');
		
		$this->load->model('checkout/order');
               if (
                    isset($this->session->data['ms_independent_payments']) &&
                    $this->session->data['ms_independent_payments']['status'] == true
                ) {
                      $comment = $this->session->data['ms_independent_payments']['bank_transfer'];
                } else {
		$comment  = $this->language->get('text_instruction') . "\n\n";
		$comment .= $this->config->get('bank_transfer_bank_' . $this->config->get('config_language_id')) . "\n\n";
		$comment .= $this->language->get('text_payment');
                }

		
		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('bank_transfer_order_status_id'), $comment, true);

	}
}
?>
