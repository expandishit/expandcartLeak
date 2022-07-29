<?php

use Dompdf\Dompdf;
use Mpdf\HTMLParserMode;

class CustomEmailTemplates {
	private $sgl;
	private $cfg = array();
	private $logo = 'Store Logo';
	private $languageCode = 'en';

	public function __construct($registry) {
		$this->sgl = $registry;

		$this->languageCode = $this->sgl->get('config')->get('config_language');

		$this->loadConfig(0);

		if (is_file(DIR_IMAGE . $this->cfg['config_logo'])) {
			$logoPath = HTTPS_IMAGE . $this->cfg['config_logo'];
		} else {
			$logoPath = '';
		}
		$this->logo = '<img src="'. $logoPath .'" alt="" style="margin-bottom: 0px; border: none;width: 100px;" />';
	}

	private function loadConfig($store_id) {
		$query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "'");

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$this->cfg[$result['key']] = $result['value'];
			} else {
				if (version_compare(VERSION, '2.1') > 0) {
					$this->cfg[$result['key']] = json_decode($result['value'], true);
				} else {
					$this->cfg[$result['key']] = unserialize($result['value']);
				}
			}
		}
	}

	public function getEmailTemplate($data = array()) {

		$store_id = $this->sgl->get('config')->get('config_store_id');
		$language_id = $this->sgl->get('config')->get('config_language_id');

		if (isset($this->sgl->customerLanguageId)) {
			if (!empty($this->sgl->customerLanguageId)) {
				$language_id = $this->sgl->customerLanguageId;
			}
		}

		$email = '';
		$bcc_emails = array();
		$message_to_admin = false;

		$code = '';

		$data['class'] = strtolower($data['class']);
		$data['function'] = strtolower($data['function']);
		
		if ($data['class']) {
			switch ($data['class']) {
				case 'modelsaleorder':
				case 'modelcheckoutorder':
					if ($data['function'] == 'addorderhistory' || $data['function'] == 'confirm' || $data['function'] == 'update') {
						if ($data['vars']['order_id']) {
							if (isset($this->sgl->get('request')->post['order_status_id'])) {
								$order_status_id = $this->sgl->get('request')->post['order_status_id'];
							} else {
								if (isset($data['vars']['data']['order_status_id'])) {
									$order_status_id = $data['vars']['data']['order_status_id'];
								} else {
									$order_status_id = $data['vars']['order_status_id'];
								}
							}

							$code = 'order.status_' . (int)$order_status_id;
							$email = $data['vars']['order_info']['email'];
							$store_id = $data['vars']['order_info']['store_id'];
							$language_id = $data['vars']['order_info']['language_id'];
						}
					}

					break;
				case 'modelaccountreturn':
				case 'modelsalereturn':
					if ($data['function'] == 'addreturn' || $data['function'] == 'addreturnhistory') {
						if (isset($this->sgl->get('request')->post['return_status_id'])) {
							$return_status_id = $this->sgl->get('request')->post['return_status_id'];
						} elseif (isset($data['vars']['data']['return_status_id'])) {
							$return_status_id = $data['vars']['data']['return_status_id'];
						} else {
							$return_status_id = $this->cfg['config_return_status_id'];
						}

						$code = 'return.status_' . (int)$return_status_id;

						if (isset($this->sgl->get('request')->post['email'])) {
							$email = $this->sgl->get('request')->post['email'];

							$order_info = $this->sgl->get('db')->query("SELECT store_id, language_id, email FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$this->sgl->get('request')->post['order_id'] . "'");
						} else {
							$return_info = $this->sgl->get('db')->query("SELECT order_id, email FROM `" . DB_PREFIX . "return` WHERE return_id = '" . (int)$data['vars']['return_id'] . "'");

							$email = $return_info->row['email'];

							$order_info = $this->sgl->get('db')->query("SELECT store_id, language_id, email FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$return_info->row['order_id'] . "'");
						}

						$store_id = $order_info->row['store_id'];
						$language_id = $order_info->row['language_id'];
					}

					break;
				case 'controllermarketingcontact':
				case 'controllersalecontact':
					if ($this->sgl->get('request')->post['mail_template'] && $data['function'] == 'send') {
						$code = $this->sgl->get('request')->post['mail_template'];
						$email = $data['vars']['email'];
						$store_id = $this->sgl->get('request')->post['store_id'];
					}

					break;
				case 'controllerinformationcontact':
					$code = 'contact.confirmation';
					$email = $this->sgl->get('request')->post['email'];
					$bcc_emails[] = $email;

					$message_to_admin = true;

					break;
				case 'modelaccountcustomer':
				case 'modelmoduledsociallogin':
					if ($data['function'] == 'addcustomer' || $data['function'] == 'addcustomerv2') {
						if (!$data['vars']['customer_group_info']['approval']) {
							$code = 'customer.register';
						} else {
							$code = 'customer.register.approval';
						}

						$email = $data['vars']['data']['email'];
					}

					break;
				case 'controlleraccountforgotten':
					$code = 'customer.password.reset';
					$email = $this->sgl->get('request')->post['email'];

					break;
				case 'modelsalecustomer':
					if ($data['function'] == 'approve') {
						$code = 'customer.approve';
						$email = $data['vars']['customer_info']['email'];
						$store_id = $data['vars']['customer_info']['store_id'];
					}else if($data['function'] == 'addtransaction'){
						$code = 'customer.balance';
						$email = $data['vars']['customer_info']['email'];
						$store_id = $data['vars']['customer_info']['store_id'];
					}

					break;
				case 'modelcheckoutvoucher':
					$code = 'customer.voucher';
					$email = $data['vars']['voucher']['to_email'];

					break;
				case 'modelaffiliateaffiliate':
					if ($data['function'] == 'addaffiliate') {
						if (!$this->sgl->get('config')->get('config_affiliate_approval')) {
							$code = 'affiliate.register';
						} else {
							$code = 'affiliate.register.approval';
						}

						$email = $data['vars']['data']['email'];
					}

					// Opencart 2.0.x and above
					if ($data['function'] == 'addtransaction') {
						$code = 'affiliate.add.transaction';

						$email = $data['vars']['affiliate_info']['email'];
					}
					// END Opencart 2.0.x and above

					break;
				case 'controlleraffiliateforgotten':
					$code = 'affiliate.password.reset';
					$email = $this->sgl->get('request')->post['email'];

					break;
				case 'modelsaleaffiliate':
				case 'modelmarketingaffiliate':
					if ($data['function'] == 'approve') {
						$code = 'affiliate.approve';
						$email = $data['vars']['affiliate_info']['email'];
						$store_id = $data['vars']['affiliate_info']['store_id'];
					}

					if ($data['function'] == 'addtransaction') {
						$code = 'affiliate.add.transaction';

						$email = $data['vars']['affiliate_info']['email'];
					}

					break;
				case 'modelcatalogreview':
					if ($data['function'] == 'addreview') {
						$code = 'reviews.added';
						$email = $this->sgl->get('config')->get('config_email');
					}

					break;
				case 'controllercommoncustomemailtemplates':
					if ($data['function'] == 'cron') {
						$code = 'cron.invoice';
						$email = $data['vars']['order_info']['email'];
						$store_id = $data['vars']['order_info']['store_id'];
						$language_id = $data['vars']['order_info']['language_id'];
					}

					break;
				case 'msmail': //Seller Order Mail
						if($data['function'] == 'sendmail' && $data['has_mail_template']){
							$code = $data['vars']['order_info']['order_id'] ? 'seller.order' : 'seller.contact';
							$email = $data['vars']['data']['recipients'];
							$store_id = $data['vars']['order_info']['store_id'];
						}
					break;

				case 'controllermultisellerseller': //Seller Enable/Disable

						if ($data['function'] == 'dtupdatestatus_enable') {
							$code = 'seller.enable';
							$email = $data['vars']['seller_email'];
						}

						if ($data['function'] == 'dtupdatestatus_disable') {
							$code = 'seller.disable';
							$email = $data['vars']['seller_email'];
						}
					break;
				case 'controllercatalogproduct':

					if ($data['function'] == 'dtupdatestatus_approve') {
						$code = 'product.approve';
						$email = $data['vars']['seller_email'];
					}

					if ($data['function'] == 'dtupdatestatus_reject') {
						$code = 'product.reject';
						$email = $data['vars']['seller_email'];
					}

					break;
			}
		}

		$this->loadConfig($store_id);

		$this->cfg['config_language_id'] = $language_id;
		if ($code && $this->cfg['custom_email_templates_status']) {
			$result = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "cet_template` cett LEFT JOIN `" . DB_PREFIX . "cet_description` cetd ON (cett.template_id = cetd.template_id) WHERE cetd.language_id = '" . (int)$language_id . "' AND cett.store_id = '" . (int)$store_id . "' AND status = '1' AND `code` = '" . $this->sgl->get('db')->escape($code) . "' LIMIT 1");

			if ($result->row) {
				$method_name = 'prepare' . str_replace('.', '', preg_replace('#_[0-9]+#i', '', $code)) . 'Template';
				if (method_exists($this, $method_name)) {
					$TemplateDate = $result->row;
					$find = array('{FIRSTNAME}',
								  '{LASTNAME}',
								  '{LOGO}',
								  '{EMAIL}',
								  '{TELEPHONE}'
								);
					$replace = array('FIRSTNAME' => '{firstname}',
									 'LASTNAME'  => '{lastname}',
									 'LOGO'      => '{logo}',
									 'EMAIL'     => '{email}',
									 'TELEPHONE' => '{telephone}'
									);
					$TemplateDate['description'] = str_replace($find, $replace, $TemplateDate['description']);
					$template_info = $this->{$method_name}($TemplateDate, $data['vars']);

					if ($template_info) {
						if (!$message_to_admin && $this->cfg['custom_email_templates_notify_admin_status']) {
							$bcc_emails[] = $this->sgl->get('config')->get('config_email');

							//$this->disableDefaultAdminNotifications($store_id);
						}

						if ($result->row['bcc']) {
							$explode_bcc_emails = explode(',', $result->row['bcc']);

							foreach ($explode_bcc_emails as $bcc_email) {
								$bcc_emails[] = $bcc_email;
							}
						}

						if ($this->cfg['custom_email_templates_bcc']) {
							$explode_bcc_emails = explode(',', $this->cfg['custom_email_templates_bcc']);

							foreach ($explode_bcc_emails as $bcc_email) {
								$bcc_emails[] = $bcc_email;
							}
						}

						$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html>' . "\n";
						$html .= '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n";
						$html .= '  <head>' . "\n";
						$html .= '    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . "\n";
						$html .= '    <meta name="viewport" content="width=device-width">' . "\n";
						$html .= '    <style>' . "\n";
						$html .= '    	   *{ direction:' . $this->getLanguageDirection()  . '; }' . "\n";
						$html .= '    	   .cet_container, td, th, input, select, textarea, option, optgroup { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; color: ' . $this->cfg['custom_email_templates_text_color'] . '; }' . "\n";
						$html .= '    	   .cet_container { padding: 0px; margin: 0px; max-width: ' . $this->cfg['custom_email_templates_layout_width'] . '!important; }' . "\n";
						$html .= '    	   a,a:link,a:visited,a:hover { color: ' . $this->cfg['custom_email_templates_link_color'] . '; }' . "\n";
						$html .= '    </style>' . "\n";
						$html .= '  </head>' . "\n";
						$html .= '  <body style="background-color: ' . $this->cfg['custom_email_templates_background_color'] . '; ' . (($this->cfg['custom_email_templates_background_image']) ? 'background-image: url(\'' . HTTP_SERVER . $this->cfg['custom_email_templates_background_image'] . '\'); background-repeat: ' . $this->cfg['custom_email_templates_background_repeat'] . ';' : '') . '">' . "\n";

						$found_container = false;

						if (stripos($template_info['message'], '"cet_container"') === false) {
							$html .= '<div id="cet_container" class="cet_container">' . "\n";

							$found_container = true;
						}

						$html .= html_entity_decode($template_info['message'], ENT_COMPAT, 'UTF-8');

						if ($found_container) {
							$html .= '</div>' . "\n";
						}

						$html .= '  </body>' . "\n";
						$html .= '</html>';

						$bcc_html = $html;

						$text = "";

						if ($this->cfg['custom_email_templates_plain_text_status']) {
							$text = strip_tags($html);
							$text = str_replace("\r\n", "\n", $html);
							$text = str_replace("\r", "\n", $text);
							//$text = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/', '&amp;', $text);

							$dom = new DOMDocument('1.0', 'UTF-8');
							$dom->strictErrorChecking = false;

							if (!$dom->loadHTML($text)) {
								$this->sgl->get('log')->write("Custom Email Templates: Could not load HTML! Code: " . $code);
							}

							$text = $this->_html_to_plain_text($dom->getElementById('cet_container'));

							$text = trim(preg_replace("/[ \t]*\n[ \t]*/im", "\n", $text));
						}

						$history_id = $this->addHistory($result->row['template_id'], $email, $template_info['subject'], $html);

						if (isset($this->sgl->get('request')->post['fattachments'])) {
							$this->addAttachment($history_id, $this->sgl->get('request')->post['fattachments']);
						}

						if ($this->cfg['custom_email_templates_track_status']) {
							$str_tracking = '<img src="' . HTTP_CATALOG . 'index.php?route=common/custom_email_templates/tracking&_h=' . (int)$history_id . '&_c=' . md5($history_id . $email . $code . (int)$result->row['template_id']) . '" border="0" height="1" width="1">';

							if (stripos($html, '</body>') !== false) {
								$html = str_replace('</body>', $str_tracking . '</body>', $html);
							} else {
								$html .= $str_tracking;
							}
						}

						return array(
							'template_id' => $result->row['template_id'],
							'subject'     => $template_info['subject'],
							'html'        => $html,
							'text'        => $text,
							'bcc_html'    => $bcc_html,
							'bcc_emails'  => array_unique($bcc_emails),
							'history_id'  => $history_id,
							'invoice'     => (isset($this->cfg['custom_email_templates_attach_invoice']) && isset($order_status_id) && (in_array($order_status_id, (array)$this->cfg['custom_email_templates_attach_invoice']))) ? 1 : 0
						);
					}
				}
			}
		}

		return false;
	}

	private function prepareOrderStatusTemplate($template, $vars) {
		$showcase = '';

		if ($template['product'] && $template['product_limit'] > 0) {
			$customer_info = $this->getCustomer($vars['order_info']['customer_id']);

			if ($customer_info) {
				$customer_group_id = (int)$customer_info['customer_group_id'];
			} else {
				$customer_group_id = (int)$this->cfg['config_customer_group_id'];
			}

			$showcase = $this->productsShowcaseSection((int)$customer_group_id, $template['store_id'], $template['product'], $template['product_limit']);
		}

		if (isset($vars['data']['order_status_id'])) {
			$order_status_id = $vars['data']['order_status_id'];
		} else {
			$order_status_id = $vars['order_status_id'];
		}

		$invoice_no = $vars['order_info']['invoice_no'];
		if (
		    isset($this->cfg['custom_email_templates_generate_invoice_number']) 
            && in_array($order_status_id, $this->cfg['custom_email_templates_generate_invoice_number'])
            && !$this->cfg['config_stop_auto_generate_invoice_no']
        ) {
			if (!$vars['order_info']['invoice_no'] && $order_status_id) {
				$query = $this->sgl->get('db')->query("SELECT MAX(invoice_no) AS invoice_no FROM `" . DB_PREFIX . "order` WHERE invoice_prefix = '" . $this->sgl->get('db')->escape($vars['order_info']['invoice_prefix']) . "'");

				if ($query->row['invoice_no']) {
					$invoice_no = (int)$query->row['invoice_no'] + 1;
				} else {
					$invoice_no = 1;
				}

				$this->sgl->get('db')->query("UPDATE `" . DB_PREFIX . "order` SET invoice_no = '" . (int)$invoice_no . "', invoice_prefix = '" . $this->sgl->get('db')->escape($vars['order_info']['invoice_prefix']) . "' WHERE order_id = '" . (int)$vars['order_id'] . "'");
			}
		}

		if (((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'))) {
			if (defined('HTTPS_CATALOG')) {
				$base = HTTPS_CATALOG;
			} elseif (defined('HTTP_CATALOG')) {
				$base = HTTP_CATALOG;
			} else {
				$base = HTTPS_SERVER;
			}
		} else {
			if (defined('HTTP_CATALOG')) {
				$base = HTTP_CATALOG;
			} elseif (defined('HTTP_CATALOG')) {
				$base = HTTP_CATALOG;
			} else {
				$base = HTTP_SERVER;
			}
		}

		$order_status_query = $this->sgl->get('db')->query("SELECT name FROM `" . DB_PREFIX . "order_status` WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$vars['order_info']['language_id'] . "'");

		if ($order_status_query->num_rows) {
			$order_status_name = $order_status_query->row['name'];
		} else {
			$order_status_name = '';
		}

		if ($vars['order_info']['shipping_firstname']) {
			$shipping_address = $vars['order_info']['shipping_firstname'] . ' ' . $vars['order_info']['shipping_lastname'] . '<br />' . (($vars['order_info']['shipping_company']) ? $vars['order_info']['shipping_company'] . '<br />' : '') . '' . $vars['order_info']['shipping_address_1'] . '<br />' . (($vars['order_info']['shipping_address_2']) ? $vars['order_info']['shipping_address_2'] . '<br />' : '') . $vars['order_info']['shipping_postcode'] . ' ' . $vars['order_info']['shipping_city'] . '<br />' . $vars['order_info']['shipping_zone'] . '<br />' . $vars['order_info']['shipping_country'];
		} else {
			$shipping_address = '';
		}

		if ($vars['order_info']['payment_firstname']) {
			if (isset($vars['order_info']['payment_company_id'])) {
				$payment_company_id = $vars['order_info']['payment_company_id'];
			} else {
				$payment_company_id = '';
			}

			if (isset($vars['order_info']['payment_tax_id'])) {
				$payment_tax_id = $vars['order_info']['payment_tax_id'];
			} else {
				$payment_tax_id = '';
			}

			if (isset($this->cfg['custom_email_templates_mlanguage'][$vars['order_info']['language_id']])) {
				$text_tax_id = $this->cfg['custom_email_templates_mlanguage'][$vars['order_info']['language_id']]['tax_id'];
				$text_company_id = $this->cfg['custom_email_templates_mlanguage'][$vars['order_info']['language_id']]['company_id'];
			} else {
				$text_tax_id = '';
				$text_company_id = '';
			}

			$payment_address = $vars['order_info']['payment_firstname'] . ' ' . $vars['order_info']['payment_lastname'] . '<br />' . (($vars['order_info']['payment_company']) ? $vars['order_info']['payment_company'] : '') . '' . $vars['order_info']['payment_address_1'] . '<br />' . (($vars['order_info']['payment_address_2']) ? $vars['order_info']['payment_address_2'] . '<br />' : '') . $vars['order_info']['payment_postcode'] . ' ' . $vars['order_info']['payment_city'] . '<br />' . $vars['order_info']['payment_zone'] . '<br />' . $vars['order_info']['payment_country'] . (($payment_company_id) ? '<br />' . $text_company_id . ' ' . $payment_company_id : '') . (($payment_tax_id) ? '<br />' . $text_tax_id . ' ' . $payment_tax_id: '');
		} else {
			$payment_address = '';
		}

		$order_link = '';

		if ($vars['order_info']['customer_id']) {
			$order_link = $vars['order_info']['store_url'] . 'index.php?route=account/order/info&order_id=' . $vars['order_id'];
		}

		$product_data = array();

		$products_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$vars['order_id'] . "'");

		if (version_compare(VERSION, '2.0') < 0) {
			//nothing
		} else {
			$this->sgl->get('load')->model('tool/upload');
		}

		$admin_folder = '';

		if (defined('HTTP_CATALOG') || defined('HTTPS_CATALOG')) {
			$parts = explode('/', trim(DIR_APPLICATION, '/'));
			$admin_folder = end($parts);
		}

		$this->sgl->get('load')->model('tool/image');

		$external_products_videos_urls = '';

		foreach ($products_query->rows as $product) {
			$option_data = '';

			$product_info = $this->getProduct($product['product_id']);

			if(isset($product_info['external_video_url']) && 
				!empty($product_info['external_video_url'])){
			
				
				$external_products_videos_urls .= "<br />" . $product_info['external_video_url'];
			
			}

			$order_options_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "order_option` WHERE order_id = '" . (int)$vars['order_id'] . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

			foreach ($order_options_query->rows as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					if (version_compare(VERSION, '2.0') < 0) {
						$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
						$value = (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value);
					} else {
						$upload_info = $this->sgl->get('model_tool_upload')->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}
				}

				$option_data .= '<br /><i>' . $option['name'] . ': ' . $value . '</i>';
			}

			$products_cfg = $this->cfg['custom_email_templates_products_section'];

			if ($product_info && $product_info['image']) {
				if ($products_cfg['column']['image']['image_width'] && $products_cfg['column']['image']['image_height']) {
					$product_image = $this->sgl->get('model_tool_image')->resize($product_info['image'], $products_cfg['column']['image']['image_width'], $products_cfg['column']['image']['image_height']);
				} else {
					$product_image = $this->sgl->get('model_tool_image')->resize($product_info['image'], 80, 80);
				}
			} else {
				$product_image = '';
			}

			if ($admin_folder) {
				$url = str_replace('/' . $admin_folder . '/', '/', $this->sgl->get('url')->link('product/product', 'product_id=' . (int)$product['product_id'], 'NONSSL'));
			} else {
				$url = $this->sgl->get('url')->link('product/product', 'product_id=' . (int)$product['product_id'], 'NONSSL');
			}

			$productVoucherCode = array();

			if (isset($product['code_generator']) && !in_array($vars['order_info']['payment_code'], ['cod', 'ccod', 'bank_transfer', 'cheque', 'my_fatoorah', 'payoneer', 'free_checkout', 'tamara', 'tamara_installment'])) {
				if (!empty($product['code_generator'])) {
					$productVoucherCode = json_decode($product['code_generator'], true);
				}
			}

			$product_data[] = array(
				'image'       => '<img src="' . str_replace(' ', '%20', $product_image) . '" alt="' . $product['name'] . '" class="product_image" />',
				'product'     => ($products_cfg['column']['product']['link_status']) ? '<a href="' . $url . '">' . $product['name'] . '</a>' : $product['name'],
				'model'       => $product['model'],
				'quantity'    => $product['quantity'],
				'price'       => $this->sgl->get('currency')->format($product['price'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
				'price_gross' => $this->sgl->get('currency')->format(($product['price'] + $product['tax']), $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
				'attribute'   => $this->getProductAttributes($product['product_id'], $vars['order_info']['language_id']),
				'option'      => $option_data,
				'sku'         => $product_info ? $product_info['sku'] : '',
				'upc'         => $product_info ? $product_info['upc'] : '',
				'tax'         => $this->sgl->get('currency')->format($product['tax'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
				'total'       => $this->sgl->get('currency')->format($product['total'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
				'total_gross' => $this->sgl->get('currency')->format($product['total'] + ($product['tax'] * $product['quantity']), $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
				'product_code' => $productVoucherCode
			);
		}
			// var_dump($external_products_videos_urls); die();

		$voucher_data = array();

		$vouchers_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$vars['order_id'] . "'");

		foreach ($vouchers_query->rows as $voucher) {
			$voucher_data[] = array(
				'description' => $voucher['description'],
				'amount'      => $this->sgl->get('currency')->format($voucher['amount'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value'])
			);
		}

		$total_data = array();
		$tax_amount = 0;

		$order_totals_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$vars['order_id'] . "' ORDER BY sort_order ASC");

		foreach ($order_totals_query->rows as $total) {
			if ($total['code'] == 'tax') {
				$tax_rate = preg_replace('#[^0-9\-\.%]+#i', '', $total['title']);

				$total_data[$total['code']][] = array(
					'title'    => $total['title'],
					'tax_rate' => $tax_rate,
					'value'    => $this->sgl->get('currency')->format($total['value'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value'])
				);

				$tax_amount += $total['value'];

			} else if ($total['code'] == 'reward' || $total['code'] == 'earn_point') {

				$total_data[$total['code']][] = array(
					'title'	=>	$total['title'],
					'value' => 	$total['text'],
				);

			} else {
				$total_data[$total['code']][] = array(
					'title' => $total['title'],
					'value' => $this->sgl->get('currency')->format($total['value'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value'])
				);
			}
		}

		if (isset($vars['data']['comment'])) {
			$comment = $vars['data']['comment'];
		} elseif (isset($vars['comment'])) {
			$comment = $vars['comment'];
		} else {
			$comment = '';
		}

		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{delivery_address}',
			'{shipping_address}',
			'{payment_address}',
			'{order_date}',
			'{products}',
			'{totals}',
			'{vouchers}',
			'{showcase}',
			'{date}',
			'{payment}',
			'{shipment}',
			'{order_id}',
			'{total}',
			'{invoice_number}',
			'{order_link}',
			'{store_url}',
			'{status_name}',
			'{store_name}',
			'{ip}',
			'{comment}',
			'{sub_total}',
			'{shipping_cost}',
			'{client_comment}',
			'{taxes}',
			'{tax_amount}',
			'{email}',
			'{telephone}',
			'{carrier}',
			'{tracking_number}',
			'{carrier_link}',
			'{external_products_videos_urls}'
		);

		$replace = array(
			'logo'             => $this->logo,
			'firstname'        => $vars['order_info']['firstname'],
			'lastname'         => $vars['order_info']['lastname'],
			'delivery_address' => $shipping_address,
			'shipping_address' => $shipping_address,
			'payment_address'  => $payment_address,
			'order_date'       => date($this->cfg['custom_email_templates_date_format'], strtotime($vars['order_info']['date_added'])),
			'products'         => $this->productsSection($product_data, $voucher_data, $total_data),
			'totals'           => $this->totalsSection($total_data),
			'vouchers'         => $this->vouchersSection($voucher_data),
			'showcase'         => $showcase,
			'date'             => date($this->cfg['custom_email_templates_date_format'], time()),
			'payment'          => $vars['order_info']['payment_method'],
			'shipment'         => $vars['order_info']['shipping_method'],
			'order_id'         => $vars['order_id'],
			'total'            => $this->sgl->get('currency')->format($vars['order_info']['total'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
			'invoice_number'   => $vars['order_info']['invoice_prefix'] . $invoice_no,
			'order_link'       => $order_link,
			'store_url'        => $vars['order_info']['store_url'],
			'status_name'      => $order_status_name,
			'store_name'       => $vars['order_info']['store_name'],
			'ip'               => $vars['order_info']['ip'],
			'comment'          => $this->commentSection($comment),
			'sub_total'        => (isset($total_data['sub_total'][0])) ? $total_data['sub_total'][0]['value'] : '',
			'shipping_cost'    => (isset($total_data['shipping'][0]['value'])) ? $total_data['shipping'][0]['value'] : '',
			'client_comment'   => $vars['order_info']['comment'],
			'taxes'            => (isset($total_data['tax'])) ? $this->taxesSection($total_data['tax']) : '',
			'tax_amount'       => $this->sgl->get('currency')->format($tax_amount, $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
			'email'            => $vars['order_info']['email'],
			'telephone'        => $vars['order_info']['telephone'],
			'carrier'          => '',//(isset($trackers['tracker_carrier_name'])) ? $trackers['tracker_carrier_name'] : '',
			'tracking_number'  => '',//($trackers && $data['tracking_numbers']) ? htmlspecialchars($data['tracking_numbers']) : '',
			'carrier_href'     => '',//(isset($trackers['tracker_carrier_link'])) ? sprintf($trackers['tracker_carrier_link'], trim($data['tracking_numbers'])) : ''
			'external_products_videos_urls' => $external_products_videos_urls
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareReturnStatusTemplate($template, $vars) {
		$showcase = '';

		if ($template['product'] && $template['product_limit'] > 0) {
			$showcase = $this->productsShowcaseSection((int)$this->cfg['config_customer_group_id'], $template['store_id'], $template['product'], $template['product_limit']);
		}

		$return_data = array();
		$_tmp = array();

		$return_reasons = $this->getReturnReasons();
		$return_actions = $this->getReturnActions();
		$return_statuses = $this->getReturnStatuses();

		if (isset($this->sgl->get('request')->post['email'])) {
			$firstname = $this->sgl->get('request')->post['firstname'];
			$lastname = $this->sgl->get('request')->post['lastname'];
			$email = $this->sgl->get('request')->post['email'];
			$telephone = $this->sgl->get('request')->post['telephone'];
			$order_id = $this->sgl->get('request')->post['order_id'];
			$order_date = $this->sgl->get('request')->post['date_ordered'];
			$comment = $this->sgl->get('request')->post['comment'];

			if (isset($this->sgl->get('request')->post['return_product'])) {
				foreach ($this->sgl->get('request')->post['return_product'] as $product) {
					$return_status_id = (isset($product['return_status_id'])) ? $product['return_status_id'] : (int)$this->cfg['config_return_status_id'];

					$return_data[] = array(
						'product'       => $product['product'],
						'model'         => $product['model'],
						'quantity'      => (int)$product['quantity'],
						'reason'        => (isset($product['return_reason_id']) && isset($return_reasons[$product['return_reason_id']])) ? $return_reasons[$product['return_reason_id']]['name'] : '',
						'action'        => (isset($product['return_action_id']) && isset($return_actions[$product['return_action_id']])) ? $return_actions[$product['return_action_id']]['name'] : '',
						'return_status' => (isset($return_status_id) && isset($return_statuses[$return_status_id])) ? $return_statuses[$return_status_id]['name'] : '',
						'opened'        => ($product['opened'] == 1) ? $this->sgl->get('language')->get('text_yes') : $this->sgl->get('language')->get('text_no'),
						'comment'       => $product['comment']
					);
				}
			} else {
				$return_status_id = (isset($this->sgl->get('request')->post['return_status_id'])) ? $this->sgl->get('request')->post['return_status_id'] : (int)$this->cfg['config_return_status_id'];

				$return_data[] = array(
					'product'       => $this->sgl->get('request')->post['product'],
					'model'         => $this->sgl->get('request')->post['model'],
					'quantity'      => (int)$this->sgl->get('request')->post['quantity'],
					'reason'        => (isset($this->sgl->get('request')->post['return_reason_id']) && isset($return_reasons[$this->sgl->get('request')->post['return_reason_id']])) ? $return_reasons[$this->sgl->get('request')->post['return_reason_id']]['name'] : '',
					'action'        => (isset($this->sgl->get('request')->post['return_action_id']) && isset($return_actions[$this->sgl->get('request')->post['return_action_id']])) ? $return_actions[$this->sgl->get('request')->post['return_action_id']]['name'] : '',
					'return_status' => (isset($return_status_id) && isset($return_statuses[$return_status_id])) ? $return_statuses[$return_status_id]['name'] : '',
					'opened'        => ($this->sgl->get('request')->post['opened'] == 1) ? $this->sgl->get('language')->get('text_yes') : $this->sgl->get('language')->get('text_no'),
					'comment'       => ''
				);
			}
		} else {
			$return_info = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "return` WHERE return_id = '" . (int)$vars['return_id'] . "'");

			$firstname = $return_info->row['firstname'];
			$lastname = $return_info->row['lastname'];
			$email = $return_info->row['email'];
			$telephone = $return_info->row['telephone'];
			$order_id = $return_info->row['order_id'];
			$order_date = $return_info->row['date_ordered'];

			if (isset($this->sgl->get('request')->post['comment'])) {
				$comment = $this->sgl->get('request')->post['comment'];
			} else {
				$comment = '';
			}

			if (!isset($return_info->row['product'])) {
				$returns_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "return_product` WHERE return_id = '" . (int)$vars['return_id'] . "'");

				foreach ($returns_query->rows as $product) {
					$return_status_id = (isset($product['return_status_id'])) ? $product['return_status_id'] : (int)$this->cfg['config_return_status_id'];

					$return_data[] = array(
						'product'       => $product['product'],
						'model'         => $product['model'],
						'quantity'      => (int)$product['quantity'],
						'reason'        => (isset($product['return_reason_id']) && isset($return_reasons[$product['return_reason_id']])) ? $return_reasons[$product['return_reason_id']]['name'] : '',
						'action'        => (isset($product['return_action_id']) && isset($return_actions[$product['return_action_id']])) ? $return_actions[$product['return_action_id']]['name'] : '',
						'return_status' => (isset($return_status_id) && isset($return_statuses[$return_status_id])) ? $return_statuses[$return_status_id]['name'] : '',
						'opened'        => ($product['opened'] == 1) ? $this->sgl->get('language')->get('text_yes') : $this->sgl->get('language')->get('text_no'),
						'comment'       => $product['comment']
					);
				}
			} else {
				$return_status_id = (isset($this->sgl->get('request')->post['return_status_id'])) ? $this->sgl->get('request')->post['return_status_id'] : (int)$this->cfg['config_return_status_id'];

				$return_data[] = array(
					'product'       => $return_info->row['product'],
					'model'         => $return_info->row['model'],
					'quantity'      => (int)$return_info->row['quantity'],
					'reason'        => (isset($return_info->row['return_reason_id']) && isset($return_reasons[$return_info->row['return_reason_id']])) ? $return_reasons[$return_info->row['return_reason_id']]['name'] : '',
					'action'        => (isset($return_info->row['return_action_id']) && isset($return_actions[$return_info->row['return_action_id']])) ? $return_actions[$return_info->row['return_action_id']]['name'] : '',
					'return_status' => (isset($return_status_id) && isset($return_statuses[$return_status_id])) ? $return_statuses[$return_status_id]['name'] : '',
					'opened'        => ($return_info->row['opened'] == 1) ? $this->sgl->get('language')->get('text_yes') : $this->sgl->get('language')->get('text_no'),
					'comment'       => ''
				);
			}
		}

		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{email}',
			'{telephone}',
			'{order_id}',
			'{order_date}',
			'{comment}',
			'{returns}',
			'{showcase}'
		);
		
		$replace = array(
			'logo'       => $this->logo,
			'firstname'  => $firstname,
			'lastname'   => $lastname,
			'email'      => $email,
			'telephone'  => $telephone,
			'order_id'   => $order_id,
			'order_date' => date($this->cfg['custom_email_templates_date_format'], strtotime($order_date)),
			'comment'    => nl2br($comment),
			'returns'    => $this->returnsSection($return_data),
			'showcase'   => $showcase
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareMailTemplate($template, $vars) {
		$showcase = '';

		if ($template['product'] && $template['product_limit'] > 0) {
			$showcase = $this->productsShowcaseSection((int)$this->cfg['config_customer_group_id'], $template['store_id'], $template['product'], $template['product_limit']);
		}

		$find = array(
			'{showcase}'
		);

		$replace = array(
			'showcase' => $showcase
		);

		$message = str_replace($find, $replace, $vars['cet']->sgl->get('request')->post['message']);

		return array(
			'subject' => $vars['cet']->sgl->get('request')->post['subject'],
			'message' => $message
		);
	}

	private function prepareCustomerRegisterTemplate($template, $vars) {
		$showcase = '';

		if ($template['product'] && $template['product_limit'] > 0) {
			$showcase = $this->productsShowcaseSection((int)$vars['customer_group_info']['customer_group_id'], $template['store_id'], $template['product'], $template['product_limit']);
		}

		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{date}',
			'{store_name}',
			'{email}',
			'{password}',
			'{account_link}',
			'{activate_link}',
			'{showcase}'
		);

		$replace = array(
			'logo' => $this->logo,
			'firstname'     => $vars['data']['firstname'],
			'lastname'      => $vars['data']['lastname'],
			'date'          => date($this->cfg['custom_email_templates_date_format'], time()),
			'store_name'    => $this->sgl->get('config')->get('config_name'),
			'email'         => $vars['data']['email'],
			'password'      => $vars['data']['password'],
			'account_link'  => '<a href="' . $this->sgl->get('url')->link('account/login', '', 'SSL') . '">' . $this->sgl->get('url')->link('account/login', '', 'SSL') . '</a>',
			'activate_link' => (isset($vars['data']['confirm_code']) && $vars['data']['confirm_code']) ? '<a href="' . $this->sgl->get('url')->link('account/activation/activate', 'token=' . $vars['data']['confirm_code'], 'SSL') . '">' . $this->sgl->get('url')->link('account/activation/activate', 'token=' . $vars['data']['confirm_code'], 'SSL') . '</a>' : '',
			'showcase'      => $showcase
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareCustomerRegisterApprovalTemplate($template, $vars) {
		$showcase = '';

		if ($template['product'] && $template['product_limit'] > 0) {
			$showcase = $this->productsShowcaseSection((int)$vars['customer_group_info']['customer_group_id'], $template['store_id'], $template['product'], $template['product_limit']);
		}

		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{date}',
			'{store_name}',
			'{email}',
			'{account_link}',
			'{showcase}'
		);

		$replace = array(
			'logo'          => $this->logo,
			'firstname'     => $vars['data']['firstname'],
			'lastname'      => $vars['data']['lastname'],
			'date'          => date($this->cfg['custom_email_templates_date_format'], time()),
			'store_name'    => $this->sgl->get('config')->get('config_name'),
			'email'         => $vars['data']['email'],
			'account_link'  => '<a href="' . $this->sgl->get('url')->link('account/login', '', 'SSL') . '">' . $this->sgl->get('url')->link('account/login', '', 'SSL') . '</a>',
			'showcase'      => $showcase
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareCustomerPasswordResetTemplate($template, $vars) {
		$customer_info = $this->getCustomerByEmail($vars['cet']->sgl->get('request')->post['email']);

		$showcase = '';

		if ($template['product'] && $template['product_limit'] > 0) {
			$customer_group_id = (isset($customer_info['customer_group_id'])) ? (int)$customer_info['customer_group_id'] : (int)$this->cfg['config_customer_group_id'];

			$showcase = $this->productsShowcaseSection((int)$customer_group_id, $template['store_id'], $template['product'], $template['product_limit']);
		}

		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{date}',
			'{store_name}',
			'{email}',
			'{password}',
			'{account_link}',
			'{showcase}'
		);

		$replace = array(
			'logo'          => $this->logo,
			'firstname'     => ($customer_info) ? $customer_info['firstname'] : '',
			'lastname'      => ($customer_info) ? $customer_info['lastname'] : '',
			'date'          => date($this->cfg['custom_email_templates_date_format'], time()),
			'store_name'    => $this->sgl->get('config')->get('config_name'),
			'email'         => $vars['cet']->sgl->get('request')->post['email'],
			'password'      => $vars['password'],
			'account_link'  => '<a href="' . $this->sgl->get('url')->link('account/login', '', 'SSL') . '">' . $this->sgl->get('url')->link('account/login', '', 'SSL') . '</a>',
			'showcase'      => $showcase
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareCustomerApproveTemplate($template, $vars) {
		$showcase = '';

		if ($template['product'] && $template['product_limit'] > 0) {
			$showcase = $this->productsShowcaseSection((int)$vars['customer_info']['customer_group_id'], $template['store_id'], $template['product'], $template['product_limit']);
		}

		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{date}',
			'{store_name}',
			'{email}',
			'{account_link}',
			'{showcase}'
		);

		$replace = array(
			'logo'          => $this->logo,
			'firstname'     => $vars['customer_info']['firstname'],
			'lastname'      => $vars['customer_info']['lastname'],
			'date'          => date($this->cfg['custom_email_templates_date_format'], time()),
			'store_name'    => $vars['store_name'],
			'email'         => $vars['customer_info']['email'],
			'account_link'  => '<a href="' . $vars['store_url'] . '">' . $vars['store_url'] . '</a>',
			'showcase'      => $showcase
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareCustomerVoucherTemplate($template, $vars) {
		$showcase = '';

		if ($template['product'] && $template['product_limit'] > 0) {
			$showcase = $this->productsShowcaseSection((int)$vars['order_info']['customer_group_id'], $template['store_id'], $template['product'], $template['product_limit']);
		}

		$find = array(
			'{logo}',
			'{recip_name}',
			'{recip_email}',
			'{date}',
			'{store_name}',
			'{name}',
			'{amount}',
			'{message}',
			'{store_link}',
			'{image}',
			'{code}',
			'{showcase}'
		);

		if (is_file(DIR_IMAGE . $vars['voucher']['image'])) {
			$image = '<img src="' . HTTP_SERVER . 'image/' . STORECODE . '/' . $vars['voucher']['image'] . '" alt="' . $vars['voucher']['name'] . '" title="' . $vars['voucher']['name'] . '" />';
		} else {
			$image = '';
		}

		$replace = array(
			'logo'        => $this->logo,
			'recip_name'  => $vars['voucher']['to_name'],
			'recip_email' => $vars['voucher']['to_email'],
			'date'        => date($this->cfg['custom_email_templates_date_format'], time()),
			'store_name'  => $vars['order_info']['store_name'],
			'name'        => $vars['voucher']['name'],
			'amount'      => $this->sgl->get('currency')->format($vars['voucher']['amount'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
			'message'     => $vars['voucher']['message'],
			'store_link'  => '<a href="' . $vars['order_info']['store_url'] . '">' . $vars['order_info']['store_url'] . '</a>',
			'image'       => $image,
			'code'        => $vars['voucher']['code'],
			'showcase'    => $showcase
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function preparecustomerbalanceTemplate($template, $vars) {
		$showcase = '';
		if ($template['product'] && $template['product_limit'] > 0) {
			$showcase = $this->productsShowcaseSection((int)$vars['order_info']['customer_group_id'], $template['store_id'], $template['product'], $template['product_limit']);
		}

		$find = array(
			'{logo}',
			'{date}',
			'{store_name}',
			'{customer_first_name}',
			'{customer_last_name}',
			'{customer_email}',
			'{customer_phone}',
			'{amount}',
			'{total}',
			'{message}',
			'{showcase}'
		);

		$replace = array(
			'logo'        => $this->logo,
			'date'        => date($this->cfg['custom_email_templates_date_format'], time()),
			'store_name'  => $vars['store_name'],
			'customer_first_name'        => $vars['customer_info']['firstname'],
			'customer_last_name'        => $vars['customer_info']['lastname'],
			'customer_email'        => $vars['customer_info']['email'],
			'customer_phone'        => $vars['customer_info']['telephone'],
			'amount'      => $vars['amout_format'],
			'total'      => $vars['totlat_format'],
			'message'     => $vars['voucher']['message'],
			'store_link'  => '<a href="' . $vars['order_info']['store_url'] . '">' . $vars['order_info']['store_url'] . '</a>',
			'image'       => $image,
			'code'        => $vars['voucher']['code'],
			'showcase'    => $showcase
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareAffiliateRegisterTemplate($template, $vars) {

		$store_name = $this->sgl->get('config')->get('config_name');
		if(is_array($store_name)){
			$store_name = isset($store_name[$this->languageCode]) ? $store_name[$this->languageCode] : '';
		}

		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{date}',
			'{store_name}',
			'{email}',
			'{password}',
			'{affiliate_code}',
			'{account_link}'
		);

		$replace = array(
			'logo'          => $this->logo,
			'firstname'      => $vars['data']['firstname'],
			'lastname'       => $vars['data']['lastname'],
			'date'           => date($this->cfg['custom_email_templates_date_format'], time()),
			'store_name'     => $store_name,
			'email'          => $vars['data']['email'],
			'password'       => $vars['data']['password'],
			'affiliate_code' => $vars['salt'],
			'account_link'   => '<a href="' . $this->sgl->get('url')->link('affiliate/login', '', 'SSL') . '">' . $this->sgl->get('url')->link('affiliate/login', '', 'SSL') . '</a>'
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareAffiliateRegisterApprovalTemplate($template, $vars) {
		$store_name = $this->sgl->get('config')->get('config_name');
		if(is_array($store_name)){
			$store_name = isset($store_name[$this->languageCode]) ? $store_name[$this->languageCode] : '';
		}

		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{date}',
			'{store_name}',
			'{email}',
			'{password}',
			'{affiliate_code}',
			'{account_link}'
		);

		$replace = array(
			'logo'          => $this->logo,
			'firstname'      => $vars['data']['firstname'],
			'lastname'       => $vars['data']['lastname'],
			'date'           => date($this->cfg['custom_email_templates_date_format'], time()),
			'store_name'     => $store_name,
			'email'          => $vars['data']['email'],
			'password'       => $vars['data']['password'],
			'affiliate_code' => $vars['salt'],
			'account_link'   => '<a href="' . $this->sgl->get('url')->link('affiliate/login', '', 'SSL') . '">' . $this->sgl->get('url')->link('affiliate/login', '', 'SSL') . '</a>'
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareAffiliatePasswordResetTemplate($template, $vars) {
		$affiliate_info = $this->getAffiliateByEmail($vars['cet']->sgl->get('request')->post['email']);

		$store_name = $this->sgl->get('config')->get('config_name');
		if(is_array($store_name)){
			$store_name = isset($store_name[$this->languageCode]) ? $store_name[$this->languageCode] : '';
		}
		
		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{date}',
			'{store_name}',
			'{email}',
			'{password}',
			'{account_link}'
		);

		$replace = array(
			'logo'          => $this->logo,
			'firstname'     => ($affiliate_info) ? $affiliate_info['firstname'] : '',
			'lastname'      => ($affiliate_info) ? $affiliate_info['lastname'] : '',
			'date'          => date($this->cfg['custom_email_templates_date_format'], time()),
			'store_name'    => $store_name,
			'email'         => $vars['cet']->sgl->get('request')->post['email'],
			'password'      => $vars['password'],
			'account_link'  => '<a href="' . $this->sgl->get('url')->link('account/login', '', 'SSL') . '">' . $this->sgl->get('url')->link('account/login', '', 'SSL') . '</a>'
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareAffiliateApproveTemplate($template, $vars) {
		$host = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTP_CATALOG;

		$store_name = $this->sgl->get('config')->get('config_name');
		if(is_array($store_name)){
			$store_name = isset($store_name[$this->languageCode]) ? $store_name[$this->languageCode] : '';
		}
		
		if ($vars['affiliate_info']['store_id']) {
			$store_info = $this->getStore($vars['affiliate_info']['store_id']);

			if ($store_info) {
				$host = ($store_info['ssl']) ? $store_info['ssl'] : $store_info['url'];
				$store_name = $store_info['name'];
			}
		}

		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{date}',
			'{store_name}',
			'{email}',
			'{account_link}'
		);

		$replace = array(
			'logo'          => $this->logo,
			'firstname'     => $vars['affiliate_info']['firstname'],
			'lastname'      => $vars['affiliate_info']['lastname'],
			'date'          => date($this->cfg['custom_email_templates_date_format'], time()),
			'store_name'    => $store_name,
			'email'         => $vars['affiliate_info']['email'],
			'account_link'  => '<a href="' . $host . 'index.php?route=affiliate/login">' . $host . 'index.php?route=affiliate/login</a>'
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareAffiliateAddTransactionTemplate($template, $vars) {
		if (defined('HTTP_CATALOG')) {
			$host = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTP_CATALOG;
		} else {
			$host = defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTP_SERVER;
		}

		$store_name = $this->sgl->get('config')->get('config_name');
		if(is_array($store_name)){
			$store_name = isset($store_name[$this->languageCode]) ? $store_name[$this->languageCode] : '';
		}

		if ($vars['affiliate_info']['store_id']) {
			$store_info = $this->getStore($vars['affiliate_info']['store_id']);

			if ($store_info) {
				$host = ($store_info['ssl']) ? $store_info['ssl'] : $store_info['url'];
				$store_name = $store_info['name'];
			}
		}

		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{date}',
			'{store_name}',
			'{description}',
			'{order_id}',
			'{commission}',
			'{account_link}'
		);

		$replace = array(
			'logo'          => $this->logo,
			'firstname'    => $vars['affiliate_info']['firstname'],
			'lastname'     => $vars['affiliate_info']['lastname'],
			'date'         => date($this->cfg['custom_email_templates_date_format'], time()),
			'store_name'   => $store_name,
			'description'  => (isset($vars['description'])) ? $vars['description'] : 'Order ID: #' . $vars['order_id'],
			'order_id'     => $vars['order_id'],
			'commission'   => $this->sgl->get('currency')->format($vars['amount'], $this->cfg['config_currency']),
			'account_link' => '<a href="' . $host . 'index.php?route=affiliate/login">' . $host . 'index.php?route=affiliate/login</a>'
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareContactConfirmationTemplate($template, $vars) {
		$customer_info = $this->getCustomerByEmail($vars['cet']->sgl->get('request')->post['email']);

		$showcase = '';

		if ($template['product'] && $template['product_limit'] > 0) {
			$customer_group_id = (isset($customer_info['customer_group_id'])) ? (int)$customer_info['customer_group_id'] : (int)$this->cfg['config_customer_group_id'];

			$showcase = $this->productsShowcaseSection((int)$customer_group_id, $template['store_id'], $template['product'], $template['product_limit']);
		}

		$find = array(
			'{logo}',
			'{firstname}',
			'{email}',
			'{date}',
			'{enquiry}',
			'{showcase}',
			'{phone}'
		);

		$replace = array(
			'logo'          => $this->logo,
			'firstname' => $vars['cet']->sgl->get('request')->post['name'],
			'email'     => $vars['cet']->sgl->get('request')->post['email'],
			'date'      => date($this->cfg['custom_email_templates_date_format'], time()),
			'enquiry'   => $vars['cet']->sgl->get('request')->post['enquiry'],
			'showcase'  => $showcase,
			'phone'		=> $vars['cet']->sgl->get('request')->post['phone']
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareReviewsAddedTemplate($template, $vars) {
		$product_info = $this->getProduct($vars['product_id']);

		$find = array(
			'{product}',
			'{author}',
			'{date}',
			'{rating}',
			'{review}'
		);

		if (is_array($vars['data']['rating'])) {
			$avg = round(array_sum($vars['data']['rating']) / count($vars['data']['rating']));
		} else {
			$avg = $vars['data']['rating'];
		}

		$replace = array(
			'product'  => ($product_info) ? '<a href="' . $this->sgl->get('url')->link('product/product', 'product_id=' . (int)$vars['product_id'], 'NONSSL') . '">' . $product_info['name'] . '</a>' : '',
			'author'   => $vars['data']['name'],
			'date'     => date($this->cfg['custom_email_templates_date_format'], time()),
			'rating'   => $avg,
			'review'   => $vars['data']['text']
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function prepareCronInvoiceTemplate($template, $vars) {
		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{date}',
			'{order_id}',
			'{invoice_number}'
		);

		$replace = array(
			'logo'          => $this->logo,
			'firstname'      => $vars['order_info']['firstname'],
			'lastname'       => $vars['order_info']['lastname'],
			'date'           => date($this->cfg['custom_email_templates_date_format'], strtotime($vars['order_info']['date_added'])),
			'order_id'       => $vars['order_info']['order_id'],
			'invoice_number' => $vars['order_info']['invoice_prefix'] . $vars['order_info']['invoice_no'],
		);

		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function preparesellerenableTemplate($template, $vars) {
		$showcase = '';

		$find = array(
			'{logo}',
			'{store_url}',
			'{store_name}',
			'{seller_firstname}',
			'{seller_lastname}',
			'{seller_email}',
			'{seller_nickname}',
		);

		$replace = array(
			'logo'                  => $this->logo,
			'store_url'             => $vars['store_url'],
			'store_name'            => $this->sgl->get('config')->get('config_name'),
			'seller_firstname'      => $vars['seller_firstname'],
			'seller_lastname'       => $vars['seller_lastname'],
			'seller_email'          => $vars['seller_email'],
			'seller_nickname'       => $vars['seller_nickname']
		);
		
		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	private function preparesellerdisableTemplate($template, $vars) {
		$showcase = '';

		$find = array(
			'{logo}',
			'{store_url}',
			'{store_name}',
			'{seller_firstname}',
			'{seller_lastname}',
			'{seller_email}',
			'{seller_nickname}',
		);

		$replace = array(
			'logo'                  => $this->logo,
			'store_url'             => $vars['store_url'],
			'store_name'            => $this->sgl->get('config')->get('config_name'),
			'seller_firstname'      => $vars['seller_firstname'],
			'seller_lastname'       => $vars['seller_lastname'],
			'seller_email'          => $vars['seller_email'],
			'seller_nickname'       => $vars['seller_nickname']
		);
		
		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}
	private function prepareproductapproveTemplate($template, $vars) {
		$showcase = '';

		$find = array(
			'{logo}',
			'{store_url}',
			'{seller_firstname}',
			'{seller_lastname}',
			'{seller_email}',
			'{seller_nickname}',
			'{product_name}'
		);

		$replace = array(
			'logo'                  => $this->logo,
			'store_url'             => $vars['store_url'],
			'seller_firstname'      => $vars['seller_firstname'],
			'seller_lastname'       => $vars['seller_lastname'],
			'seller_email'          => $vars['seller_email'],
			'seller_nickname'       => $vars['seller_nickname'],
			'product_name'			=> $vars['product_name']
		);
		
		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}
	private function prepareproductrejectTemplate($template, $vars) {
		$showcase = '';

		$find = array(
			'{logo}',
			'{store_url}',
			'{seller_firstname}',
			'{seller_lastname}',
			'{seller_email}',
			'{seller_nickname}',
			'{product_name}'
		);

		$replace = array(
			'logo'                  => $this->logo,
			'store_url'             => $vars['store_url'],
			'seller_firstname'      => $vars['seller_firstname'],
			'seller_lastname'       => $vars['seller_lastname'],
			'seller_email'          => $vars['seller_email'],
			'seller_nickname'       => $vars['seller_nickname'],
			'product_name'			=> $vars['product_name']
		);
		
		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}
    private function preparesellercontactTemplate($template, $vars) {
        $showcase = '';

        $find = array(
            '{logo}',
            '{firstname}',
            '{lastname}',
            '{store_name}',
            '{client_comment}',
            '{email}',
            '{seller_name}',
            '{seller_email}',
        );

        $replace = array(
            'logo'             => $this->logo,
            'firstname'        => $vars['data']['customer_name'],
            'lastname'        => '',
            'store_name'       => $this->sgl->get('config')->get('config_name'),
			'client_comment'   => $vars['data']['customer_message'],
            'email'            => $vars['data']['customer_email'],
            'seller_name'      => $vars['data']['addressee'],
            'seller_email'     => $vars['data']['recipients'],
        );

        $subject = trim(str_replace($find, $replace, $template['subject']));
        $message = str_replace($find, $replace, $template['description']);

        return array(
            'subject' => $subject,
            'message' => $message
        );
    }

	private function preparesellerorderTemplate($template, $vars) {

		$showcase = '';
		if ($template['product'] && $template['product_limit'] > 0) {
			$customer_info = $this->getCustomer($vars['order_info']['customer_id']);

			if ($customer_info) {
				$customer_group_id = (int)$customer_info['customer_group_id'];
			} else {
				$customer_group_id = (int)$this->cfg['config_customer_group_id'];
			}

			$showcase = $this->productsShowcaseSection((int)$customer_group_id, $template['store_id'], $template['product'], $template['product_limit']);
		}
		
		if (isset($vars['order_info']['order_status_id'])) {
			$order_status_id = $vars['order_info']['order_status_id'];
		} else {
			$order_status_id = 0;
		}

		if (isset($vars['order_info']['order_id'])) {
			$order_id = $vars['order_info']['order_id'];
		} else {
			$order_id = 0;
		}
		
		$invoice_no = $vars['order_info']['invoice_no'];

		if (isset($this->cfg['custom_email_templates_generate_invoice_number']) && in_array($order_status_id, $this->cfg['custom_email_templates_generate_invoice_number'])) {
			if (!$vars['order_info']['invoice_no'] && $order_status_id) {
				$query = $this->sgl->get('db')->query("SELECT MAX(invoice_no) AS invoice_no FROM `" . DB_PREFIX . "order` WHERE invoice_prefix = '" . $this->sgl->get('db')->escape($vars['order_info']['invoice_prefix']) . "'");

				if ($query->row['invoice_no']) {
					$invoice_no = (int)$query->row['invoice_no'] + 1;
				} else {
					$invoice_no = 1;
				}

				$this->sgl->get('db')->query("UPDATE `" . DB_PREFIX . "order` SET invoice_no = '" . (int)$invoice_no . "', invoice_prefix = '" . $this->sgl->get('db')->escape($vars['order_info']['invoice_prefix']) . "' WHERE order_id = '" . (int)$order_id . "'");
			}
		}

		if (((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'))) {
			if (defined('HTTPS_CATALOG')) {
				$base = HTTPS_CATALOG;
			} elseif (defined('HTTP_CATALOG')) {
				$base = HTTP_CATALOG;
			} else {
				$base = HTTPS_SERVER;
			}
		} else {
			if (defined('HTTP_CATALOG')) {
				$base = HTTP_CATALOG;
			} elseif (defined('HTTP_CATALOG')) {
				$base = HTTP_CATALOG;
			} else {
				$base = HTTP_SERVER;
			}
		}

		$order_status_query = $this->sgl->get('db')->query("SELECT name FROM `" . DB_PREFIX . "order_status` WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$vars['order_info']['language_id'] . "'");

		if ($order_status_query->num_rows) {
			$order_status_name = $order_status_query->row['name'];
		} else {
			$order_status_name = '';
		}

		if ($vars['order_info']['shipping_firstname']) {
			$shipping_address = $vars['order_info']['shipping_firstname'] . ' ' . $vars['order_info']['shipping_lastname'] . '<br />' . (($vars['order_info']['shipping_company']) ? $vars['order_info']['shipping_company'] . '<br />' : '') . '' . $vars['order_info']['shipping_address_1'] . '<br />' . (($vars['order_info']['shipping_address_2']) ? $vars['order_info']['shipping_address_2'] . '<br />' : '') . $vars['order_info']['shipping_postcode'] . ' ' . $vars['order_info']['shipping_city'] . '<br />' . $vars['order_info']['shipping_zone'] . '<br />' . $vars['order_info']['shipping_country'];
		} else {
			$shipping_address = '';
		}

		if ($vars['order_info']['payment_firstname']) {
			if (isset($vars['order_info']['payment_company_id'])) {
				$payment_company_id = $vars['order_info']['payment_company_id'];
			} else {
				$payment_company_id = '';
			}

			if (isset($vars['order_info']['payment_tax_id'])) {
				$payment_tax_id = $vars['order_info']['payment_tax_id'];
			} else {
				$payment_tax_id = '';
			}

			if (isset($this->cfg['custom_email_templates_mlanguage'][$vars['order_info']['language_id']])) {
				$text_tax_id = $this->cfg['custom_email_templates_mlanguage'][$vars['order_info']['language_id']]['tax_id'];
				$text_company_id = $this->cfg['custom_email_templates_mlanguage'][$vars['order_info']['language_id']]['company_id'];
			} else {
				$text_tax_id = '';
				$text_company_id = '';
			}

			$payment_address = $vars['order_info']['payment_firstname'] . ' ' . $vars['order_info']['payment_lastname'] . '<br />' . (($vars['order_info']['payment_company']) ? $vars['order_info']['payment_company'] : '') . '' . $vars['order_info']['payment_address_1'] . '<br />' . (($vars['order_info']['payment_address_2']) ? $vars['order_info']['payment_address_2'] . '<br />' : '') . $vars['order_info']['payment_postcode'] . ' ' . $vars['order_info']['payment_city'] . '<br />' . $vars['order_info']['payment_zone'] . '<br />' . $vars['order_info']['payment_country'] . (($payment_company_id) ? '<br />' . $text_company_id . ' ' . $payment_company_id : '') . (($payment_tax_id) ? '<br />' . $text_tax_id . ' ' . $payment_tax_id: '');
		} else {
			$payment_address = '';
		}

		$order_link = '';

		if ($vars['order_info']['customer_id']) {
			$order_link = $vars['order_info']['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id;
		}

		if (version_compare(VERSION, '2.0') < 0) {
			//nothing
		} else {
			$this->sgl->get('load')->model('tool/upload');
		}

		$admin_folder = '';

		if (defined('HTTP_CATALOG') || defined('HTTPS_CATALOG')) {
			$parts = explode('/', trim(DIR_APPLICATION, '/'));
			$admin_folder = end($parts);
		}

		$this->sgl->get('load')->model('tool/image');
		
		$product_data = array();

		foreach ($vars['order_products'] as $product) {
			$option_data = '';

			$product_info = $this->getProduct($product['product_id']);

			$order_options_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "order_option` WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

			foreach ($order_options_query->rows as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					if (version_compare(VERSION, '2.0') < 0) {
						$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
						$value = (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value);
					} else {
						$upload_info = $this->sgl->get('model_tool_upload')->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}
				}

				$option_data .= '<br /><i>' . $option['name'] . ': ' . $value . '</i>';
			}

			$products_cfg = $this->cfg['custom_email_templates_products_section'];

			if ($product_info && $product_info['image']) {
				if ($products_cfg['column']['image']['image_width'] && $products_cfg['column']['image']['image_height']) {
					$product_image = $this->sgl->get('model_tool_image')->resize($product_info['image'], $products_cfg['column']['image']['image_width'], $products_cfg['column']['image']['image_height']);
				} else {
					$product_image = $this->sgl->get('model_tool_image')->resize($product_info['image'], 80, 80);
				}
			} else {
				$product_image = '';
			}

			if ($admin_folder) {
				$url = str_replace('/' . $admin_folder . '/', '/', $this->sgl->get('url')->link('product/product', 'product_id=' . (int)$product['product_id'], 'NONSSL'));
			} else {
				$url = $this->sgl->get('url')->link('product/product', 'product_id=' . (int)$product['product_id'], 'NONSSL');
			}

			$product_data[] = array(
				'image'       => '<img src="' . str_replace(' ', '%20', $product_image) . '" alt="' . $product['name'] . '" class="product_image" />',
				'product'     => ($products_cfg['column']['product']['link_status']) ? '<a href="' . $url . '">' . $product['name'] . '</a>' : $product['name'],
				'model'       => $product['model'],
				'quantity'    => $product['quantity'],
				'price'       => $this->sgl->get('currency')->format($product['price'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
				'price_gross' => $this->sgl->get('currency')->format(($product['price'] + $product['tax']), $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
				'attribute'   => $this->getProductAttributes($product['product_id'], $vars['order_info']['language_id']),
				'option'      => $option_data,
				'sku'         => $product_info ? $product_info['sku'] : '',
				'upc'         => $product_info ? $product_info['upc'] : '',
				'tax'         => $this->sgl->get('currency')->format($product['tax'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
				'total'       => $this->sgl->get('currency')->format($product['total'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
				'total_gross' => $this->sgl->get('currency')->format($product['total'] + ($product['tax'] * $product['quantity']), $vars['order_info']['currency_code'], $vars['order_info']['currency_value'])
			);
		}

		/*$voucher_data = array();

		$vouchers_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");

		foreach ($vouchers_query->rows as $voucher) {
			$voucher_data[] = array(
				'description' => $voucher['description'],
				'amount'      => $this->sgl->get('currency')->format($voucher['amount'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value'])
			);
		}

		$total_data = array();
		$tax_amount = 0;

		$order_totals_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order ASC");

		foreach ($order_totals_query->rows as $total) {
			if ($total['code'] == 'tax') {
				$tax_rate = preg_replace('#[^0-9\-\.%]+#i', '', $total['title']);

				$total_data[$total['code']][] = array(
					'title'    => $total['title'],
					'tax_rate' => $tax_rate,
					'value'    => $this->sgl->get('currency')->format($total['value'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value'])
				);

				$tax_amount += $total['value'];

			} else if ($total['code'] == 'reward' || $total['code'] == 'earn_point') {

				$total_data[$total['code']][] = array(
					'title'	=>	$total['title'],
					'value' => 	$total['text'],
				);

			} else {
				$total_data[$total['code']][] = array(
					'title' => $total['title'],
					'value' => $this->sgl->get('currency')->format($total['value'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value'])
				);
			}
		}*/

        // delivery slot order data
        // check if delivery slot app installed
        if(\Extension::isInstalled('delivery_slot')){
            $delivery_slot_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "ds_delivery_slot_order` WHERE order_id = '" . (int)$order_id . "'");
            if ($delivery_slot_query->num_rows) {
                $delivery_slot_date = $delivery_slot_query->row['delivery_date'];
                $delivery_slot_time = $delivery_slot_query->row['slot_description'];
            } else {
                $delivery_slot_date = '';
                $delivery_slot_time = '';
            }
        }else {
            $delivery_slot_date = '';
            $delivery_slot_time = '';
        }


        if (isset($vars['data']['comment'])) {
			$comment = $vars['data']['comment'];
		} elseif (isset($vars['comment'])) {
			$comment = $vars['comment'];
		} else {
			$comment = '';
		}

		$find = array(
			'{logo}',
			'{firstname}',
			'{lastname}',
			'{delivery_address}',
			'{shipping_address}',
			'{payment_address}',
			'{order_date}',
			'{products}',
			//'{totals}',
			//'{vouchers}',
			'{showcase}',
			'{date}',
			'{payment}',
			'{shipment}',
			'{order_id}',
			//'{total}',
			'{invoice_number}',
			'{order_link}',
			'{store_url}',
			'{status_name}',
			'{store_name}',
			'{ip}',
			'{comment}',
			//'{sub_total}',
			'{shipping_cost}',
			'{client_comment}',
			//'{taxes}',
			//'{tax_amount}',
			'{email}',
			'{telephone}',
			'{carrier}',
			'{tracking_number}',
			'{carrier_link}',
			'{seller_name}',
			'{seller_email}',
			'{seller_nickname}',
			'{seller_company}',
			'{seller_total_products}',
			'{delivery_slot_date}',
			'{delivery_slot_time}'
		);

		$replace = array(
			'logo'             => $this->logo,
			'firstname'        => $vars['order_info']['firstname'],
			'lastname'         => $vars['order_info']['lastname'],
			'delivery_address' => $shipping_address,
			'shipping_address' => $shipping_address,
			'payment_address'  => $payment_address,
			'order_date'       => date($this->cfg['custom_email_templates_date_format'], strtotime($vars['order_info']['date_added'])),
			'products'         => $this->productsSection($product_data, $voucher_data, $total_data),
			//'totals'           => $this->totalsSection($total_data),
			//'vouchers'         => $this->vouchersSection($voucher_data),
			'showcase'         => $showcase,
			'date'             => date($this->cfg['custom_email_templates_date_format'], time()),
			'payment'          => $vars['order_info']['payment_method'],
			'shipment'         => $vars['order_info']['shipping_method'],
			'order_id'         => $order_id,
			//'total'            => $this->sgl->get('currency')->format($vars['order_info']['total'], $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
			'invoice_number'   => $vars['order_info']['invoice_prefix'] . $invoice_no,
			'order_link'       => $order_link,
			'store_url'        => $vars['order_info']['store_url'],
			'status_name'      => $order_status_name,
			'store_name'       => $vars['order_info']['store_name'],
			'ip'               => $vars['order_info']['ip'],
			'comment'          => $this->commentSection($comment),
			//'sub_total'        => (isset($total_data['sub_total'][0])) ? $total_data['sub_total'][0]['value'] : '',
			'shipping_cost'    => (isset($total_data['shipping'][0]['value'])) ? $total_data['shipping'][0]['value'] : '',
			'client_comment'   => $vars['order_info']['comment'],
			//'taxes'            => (isset($total_data['tax'])) ? $this->taxesSection($total_data['tax']) : '',
			//'tax_amount'       => $this->sgl->get('currency')->format($tax_amount, $vars['order_info']['currency_code'], $vars['order_info']['currency_value']),
			'email'            => $vars['order_info']['email'],
			'telephone'        => $vars['order_info']['telephone'],
			'carrier'          => '',//(isset($trackers['tracker_carrier_name'])) ? $trackers['tracker_carrier_name'] : '',
			'tracking_number'  => '',
			'carrier_href'     => '',
			'seller_name'     => $vars['seller']['name'],
			'seller_email'     => $vars['seller']['c.email'],
			'seller_nickname'     => $vars['seller']['ms.nickname'],
			'seller_company'     => $vars['seller']['ms.company'],
			'seller_total_products'     => $vars['seller']['total_products'],
			'delivery_slot_date'     => $delivery_slot_date,
			'delivery_slot_time'     => $delivery_slot_time
		);
		
		$subject = trim(str_replace($find, $replace, $template['subject']));
		$message = str_replace($find, $replace, $template['description']);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}

	public function invoice($order_info, $history_id = 0) {

                if(\Extension::isInstalled('custom_invoice_template') && $this->sgl->get('config')->get('custom_email_templates_custom_invoice_status') == 1)
                {   
                    $orders[] = $order_info;
                    $languageId = $this->sgl->get('config')->get('config_language_id');
                    $output = $this->renderInvoiceTemplate($orders, $languageId);
                }else{
                    
		$lng2 = $this->loadLanguage('custom_email_templates/invoice.'.$this->sgl->get('language')->get('code'));

		$data['title'] = $data['text_invoice'] = $lng2['text_invoice'];
		$data['lang'] = $this->sgl->get('language')->get('code');
		$data['text_telephone']=$lng2['text_telephone'];
		$data['text_fax']=$lng2['text_fax'];
		$data['text_to']=$lng2['text_to'];
		$data['text_from']=$lng2['text_from'];
		$data['text_ship_to']=$lng2['text_ship_to'];
		$data['text_date_added']=$lng2['text_date_added'];
		$data['text_invoice_no']=$lng2['text_invoice_no'];
		$data['text_order_id']=$lng2['text_order_id'];
		$data['text_payment_method']=$lng2['text_payment_method'];
		$data['text_shipping_method']=$lng2['text_shipping_method'];
		$data['text_total']=$lng2['text_total'];
		//$data['text_paid']=$lng2['text_paid'];
		$data['text_authorized']=$lng2['text_authorized'];
		$data['text_tax_amount']=$lng2['text_tax_amount'];
		
		$data['column_product']=$lng2['column_product'];
		$data['column_model']=$lng2['column_model'];
		$data['column_quantity']=$lng2['column_quantity'];
		$data['column_price']=$lng2['column_price'];
		$data['column_total']=$lng2['column_total'];
		$data['column_tax']=$lng2['column_tax'];


		$this->loadConfig($order_info['store_id']);

		$this->cfg['config_language_id'] = $order_info['language_id'];

		$store_address = nl2br($this->cfg['config_address']);
		$store_email = $this->cfg['config_email'];
		$store_telephone = $this->cfg['config_telephone'];
		$store_fax = $this->cfg['config_fax'];
		$store_owner = $this->cfg['config_owner'];



		if (is_file(DIR_IMAGE . $this->cfg['config_logo'])) {
			$image = DIR_IMAGE. $this->cfg['config_logo'];
		} else {
			$image = '';
		}

		// Read image path, convert to base64 encoding
		$imageData = base64_encode(file_get_contents($image));
		$data['logo'] = 'data:'.mime_content_type($image).';base64,'.$imageData;
		$data['logo_height'] = $this->cfg['config_order_invoice_logo_height'] != "" ? $this->cfg['config_order_invoice_logo_height'] : '30';
		
		$invoice_no = '';

		$invoice_info = $this->sgl->get('db')->query("SELECT invoice_prefix, invoice_no FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_info['order_id'] . "'")->row;

		if ($invoice_info['invoice_no']) {
			$invoice_no = $invoice_info['invoice_prefix'] . $invoice_info['invoice_no'];
		}

		if ($order_info['shipping_firstname']) {
			$shipping_address = $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'] . '<br />' . (($order_info['shipping_company']) ? $order_info['shipping_company'] . '<br />' : '') . '' . $order_info['shipping_address_1'] . '<br />' . (($order_info['shipping_address_2']) ? $order_info['shipping_address_2'] . '<br />' : '') . $order_info['shipping_postcode'] . ' ' . $order_info['shipping_city'] . '<br />' . $order_info['shipping_zone'] . '<br />' . $order_info['shipping_country'];
		} else {
			$shipping_address = '';
		}

		if ($order_info['payment_firstname']) {
			if (isset($order_info['payment_company_id'])) {
				$payment_company_id = $order_info['payment_company_id'];
			} else {
				$payment_company_id = '';
			}

			if (isset($order_info['payment_tax_id'])) {
				$payment_tax_id = $order_info['payment_tax_id'];
			} else {
				$payment_tax_id = '';
			}

			$payment_address = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'] . '<br />' . (($order_info['payment_company']) ? $order_info['payment_company'] : '') . '' . $order_info['payment_address_1'] . '<br />' . (($order_info['payment_address_2']) ? $order_info['payment_address_2'] . '<br />' : '') . $order_info['payment_postcode'] . ' ' . $order_info['payment_city'] . '<br />' . $order_info['payment_zone'] . '<br />' . $order_info['payment_country'] . (($payment_company_id) ? '<br />' . $lng2['text_company_id'] . ' ' . $payment_company_id : '') . (($payment_tax_id) ? '<br />' . $lng2['text_tax_id'] . ' ' . $payment_tax_id : '');
		} else {
			$payment_address = '';
		}

		if (version_compare(VERSION, '2.0') < 0) {
			//nothing
		} else {
			$this->sgl->get('load')->model('tool/upload');
		}

		$product_data = array();

		$products_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_info['order_id'] . "'");

		$this->sgl->get('load')->model('tool/image');

		foreach ($products_query->rows as $product) {
			$option_data = '';

			$product_info = $this->getProduct($product['product_id']);

			$order_options_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "order_option` WHERE order_id = '" . (int)$order_info['order_id'] . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

			foreach ($order_options_query->rows as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					if (version_compare(VERSION, '2.0') < 0) {
						$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
						$value = (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value);
					} else {
						$upload_info = $this->sgl->get('model_tool_upload')->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}
				}

				$option_data .= $option['name'] . ': ' . $value;
			}	

			$products_image_query = $this->sgl->get('db')->query("SELECT image FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product['product_id'] . "'");

			if ($products_image_query->row && $products_image_query->row['image']) {
				$image = $this->sgl->get('model_tool_image')->resize($products_image_query->row['image'], 100, 100);
			} else {
				$image = $this->sgl->get('model_tool_image')->resize('no_image.png', 100, 100);
			}

			$product_data[] = array(
				'image'       => '<img src="' . str_replace(' ', '%20', $image) . '" alt="' . $product['name'] . '" class="product_image" />',
				'name'        => $product['name'],
				'model'       => $product['model'],
				'quantity'    => $product['quantity'],
				'price'       => $this->sgl->get('currency')->formatForInvoice($product['price'], $order_info['currency_code'], $order_info['currency_value']),
				'price_gross' => $this->sgl->get('currency')->formatForInvoice(($product['price'] + $product['tax']), $order_info['currency_code'], $order_info['currency_value']),
				'attribute'   => $this->getProductAttributes($product['product_id'], $order_info['language_id']),
				'option'      => $option_data,
				'sku'         => $product_info ? $product_info['sku'] : '',
				'upc'         => $product_info ? $product_info['upc'] : '',
				'tax'         => $this->sgl->get('currency')->formatForInvoice($product['tax'], $order_info['currency_code'], $order_info['currency_value']),
				'total'       => $this->sgl->get('currency')->formatForInvoice($product['total'], $order_info['currency_code'], $order_info['currency_value']),
				'total_gross' => $this->sgl->get('currency')->formatForInvoice($product['total'] + ($product['tax'] * $product['quantity']), $order_info['currency_code'], $order_info['currency_value'])
			);
		}

		$voucher_data = array();

		$vouchers_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_info['order_id'] . "'");

		foreach ($vouchers_query->rows as $voucher) {
			$voucher_data[] = array(
				'description' => $voucher['description'],
				'amount'      => $this->sgl->get('currency')->formatForInvoice($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
			);
		}

		$total_data = array();
		$tax_amount = 0;

		$order_totals_query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_info['order_id'] . "' ORDER BY sort_order ASC");

		foreach ($order_totals_query->rows as $total) {
			if ($total['code'] == 'tax') {
				$tax_rate = preg_replace('#[^0-9\-\.%]+#i', '', $total['title']);

				$total_data[$total['code']][] = array(
					'title'    => $total['title'],
					'tax_rate' => $tax_rate,
					'value'    => $this->sgl->get('currency')->formatForInvoice($total['value'], $order_info['currency_code'], $order_info['currency_value'])
				);

				$tax_amount += $total['value'];

			} else if ($total['code'] == 'reward' || $total['code'] == 'earn_point') {

				$total_data[$total['code']][] = array(
					'title'	=>	$total['title'],
					'value' => 	$total['text'],
				);

			} else {
				$total_data[$total['code']][] = array(
					'title' => $total['title'],
					'value' => $this->sgl->get('currency')->formatForInvoice($total['value'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}
		}

		$invoice_date = date("Y-m-d H:i:s");

		$data['order_info'] = array(
			'all'              => $order_info,
			'order_id'	       => $order_info['order_id'],
			'invoice_no'       => $invoice_no,
			'date_added'       => date($this->cfg['custom_email_templates_date_format'], strtotime($order_info['date_added'])),
			'invoice_date'     => date($this->cfg['custom_email_templates_date_format'], strtotime($invoice_date)),
			'store_name'       => $order_info['store_name'],
			'store_url'        => rtrim($order_info['store_url'], '/'),
			'store_address'    => nl2br($store_address),
			'store_email'      => $store_email,
			'store_telephone'  => $store_telephone,
			'store_fax'        => $store_fax,
			'store_owner'      => $store_owner,
			'email'            => $order_info['email'],
			'telephone'        => $order_info['telephone'],
			'shipping_address' => $shipping_address,
			'shipping_method'  => $order_info['shipping_method'],
			'payment_address'  => $payment_address,
			'payment_method'   => $order_info['payment_method'],
			'products'         => $product_data,
			'vouchers'         => $voucher_data,
			'totals'           => $total_data,
			'tax_amount'       => $this->sgl->get('currency')->formatForInvoice($tax_amount, $order_info['currency_code'], $order_info['currency_value']),
			'total'            => $this->sgl->get('currency')->formatForInvoice($order_info['total'], $order_info['currency_code'], $order_info['currency_value']),
            'comment'          => nl2br($order_info['comment'])
		);

		extract($data);

		ob_start();
                    header('Content-Type: text/html; charset=utf-8');
                    try {
                        $file_path_ar = ((defined('DIR_CATALOG')) ? DIR_CATALOG . 'view/theme/' : DIR_TEMPLATE) . 'default/template/custom_email_templates/invoice/ar_' . ($this->cfg['custom_email_templates_invoice_template'] ?? 'default.tpl');
                        $file_path_en = ((defined('DIR_CATALOG')) ? DIR_CATALOG . 'view/theme/' : DIR_TEMPLATE) . 'default/template/custom_email_templates/invoice/' . ($this->cfg['custom_email_templates_invoice_template'] ?? 'default.tpl');
                        if ($this->sgl->get('language')->get('code') == 'ar' && file_exists($file_path_ar))
                            require($file_path_ar);
                        else if (file_exists($file_path_en))
                            require($file_path_en);
                    } catch (Exception $e) {
                        file_put_contents(BASE_STORE_DIR . 'logs/errors.txt', $e, FILE_APPEND);
                    }

		$output = ob_get_contents();

		ob_end_clean();
        }

		foreach (glob(DIR_DOWNLOAD . 'invoice_00*_*.pdf') as $old_invoice) {
			if (file_exists($old_invoice)) {
				unlink($old_invoice);
			}
		}

		//Generate PDF for the order invoice 
		$filename = DIR_DOWNLOAD . 'invoice_00' . $order_info['order_id'] . '_' . strtotime($invoice_date) . '.pdf';
		$mpdf = new \Mpdf\Mpdf(['tempDir' => TEMP_DIR_PATH]);
		$mpdf->autoScriptToLang = true;
		$mpdf->autoLangToFont = true;
        $mpdf->WriteHTML('table, tbody, td, tr, th {border: 1px solid #000;}', HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($output);
		$mpdf->Output($filename);

		$this->sgl->get('db')->query("UPDATE `" . DB_PREFIX . "order` SET invoice_sent = '1', invoice_date = '" . $this->sgl->get('db')->escape($invoice_date) . "' WHERE order_id = '" . (int)$order_info['order_id'] . "'");
		$this->sgl->get('db')->query("INSERT INTO `" . DB_PREFIX . "cet_attachment` SET history_id = '" . (int)$history_id . "', file = '" . $this->sgl->get('db')->escape(basename($filename)) . "', date_added = NOW()");

		return $filename;
	}

	private function productsSection($products, $vouchers = array(), $totals = array()) {

		$html = '';
		// set Default values
        $border_size = ($this->cfg['custom_email_templates_table']['border_size'])?: '1px ';
        $padding_top = ($this->cfg['custom_email_templates_table']['padding_top']) ?: '1px ';
        $padding_right= ($this->cfg['custom_email_templates_table']['padding_right'])?: '1px ';
        $padding_left= ($this->cfg['custom_email_templates_table']['padding_left']) ?: '1px ';
        $padding_bottom = ($this->cfg['custom_email_templates_table']['padding_bottom']) ?: '1px ';
        $border_color = ($this->cfg['custom_email_templates_table']['border_color']) ?:  ' black ';
        $border_style = ($this->cfg['custom_email_templates_table']['border_style']) ?: 'solid ';


		$css = 'padding:' . $padding_top . ' ' . $padding_right . ' ' . $padding_bottom . ' ' . $padding_left . ';';
		$css .= 'border:' . $border_size . ' ' . $border_style . ' ' . $border_color . ';';

		$products_cfg = $this->cfg['custom_email_templates_products_section'];

		$html = '<table style="width:100%;padding:0px;margin:0px;' . $css . 'border-collapse:collapse;" class="products-section">' . "\n";
		$html .= '  <tr class="table-heading">' . "\n";
        $columnLanguage = $this->loadLanguage('custom_email_templates/invoice.'.$this->sgl->get('language')->get('code'));
		foreach ($products_cfg['column'] as $key => $column) {
			if ($column['status']) {
                $column_name = $key;
                if (isset($column['text'][$this->cfg['config_language_id']])){
                    $column_name = $column['text'][$this->cfg['config_language_id']];
                }else if (isset($columnLanguage['column_'.$key])){
                    $column_name = $columnLanguage['column_'.$key];
                }
				$html .= '<td class="product-td-' . $key . '" style="background:' . $this->cfg['custom_email_templates_table']['background'] . ';text-align:' . $column['align'] . ';font-size:' . $this->cfg['custom_email_templates_table']['font_size'] . ';color:' . $this->cfg['custom_email_templates_table']['font_color'] . ';font-weight:' . $this->cfg['custom_email_templates_table']['font_weight'] . ';' . $css . 'white-space:nowrap;';

				if ($key == 'image') {
					$html .= 'width:50px;';
				}

				$html .=  '">' . $column_name . '</td>' . "\n";
			}
		}

		$html .= '  </tr>' . "\n";

		$col = 1;

		foreach ($products_cfg['column'] as $key => $column) {
			if ($column['status']) {
				$col++;
			}
		}

		if ($products) {
			foreach ($products as $product) {
				$html .= '<tr>' . "\n";
                $stringData = "";
				foreach ($products_cfg['column'] as $key => $column) {
					if ($column['status']) {
						$html .= '  <td class="product-td-' . $key . '" style="text-align:' . $column['align'] . ';' . $css . ';vertical-align:middle;">' . "\n";

						if ($key == 'attribute') {
							foreach ($product['attribute'] as $attribute_group) {
								$html .= '<b>' . $attribute_group['name'] . '</b><br />' . "\n";

								foreach ($attribute_group['attribute'] as $attribute) {
									$html .= $attribute['name'] . ': ' . $attribute['text'] . '<br />' . "\n";
								}
							}
						} else {
							$html .= $product[$key];
						}

                        $stringData .= $key." ";
						$html .= '</td>' . "\n";
					}
				}

				$html .= '</tr>' . "\n";
                $html .= '<tr><td>Product Code: ';
				if (is_array($product['product_code'])) {
				    foreach ($product['product_code'] as $product_code_data){
                        $html .= "<p>".$product_code_data['code'] . "</p>";
                    }

                    $html .= '</td><tr>' . "\n";
				}

			}

		}

		if ($vouchers) {
			foreach ($vouchers as $voucher) {
				$html .= '<tr>' . "\n";

				foreach ($products_cfg['column'] as $key => $column) {
					if ($column['status']) {
						if ($key == 'image') {
							$html .= '<td class="product-td-' . $key . '" style="' . $css . 'vertical-align:middle;text-align:' . $column['align'] . ';"></td>' . "\n";
						} elseif ($key == 'product') {
							$html .= '<td class="product-td-' . $key . '" style="' . $css . 'vertical-align:middle;text-align:' . $column['align'] . ';">';
							$html .= $voucher['description'];
							$html .= '</td>' . "\n";
						} elseif ($key == 'quantity') {
							$html .= '<td class="product-td-' . $key . '" style="' . $css . 'vertical-align:middle;text-align:' . $column['align'] . ';">1</td>' . "\n";
						} elseif (in_array($key, array('price', 'price_gross', 'total', 'total_gross'))) {
							$html .= '<td class="product-td-'. $key . '" style="' . $css . 'vertical-align:middle;text-align:' . $column['align'] . ';">';
							$html .= $voucher['amount'];
							$html .= '</td>' . "\n";
						} else {
							$html .= '<td class="product-td-' . $key . '" style="' . $css . 'vertical-align:middle;text-align:' . $column['align'] . ';"></td>' . "\n";
						}
					}
				}

				$html .= '</tr>' . "\n";
			}
		}

		if ($products_cfg['totals_status']) {
			foreach ($totals as $totals2) {
				foreach ($totals2 as $total) {
					$html .= '<tr>' . "\n";
					$html .= '  <td colspan="' . ($col - 2) . '" style="text-align:right;' . $css . 'font-weight:bold;">' . $total['title'] . '</td><td style="' . $css . 'text-align:' . $products_cfg['column']['total']['align'] . ';white-space:nowrap;">' . $total['value'] . '</td>' . "\n";
					$html .= '</tr>' . "\n";
				}
			}
		}

		$html .= '</table>' . "\n";

		return $html;
	}

	private function totalsSection($totals) {
		$html = '';

		$css = 'padding:' . $this->cfg['custom_email_templates_table']['padding_top'] . ' ' . $this->cfg['custom_email_templates_table']['padding_right'] . ' ' . $this->cfg['custom_email_templates_table']['padding_bottom'] . ' ' . $this->cfg['custom_email_templates_table']['padding_left'] . ';';
		$css .= 'border:' . $this->cfg['custom_email_templates_table']['border_size'] . ' ' . $this->cfg['custom_email_templates_table']['border_style'] . ' ' . $this->cfg['custom_email_templates_table']['border_color'] . ';';
        $textAlign = $this->getLanguageDirection() == 'rtl' ? 'right' : 'left';
		if ($totals) {
			$html = '<table style="width:100%;text-align: ' . $textAlign . ' ;padding:0px;margin:0px;' . $css . 'border-collapse:collapse;" class="totals-section">' . "\n";

			foreach ($totals as $totals2) {
				foreach ($totals2 as $total) {
					$html .= '<tr>' . "\n";
					$html .= '  <td style="text-align:' . $this->cfg['custom_email_templates_totals_section']['title']['align'] . ';' . $css . 'font-weight:bold;">' . $total['title'] . '</td><td style="' . $css . 'text-align:' . $this->cfg['custom_email_templates_totals_section']['amount']['align'] . ';white-space:nowrap;">' . $total['value'] . '</td>' . "\n";
					$html .= '</tr>' . "\n";
				}
			}

			$html .= '</table>' . "\n";
		}

		return $html;
	}

	private function vouchersSection($vouchers) {
		$html = '';

		$css = 'padding:' . $this->cfg['custom_email_templates_table']['padding_top'] . ' ' . $this->cfg['custom_email_templates_table']['padding_right'] . ' ' . $this->cfg['custom_email_templates_table']['padding_bottom'] . ' ' . $this->cfg['custom_email_templates_table']['padding_left'] . ';';
		$css .= 'border:' . $this->cfg['custom_email_templates_table']['border_size'] . ' ' . $this->cfg['custom_email_templates_table']['border_style'] . ' ' . $this->cfg['custom_email_templates_table']['border_color'] . ';';

		if ($vouchers) {
			$html = '<table style="width:100%;text-align:left;padding:0px;margin:0px;' . $css . 'border-collapse:collapse;" class="vouchers-section">' . "\n";

			foreach ($vouchers as $voucher) {
				$html .= '<tr>' . "\n";
				$html .= '  <td style="' . $css . 'text-align:' . $this->cfg['custom_email_templates_products_section']['column']['product']['align'] . ';">' . $voucher['description'] . '</td><td style="' . $css . 'text-align:' . $this->cfg['custom_email_templates_products_section']['column']['total']['align'] . ';white-space:nowrap;">' . $voucher['amount'] . '</td>' . "\n";
				$html .= '</tr>' . "\n";
			}

			$html .= '</table>' . "\n";
		}

		return $html;
	}

	private function taxesSection($taxes) {
		$html = '';

		$css = 'padding:' . $this->cfg['custom_email_templates_table']['padding_top'] . ' ' . $this->cfg['custom_email_templates_table']['padding_right'] . ' ' . $this->cfg['custom_email_templates_table']['padding_bottom'] . ' ' . $this->cfg['custom_email_templates_table']['padding_left'] . ';';
		$css .= 'border:' . $this->cfg['custom_email_templates_table']['border_size'] . ' ' . $this->cfg['custom_email_templates_table']['border_style'] . ' ' . $this->cfg['custom_email_templates_table']['border_color'] . ';';

		if ($taxes) {
			$taxes_cfg = $this->cfg['custom_email_templates_taxes_section'];
			$column_rate_name = isset($taxes_cfg['rate']['text'][$this->cfg['config_language_id']]) ? $taxes_cfg['rate']['text'][$this->cfg['config_language_id']] : '';
			$column_tax_name = isset($taxes_cfg['amount']['text'][$this->cfg['config_language_id']]) ? $taxes_cfg['amount']['text'][$this->cfg['config_language_id']] : '';

			$html = '<table style="width:100%;text-align:left;padding:0px;margin:0px;' . $css . 'border-collapse:collapse;" class="taxes-section">' . "\n";

			$html .= '  <tr class="table-heading">' . "\n";
			$html .= '    <td style="' . $css . 'background:' . $this->cfg['custom_email_templates_table']['background'] . ';"></td><td style="' . $css . 'background:' . $this->cfg['custom_email_templates_table']['background'] . ';font-size:' . $this->cfg['custom_email_templates_table']['font_size'] . ';color:' . $this->cfg['custom_email_templates_table']['font_color'] . ';font-weight:' . $this->cfg['custom_email_templates_table']['font_weight'] . ';white-space:nowrap;text-align:' . $taxes_cfg['rate']['align'] . ';">' . $column_rate_name . '</td><td style="' . $css . 'font-size:' . $this->cfg['custom_email_templates_table']['font_size'] . ';color:' . $this->cfg['custom_email_templates_table']['font_color'] . ';font-weight:' . $this->cfg['custom_email_templates_table']['font_weight'] . ';white-space:nowrap;text-align:' . $taxes_cfg['amount']['align'] . ';">' . $column_tax_name . '</td>' . "\n";
			$html .= '  </tr>' . "\n";

			foreach ($taxes as $tax) {
				$html .= '<tr>' . "\n";
				$html .= '  <td style="' . $css . 'font-weight:bold;text-align:' . $taxes_cfg['title']['align'] . ';">' . $tax['title'] . '</td><td style="' . $css . 'text-align:' . $taxes_cfg['rate']['align'] . ';white-space:nowrap;">' . $tax['tax_rate'] . '</td><td style="' . $css . 'text-align:' . $taxes_cfg['amount']['align'] . ';white-space:nowrap;">' . $tax['value'] . '</td>' . "\n";
				$html .= '</tr>' . "\n";
			}

			$html .= '</table>' . "\n";
		}

		return $html;
	}

	private function returnsSection($returns) {
		$html = '';

		$css = 'padding:' . $this->cfg['custom_email_templates_table']['padding_top'] . ' ' . $this->cfg['custom_email_templates_table']['padding_right'] . ' ' . $this->cfg['custom_email_templates_table']['padding_bottom'] . ' ' . $this->cfg['custom_email_templates_table']['padding_left'] . ';';
		$css .= 'border:' . $this->cfg['custom_email_templates_table']['border_size'] . ' ' . $this->cfg['custom_email_templates_table']['border_style'] . ' ' . $this->cfg['custom_email_templates_table']['border_color'] . ';';

		$returns_cfg = $this->cfg['custom_email_templates_returns_section'];

		$html = '<table style="width:100%;padding:0px;margin:0px;' . $css . 'border-collapse:collapse;" class="returns-section">' . "\n";
		$html .= '  <tr class="table-heading">' . "\n";

		foreach ($returns_cfg['column'] as $key => $column) {
			if ($column['status'] && $key != 'comment') {
				if ($key == 'action' && (isset($returns[0]['action']) && !$returns[0]['action'])) {
					continue;
				}

				$column_name =  isset($column['text'][$this->cfg['config_language_id']]) ? $column['text'][$this->cfg['config_language_id']] : '';

				$html .= '<td class="return-td-' . $key . '" style="background:' . $this->cfg['custom_email_templates_table']['background'] . ';text-align:' . $column['align'] . ';font-size:' . $this->cfg['custom_email_templates_table']['font_size'] . ';color:' . $this->cfg['custom_email_templates_table']['font_color'] . ';font-weight:' . $this->cfg['custom_email_templates_table']['font_weight'] . ';' . $css . 'white-space:nowrap;">' . $column_name . '</td>' . "\n";
			}
		}

		$html .= '  </tr>' . "\n";

		$col = 0;

		foreach ($returns_cfg['column'] as $key => $column) {
			if ($column['status'] && $key != 'comment') {
				if ($key == 'action' && (isset($returns[0]['action']) && !$returns[0]['action'])) {
					continue;
				}

				$col++;
			}
		}

		if ($returns) {
			foreach ($returns as $return) {
				$html .= '<tr>' . "\n";

				foreach ($returns_cfg['column'] as $key => $column) {
					if ($column['status'] && $key != 'comment') {
						if ($key == 'action' && (isset($return['action']) && !$return['action'])) {
							continue;
						}

						$html .= '  <td class="return-td-' . $key . '" style="text-align:' . $column['align'] . ';' . $css . ';vertical-align:middle;">' . $return[$key] . '</td>' . "\n";
					}
				}

				$html .= '</tr>' . "\n";

				if (version_compare(VERSION, '1.5.1.3') <= 0) {
					if ($returns_cfg['column']['comment']['status']) {
						$column_name =  isset($returns_cfg['column']['comment']['text'][$this->cfg['config_language_id']]) ? $returns_cfg['column']['comment']['text'][$this->cfg['config_language_id']] : '';

						$html .= '<tr class="table-heading">' . "\n";
						$html .= '  <td colspan="' . $col . '" class="return-td-comment" style="background:' . $this->cfg['custom_email_templates_table']['background'] . ';text-align:' . $returns_cfg['column']['comment']['align'] . ';font-size:' . $this->cfg['custom_email_templates_table']['font_size'] . ';color:' . $this->cfg['custom_email_templates_table']['font_color'] . ';font-weight:' . $this->cfg['custom_email_templates_table']['font_weight'] . ';' . $css . 'white-space:nowrap;">' . $column_name . '</td>' . "\n";
						$html .= '</tr>' . "\n";
						$html .= '<tr>' . "\n";
						$html .= '  <td colspan="' . $col . '" class="return-td-comment" style="text-align:' . $returns_cfg['column']['comment']['align'] . ';' . $css . ';vertical-align:middle;">' . $return['comment'] . '</td>' . "\n";
						$html .= '</tr>' . "\n";
					}
				}
			}
		}

		$html .= '</table>' . "\n";

		return $html;
	}

	private function commentSection($comment) {
		$html = '';

		$css = 'padding:' . $this->cfg['custom_email_templates_table']['padding_top'] . ' ' . $this->cfg['custom_email_templates_table']['padding_right'] . ' ' . $this->cfg['custom_email_templates_table']['padding_bottom'] . ' ' . $this->cfg['custom_email_templates_table']['padding_left'] . ';';
		$css .= 'border:' . $this->cfg['custom_email_templates_table']['border_size'] . ' ' . $this->cfg['custom_email_templates_table']['border_style'] . ' ' . $this->cfg['custom_email_templates_table']['border_color'] . ';';

		if ($comment) {
			$comment_cfg = $this->cfg['custom_email_templates_comment_section'];
			$column_name = isset($comment_cfg['text'][$this->cfg['config_language_id']]) ? $comment_cfg['text'][$this->cfg['config_language_id']] : '';

			if ($comment_cfg['table']) {
				$html = '<table style="width:100%;text-align:left;padding:0px;margin:0px;' . $css . 'border-collapse:collapse;" class="comment-section">' . "\n";
				$html .= '  <tr class="table-heading">' . "\n";
				$html .= '    <td style="' . $css . 'font-size:' . $this->cfg['custom_email_templates_table']['font_size'] . ';color:' . $this->cfg['custom_email_templates_table']['font_color'] . ';font-weight:' . $this->cfg['custom_email_templates_table']['font_weight'] . ';white-space:nowrap;text-align:' . $comment_cfg['align'] . ';">' . $column_name . '</td>' . "\n";
				$html .= '  </tr>' . "\n";
				$html .= '  <tr>' . "\n";
				$html .= '    <td style="' . $css . 'font-weight:normal;text-align:' . $comment_cfg['align'] . ';">' . nl2br($comment) . '</td>' . "\n";
				$html .= '  </tr>' . "\n";
				$html .= '</table>' . "\n";
			} else {
				$html = nl2br($comment) . "\n";
			}
		}

		return $html;
	}

	private function productsShowcaseSection($customer_group_id, $store_id, $type, $limit) {
		$html = "";

		if ($type == '1') {//latest
			$query = $this->sgl->get('db')->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$store_id . "' ORDER BY p.date_added DESC LIMIT " . (int)$limit);
		} elseif ($type == '2') {//bestseller
			$query = $this->sgl->get('db')->query("SELECT op.product_id, SUM(op.quantity) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$store_id . "' GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit);
		} elseif ($type == '3') {//most viewed
			$query = $this->sgl->get('db')->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$store_id . "' ORDER BY p.viewed DESC, p.date_added DESC LIMIT " . (int)$limit);
		} else {//in promotion
			$query = $this->sgl->get('db')->query("SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$store_id . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start < NOW()) AND (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end > NOW())) GROUP BY ps.product_id ORDER BY p.sort_order LIMIT " . (int)$limit);
		}

		$product_data = array();

		$showcase_cfg = $this->cfg['custom_email_templates_showcase'];
		$button_text = isset($showcase_cfg['text'][$this->cfg['config_language_id']]) ? $showcase_cfg['text'][$this->cfg['config_language_id']] : '';

		$admin_folder = '';

		if (defined('HTTP_CATALOG') || defined('HTTPS_CATALOG')) {
			$parts = explode('/', trim(DIR_APPLICATION, '/'));
			$admin_folder = end($parts);
		}

		foreach ($query->rows as $result) {
			$product_info = $this->getProduct($result['product_id']);

			if ($admin_folder) {
				$url = str_replace('/' . $admin_folder . '/', '/', $this->sgl->get('url')->link('product/product', 'product_id=' . (int)$product_info['product_id'], 'NONSSL'));
			} else {
				$url = $this->sgl->get('url')->link('product/product', 'product_id=' . (int)$product_info['product_id'], 'NONSSL');
			}

			$store_info = $this->getStore($store_id);

			if ($store_info) {
				$host = $store_info['url'];

				if (substr($host, -1) != '/') {
					$host .= '/';
				}

				$url = str_replace($this->sgl->get('config')->get('config_url'), $host, $url);
			}

			$width = $showcase_cfg['product_image_width'] ? (int)$showcase_cfg['product_image_width'] : 120;
			$height = $showcase_cfg['product_image_height'] ? (int)$showcase_cfg['product_image_height'] : 120;

			$this->sgl->get('load')->model('tool/image');

			if ($product_info['image']) {
				$product_image = $this->sgl->get('model_tool_image')->resize($product_info['image'], $width, $height);
			} else {
				$product_image = $this->sgl->get('model_tool_image')->resize('no_image.png', $width, $height);
			}

			$html .= '<div style="display:block;width:100%;text-align:left;overflow:auto">' . "\n";
			$html .= '  <div style="padding:16px;clear:both;">' . "\n";
			$html .= '    <img src="' . $product_image . '" alt="' . $product_info['name'] . '" title="' . $product_info['name'] . '" style="border:none;float:left;padding:0 15px 0 0;" />' . "\n";
			$html .= '    <div style="margin-left:130px;min-height:110px;padding-left:15px;">' . "\n";
			$html .= '      <div style="font-weight:' . $showcase_cfg['product_name_weight'] . ';font-size:' . $showcase_cfg['product_name_size'] . ';margin:0px 0 13px 0;color:' . $showcase_cfg['product_name_color'] . ';">' . $product_info['name'] . '</div>' . "\n";
			$html .= '      <div style="font-weight:' . $showcase_cfg['product_description_weight'] . ';font-size:' . $showcase_cfg['product_description_size'] . ';margin:0px 0 12px 0;color:' . $showcase_cfg['product_description_color'] . ';">' . utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, 140) . '..</div>' . "\n";
			$html .= '      <div style=""><a href="' . $url . '" style="text-decoration:none;display:inline-block;font-size:' . $showcase_cfg['button_size'] . ';background:' . $showcase_cfg['button_background'] . ';color:' . $showcase_cfg['button_color'] . ';padding:4px 7px;border-radius:4px;">' . $button_text . '</a></div>' . "\n";
			$html .= '    </div>' . "\n";
			$html .= '  </div>' . "\n";
			$html .= '</div>' . "\n";
		}

		return $html;
	}

	private function disableDefaultAdminNotifications($store_id) {
		$this->sgl->get('db')->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '0' WHERE `key` = 'config_order_mail' AND store_id = '" . (int)$store_id . "'");
		$this->sgl->get('db')->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '0' WHERE `key` = 'config_account_mail' AND store_id = '" . (int)$store_id . "'");
		$this->sgl->get('db')->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '0' WHERE `key` = 'config_affiliate_mail' AND store_id = '" . (int)$store_id . "'");
	}

	private function getProduct($product_id) {
		$query = $this->sgl->get('db')->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->cfg['config_language_id'] . "'");

		return $query->row;
	}

	private function getProductAttributes($product_id, $language_id) {
		$product_attribute_group_data = array();

		$product_attribute_group_query = $this->sgl->get('db')->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$language_id . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

		foreach ($product_attribute_group_query->rows as $product_attribute_group) {
			$product_attribute_data = array();

			$product_attribute_query = $this->sgl->get('db')->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$language_id . "' AND pa.language_id = '" . (int)$language_id . "' ORDER BY a.sort_order, ad.name");

			foreach ($product_attribute_query->rows as $product_attribute) {
				$product_attribute_data[] = array(
					'attribute_id' => $product_attribute['attribute_id'],
					'name'         => $product_attribute['name'],
					'text'         => $product_attribute['text']
				);
			}

			$product_attribute_group_data[] = array(
				'attribute_group_id' => $product_attribute_group['attribute_group_id'],
				'name'               => $product_attribute_group['name'],
				'attribute'          => $product_attribute_data
			);			
		}

		return $product_attribute_group_data;
	}

	private function getStore($store_id) {
		$query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "store` WHERE store_id = '" . (int)$store_id . "'");

		return $query->row;
	}

	private function getCustomer($customer_id) {
		$query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row;
	}

	private function getCustomerByEmail($email) {
		$query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE email = '" . $this->sgl->get('db')->escape($email) . "' LIMIT 1");

		return $query->row;
	}

	private function getAffiliateByEmail($email) {
		$query = $this->sgl->get('db')->query("SELECT * FROM `" . DB_PREFIX . "affiliate` WHERE email = '" . $this->sgl->get('db')->escape($email) . "' LIMIT 1");

		return $query->row;
	}

	private function addHistory($template_id, $email, $subject, $message) {
		$this->sgl->get('db')->query("INSERT INTO `" . DB_PREFIX . "cet_history` SET template_id = '" . (int)$template_id . "', email = '" . $this->sgl->get('db')->escape($email) . "', subject = '" . $this->sgl->get('db')->escape($subject) . "', description = '" . $this->sgl->get('db')->escape($message) . "', track = '" . (($this->cfg['custom_email_templates_track_status']) ? 1 : 0) . "', date_added = NOW()");

		return $this->sgl->get('db')->getLastId();
	}

	private function addAttachment($history_id, $data) {
		if ($data) {
			foreach ($data as $attachment) {
				foreach ($attachment as $file) {
					$this->sgl->get('db')->query("INSERT INTO `" . DB_PREFIX . "cet_attachment` SET history_id = '" . (int)$history_id . "', file = '" . $this->sgl->get('db')->escape(basename($file)) . "', date_added = NOW()");
				}
			}
		}
	}

	private function getReturnReasons() {
		$return_reason_data = array();

		$query = $this->sgl->get('db')->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "return_reason` WHERE language_id = '" . (int)$this->cfg['config_language_id'] . "' ORDER BY name");

		foreach ($query->rows as $result) {
			$return_reason_data[$result['return_reason_id']] = array(
				'id'   => $result['return_reason_id'],
				'name' => $result['name']
			);
		}

		return $return_reason_data;
	}

	private function getReturnActions() {
		$return_action_data = array();

		$query = $this->sgl->get('db')->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "return_action` WHERE language_id = '" . (int)$this->cfg['config_language_id'] . "' ORDER BY name");

		foreach ($query->rows as $result) {
			$return_action_data[$result['return_action_id']] = array(
				'id'   => $result['return_action_id'],
				'name' => $result['name']
			);
		}

		return $return_action_data;
	}

	private function getReturnStatuses() {
		$return_status_data = array();

		$query = $this->sgl->get('db')->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "return_status` WHERE language_id = '" . (int)$this->cfg['config_language_id'] . "' ORDER BY name");

		foreach ($query->rows as $result) {
			$return_status_data[$result['return_status_id']] = array(
				'id'   => $result['return_status_id'],
				'name' => $result['name']
			);
		}

		return $return_status_data;
	}

	private function _html_to_plain_text($node) {
		if ($node instanceof DOMText) {
			return preg_replace("/\\s+/im", " ", $node->wholeText);
		}
		if ($node instanceof DOMDocumentType) {
			// ignore
			return "";
		}

		// Next
		$nextNode = $node->nextSibling;
		while ($nextNode != null) {
			if ($nextNode instanceof DOMElement) {
				break;
			}
			$nextNode = $nextNode->nextSibling;
		}
		$nextName = null;
		if ($nextNode instanceof DOMElement && $nextNode != null) {
			$nextName = strtolower($nextNode->nodeName);
		}

		// Previous
		$nextNode = $node->previousSibling;
		while ($nextNode != null) {
			if ($nextNode instanceof DOMElement) {
				break;
			}
			$nextNode = $nextNode->previousSibling;
		}
		$prevName = null;
		if ($nextNode instanceof DOMElement && $nextNode != null) {
			$prevName = strtolower($nextNode->nodeName);
		}

		$name = strtolower($node->nodeName);

		// start whitespace
		switch ($name) {
			case "hr":
				return "------\n";

			case "style":
			case "head":
			case "title":
			case "meta":
			case "script":
				// ignore these tags
				return "";

			case "h1":
			case "h2":
			case "h3":
			case "h4":
			case "h5":
			case "h6":
				// add two newlines
				$output = "\n";
				break;

			case "p":
			case "div":
				// add one line
				$output = "\n";
				break;

			case "tr":
				// add one line
				$output = "\n";
				break;

			default:
				// print out contents of unknown tags
				$output = "";
				break;
		}

		// debug $output .= "[$name,$nextName]";

		if($node->childNodes){
			for ($i = 0; $i < $node->childNodes->length; $i++) {
				$n = $node->childNodes->item($i);

				$text = $this->_html_to_plain_text($n);

				$output .= $text;
			}
		}

		// end whitespace
		switch ($name) {
			case "style":
			case "head":
			case "title":
			case "meta":
			case "script":
				// ignore these tags
				return "";

			case "h1":
			case "h2":
			case "h3":
			case "h4":
			case "h5":
			case "h6":
				$output .= "\n";
				break;

			case "p":
			case "br":
				// add one line
				if ($nextName != "div")
					$output .= "\n";
				break;

			case "div":
				// add one line only if the next child isn't a div
				if (($nextName != "div" && $nextName != null))
					$output .= "\n";
				break;

			case "a":
				// links are returned in [text](link) format
				$href = $node->getAttribute("href");
				if ($href == null) {
					// it doesn't link anywhere
					if ($node->getAttribute("name") != null) {
						$output = "$output";
					}
				} else {
					if ($href == $output) {
						// link to the same address: just use link
						$output;
					} else {
						// No display
						$output = $href . "\n" . $output;
					}
				}

				// does the next node require additional whitespace?
				switch ($nextName) {
					case "h1": case "h2": case "h3": case "h4": case "h5": case "h6":
						$output .= "\n";
						break;
				}

			default:
				// do nothing
		}

		return $output;
	}

	public function loadLanguage($filename) {

		$expandish_path = (defined('DIR_CATALOG')) ? DIR_CATALOG : DIR_APPLICATION;

		$file = $expandish_path.'language/' . $filename . '.json';
		
		if (file_exists($file)) {
			$words = file_get_contents($file);
			$data = json_decode($words, true);

			return $data;
		}

		$file = $expandish_path.'language/'. 'english/' . $filename . '.php';

		if (file_exists($file)) {
			$_ = array();

			require($file);

			$data = array_merge(array(), $_);

			return $data;
		} else {
			trigger_error('Error: Could not load language ' . $filename . '!');
		}
  	}
        
        public function renderInvoiceTemplate($orders, $languageId) {
        $this->sgl->get('load')->model('module/custom_invoice_template');
        $template = $this->sgl->get('model_module_custom_invoice_template')->getTemplate(1)[$languageId];
		
        if (empty($template)) {
            return '';
        }
        $shortCodes = $this->sgl->get('model_module_custom_invoice_template')->getShortCodes();
        $templateToRender = '';
        $languageCodeDir = ($this->languageCode == 'ar') ? 'rtl' : 'ltr';
        foreach ($orders as $orderData) {
            if(isset($orderData['languageCode']) &&$orderData['languageCode'] == 'ar'){
                $languageCodeDir = 'rtl';
            }
            $templateData = $template;
            foreach ($shortCodes['order_info'] as $shortCode) {
                $key = explode('.', $shortCode)[1];
                $key = str_replace('}', '', $key);
                $replace = $orderData[$key];
                if ($key == 'store_logo') {
                    $replace = '<img src="' . $orderData[$key] . '"/>';
                } elseif ($key == 'invoice_no_barcode') {
                    $replace = '<img src="data:image/png;base64, ' . $orderData[$key] . '"/>';
                }
                $templateData = str_replace($shortCode, $replace, $templateData);
            }

            $productsData = '';

            foreach ($orderData['product'] as $productData) {
                preg_match('/\[PRODUCTS\](.+)\[PRODUCTS\]/s', $templateData, $productsTemplate);
                $productsTemplate = $productsTemplate[1];
                foreach ($shortCodes['products'] as $shortCode) {
                    $key = explode('.', $shortCode)[1];
                    $key = str_replace('}', '', $key);
                    $replace = $productData[$key];
                    if ($key == 'image') {
						if (\Filesystem::isExists('image/' . $replace)) {
							$replace =  \Filesystem::getUrl('image/' . $replace);
							$replace = '<img src="'.$replace.'" width="150" height="150"/>';
                        }
                    } elseif ($key == 'option') {
                        $replace = '';
                        if (!empty($productData['option'])) {
                            foreach ($productData['option'] as $optionData) {
                                $replace .= $optionData['name'] . ' : ' . $optionData['value'];
                            }
                        }
                    } elseif ($key == 'seller_country' || $key == 'seller_zone' || $key == 'seller_address' || $key == 'nickname') {
                        $replace = '';
                        if (!empty($productData['seller'])) {
                            $key = str_replace('seller_', '', $key);
                            $replace = $productData['seller']->{$key};
                        }
                    } elseif ($key == 'barcode_image' && $productData['barcode_image']) {
                        $replace = '<img src="data:image/png;base64, ' . $productData[$key] . '"/>';
                    }
                    $productsTemplate = str_replace($shortCode, $replace, $productsTemplate);
                }
                $productsData .= $productsTemplate;
            }

            $templateData = preg_replace('/\[PRODUCTS\](.+)\[PRODUCTS\]/s', $productsData, $templateData);

            $totalsData = '';
            $this->sgl->get('load')->model('sale/order');
            $orderData['total'] = $this->sgl->get('model_sale_order')->getOrderTotals($orderData['order_id']);
            if (!empty($orderData['total'])) {
                foreach ($orderData['total'] as $totalData) {
                    preg_match('/\[TOTALS\](.+)\[TOTALS\]/s', $templateData, $totalsTemplate);
                    $totalsTemplate = $totalsTemplate[1];
                    foreach ($shortCodes['totals'] as $shortCode) {
                        $key = explode('.', $shortCode)[1];
                        $key = str_replace('}', '', $key);
                        $totalsTemplate = str_replace($shortCode, $totalData[$key], $totalsTemplate);
                    }
                    $totalsData .= $totalsTemplate;
                }
            }

            $templateData = preg_replace('/\[TOTALS\](.+)\[TOTALS\]/s', $totalsData, $templateData);

            if (!empty($orderData['voucher'])) {
                foreach ($shortCodes['voucher'] as $shortCode) {
                    $key = explode('.', $shortCode)[1];
                    $key = str_replace('}', '', $key);
                    $templateData = str_replace($shortCode, $orderData['voucher'][$key], $templateData);
                }
            }

            if (!empty($orderData['delivery_slot'])) {
                foreach ($shortCodes['delivery_slot'] as $shortCode) {
                    $key = explode('.', $shortCode)[1];
                    $key = str_replace('}', '', $key);
                    $templateData = str_replace($shortCode, $orderData['order_delivery_slot'][$key], $templateData);
                }
            }
            $templateData = preg_replace('/{[^}]+}/', '', $templateData);
            $templateToRender .= $templateData;
        }

        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html>' . "\n";
        $html .= '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n";
        $html .= '  <head>' . "\n";
        $html .= '    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . "\n";
        $html .= '    <meta name="viewport" content="width=device-width">' . "\n";
        $html .= '  </head>' . "\n";
        $html .= '    <style>' . "\n";
        $html .= '    	   .cet_container, td, th, input, select, textarea, option, optgroup { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; color: ' . $this->cfg['custom_email_templates_text_color'] . '; }' . "\n";
        $html .= '    	   .cet_container { padding: 0px; margin: 0px; max-width: ' . $this->cfg['custom_email_templates_layout_width'] . '!important; }' . "\n";
        $html .= '    	   a,a:link,a:visited,a:hover { color: ' . $this->cfg['custom_email_templates_link_color'] . '; }' . "\n";
        $html .='body {
                       background: #FFFFFF;
                       max-width: 900px;
                       margin-right: auto;
                       margin-left: auto; }';
        $html .='table { border-collapse: separate !important;table.TableFormat.Bidi = true;}';
        $html .='.content{padding: 0px 20px 0px 20px;}';
        $html .= '    </style>' . "\n";
        $html .= '  </head>' . "\n";
        $html .= "  <body dir='$languageCodeDir'".'style="background-color: ' . $this->cfg['custom_email_templates_background_color'] . '; ' . (($this->cfg['custom_email_templates_background_image']) ? 'background-image: url(\'' . HTTP_SERVER . $this->cfg['custom_email_templates_background_image'] . '\'); background-repeat: ' . $this->cfg['custom_email_templates_background_repeat'] . ';' : '') . '">' . "\n";
        $html .= "  <div style='direction:$languageCodeDir'>" . "\n";
        $html .=    html_entity_decode($templateToRender);
        $html .= '  </div>' . "\n";
        $html .= '  </body>' . "\n";



        return $html;
    }

    /**
     * return language direction (rtl or ltr)
     * @return string
     */
    private function getLanguageDirection(){

        $languageDirection = 'ltr';

        if (strtolower($this->sgl->get('language')->get('code')) == 'ar'
            ||
            (!empty($this->sgl->get('language')->get('direction')) && strtolower($this->sgl->get('language')->get('direction')) == 'rtl')
        ){
            $languageDirection = 'rtl';
        }

        return $languageDirection;
    }
}
