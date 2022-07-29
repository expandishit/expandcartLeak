<?php

class Sprint6
{
    private $clientdb;

    private $masterdb;

    private $config;

    private $registry;

    private $log = [];

    public function __construct($registry, $clientdb, $masterdb, $config)
    {
        $this->config   = $config;
        $this->masterdb = $masterdb;
        $this->clientdb = $clientdb;
        $this->registry = $registry;
    }

    public function query($sql)
    {
        try {
            return $this->clientdb->query($sql);
        } catch (\Exception $e) {
            $this->log[] = $e->getMessage();
        }
    }

    public function insertSetting($group, $data)
    {
        $sql = "INSERT INTO `setting` SET store_id = 0, `group` = '%s', `key` = '%s', `value` = '%s', serialized = %d";

        foreach ($data as $key => $value) {
            if (!$this->config->get($key)) {
                $parsedSql = vsprintf($sql, [
                    $group,
                    is_string($key) ? $key : $group . "_" . $key,
                    is_array($value) ? serialize($value) : $value,
                    is_array($value) ? 1 : 0,
                ]);
                $this->query($parsedSql);
            }
        }
    }

    public function updateSetting($group, $data)
    {
        $sql = "UPDATE `setting` SET `value` = '%s', serialized = %d WHERE store_id = 0 AND `group` = '%s' AND `key` = '%s'";

        foreach ($data as $key => $value) {
            if (!$this->config->get($key)) {
                $parsedSql = vsprintf($sql, [
                    is_array($value) ? serialize($value) : $value,
                    is_array($value) ? 1 : 0,
                    $group,
                    is_string($key) ? $key : $group . "_" . $key,
                ]);
                $this->query($parsedSql);
            }
        }
    }

    public function removeSettingByKey($key)
    {
        $this->query(sprintf("DELETE FROM `setting` WHERE `key` = '%s'", $key));
    }

    public function insertExtension($code)
    {
        if (\Extension::isInstalled($code)) {
            return false;
        }

        $sql = "INSERT INTO extension SET `type` = 'module', `code` = '%s'";
        $this->query(sprintf($sql, $code));

        return $this->clientdb->getLastId();
    }

    public function upgrade()
    {
        if (IS_ADMIN){

            $assistant_version = $this->config->get('assistant_version') ?: '1.0';

            if ($assistant_version >= '1.1') {
                $this->log[] = 'version is updated';
                return false;
                }

            if ($this->config->get('running_migration')) {
                $this->log[] = 'running process';
                return false;
            }

            $this->insertSetting('migration', ['running_migration' => 1]);

//
//            $download = $this->query('SELECT count(product_id) as total FROM `product_to_download`');
//            if ($download->row['total'] > 0) {
//                $this->insertExtension('product_attachments');
//                $this->insertSetting('product_attachments', ['product_attachments' => [
//                    'status' => 1,
//                ]]);
//
//                try {
//                    $this->purchaseApp('product_attachments');
//                } catch (\Exception $e) {
//                    $this->log[] = $e->getMessage();
//                }
//            }

            $modelPath = 'model/setting/setting.php';

            require_once $modelPath;

            $class = new ModelSettingSetting($this->registry);

            $getting_started_guides = $class->getGuideValue("GETTING_STARTED");

            $assistant_guide_json = json_decode(file_get_contents('json/assistant_guide.json'), true);

            $assistant_guide_array=array();
            foreach ($assistant_guide_json as $assistant_guide_json_item){
                $assistant_guide_array[]=$assistant_guide_json_item["name"];
            }

            foreach ($getting_started_guides as $key => $value){
                if (in_array($key,$assistant_guide_array)){
                    if ($value==1){
                        $class->editGuideValue("ASSISTANT",$key, '1');
                    }else{
                        $class->editGuideValue("ASSISTANT",$key, '0');
                    }

                    if (($removed_key = array_search($key, $assistant_guide_array)) !== false) {
                        unset($assistant_guide_array[$removed_key]);
                    }
                }
            }

            foreach ($assistant_guide_array as $assistant_guide){
                $class->editGuideValue("ASSISTANT",$assistant_guide, '0');
            }

            $query = $this->clientdb->query("SELECT * FROM " . DB_PREFIX . "geo_zone");

            foreach ($query->rows as $item){
                if ($item['geo_zone_id'] > 5){
                    $class->editGuideValue("ASSISTANT","GEO_ZONES", '1');
                }
            }

            $this->insertExtension('product_review');

    //        $this->insertExtension('product_preparation_period');
    //        $this->insertSetting('product_preparation_period', ['product_preparation_period' => [
    //            'status' => 1,
    //        ]]);

    //        $this->query(
    //            "ALTER TABLE `product` Add `demo` tinyint(1) DEFAULT 0, Add `is_simple` tinyint(1)  DEFAULT 0,Add `unlimited` tinyint(1) DEFAULT 0"
    //        );
    //
    //        $this->query("ALTER TABLE `product_special` Add `default` tinyint(1) DEFAULT 0");
    //        $this->query(
    //            "ALTER TABLE `attribute` Add `type` varchar(100) NOT NULL DEFAULT 'text', Add `glyphicon` varchar(100) NOT NULL DEFAULT 'fa fa-check-circle'"
    //        );
    //
    //        $this->insertSetting('config', ['enable_advanced_mode' => 1]);
    //
    //        $generalUse = $this->query('SELECT product_id FROM product where general_use IS NOT NULL and general_use <> ""');
    //
    //        if ($generalUse->num_rows > 0) {
    //            $this->insertSetting('config', ['enable_general_use_field' => 1]);
    //        }

    //        try {
    //            $this->installRewardApp();
    //        } catch (\Exception $e) {
    //            $this->log[] = $e->getMessage();
    //        }
    //
    //        try {
    //            $this->installAdvancedAttributes();
    //        } catch (\Exception $e) {
    //            $this->log[] = $e->getMessage();
    //        }
    //
            $this->removeSettingByKey('running_migration');

            if ($this->config->get('assistant_version')) {
                $this->updateSetting('assistant', ['assistant_version' => "1.1"]);
            } else {
                $this->insertSetting('assistant', ['assistant_version' => "1.1"]);
            }
        }
    }

