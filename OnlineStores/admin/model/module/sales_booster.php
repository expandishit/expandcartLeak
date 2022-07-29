<?php

class ModelModuleSalesBooster extends Model
{
    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'sales_booster', $inputs
        );

        return true;
    }

    public function install()
    {
        $alterQuery = 'ALTER TABLE `product` ADD `sls_bstr` TEXT NULL AFTER `status`';
        $this->db->query($alterQuery);

        $query = "CREATE TABLE IF NOT EXISTS `" .DB_PREFIX. "sales_booster_layouts` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        )";
        $this->db->query($query);

        $query = "CREATE TABLE `" .DB_PREFIX. "sales_booster_layouts_description` (
                  `layout_id` int(11) NOT NULL,
                  `language_id` int(11) NOT NULL,
                  `name` varchar(255) NOT NULL,
                  `description` text NOT NULL,
                  PRIMARY KEY (`layout_id`,`language_id`),
                  KEY `name` (`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($query);


        /// Adding initial layouts
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        $l1D =
        '<table class="table table-bordered" style="text-align: center;">
            <tbody>
                <tr>
                    <td>
                        <p><img src="/image/modules/sb/COD.png" style="width:100%;max-width: 128px;" data-filename="sb/COD.png"></p>
                        <p><b><span style="font-size: 15px;">دفع عند الاستلام&nbsp;</span></b></p>
                    </td>
                    <td>
                        <p><img src="/image/modules/sb/free-shipping.png" style="width:100%;max-width: 128px;" data-filename="sb/free-shipping.png"></p>
                        <p><b><span style="font-size: 15px;">شحن مجاني&nbsp;</span></b></p>
                    </td>
                    <td>
                        <p><img src="/image/modules/sb/return.png" style="width:100%;max-width: 128px;" data-filename="sb/return.png"></p>
                        <p><span style="font-size: 15px;"><b>استرجاع خلال ١٤ يوم&nbsp;</b></span></p>
                    </td>
                    <td>
                        <p><img src="/image/modules/sb/express-delivery.png" style="width:100%;max-width: 128px;" data-filename="sb/express-delivery.png"></p>
                        <p><span style="font-size: 15px;"><b>يصلك المنتج خلال ٤ أيام</b></span></p>
                    </td>
                    <td>
                        <p><img src="/image/modules/sb/secure-payment.png" style="width:100%;max-width: 128px;" data-filename="sb/secure-payment.png"></p>
                        <p><span style="font-size: 15px;"><b>دفع امن ١٠٠٪&nbsp;</b></span></p>
                    </td>
                </tr>
            </tbody>
        </table>';

        $l2D = '<div style="text-align: center;">
                    <img src="/image/modules/sb/money-back.jpg" style="height: 175px;">
                </div>';

        $l3D = '<div style="text-align: center;">
                    <img src="/image/modules/sb/trust-badges.png" style="margin: auto; height: 145px; text-align: center;">
                </div>';
        
        $initData = array(
                    '0' => ['name' => 'نموذج 1', 'description' => $l1D], 
                    '1' => ['name' => 'نموذج 2', 'description' => $l2D], 
                    '2' => ['name' => 'نموذج 3', 'description' => $l3D]
                );

        $data = [];
        for($i=0; $i<count($initData); $i++){
            foreach ($languages as $language) {
                $data['layout_description'][$language['language_id']]['name']        = $initData[$i]['name'];
                $data['layout_description'][$language['language_id']]['description'] = $initData[$i]['description'];
            }
            $this->addLayout($data);
        }
    }

    public function uninstall()
    {
        $alterQuery = 'ALTER TABLE `product` DROP COLUMN `sls_bstr`';
        $this->db->query($alterQuery);

        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "sales_booster_layouts`";
        $this->db->query($query);

        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "sales_booster_layouts_description`";
        $this->db->query($query);
    }

    public function getSettings()
    {
        return $this->config->get('sales_booster_module');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function getLayouts() {
        $sql = "SELECT sb.id , sbd1.name FROM " . DB_PREFIX . "sales_booster_layouts sb LEFT JOIN " . DB_PREFIX . "sales_booster_layouts_description sbd1 ON (sb.id = sbd1.layout_id) WHERE sbd1.language_id = '" . (int)$this->config->get('config_language_id') . "'";
                        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }

    public function getLayoutDescriptions($layout_id) {
        $layout_description_data = array();
        
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "sales_booster_layouts_description WHERE layout_id = '" . (int)$layout_id . "'");
        
        foreach ($query->rows as $result) {
            $layout_description_data[$result['language_id']] = array(
                'name'             => $result['name'],
                'description'      => $result['description']
            );
        }
        
        return $layout_description_data;
    }

    public function addLayout($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "sales_booster_layouts SET createdDate = NOW()");

        $layout_id = $this->db->getLastId();

        foreach ($data['layout_description'] as $language_id => $value) {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "sales_booster_layouts_description SET
                layout_id = '" . (int)$layout_id . "',
                language_id = '" . (int)$language_id . "',
                name = '" . $this->db->escape($value['name']) . "',
                description = '" . $this->db->escape($value['description']) . "'
            ");
        }       
    }

    public function editLayout($layout_id, $data)
    {

        $this->db->query("DELETE FROM " . DB_PREFIX . "sales_booster_layouts_description WHERE layout_id = '" . (int)$layout_id . "'");

        foreach ($data['layout_description'] as $language_id => $value) {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "sales_booster_layouts_description SET
                layout_id = '" . (int)$layout_id . "',
                language_id = '" . (int)$language_id . "',
                name = '" . $this->db->escape($value['name']) . "',
                description = '" . $this->db->escape($value['description']) . "'
            ");
        }
    }

    public function deleteLayout($layout_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "sales_booster_layouts WHERE id = '" . (int)$layout_id . "'");
        
        $this->db->query("DELETE FROM " . DB_PREFIX . "sales_booster_layouts_description WHERE layout_id = '" . (int)$layout_id . "'");
    }
}
