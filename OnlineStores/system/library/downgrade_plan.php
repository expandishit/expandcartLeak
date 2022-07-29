<?php

class DowngradePlan
{
    private $clientdb;

    private $masterdb;

    private $config;

    private $registry;

    private $log = [];

    private $plan_id = PRODUCTID;

    private $genericConstants;

    public function __construct($registry, $clientdb, $masterdb, $config,$genericConstants)
    {
        if (! IS_ADMIN) {

            $this->config = $config;
            $this->masterdb = $masterdb;
            $this->clientdb = $clientdb;
            $this->registry = $registry;
            $this->genericConstants = $genericConstants;
            $trial = null;
            $modelPath = 'admin/model/plan/trial.php';

            try {
                require_once $modelPath;
                $planTrial = new ModelPlanTrial($this->registry);
                $trial = $planTrial->getLastTrial();
            } catch (\Exception $e) {
                $this->log[] = $e->getMessage();
            }

            if ($trial) {
                $this->plan_id = $trial['plan_id'];
            }

            $products_limit = $this->genericConstants["plans_limits"][$this->plan_id]['products_limit'];

            if (
                ($products_limit) && ($this->config->get('plan_downgrade_id') != $this->plan_id)
            ) {

                $modelPath = 'expandish/model/catalog/product.php';

                try {
                    //$this->load->model('catalog/product');
                    require_once $modelPath;
                    $catalogProduct = new ModelCatalogProduct($this->registry);

                    //$this->model_catalog_product->disableTrialProducts($this->products_limit);
                    $catalogProduct->disableTrialProducts($products_limit);

                } catch (\Exception $e) {
                    $this->log[] = $e->getMessage();
                }

                if ($this->config->get('plan_downgrade_id') != $this->plan_id) {
                    $data['plan_downgrade_id'] = $this->plan_id;
                    $this->insertUpdateSetting('plan', $data);
                }
            }
        }
    }

    public function query($sql)
    {
        try {
            return $this->clientdb->query($sql);
        } catch (\Exception $e) {
            $this->log[] = $e->getMessage();
        }
    }

    public function insertUpdateSetting($group, $data, $store_id = 0) {
        foreach ($data as $key => $value) {
            $this->config->set($key, $value);
            $query = $this->clientdb->query("SELECT 1 FROM " . DB_PREFIX . "setting WHERE `group` = '" . $this->clientdb->escape($group) . "' AND `key` = '" .
                $this->clientdb->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
            if($query->num_rows > 0) {
                if (!is_array($value)) {
                    $this->clientdb->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->clientdb->escape($value) . "' WHERE `group` = '" .
                        $this->clientdb->escape($group) . "' AND `key` = '" . $this->clientdb->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
                } else {
                    $this->clientdb->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->clientdb->escape(serialize($value)) . "', serialized = '1' WHERE `group` = '" . $this->clientdb->escape($group) . "' AND `key` = '" . $this->clientdb->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
                }
            } else {
                if (!is_array($value)) {
                    $this->clientdb->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" .
                        $this->clientdb->escape($group) . "', `key` = '" . $this->clientdb->escape($key) . "', `value` = '" . $this->clientdb->escape($value) . "'");
                } else {
                    $this->clientdb->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" .
                        $this->clientdb->escape($group) . "', `key` = '" . $this->clientdb->escape($key) . "', `value` = '" .
                        $this->clientdb->escape(serialize($value)) . "', serialized = '1'");
                }

            }
        }
    }

    public function __destruct()
    {
    }
}
