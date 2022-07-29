<?php

use ExpandCart\Foundation\Providers\Extension;

class ControllerAccountTaps extends Controller
{
    public function renderTaps($active_tap)
    {
        $this->language->load_json('account/taps', true);
        $this->language->load_json('account/account', true);

        $this->data['active_tap'] = $active_tap;

        $this->data['return_type'] = $this->config->get('config_return_type') ? $this->config->get('config_return_type') : "return";

        //Checking Product Video Links App is installed to show my videos page link
        $this->_addProductVideoLinksAppData();

        //Checking Auctions App is installed to show my Auctions page link
        $this->_addAuctionsAppData();

        //$this->data['edit'] = $this->url->link('account/edit', '', 'SSL');
        //$this->data['password'] = $this->url->link('account/password', '', 'SSL');
        //$this->data['address'] = $this->url->link('account/address', '', 'SSL');
        //$this->data['wishlist'] = $this->url->link('account/wishlist');
        //$this->data['order'] = $this->url->link('account/order', '', 'SSL');
        //$this->data['download'] = $this->url->link('account/download', '', 'SSL');
        //$this->data['return'] = $this->url->link('account/return', '', 'SSL');
        //$this->data['transaction'] = $this->url->link('account/transaction', '', 'SSL');
        //$this->data['newsletter'] = $this->url->link('account/newsletter', '', 'SSL');

        //if ($this->config->get('reward_status')) {
        //	$this->data['reward'] = $this->url->link('account/reward', '', 'SSL');
        //} else {
        //	$this->data['reward'] = '';
        //}

        //Check if MS & MS Messaging is installed
        $this->load->model('multiseller/status');
        $multiseller = $this->model_multiseller_status->is_installed();


        $this->data['ms_is_active'] = false;
        $this->data['ms_messaging_is_active'] = false;
        if ($multiseller) {
            //get seller title
            $this->load->model('seller/seller');
            $seller_title = $this->model_seller_seller->getSellerTitle();

            $multisellerMessaging = $this->model_multiseller_status->is_messaging_installed();
            if ($multisellerMessaging)
                $this->data['ms_messaging_is_active'] = true;
            $this->data['ms_seller_created'] = $this->MsLoader->MsSeller->isCustomerSeller($this->customer->getId());
            $this->data['ms_seller_active'] = ($this->MsLoader->MsSeller->getStatus($this->customer->getId()) == MsSeller::STATUS_ACTIVE);
            $this->data['ms_is_active'] = true;
            // $this->data = array_merge($this->data, $this->language->load_json('multiseller/multiseller'));
            $this->language->load_json('multiseller/multiseller');
            $this->data['ms_account_sellerinfo_new'] = sprintf($this->language->get('ms_account_sellerinfo_new'), $seller_title);
            $this->data['ms_account_sellerinfo'] = sprintf($this->language->get('ms_account_sellerinfo'), $seller_title);

            $this->load->model('account/messagingseller');
            $this->data['unread_messages_count'] = $this->model_account_messagingseller->getTotalUnreadForUser($this->customer->getId())['unread_count'] ?? 0;
        }
        ///////////////////////////////////////////


        /*$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        $this->data['ms_is_active'] = false;

        if($queryMultiseller->num_rows) {
            $this->data['ms_seller_created'] = $this->MsLoader->MsSeller->isCustomerSeller($this->customer->getId());
            $this->data['ms_seller_active'] = ($this->MsLoader->MsSeller->getStatus($this->customer->getId()) == MsSeller::STATUS_ACTIVE);
            $this->data['ms_is_active'] = true;
            $this->data = array_merge($this->data, $this->language->load_json('multiseller/multiseller'));
        }*/

        $networkMarketingApplication = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'network_marketing'"
        );

        $this->data['networkMarketing']['installed'] = false;

        if ($networkMarketingApplication->num_rows) {

            $this->data['networkMarketing']['installed'] = true;

            $this->load->model('network_marketing/settings');

            $networkMarketingSettings = $this->model_network_marketing_settings->getSettings();

            $this->data['networkMarketing']['status'] = $networkMarketingSettings['nm_status'];
        }

        //Buyer Subscription Plan App
        $this->data['buyer_subscription_plan_is_installed'] = Extension::isInstalled('buyer_subscription_plan') && $this->config->get('buyer_subscription_plan_status');

        if ($this->data['buyer_subscription_plan_is_installed']) {
            $this->load->model('account/subscription');
            $buyer_subscription_plan  = $this->model_account_subscription->getCustomerSubscriptionPlan($this->customer->getId()); //if empty, then it's free plan

            //paid subscription plan
            if (!empty($buyer_subscription_plan)) {
                $this->data['buyer_subscription_plan_expiration_date'] = $this->model_account_subscription->getSubsciptionExpirationDate($buyer_subscription_plan)->format('d/m/Y');
                $this->data['buyer_subscription_plan'] = $buyer_subscription_plan;
            }
        }

        if ($this->customer->isLogged()) {
            $this->data['customer_avatar'] = '';
            $this->data['customer_name'] = $this->customer->getFirstName();
            $words = mb_split("\s", $this->customer->getFirstName());
            for ($i = 0; $i <= 1; $i++) if (isset($words[$i])) $this->data['customer_avatar'] .= ("<span>" . mb_substr($words[$i], 0, 1) . "</span>");
        }

        //render view template
        $this->template = 'default/template/account/taps.expand';

        $this->response->setOutput($this->render_ecwig());
    }


    /**
     * Check if Product Video Links App is installed to show videos,
     * Then append required data to $this->data[] array to render in view.
     *
     * @return void
     */
    private function _addProductVideoLinksAppData()
    {

        $this->load->model('module/product_video_links');

        $product_video_links_installed =  $this->model_module_product_video_links->isInstalled();

        $this->data['show_videos'] = $product_video_links_installed;

        if ($product_video_links_installed) {
            //Get Seller title from setting table 
            $this->load->model('seller/seller');
            $products_title = $this->model_seller_seller->getProductsTitle();
            $this->data['text_my_products'] = sprintf($this->language->get('text_my_products'), $products_title);
        }
    }



    /**
     * Check if Auctions App is installed to show auctions,
     * Then append required data to $this->data[] array to render in view.
     *
     * @return void
     */
    private function _addAuctionsAppData()
    {

        $this->load->model('module/auctions/auction');

        $auctions_app_installed = Extension::isInstalled('auctions') && $this->config->get('auctions_status');

        $this->data['auctions_app_installed'] = $auctions_app_installed;
    }
}
