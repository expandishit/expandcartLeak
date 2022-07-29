<?php
/**
 * Created by PhpStorm.
 * User: ANH To
 * Date: 10/9/14
 * Time: 10:35 AM
 */

class ModelPromotionsRewardPointsSetup extends Model
{
	public function beforeSaveSetting()
	{
		$total_sql = "SELECT `value` as `order` FROM " . DB_PREFIX . "setting WHERE `key` = 'total_sort_order'";
		$total_query = $this->db->query($total_sql);
		$total_sort_order = $total_query->row['order'];

		$config_sql = "SELECT `value`, `key` FROM " . DB_PREFIX . "setting WHERE `key` = 'config_template'";
		$config_query = $this->db->query($config_sql);
		$path_application_root = explode("admin", DIR_APPLICATION);
		$path_application_root = $path_application_root[0];

		foreach($config_query->rows as $template)
		{
			$current_template = $template['value'];
			/** Read file vqmod rewardpoints_frontend */

			if(file_exists($path_application_root."vqmod/xml/rewardpoints_frontend_tpl.txt"))
			{
				if(!is_writable($path_application_root."vqmod/xml"))
				{
					return false;
				}

				$xml = file_get_contents($path_application_root."vqmod/xml/rewardpoints_frontend_tpl.txt");
				if($template['value'] != 'default')
				{
					/** Replace default template to current template of store */
					$xml = str_replace('<file name="catalog/view/theme/customer_theme/template/product/category.tpl">', '<file name="catalog/view/theme/'.$current_template.'/template/product/category.tpl">', $xml);
					$xml = str_replace('<file name="catalog/view/theme/customer_theme/template/product/product.tpl">', '<file name="catalog/view/theme/'.$current_template.'/template/product/product.tpl">', $xml);
				}

				/** Backup file in case you have customized something */
				@copy($path_application_root."vqmod/xml/rewardpoints_frontend_tpl.xml", $path_application_root."vqmod/xml/rewardpoints_frontend_tpl-backup-".date('Y-m-d', time())."-".time().".txt");
				@unlink($path_application_root."vqmod/xml/rewardpoints_frontend_tpl.xml");
				file_put_contents($path_application_root."vqmod/xml/rewardpoints_frontend_tpl.xml", $xml);
			}
		}
	}
}
