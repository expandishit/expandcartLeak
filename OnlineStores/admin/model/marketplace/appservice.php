<?php

class ModelMarketplaceAppService extends Model
{
    /**
     * @var string
     */
    private $appserviceTable = 'appservice';

    /**
     * @var string
     */
    private $appservicedescTable = 'appservicedesc';

    /**
     * @var string
     */
    private $storeappserviceTable = 'storeappservice';

    /**
     * @var string
     */
    private $packageappserviceTable = 'packageappservice';

    private $product_id = PRODUCTID;

    public function __construct($registry) {
        parent::__construct($registry);
//        $this->load->model('plan/trial');
//        $trial = $this->model_plan_trial->getLastTrial();
//        if ($trial){
//            $this->product_id = $trial['plan_id'];
//        }
    }

    /**
     * Get applicatoins and services.
     *
     * @param array $filter
     *
     * @return array|void
     */
    public function getAppService($filter)
    {
        $query = $fields = [];

        $fields = [
            '`appservicedesc`.`Name`',
            '`appservicedesc`.`Description`',
            '`appservicedesc`.`MiniDescription`',
            '`appservice`.`id`',
            '`appservice`.`HomeImage` image',
            '`appservice`.`freeplan`',
            '`appservice`.`freepaymentterm`',
            '`appservice`.`recurring`',
            'IFNULL(`appservice`.`whmcsappserviceid`, 0) whmcsappserviceid',
            'IFNULL(`storeappservice`.`isbundle`, 0) isbundle',
            'IFNULL(`storeappservice`.`id`, -1) storeappserviceid',
            'IFNULL(`packageappservice`.`id`, -1) packageappserviceid',
            '`appservice`.`price`',
            '`appservice`.`type`',
            '`appservice`.`name` CodeName',
            '`appservice`.`name` extension',
            '`appservice`.`AppImage`',
            '`appservice`.`CoverImage`',
            '`appservice`.`IsNew`',
            '`appservice`.`IsQuantity`',
            '`appservice`.`link`',
            '`appservice`.`category`',
        ];
        $langauge_code = (in_array($this->language->get('code'),['ar','en'])) ? $this->language->get('code') : 'en';
        $query[] = 'SELECT ' . implode(', ', $fields) . ' FROM ' . $this->appserviceTable;
        $query[] = 'LEFT JOIN ' . $this->storeappserviceTable;
        $query[] = 'ON `appservice`.`id` = `storeappservice`.`appserviceid`';
        $query[] = 'AND `storeappservice`.`storecode`="' . STORECODE . '"';
        $query[] = 'LEFT JOIN ' . $this->packageappserviceTable;
        $query[] = 'ON `appservice`.`id` = `packageappservice`.`AppServiceId`';
        $query[] = 'AND `packageappservice`.`PackageId`="' . $this->product_id . '"';
        $query[] = 'JOIN ' . $this->appservicedescTable;
        $query[] = 'ON `appservice`.`id` = `appservicedesc`.`appserviceid`';
        $query[] = 'WHERE `appservicedesc`.`lang`="' . $langauge_code . '"';

        if (STAGING_MODE != 1){
            $query[] = 'AND `appservice`.`published`=1';
        }

        if (isset($filter['lookup'])) {
            $words = explode(' ', $this->db->escape($filter['lookup']));
            foreach ($words as $word) {
                $query[] = 'AND (appservicedesc.Name LIKE "%' . $this->db->escape($word) . '%"';
                $query[] = 'OR appservice.name LIKE "%' . $this->db->escape($word) . '%"';
                $query[] = 'OR appservicedesc.MiniDescription LIKE "%' . $this->db->escape($word) . '%")';
            }
        }

        if (isset($filter['application'])) {
            $query[] = 'AND appservice.type=1';
        }

        if (isset($filter['service'])) {
            $query[] = 'AND appservice.type=2';
        }

        if (isset($filter['isnew'])) {
            $query[] = 'AND appservice.IsNew=1';
        }

        if (isset($filter['free']) && !isset($filter['paid'])) {
            $query[] = 'AND appservice.price < 1';
        }

        if (isset($filter['paid']) && !isset($filter['free'])) {
            $query[] = 'AND appservice.price > 0';
        }

        if (isset($filter['purchased'])) {
            $query[] = 'AND storeappservice.id >= 0';
        }

        if (isset($filter['isbundle'])) {
            $query[] = 'AND packageappservice.id >= 0';
        }

        if (isset($filter['categories'])) {
            $categories = $filter['categories'];
            $categoryFilter = [];
            foreach ($categories as $category) {
                $categoryFilter[] = '(appservice.category LIKE "%' . $this->db->escape($category) . '%")';
            }

            $query[] = 'AND (' . implode('OR', $categoryFilter) . ')';
        }

        $data = $this->ecusersdb->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Get App/Service by ID
     *
     * @param $appserviceId
     * @return array|void
     */
    public function getAppServiceById($appserviceId) {
        $sql =  "SELECT `appservicedesc`.`Name`, ".
            "`appservicedesc`.`Description`, ".
            "`appservicedesc`.`MiniDescription`, ".
            "`appservice`.`id`, ".
            "`appservice`.`freeplan`, ".
            "`appservice`.`freepaymentterm`, ".
            "`appservice`.`recurring`, ".
            "IFNULL(`appservice`.`whmcsappserviceid`, 0) whmcsappserviceid, ".
            "IFNULL(`storeappservice`.`isbundle`, 0) isbundle, ".
            "IFNULL(`storeappservice`.`id`, -1) storeappserviceid, ".
            "IFNULL(`packageappservice`.`id`, -1) packageappserviceid, ".
            "`appservice`.`price`, ".
            "`appservice`.`link`, ".
            "`appservice`.`type`, ".
            "`appservice`.`name` CodeName, ".
            "`appservice`.`name` extension, ".
            "`appservice`.`AppImage`, ".
            "`appservice`.`CoverImage`, ".
            "`appservice`.`IsNew`, ".
            "`appservice`.`IsQuantity`, ".
            "`appservice`.`category` ".
            "FROM `appservice` ".
            "LEFT JOIN `storeappservice` ON `appservice`.`id` = `storeappservice`.`appserviceid`  AND `storeappservice`.`storecode`='" . STORECODE . "' ".
            "LEFT JOIN `packageappservice` ON `appservice`.`id` = `packageappservice`.`AppServiceId` AND `packageappservice`.`PackageId` = ". $this->product_id . " " .
            "JOIN `appservicedesc` ON `appservice`.`id` = `appservicedesc`.`appserviceid` AND `appservicedesc`.`lang`='" . $this->language->get('code') . "' ".
            "WHERE `appservice`.`id`=" . $appserviceId;

        $query = $this->ecusersdb->query($sql);
        return $query->rows[0];
    }

    public function getAppServiceByIds($appserviceIds) {
        $sql =  "SELECT `appservicedesc`.`Name`, ".
            "`appservicedesc`.`Description`, ".
            "`appservicedesc`.`MiniDescription`, ".
            "`appservice`.`id`, ".
            "`appservice`.`freeplan`, ".
            "`appservice`.`freepaymentterm`, ".
            "`appservice`.`recurring`, ".
            "IFNULL(`appservice`.`whmcsappserviceid`, 0) whmcsappserviceid, ".
            "IFNULL(`storeappservice`.`isbundle`, 0) isbundle, ".
            "IFNULL(`storeappservice`.`id`, -1) storeappserviceid, ".
            "IFNULL(`packageappservice`.`id`, -1) packageappserviceid, ".
            "`appservice`.`price`, ".
            "`appservice`.`link`, ".
            "`appservice`.`type`, ".
            "`appservice`.`name` CodeName, ".
            "`appservice`.`name` extension, ".
            "`appservice`.`AppImage`, ".
            "`appservice`.`CoverImage`, ".
            "`appservice`.`IsNew`, ".
            "`appservice`.`IsQuantity`, ".
            "`appservice`.`category` ".
            "FROM `appservice` ".
            "LEFT JOIN `storeappservice` ON `appservice`.`id` = `storeappservice`.`appserviceid`  AND `storeappservice`.`storecode`='" . STORECODE . "' ".
            "LEFT JOIN `packageappservice` ON `appservice`.`id` = `packageappservice`.`AppServiceId` AND `packageappservice`.`PackageId` = ". PRODUCTID . " " .
            "JOIN `appservicedesc` ON `appservice`.`id` = `appservicedesc`.`appserviceid` AND `appservicedesc`.`lang`='" . $this->language->get('code') . "' ".
            "WHERE `appservice`.`id` in (" . $appserviceIds .")".
            "ORDER BY FIELD(`appservice`.`id`,$appserviceIds)";

        $query = $this->ecusersdb->query($sql);
        return $query->rows;
    }

    /**
     * Get recommended applications and services.
     *
     * @return array|void
     */
    public function getRecommendedAppService()
    {
        // todo: update this query with recommendation factors
        $country_code = $this->getCountryCode($this->config->get('config_country_id'));

        if ($country_code == null){
            $country_code="All";
        }
        $query = $fields = [];

        $fields = [
            '`appservicedesc`.`Name`',
            '`appservicedesc`.`Description`',
            '`appservicedesc`.`MiniDescription`',
            '`appservice`.`id`',
            '`appservice`.`HomeImage` image',
            '`appservice`.`freeplan`',
            '`appservice`.`freepaymentterm`',
            '`appservice`.`recurring`',
            'IFNULL(`appservice`.`whmcsappserviceid`, 0) whmcsappserviceid',
            'IFNULL(`storeappservice`.`isbundle`, 0) isbundle',
            'IFNULL(`storeappservice`.`id`, -1) storeappserviceid',
            'IFNULL(`packageappservice`.`id`, -1) packageappserviceid',
            '`appservice`.`price`',
            '`appservice`.`type`',
            '`appservice`.`name` CodeName',
            '`appservice`.`name` extension',
            '`appservice`.`AppImage`',
            '`appservice`.`CoverImage`',
            '`appservice`.`IsNew`',
            '`appservice`.`IsQuantity`',
            '`appservice`.`link`',
            '`appservice`.`provider_id`',
            '`appservice`.`recommended`'
        ];

        $query[] = 'SELECT ' . implode(', ', $fields) . ' FROM ' . $this->appserviceTable;
        $query[] = 'LEFT JOIN ' . $this->storeappserviceTable;
        $query[] = 'ON `appservice`.`id` = `storeappservice`.`appserviceid`';
        $query[] = 'AND `storeappservice`.`storecode`="' . STORECODE . '"';
        $query[] = 'LEFT JOIN ' . $this->packageappserviceTable;
        $query[] = 'ON `appservice`.`id` = `packageappservice`.`AppServiceId`';
        $query[] = 'AND `packageappservice`.`PackageId`="' . $this->product_id . '"';
        $query[] = 'JOIN ' . $this->appservicedescTable;
        $query[] = 'ON `appservice`.`id` = `appservicedesc`.`appserviceid`';
        $query[] = 'WHERE `appservicedesc`.`lang`="' . $this->language->get('code') . '"';
//        $query[] = 'AND appservice.type=2';
        $query[] = 'AND (supported_countries like "'."%$country_code%".'" OR supported_countries is null)';

        if (STAGING_MODE != 1){
            $query[] = 'AND `appservice`.`published`=1';
        }
        $query[] ='order by';

        $query[] ='provider_id != 1 desc,provider_id asc,';

        $query[] ='`supported_countries` like "%'.$country_code.'%" desc,';

        $query[] ='recommended desc';

        $query[] ='LIMIT 4';

        $data = $this->ecusersdb->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Get applicatoins and services count.
     *
     * @return array|void
     */
    public function getCounts()
    {
        $query = 'SELECT type, COUNT(type) as _c FROM `appservice`  INNER  JOIN (select distinct appserviceid FROM appservicedesc) `appservicedesc`  ON  `appservice`.`id` = `appservicedesc`.`appserviceid`';

        if (STAGING_MODE != 1){
            $query .= ' WHERE appservice.published=1';
        }
        $query .=  ' GROUP BY  type';
        $data = $this->ecusersdb->query($query);

        $counts = array_column($data->rows, '_c', 'type');

        if ($data->num_rows) {
            return [
                'apps' => $counts[1],
                'services' => $counts[2]
            ];
        }

        return false;
    }

    /**
     * Get applicatoins and services categories.
     *
     * @return array|void
     */
    public function getCategories()
    {
        $query = 'SELECT category FROM `appservice` where category != "" ';

        if (STAGING_MODE != 1){
            $query .= ' and published=1';
        }

        $query .=' GROUP BY category';



        $data = $this->ecusersdb->query($query);

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Resolve categories in an 1-D array.
     *
     * @return array
     */
    public function resolveCategories($rows)
    {
        $rows = array_column($rows, 'category');

        $categories = [];

        foreach ($rows as $row) {
            $row = explode(',', $row);
            $categories = array_merge($categories, $row);
        }

        return array_unique($categories);
    }
    /***
     * Get installed mobile apps
     * Author: Bassem
     */
    public function getInstalledMobileApps()
    {
        # code...
        $query = [];
        $query[] = "SELECT * FROM ". $this->storeappserviceTable." as store_apps ";
        $query[] = " JOIN ". $this->appserviceTable." as app_service ";
        $query[] = " ON  store_apps.appserviceid = app_service.id";
        $query[] = " WHERE  store_apps.storecode = '" . STORECODE . "'";
        if (STAGING_MODE != 1){
            $query[] = " AND app_service.published=1";
        }
        $data = $this->ecusersdb->query(implode(" ",$query));
        if($data->num_rows > 0)
            return $data->rows;

        return false;
     }

    public function isAppCanBeInstalled($code)
    {
        $query = $fields = [];

        $fields = [
//            '`appservicedesc`.`Name`',
//            '`appservicedesc`.`Description`',
//            '`appservicedesc`.`MiniDescription`',
            '`appservice`.`id`',
//            '`appservice`.`HomeImage` image',
//            '`appservice`.`freeplan`',
//            '`appservice`.`freepaymentterm`',
//            '`appservice`.`recurring`',
//            'IFNULL(`appservice`.`whmcsappserviceid`, 0) whmcsappserviceid',
//            'IFNULL(`storeappservice`.`isbundle`, 0) isbundle',
            'IFNULL(`storeappservice`.`id`, -1) storeappserviceid',
            'IFNULL(`packageappservice`.`id`, -1) packageappserviceid',
//            '`appservice`.`price`',
//            '`appservice`.`type`',
//            '`appservice`.`name` CodeName',
//            '`appservice`.`name` extension',
//            '`appservice`.`AppImage`',
//            '`appservice`.`CoverImage`',
//            '`appservice`.`IsNew`',
//            '`appservice`.`IsQuantity`',
//            '`appservice`.`link`',
        ];

        $query[] = 'SELECT ' . implode(', ', $fields) . ' FROM ' . $this->appserviceTable;
        $query[] = 'LEFT JOIN ' . $this->storeappserviceTable;
        $query[] = 'ON `appservice`.`id` = `storeappservice`.`appserviceid`';
        $query[] = 'AND `storeappservice`.`storecode`="' . STORECODE . '"';
        $query[] = 'LEFT JOIN ' . $this->packageappserviceTable;
        $query[] = 'ON `appservice`.`id` = `packageappservice`.`AppServiceId`';
        $query[] = 'AND `packageappservice`.`PackageId`="' . $this->product_id . '"';
        $query[] = 'JOIN ' . $this->appservicedescTable;
        $query[] = 'ON `appservice`.`id` = `appservicedesc`.`appserviceid`';
        $query[] = 'WHERE `appservicedesc`.`lang`="' . $this->language->get('code') . '"';
        $query[] = 'AND `appservice`.`name` = "' . $code . '"';

        $data = $this->ecusersdb->query(implode(' ', $query));

        if ($data->num_rows) {
            $purchased = $data->row['storeappserviceid'] != -1;
            $isbundle = $data->row['packageappserviceid'] != -1;
            return $purchased || $isbundle;
        }

        return false;
    }

    public function getCountryCode($country_id) {

        $query = $this->db->query("SELECT iso_code_2 FROM "
            . DB_PREFIX . "`country`  WHERE `country_id` = '" .
            $country_id . "'");
        return $query->row['iso_code_2'];
    }

}
