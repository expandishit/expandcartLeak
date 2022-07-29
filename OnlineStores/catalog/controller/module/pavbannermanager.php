<?php
/******************************************************
 * @package Pav bannermanager module for Opencart 1.5.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2012 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/


class ControllerModulePavbannermanager extends Controller {

	protected function index($setting) {
		static $module = 0;
	
		$this->load->model('tool/image');
		
		
		if (file_exists('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/pavbannermanager.css')) {
			$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/pavbannermanager.css');
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/pavbannermanager.css');
		}
		

		$prefix_class = isset($setting['prefix'])?$setting['prefix']:'';
		//$layout_id = isset($setting['layout_id'])?$setting['layout_id']:'';
		//$position = isset($setting['position'])?$setting['position']:'';
		//$sort_order = isset($setting['sort_order'])?$setting['sort_order']:2;
		$language = $this->config->get('config_language_id');
		$status = isset($setting['status'])?$setting['status']:0;
		$banner_layout = isset($setting['banner_layout'])?$setting['banner_layout']:1;

		$defaultbanner = array();
		$banners = isset($setting['banners'])?$setting['banners']:$defaultbanner;

		foreach ($banners as &$banner) {
			$banner['thumb'] = $this->model_tool_image->resize($banner['image'], $banner['width'], $banner['height']);
		}

		$this->data['language'] = $language;
		$this->data['prefix_class'] = $prefix_class;
		$this->data['status'] = $status;
		$this->data['banner_layout'] = $banner_layout;
		$this->data['banners'] = $banners;

	
			
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/pavbannermanager.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/pavbannermanager.tpl';
		} else {
			$this->template = 'default/template/module/pavbannermanager.tpl';
		}
		

		$this->render();
	}
}
?>