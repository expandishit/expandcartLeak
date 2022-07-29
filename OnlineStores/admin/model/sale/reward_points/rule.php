<?php
/**
 * User: Anh TO
 * Date: 10/4/14
 * Time: 9:46 AM
 */

class ModelSaleRewardPointsRule extends Model
{
    public function getExtension($type)
    {
        $this->load->model('setting/extension');

        $extensions = $this->model_setting_extension->getInstalled($type);

        foreach ($extensions as $key => $value) {
            if (!file_exists(DIR_APPLICATION . 'controller/'.$type.'/' . $value . '.php')) {
                $this->model_setting_extension->uninstall($type, $value);

                unset($extensions[$key]);
            }
        }
        $methods = array();

        foreach ($extensions as $extension) {
            $extension = basename($extension, '.php');

            $this->language->load($type.'/' . $extension);


            $text_link = $this->language->get('text_' . $extension);


            $methods[] = array(
                'name'       => $this->language->get('heading_title'),
                'status'     => $this->config->get($extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                'sort_order' => $this->config->get($extension . '_sort_order'),
                'code'       => $extension
            );
        }

        return $methods;
    }

    public function getCountries()
    {
        $this->language->load('localisation/country');
        $this->load->model('localisation/country');

        return $this->model_localisation_country->getCountries();
    }
}
