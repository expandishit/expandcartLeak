<?php

namespace Api\Models\Setting;

use Psr\Container\ContainerInterface;

class GeneralSetting
{
    private $load;
    private $registry;
    private $languagecodes;
    private $languageids;
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config = $container['config'];
        $this->load = $container['loader'];
        $this->languagecodes = $container['languagecodes'];
        $this->languageids = $container['languageids'];
    }

    public function getGeneralStoreSetting()
    {
        $this->load->model('setting/setting');
        $storeData = $this->registry->get('model_setting_setting')->getSetting('config');
        $this->load->model('localisation/language');
        $languages = $this->registry->get('model_localisation_language')->getLanguages();
        $infoData['email'] = $storeData['config_email'];
        $infoData['phone'] = $storeData['config_telephone'];
        // Check if config_name has initial value and not localised
        if (!is_array($storeData['config_name']))
        {
            foreach ($languages as $key => $language)
            {
                $infoData['name'][$key] = $storeData['config_name'];
            }
        }
        elseif (is_array($storeData['config_name']))
        {
            $infoData['name'] = $storeData['config_name'];
        }
        // Check if config_address has initial value and not localised
        if (!is_array($storeData['config_address']))
        {
            foreach ($languages as $key => $language)
            {
                $infoData['address'][$key] = $storeData['config_address'];
            }
        }
        elseif (is_array($storeData['config_address']))
        {
            $infoData['address'] = $storeData['config_address'];
        }
        // Check if config_title has initial value and not localised
        if (!is_array($storeData['config_title']))
        {
            foreach ($languages as $key => $language)
            {
                $infoData['meta_title'][$key] = $storeData['config_title'];
            }
        }
        elseif (is_array($storeData['config_title']))
        {
            $infoData['meta_title'] = $storeData['config_title'];
        }
        // Check if config_meta_description has initial value and not localised
        if (!is_array($storeData['config_meta_description']))
        {
            foreach ($languages as $key => $language)
            {
                $infoData['meta_description'][$key] = $storeData['config_meta_description'];
            }
        }
        elseif (is_array($storeData['config_title']))
        {
            $infoData['meta_description'] = $storeData['config_meta_description'];
        }
        return $infoData;
    }

	public function getSettingByKey($key, $group = null, $store_id = 0)
	{
		$this->load->model('setting/setting');
        return $this->registry->get('model_setting_setting')->getSettingByKey($key , $group , $store_id);
	}
	
	public function getSettingByValue($value, $group = null, $store_id = 0)
	{
		$this->load->model('setting/setting');
        return $this->registry->get('model_setting_setting')->getSettingByValue($value , $group , $store_id);
	}
	
    public function updateGeneralSetting($data)
    {
        //return $data;
        $this->load->model('setting/setting');
        $this->registry->get('model_setting_setting')->insertUpdateSetting('config', $data);
        return true;
    }

	/**
     * Delete setting by keys
     *
     * @param array $keys
     * @return void
     */
	public function deleteByKeys(array $keys=[]): void 
    {
        $this->load->model('setting/setting');
		$this->registry->get('model_setting_setting')->deleteByKeys($keys);
    }
	
	public function insertUpdateSetting($group, $data, $store_id = 0)
    {
        $this->load->model('setting/setting');
        return $this->registry->get('model_setting_setting')->insertUpdateSetting($group, $data, $store_id);
    }
	
    /**
     * @param $guidename
     * @param $key
     * @param $value
     * @return mixed
     */
    public function editGuideValue($guidename, $key, $value)
    {
        $this->load->model('setting/setting');
        return $this->registry->get('model_setting_setting')->editGuideValue($guidename, $key, $value);
    }

    /**
     * @param $guidename
     * @param $key
     * @param $value
     * @return mixed
     */
    public function getGuideValue($guidename)
    {
        $this->load->model('setting/setting');
        return $this->registry->get('model_setting_setting')->getGuideValue($guidename);
    } 
	

}
