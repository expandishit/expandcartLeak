<?php

class ControllerModuleCustomInvoiceTemplate extends Controller
{
    public function install()
    {
        $this->load->model('module/custom_invoice_template');
        $this->model_module_custom_invoice_template->install();
    }

    public function uninstall()
    {
        $this->load->model('module/custom_invoice_template');
        $this->model_module_custom_invoice_template->uninstall();
    }

    public function index()
    {
        $this->language->load('module/custom_invoice_template');
        $this->load->model('module/custom_invoice_template');
        $this->load->model('localisation/language');

        $this->document->setTitle($this->language->get('cit_title'));

        $this->data['cit'] = $this->config->get('cit');
        $this->data['cit_template'] = $this->model_module_custom_invoice_template->getTemplate(1);
        $this->data['cit_short_codes'] = $this->model_module_custom_invoice_template->getShortCodes();
        $this->data['submit_link'] = $this->url->link('module/custom_invoice_template/saveSettings', '', 'SSL');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('cit_title'),
            'href'      => $this->url->link('module/custom_invoice_template', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->template = 'module/custom_invoice_template/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function saveSettings()
    {
        $this->language->load('module/custom_invoice_template');
        $this->load->model('module/custom_invoice_template');
        $this->load->model('setting/setting');
        $template = $this->request->post['cit_template'];
        unset($this->request->post['cit_template']);
        $this->model_setting_setting->editSetting('cit', $this->request->post);
        $this->model_module_custom_invoice_template->updateTemplate($template, 1);
        $json['success'] = '1';
        $json['success_msg'] = $this->language->get('cit_save');
        $this->response->setOutput(json_encode($json));
    }

    public function renderTemplate($orders, $languageId)
    {
        $this->load->model('module/custom_invoice_template');
        $template = $this->model_module_custom_invoice_template->getTemplate(1)[$languageId];
        if (empty($template)) {
            return '';
        }
        $shortCodes = $this->model_module_custom_invoice_template->getShortCodes();
        $templateToRender = '';
        foreach($orders as $orderData) {
            $templateData = $template;
            foreach ($shortCodes['order_info'] as $shortCode) {
                $key = explode('.', $shortCode)[1];
                $key = str_replace('}', '', $key);
                $replace = $orderData[$key];
                if ($key == 'store_logo') {
                    $replace = '<img src="'.$orderData[$key].'"/>';
                } elseif ($key == 'invoice_no_barcode') {
                    $replace = '<img src="data:image/png;base64, '.$orderData[$key].'"/>';
                }
                $templateData = str_replace($shortCode, $replace, $templateData);
            }

            $productsData = '';
            $newTemplate = preg_replace('/\[PRODUCTS\](.+)\[PRODUCTS\]/s', $templateData, $productsData);
            $templateData = $newTemplate ? $newTemplate : $templateData;
            $serial=1;
            foreach ($orderData['product'] as $productData) {
                preg_match('/\[PRODUCTS\](.+)\[PRODUCTS\]/s', $templateData, $productsTemplate);
                $productsTemplate = $productsTemplate[1];
                foreach ($shortCodes['products'] as $shortCode) {
                    $key = explode('.', $shortCode)[1];
                    $key = str_replace('}', '', $key);
                    $replace = $productData[$key];
                    if ($key == 'serial') {
                        $replace .= '<b >'.$serial.'- </b>';
                    }
                    if ($key == 'image') {
                        if (\Filesystem::isExists('image/' . $replace)) {
							$replace =  \Filesystem::getUrl('image/' . $replace);
							$image = file_get_contents($replace);
                            $base64image = base64_encode($image);
                            $replace = '<img src="data:image/png;base64,'.$base64image.'" style="width:150px;height:150px"/>';
                        }else{
                            $replace = '<img src="'.$replace.'" alt="'.$productData['name'].'" style="width:150px;height:150px"/>';
                        }
                    } elseif ($key == 'option') {
                        $replace = '';
                        if (!empty($productData['option'])) {
                            foreach ($productData['option'] as $optionData) {
                                $replace .= $optionData['name'] . ' : ' . $optionData['value'];
                            }
                        }
                    } elseif ($key == 'seller_country' || $key == 'seller_zone' || $key == 'seller_address'|| $key == 'nickname') {
                        $replace = '';
                        if (!empty($productData['seller'])) {
                            $key = str_replace('seller_', '', $key);
                            $replace = $productData['seller']->{$key};
                        }
                    } elseif ($key == 'barcode_image' && $productData['barcode_image']) {
                        $replace = '<img src="data:image/png;base64, '.$productData[$key].'"/>';
                    }
                    elseif ($key =='name' && $productData['bundlesData']){
                        $replace .= '</br>';
                        foreach ($productData['bundlesData'] as $bundle){
                            $replace .= '<img src="'.$bundle['thumb'].'" height="30">';
                            $replace .= '<strong class="item-name">"'.$bundle['product_name'].'" </strong>   <br />';
                            $replace .= '<span class="price">"'.($bundle['price'] * (1 - $bundle['discount'])).'"</span>';
                            $replace .= $this->language->get('instead_of') . "<s>{$bundle['price']}</s>";
                            $replace .=$this->language->get('with_discount') .  ($bundle['discount']*100) . "%";
                        }
                    }
                    $productsTemplate = str_replace($shortCode, $replace, $productsTemplate);
                }
                $serial++;
                $productsData .= $productsTemplate;
            }

            $templateData = preg_replace('/\[PRODUCTS\](.+)\[PRODUCTS\]/s', $productsData, $templateData);

            $totalsData = '';
            
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

        return $templateToRender;
    }
}