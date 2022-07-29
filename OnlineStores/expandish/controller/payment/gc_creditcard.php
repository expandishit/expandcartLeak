<?php

//Include library files
require_once('giropay/GiroCheckout_SDK/GiroCheckout_SDK.php');
require_once('giropay/GiroCheckout_Utility.php');

/**
 * GC credit card payment handler for the store front
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class ControllerPaymentGCCreditCard extends Controller
{

	public function index()
	{
		$this->language->load_json('payment/gc_creditcard');
		$this->load->model('checkout/order');

		$this->data['text_title'] = $this->language->get('text_title');
		// $this->data['text_name'] = $this->language->get('text_name');
		$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['entry_bankcode'] = $this->language->get('entry_bankcode');
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['text_loading'] = $this->language->get('text_wait');

		// if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/default/template/payment/gc_creditcard.expand')) {
		// 	$this->template = 'customtemplates/' . STORECODE . '/default/template/payment/gc_creditcard.expand';
		// } else {
		// 	$this->template =  'default/template/payment/gc_creditcard.expand';
		// }
        $this->template =  'default/template/payment/gc_creditcard.expand';

		$this->render_ecwig();
	}

	public function getParameters()
	{
		$json = array();

		$this->language->load('payment/gc_creditcard');
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$sLang = "en";

		try {
			//Sends request to Girocheckout.
			$request = new GiroCheckout_SDK_Request('creditCardTransaction');
			$request->setSecret($this->config->get('gc_creditcard_secret'));
			$request->addParam('merchantId', $this->config->get('gc_creditcard_merchant_id'))
				->addParam('projectId', $this->config->get('gc_creditcard_project_id'))
				->addParam('merchantTxId', $this->session->data['order_id'])
				->addParam('amount', round($this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false), 2) * 100)
				->addParam('currency', $order_info['currency_code'])
				->addParam('purpose', GiroCheckout_Utility::getPurpose('Order ', $order_info))
				->addParam('locale', $sLang)
				->addParam('urlRedirect', $this->url->link('payment/gc_creditcard/creditcardRedirect'))
				->addParam('urlNotify', $this->url->link('payment/gc_creditcard/creditcardNotify'))
				->addParam('sourceId', GiroCheckout_Utility::getGcSource())
				->addParam('orderId', $this->session->data['order_id'])
				->addParam('customerId', $order_info['customer_id'])
				->submit();

			if ($request->requestHasSucceeded()) {
				$json["success"] = $request->getResponseParam('redirect');
			} else {
				$json['error'] = true;
				$json['error_msg'] = GiroCheckout_SDK_ResponseCode_helper::getMessage($request->getResponseParam('rc'), $sLang);
			}
		} catch (Exception $e) {
			$json['error'] = true;
			$json['error_msg'] = GiroCheckout_SDK_ResponseCode_helper::getMessage(5100, $sLang);
		}

		$this->response->setOutput(json_encode($json));
	}

	public function creditcardRedirect() {

        try {
            $sLang = strtolower(substr($this->config->get('config_language'), 0, 2));
            $notify = new GiroCheckout_SDK_Notify('creditCardTransaction');
            $notify->setSecret(trim((string) $this->config->get('gc_creditcard_secret')));
            $notify->parseNotification($this->request->get);

            if ($notify->paymentSuccessful()) {
                $this->response->redirect($this->url->link('checkout/success'));
            } else {
                $errorMsg = $notify->getResponseMessage($notify->getResponseParam('gcResultPayment'), $sLang);
                $this->session->data['error'] = $errorMsg;
                $this->response->redirect($this->url->link('checkout/checkout'));
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            $this->session->data['error'] = $errorMsg;
            $this->response->redirect($this->url->link('checkout/checkout'));
        }
    }

	public function creditcardNotify()
	{
		try {
			$notify = new GiroCheckout_SDK_Notify('creditCardTransaction');
			$notify->setSecret(trim((string) $this->config->get('gc_creditcard_secret')));
			$notify->parseNotification($this->request->get);
			$iOrderId = $notify->getResponseParam('gcMerchantTxId');
			$iCustomerId = $this->customer->getId();
			$iState = $this->config->get('gc_creditcard_order_status_id');
			$iState = empty($iState) ? 2 : $iState;

			//Checks if the payment was successful and resirects the user
			if ($notify->paymentSuccessful()) {
				$this->load->model('checkout/order');
				$this->model_checkout_order->confirm($iOrderId, $iState, '', true);
				$notify->sendOkStatus();
				$notify->setNotifyResponseParam('Result', 'OK');
				$notify->setNotifyResponseParam('ErrorMessage', '');
				$notify->setNotifyResponseParam('MailSent', '');
				$notify->setNotifyResponseParam('OrderId', $iOrderId);
				$notify->setNotifyResponseParam('CustomerId', $iCustomerId);
				echo $notify->getNotifyResponseStringJson();
			} else {
				$notify->sendOkStatus();
				$notify->setNotifyResponseParam('Result', 'OK');
				$notify->setNotifyResponseParam('ErrorMessage', '');
				$notify->setNotifyResponseParam('MailSent', '');
				$notify->setNotifyResponseParam('OrderId', $iOrderId);
				$notify->setNotifyResponseParam('CustomerId', $iCustomerId);
				echo $notify->getNotifyResponseStringJson();
			}
		} catch (Exception $e) {
			$notify->sendBadRequestStatus();
			$notify->setNotifyResponseParam('Result', 'ERROR');
			$notify->setNotifyResponseParam('ErrorMessage', $e->getMessage());
			$notify->setNotifyResponseParam('MailSent', '');
			$notify->setNotifyResponseParam('OrderId', '');
			$notify->setNotifyResponseParam('CustomerId', $iCustomerId);
			echo $notify->getNotifyResponseStringJson();
		}
		exit;
	}
}
