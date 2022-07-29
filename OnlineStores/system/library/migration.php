<?php
class Migration {

    public function __construct($registry, $clientdb, $masterdb, $config)
    {
        $this->config   = $config;
        $this->ecusersdb = $masterdb;
        $this->db = $clientdb;
        $this->registry = $registry;
    }

    /**
     * @return false
     * @throws Exception
     */
    public function call() {

        if ($this->config->get('platform_version') == 1.1) {
            return false;
        }

        if (! $this->config->get('platform_version') || $this->config->get('platform_version') == 1.0) {
            if ( $this->config->get('running_migration')) {
                $this->record_log("migration is running");
                return false;
            }
            $this->insertSetting('migration', ['running_migration' => 1]);
            $this->installAdvancedAttributesApp();
            $this->installRewardApp();
            $this->runMysqlScripts();
            $this->generalUseFieldSql();
            $this->addProductAttachmentsToPurchasedApps();
            $this->removeSettingByKey("running_migration");
            $this->insertSetting('platform', ['platform_version' => "1.1"]);
        }
    }

    private function installAdvancedAttributesApp()
    {
        $sql="SELECT count(attribute_id) as total FROM attribute";
        $result = $this->query($sql);

        if ( $result->row['total'] > 0 ){
            if (! \Extension::isInstalled('advanced_product_attributes')){
                $this->query("START TRANSACTION;");
                $modelPath = 'admin/model/module/advanced_product_attributes/settings.php';
                if (IS_ADMIN) {
                    $modelPath = 'model/module/advanced_product_attributes/settings.php';
                }
                require_once $modelPath;
                $class = new ModelModuleAdvancedProductAttributesSettings($this->registry);
                $class->install();
                $this->insertExtension('advanced_product_attributes');
                $this->insertSetting('advanced_product_attributes', ['advanced_product_attribute_status' => 1]);
                $this->query("COMMIT;");
            }

            $sql = "SELECT id FROM appservice where name = 'advanced_product_attributes';";
            $result = $this->ecusersdb->query($sql);
            $id =(int) $result->row['id'];

            $sql = "SELECT count(id) as total FROM storeappservice where storecode = '".STORECODE."' and appserviceid = ". (int) $id;

            $result = $this->ecusersdb->query($sql);
            $count = $result->row['total'];

            if($count == 0){
                $sql = "INSERT INTO `storeappservice` ( `storecode`, `appserviceid`, `apptimestamp`) VALUES
                    ('".STORECODE."',".$id.", now());";
                $this->ecusersdb->query($sql);
            }
            $this->query("COMMIT;");
        }
        $this->record_log("app purchased and installed");

    }

    private function installRewardApp()
    {
        if (! file_exists ( ONLINE_STORES_PATH."/OnlineStores/system/logs/migrations/".STORECODE."_product_reward.json" ) ){

            $sql="SELECT count(product_reward_id) as total FROM product_reward";
            $result = $this->query($sql);

            if ( $result->row['total'] > 0 ){
                $sql = "SELECT  count(extension_id) as total from extension where `code` = 'reward_points_pro';";
                $result = $this->query($sql)->row['total'];

                if ( $result == 0 ) {
                    $this->query("START TRANSACTION;");
                    $modelPath = 'admin/controller/module/reward_points_pro.php';
                    if (IS_ADMIN) {
                        $modelPath = 'controller/module/reward_points_pro.php';
                    }
                    require_once $modelPath;
                    $class = new ControllerModuleRewardPointsPro($this->registry);
                    $class->install();
                    $this->insertExtension('reward_points_pro');
                    $this->query("COMMIT;");
                }

                $sql = "SELECT id FROM appservice where name = 'reward_points_pro';";
                $result = $this->ecusersdb->query($sql);
                $id =(int) $result->row['id'];

                $sql = "SELECT count(id) as total FROM storeappservice where storecode = '".STORECODE."' and appserviceid = ". (int) $id;
                $result = $this->ecusersdb->query($sql);
                $count = $result->row['total'];

                if($count == 0){
                    $sql = "INSERT INTO `storeappservice` ( `storecode`, `appserviceid`, `apptimestamp`) VALUES
                        ('".STORECODE."',". $id.", now());";
                    $this->ecusersdb->query($sql);
                }
            }
            $this->record_log("app purchased and installed");
        }

    }

