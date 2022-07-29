<?php 
class ModelModuleCustomerOrderFlow extends Model
{
    public function updateSettings($inputs)
    {
      $this->load->model('setting/setting');
      $this->model_setting_setting->insertUpdateSetting(
        'customer_order_flow', $inputs
      );
      return true;
    }

    public function getSettings()
    {
      return $this->config->get('customer_order_flow');
    }
}

?>