    public function installRewardApp()
    {
        if (\Extension::isInstalled('reward_points_pro')) {
            throw new \Exception('rewared app is already installed');
        }

        $data = $this->query('SELECT count(product_reward_id) as total FROM product_reward');
        if ($data->row['total'] < 1) {
            throw new \Exception('no need to install the rewared app');
        }

        $modelPath = 'admin/controller/module/reward_points_pro.php';
        if (IS_ADMIN) {
            $modelPath = 'controller/module/reward_points_pro.php';
        }

        try {
            require_once $modelPath;

            $class = new ControllerModuleRewardPointsPro($this->registry);

            $class->install();

            $this->insertExtension('reward_points_pro');
        } catch (\Exception $e) {
            $this->log[] = $e->getMessage();
        }

        try {
            $this->purchaseApp('reward_points_pro');
        } catch (\Exception $e) {
            $this->log[] = $e->getMessage();
        }
    }

    public function installAdvancedAttributes()
    {
        if (\Extension::isInstalled('advanced_product_attributes')) {
            throw new \Exception('advanced attributes app already installed');
        }

        $data = $this->query('SELECT count(attribute_id) as total FROM attribute');
        if ($data->row['total'] < 1) {
            throw new \Exception('no need to install the radvanced attributes app ');
        }

        $modelPath = 'admin/model/module/advanced_product_attributes/settings.php';
        if (IS_ADMIN) {
            $modelPath = 'model/module/advanced_product_attributes/settings.php';
        }

        try {
            require_once $modelPath;

            $class = new ModelModuleAdvancedProductAttributesSettings($this->registry);

            $class->install();

            $this->insertExtension('advanced_product_attributes');
            $this->insertSetting('advanced_product_attributes', ['advanced_product_attribute_status' => 1]);
        } catch (\Exception $e) {
            $this->log[] = $e->getMessage();
        }

        try {
            $this->purchaseApp('advanced_product_attributes');
        } catch (\Exception $e) {
            $this->log[] = $e->getMessage();
        }
    }

    public function purchaseApp($code)
    {
        $app = $this->masterdb->query(sprintf("SELECT id FROM `appservice` where name = '%s'", $code));

        if ($app->num_rows < 1) {
            throw new \Exception('invalid app');
        }

        $sql = "SELECT * FROM `storeappservice` where appserviceid = %d and storecode = '%s'";

        $ifExists = $this->masterdb->query(sprintf($sql, $app->row['id'], STORECODE));

        if ($ifExists->num_rows > 0) {
            throw new \Exception('already purchased');
        }

        $install = "INSERT INTO `storeappservice` ( `storecode`, `appserviceid`, `apptimestamp`) VALUES ('%s',%s, now())";

        $this->masterdb->query(sprintf($install, STORECODE, $app->row['id']));
    }

    public function __destruct()
    {
        if (count($this->log) > 0) {

            $log = json_encode($this->log);

            $path = sprintf('%s/OnlineStores/ecdata/migration/%s.json', ONLINE_STORES_PATH, STORECODE);

            if (file_exists($path) == false) {
                file_put_contents($path, $log, FILE_APPEND);
            }
        }
    }
}