    private function addProductAttachmentsToPurchasedApps(){
        if (! file_exists ( ONLINE_STORES_PATH."/OnlineStores/system/logs/migrations/".STORECODE."_product_attachments.json" ) ) {

            $sql = "SELECT id FROM appservice where name = 'product_attachments';";
            $result = $this->ecusersdb->query($sql);
            $id = (int) $result->row['id'];

            $sql = "INSERT INTO `storeappservice` ( `storecode`, `appserviceid`, `apptimestamp`) VALUES
                        ('" . STORECODE . "'," . $id . ", now());";
            $this->ecusersdb->query($sql);
            $this->record_log("app purchased successfully");
        }
    }

    private function runMysqlScripts()
    {
        $this->query("START TRANSACTION;");

        $sql = " INSERT INTO extension SET `type` = 'module', `code` = 'product_preparation_period';";
        $this->query($sql);

        $sql="           INSERT INTO setting SET store_id = '0', `group` = 'product_preparation_period', `key` = 'product_preparation_period', 
                        `value` = 'a:2:{s:4:\"days\";s:1:\"0\";s:6:\"status\";s:1:\"1\";}', serialized = '1';";
        $this->query($sql);

        $sql="                        INSERT INTO extension SET `type` = 'module', `code` = 'product_attachments';";
        $this->query($sql);

        $sql ="INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES (0, 'product_attachments', 'product_attachments', 'a:1:{s:6:\"status\";s:1:\"1\";}', 1);"  ;
        $this->query($sql);

        $sql ="                                  
                        ALTER TABLE `attribute`
                        
                        Add `type` varchar(100) NOT NULL DEFAULT 'text',
                        Add `glyphicon` varchar(100) NOT NULL DEFAULT 'fa fa-check-circle';
                        
                        
                        ALTER TABLE `product`
                        
                        Add `demo` tinyint(1) DEFAULT 0,
                           Add `is_simple` tinyint(1)  DEFAULT 0,
                        Add `unlimited` t   inyint(1) DEFAULT 0;
                        
                        
                        ALTER TABLE `product_special`
                        
                        Add `default` tinyint(1) DEFAULT 0;
                        
                    
                        INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
                        
                        (0, 'config', 'enable_advanced_mode', '1', 0);
                
        ";
        $array = explode(";",$sql);
        foreach ($array as $item){
            if (trim($item) != ""){
                $this->query(trim($item));
            }
        }

        $this->query("COMMIT;");
        $this->record_log("migration scripts ran successfully");

    }

    private function generalUseFieldSql(){
        if (! file_exists ( ONLINE_STORES_PATH."/OnlineStores/system/logs/migrations/".STORECODE."_general_use_field.json" ) ) {
            $sql = "SELECT count(product_id) as total FROM product where general_use IS NOT NULL and general_use <> '' ";
            $result = $this->query($sql)->row['total'];
            if ($result > 0 ){
                $sql = "INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES (0, 'config', 'enable_general_use_field', '1', 0);";
                $this->query($sql);
            }
            $this->record_log("general_use_field script ran successfully");
        }
    }

    public function insertSetting($group, $data)
    {
        $sql = "INSERT INTO `setting` SET store_id = 0, `group` = '%s', `key` = '%s', `value` = '%s', serialized = %d";
        foreach ($data as $key => $value) {
            $parsedSql = vsprintf($sql, [
                $group,
                is_string($key) ? $key : $group . "_" . $key,
                is_array($value) ? serialize($value) : $value,
                is_array($value) ? 1 : 0,
            ]);
            $this->query($parsedSql);
        }
    }

    public function removeSettingByKey($key)
    {
        $this->query(sprintf("DELETE FROM `setting` WHERE `key` = '%s'", $key));
    }

    public function insertExtension($code)
    {
        $sql = "INSERT INTO extension SET `type` = 'module', `code` = '%s'";
        $this->query(sprintf($sql, $code));
        return $this->db->getLastId();
    }

    public function query($sql)
    {
        try {
            return $this->db->query($sql);
        } catch (\Exception $e) {
            $this->record_log($e->getMessage(),'errors');
        }
    }

    public function record_log($msg){
        if (! file_exists(ONLINE_STORES_PATH."/OnlineStores/system/logs/migrations")) {
            mkdir(ONLINE_STORES_PATH ."/OnlineStores/system/logs/migrations", 0777, true);
        }

        $log_message=array(
            "StoreCode" => STORECODE,
            "DateTime" => date("Y-m-d h:i:s A"),
            "Msg" => $msg
        );
        $log_message_json=json_encode($log_message).", \r\n";
        file_put_contents(ONLINE_STORES_PATH."/OnlineStores/system/logs/migrations/".STORECODE.".json",$log_message_json,FILE_APPEND);

    }
}
?>