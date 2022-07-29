<?php
class ControllerModuleKlarnaCheckoutModule extends Controller {
	protected function index($setting) {
		$this->load->model('payment/klarna_checkout');

		// If Payment Method or Module is disabled
		if ($this->config->get('klarna_checkout_status') != 1 || !$setting['status']) {
			$this->model_payment_klarna_checkout->log('Not shown due to Payment Method or Module being disabled');
			return false;
		}

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->model_payment_klarna_checkout->log('Not shown due to empty cart');
			return false;
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$this->model_payment_klarna_checkout->log('Not shown due to cart not meeting minimum quantity reqs.');
				return false;
			}
		}

		// Validate cart has recurring products
		if ($this->cart->hasRecurringProducts()) {
			$this->model_payment_klarna_checkout->log('Not shown due to cart having recurring products.');
			return false;
		}

		list($totals, $taxes, $total) = $this->model_payment_klarna_checkout->getTotals();

		if ($this->config->get('klarna_checkout_total') > 0 && $this->config->get('klarna_checkout_total') > $total) {
			return false;
		}

		if ($this->model_payment_klarna_checkout->checkForPaymentTaxes($products)) {
			$this->model_payment_klarna_checkout->log('Payment Address based taxes used.');
			return false;
		}

		$this->setShipping();

		list($klarna_account, $connector) = $this->model_payment_klarna_checkout->getConnector($this->config->get('klarna_checkout_account'), $this->session->data['currency']);

		if (!$klarna_account || !$connector) {
			$this->model_payment_klarna_checkout->log('Couldn\'t secure connection to Klarna API.');
			return false;
		}

		$this->data['klarna_checkout'] = $this->url->link('payment/klarna_checkout', '', 'SSL');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/klarna_checkout_module.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/module/klarna_checkout_module.tpl';
        } else {
            $this->template = 'default/template/module/klarna_checkout_module.tpl';
        }

        $this->render();
	}

	private function setShipping() {
		$this->load->model('account/address');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');

        if (isset($this->session->data['shipping_country_id']) && !empty($this->session->data['shipping_country_id'])) {
			$country_info = $this->model_localisation_country->getCountry($this->session->data['shipping_country_id']);

            if (isset($this->session->data['shipping_zone_id']) && !empty($this->session->data['shipping_zone_id'])) {
                $zone_info = $this->model_localisation_zone->getZone($this->session->data['shipping_zone_id']);
            } else {
                $zone_info = $this->model_localisation_zone->getZone($this->config->get('config_zone_id'));
            }

            if (isset($this->session->data['shipping_postcode']) && !empty($this->session->data['shipping_postcode'])) {
                $postcode = $this->session->data['shipping_postcode'];
            } else {
                $postcode = '';
            }

			$this->session->data['shipping_address'] = array(
				'address_id'	 => null,
				'firstname'		 => null,
				'lastname'		 => null,
				'company'		 => null,
				'address_1'		 => null,
				'address_2'		 => null,
				'postcode'		 => $postcode,
				'city'			 => null,
				'zone_id'		 => $zone_info['zone_id'],
				'zone'			 => $zone_info['name'],
				'zone_code'		 => $zone_info['code'],
				'country_id'	 => $country_info['country_id'],
				'country'		 => $country_info['name'],
				'iso_code_2'	 => $country_info['iso_code_2'],
				'iso_code_3'	 => $country_info['iso_code_3'],
				'address_format' => '',
				'custom_field'	 => null,
			);
        } elseif (isset($this->session->data['shipping_address']) && !empty($this->session->data['shipping_address'])) {
			$this->session->data['shipping_address'] = $this->session->data['shipping_address'];
		} elseif ($this->customer->isLogged() && $this->customer->getAddressId()) {
			$this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
		} else {
			$country_info = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));

			$zone_info = $this->model_localisation_zone->getZone($this->config->get('config_zone_id'));

			$this->session->data['shipping_address'] = array(
				'address_id'	 => null,
				'firstname'		 => null,
				'lastname'		 => null,
				'company'		 => null,
				'address_1'		 => null,
				'address_2'		 => null,
				'postcode'		 => null,
				'city'			 => null,
				'zone_id'		 => $zone_info['zone_id'],
				'zone'			 => $zone_info['name'],
				'zone_code'		 => $zone_info['code'],
				'country_id'	 => $country_info['country_id'],
				'country'		 => $country_info['name'],
				'iso_code_2'	 => $country_info['iso_code_2'],
				'iso_code_3'	 => $country_info['iso_code_3'],
				'address_format' => '',
				'custom_field'	 => null,
			);
		}

		if (isset($this->session->data['shipping_address'])) {
			$this->session->data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
			$this->session->data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
			$this->session->data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];

			// Shipping Methods
			$method_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('shipping');

			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('shipping/' . $result['code']);

					$quote = $this->{'model_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

					if ($quote) {
						$method_data[$result['code']] = array(
							'title'      => $quote['title'],
							'quote'      => $quote['quote'],
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						);
					}
				}
			}

			$sort_order = array();

			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $method_data);

			$this->session->data['shipping_methods'] = $method_data;
		}
	}
}
