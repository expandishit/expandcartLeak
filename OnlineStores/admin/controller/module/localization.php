<?php
/**
*   Controller Class for Localization Module
*
*   @author Michael.
*/
class ControllerModuleLocalization extends Controller
{
    private $error = array();

	public function index()
    {

        $this->load->model('marketplace/common');
        
        $market_appservice_status = $this->model_marketplace_common->hasApp('localization');
        
        if ( !$market_appservice_status['hasapp'] )
        {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

        $this->language->load('module/localization');

        $this->document->setTitle($this->language->get('heading_title'));

        // ===================== BreadCrumbs =====================

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/localization', '', 'SSL'),
            'separator' => ' :: '
        );
        
        // ===================== End of BreadCrumbs =====================

        $this->load->model('setting/setting');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
            }
            else
            {
                $this->model_setting_setting->editSetting('localization', $this->request->post);

                $this->data['text_success'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->add_to_template_data([
            'button_save',
            'button_cancel',
            'text_module',
            'text_success_page',
        ]);

        $this->data['action'] = $this->url->link('module/localization', '', 'SSL');

        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

        $this->load->model('localisation/language');
        
        $langs = $this->model_localisation_language->getLanguages();

        $settings = $this->model_setting_setting->getSetting('localization');

        foreach ($langs as $key => $value)
        {

            $langs[$key]['fields'] = array();

            if ( $key == 'en' )
            {
                $suffix = '';
            }
            else
            {
                $suffix = '_' . $value['code'];
            }

            $langs[$key]['fields'][] = array(
                'text'      =>      $this->language->get('text_add_to_cart'),
                'name'      =>      'button_cart' . $suffix,
                'value'     =>      $this->request->post['button_cart' . $suffix] ? $this->request->post['button_cart' . $suffix] : $settings['button_cart' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  =>  $this->language->get('button_req_quote'),
                'name'  =>  'button_req_quote' . $suffix,
                'value' =>  $this->request->post['button_req_quote' . $suffix] ? $this->request->post['button_req_quote' . $suffix] : $settings['button_req_quote' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('text_product_model'),
                'name'  => 'text_product_model' . $suffix,
                'value' =>  $this->request->post['text_product_model' . $suffix] ? $this->request->post['text_product_model' . $suffix] : $settings['text_product_model' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('text_invoice_sub_total'),
                'name'  => 'text_invoice_sub_total' . $suffix,
                'value' =>  $this->request->post['text_invoice_sub_total' . $suffix] ? $this->request->post['text_invoice_sub_total' . $suffix] : $settings['text_invoice_sub_total' . $suffix],
            );
          
            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_zone'),
                'name'  => 'entry_zone' . $suffix,
                'value' =>  $this->request->post['entry_zone' . $suffix] ? $this->request->post['entry_zone' . $suffix] : $settings['entry_zone' . $suffix],
           );
          
            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('ms_account_sellerinfo_zone'),
                'name'  => 'ms_account_sellerinfo_zone' . $suffix,
                'value' =>  $this->request->post['ms_account_sellerinfo_zone' . $suffix] ? $this->request->post['ms_account_sellerinfo_zone' . $suffix] : $settings['ms_account_sellerinfo_zone' . $suffix],

            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('ms_account_sellerproduct_file'),
                'name'  => 'ms_account_sellerproduct_file' . $suffix,
                'value' =>  $this->request->post['ms_account_sellerproduct_file' . $suffix] ? $this->request->post['ms_account_sellerproduct_file' . $suffix] : $settings['ms_account_sellerproduct_file' . $suffix],

            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('text_cart'),
                'name'  => 'text_cart' . $suffix,
                'value' =>  $this->request->post['text_cart' . $suffix] ? $this->request->post['text_cart' . $suffix] : $settings['text_cart' . $suffix],

            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('text_tab_related'),
                'name'  => 'text_tab_related' . $suffix,
                'value' =>  $this->request->post['text_tab_related' . $suffix] ? $this->request->post['text_tab_related' . $suffix] : $settings['text_tab_related' . $suffix],

            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_telephone'),
                'name'  => 'entry_telephone' . $suffix,
                'value' =>  $this->request->post['entry_telephone' . $suffix] ? $this->request->post['entry_telephone' . $suffix] : $settings['entry_telephone' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_fax'),
                'name'  => 'entry_fax' . $suffix,
                'value' =>  $this->request->post['entry_fax' . $suffix] ? $this->request->post['entry_fax' . $suffix] : $settings['entry_fax' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_your_address'),
                'name'  => 'entry_your_address' . $suffix,
                'value' =>  $this->request->post['entry_your_address' . $suffix] ? $this->request->post['entry_your_address' . $suffix] : $settings['entry_your_address' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_company'),
                'name'  => 'entry_company' . $suffix,
                'value' =>  $this->request->post['entry_company' . $suffix] ? $this->request->post['entry_company' . $suffix] : $settings['entry_company' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_company_id'),
                'name'  => 'entry_company_id' . $suffix,
                'value' =>  $this->request->post['entry_company_id' . $suffix] ? $this->request->post['entry_company_id' . $suffix] : $settings['entry_company_id' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_tax_id'),
                'name'  => 'entry_tax_id' . $suffix,
                'value' =>  $this->request->post['entry_tax_id' . $suffix] ? $this->request->post['entry_tax_id' . $suffix] : $settings['entry_tax_id' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_business_type'),
                'name'  => 'entry_business_type' . $suffix,
                'value' =>  $this->request->post['entry_business_type' . $suffix] ? $this->request->post['entry_business_type' . $suffix] : $settings['entry_business_type' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_address1'),
                'name'  => 'entry_address_1' . $suffix,
                'value' =>  $this->request->post['entry_address_1' . $suffix] ? $this->request->post['entry_address_1' . $suffix] : $settings['entry_address_1' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_address2'),
                'name'  => 'entry_address_2' . $suffix,
                'value' =>  $this->request->post['entry_address_2' . $suffix] ? $this->request->post['entry_address_2' . $suffix] : $settings['entry_address_2' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_city'),
                'name'  => 'entry_city' . $suffix,
                'value' =>  $this->request->post['entry_city' . $suffix] ? $this->request->post['entry_city' . $suffix] : $settings['entry_city' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_postcode'),
                'name'  => 'entry_postcode' . $suffix,
                'value' =>  $this->request->post['entry_postcode' . $suffix] ? $this->request->post['entry_postcode' . $suffix] : $settings['entry_postcode' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_country'),
                'name'  => 'entry_country' . $suffix,
                'value' =>  $this->request->post['entry_country' . $suffix] ? $this->request->post['entry_country' . $suffix] : $settings['entry_country' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_checkout_zone'),
                'name'  => 'entry_checkout_zone' . $suffix,
                'value' =>  $this->request->post['entry_checkout_zone' . $suffix] ? $this->request->post['entry_checkout_zone' . $suffix] : $settings['entry_checkout_zone' . $suffix],
            );

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('entry_invoice'),
                'name'  => 'entry_invoice' . $suffix,
                'value' =>  $this->request->post['entry_invoice' . $suffix] ? $this->request->post['entry_invoice' . $suffix] : $settings['entry_invoice' . $suffix],
            );


            $success_page_value = $this->request->post['text_success_page' . $suffix] ? $this->request->post['text_success_page' . $suffix] : $settings['text_success_page' . $suffix];

            $langs[$key]['fields'][] = array(
                'text'  => $this->language->get('text_success_page'),
                'name'  => 'text_success_page' . $suffix,
                'value' => $success_page_value,
                'html'  =>  "<textarea class='summernote form-control' name='text_success_page" . $suffix . "'>". $success_page_value . "</textarea>",
                'type' => 'textarea',
                'help' => $this->language->get('text_success_page_help')
            );

        }

        $this->data['languages'] = $langs;

        $this->template = 'module/localization.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());

    }

    /**
    *   Validate the input data.
    *
    *   @return boolean
    */
    private function validate()
    {
        if ( !$this->user->hasPermission('modify', 'module/localization') )
        {
            $this->error[] = $this->language->get('error_permission');
            return false;
        }

        return true;
    }

    /**
     *  Adds values to template data.   
     *
     *   @param array $values an array of the values to add to the template data
     *   @return void
    */
    private function add_to_template_data($values = array())
    {
        foreach ($values as $value):
            $this->data[$value] = $this->language->get($value);
        endforeach;
    }
}
