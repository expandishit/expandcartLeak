<?php
Class ModelMarketplaceCommon extends Model {
    public function getBillingEmail() {
        return 'test@test.com';
    }

    public function hasApp($app_name) {
        $sql =  "SELECT `appservice`.`id`, IFNULL(`storeappservice`.`id`, 0) storeappserviceid, IFNULL(`packageappservice`.`id`, 0) packageappserviceid FROM `appservice` ".
                "LEFT JOIN `storeappservice` ON `appservice`.`id` = `storeappservice`.`appserviceid` AND `storeappservice`.`storecode`='" . STORECODE . "'".
                "LEFT JOIN `packageappservice` ON `appservice`.`id` = `packageappservice`.`AppServiceId` AND `packageappservice`.`PackageId`='" . PRODUCTID . "'".
                "WHERE  `appservice`.`name` = '" . $app_name . "'";

        $query = $this->db->query($sql);

        if ($query->row) {
            $this->load->model('setting/extension');
            if($this->model_setting_extension->isTrial($app_name)) {
                return array("appserviceid" => $query->row['id'], "hasapp" => true);
            }

            if ($query->row['storeappserviceid'] > 0 || $query->row['packageappserviceid'] > 0)
                return array("appserviceid" => $query->row['id'], "hasapp" => true);
            else
                return array("appserviceid" => $query->row['id'], "hasapp" => false);
        }
        else
            return array("appserviceid" => 0, "hasapp" => false);
    }

    public function getMyApps() {
        $sql =  "SELECT `appservicedesc`.`Name`, ".
                "`appservicedesc`.`MiniDescription`, ".
                "`appservice`.`HomeImage` image, ".
                "`appservice`.`id`, ".
                "`appservice`.`name` extension, ".
                "IFNULL(`appservice`.`whmcsappserviceid`, 0) whmcsappserviceid, ".
                "`storeappservice`.`isbundle` ".
                "FROM `appservice` ".
                "JOIN `storeappservice` ON `appservice`.`id` = `storeappservice`.`appserviceid` ".
                "LEFT JOIN `packageappservice` ON `appservice`.`id` = `packageappservice`.`AppServiceId` AND `packageappservice`.`PackageId` = ". PRODUCTID . " " .
                "JOIN `appservicedesc` ON `appservice`.`id` = `appservicedesc`.`appserviceid` AND `appservicedesc`.`lang`='" . $this->language->get('code') . "' ".
                "WHERE `appservice`.`type`=1 AND `storeappservice`.`storecode`='" . STORECODE . "'  AND `packageappservice`.`id` IS NULL ";
        $sql = $sql . "UNION " .
            "SELECT `appservicedesc`.`Name`, ".
            "`appservicedesc`.`MiniDescription`, ".
            "`appservice`.`HomeImage` image, ".
            "`appservice`.`id`, ".
            "`appservice`.`name` extension, ".
            "IFNULL(`appservice`.`whmcsappserviceid`, 0) whmcsappserviceid, ".
            "1 isbundle ".
            "FROM `appservice` ".
            "JOIN `packageappservice` ON `appservice`.`id` = `packageappservice`.`AppServiceId` ".
            "JOIN `appservicedesc` ON `appservice`.`id` = `appservicedesc`.`appserviceid` AND `appservicedesc`.`lang`='" . $this->language->get('code') . "' ".
            "WHERE `appservice`.`type`=1 AND `packageappservice`.`PackageId`=" . PRODUCTID;
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getAvailableApps() {
        $sql =  "SELECT `appservicedesc`.`Name`, ".
            "`appservicedesc`.`MiniDescription`, ".
            "`appservice`.`HomeImage` image, ".
            "`appservice`.`id`, ".
            "`appservice`.`freeplan`, ".
            "`appservice`.`freepaymentterm`, ".
            "`appservice`.`recurring`, ".
            "`appservice`.`IsNew`, ".
            "IFNULL(`appservice`.`whmcsappserviceid`, 0) whmcsappserviceid, ".
            "IFNULL(`storeappservice`.`isbundle`, 0) isbundle, ".
            "IFNULL(`storeappservice`.`id`, -1) storeappserviceid, ".
            "IFNULL(`packageappservice`.`id`, -1) packageappserviceid, ".
            "`appservice`.`price`, ".
            "`appservice`.`name` extension ".
            "FROM `appservice` ".
            "LEFT JOIN `storeappservice` ON `appservice`.`id` = `storeappservice`.`appserviceid` AND `storeappservice`.`storecode`='" . STORECODE . "' ".
            "LEFT JOIN `packageappservice` ON `appservice`.`id` = `packageappservice`.`AppServiceId` AND `packageappservice`.`PackageId` = ". PRODUCTID . " " .
            "JOIN `appservicedesc` ON `appservice`.`id` = `appservicedesc`.`appserviceid` AND `appservicedesc`.`lang`='" . $this->language->get('code') . "' ".
            "WHERE `appservice`.`type`=1 ORDER BY `appservice`.`order`";
            //"AND NOT EXISTS (SELECT 1 FROM `storeappservice` WHERE `appservice`.`id` = `storeappservice`.`appserviceid` AND `storeappservice`.`storecode`='" . STORECODE . "')";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getAvaliableServices() {
        $sql =  "SELECT `appservicedesc`.`Name`, ".
            "`appservicedesc`.`MiniDescription`, ".
            "`appservice`.`HomeImage` image, ".
            "`appservice`.`id`, ".
            "`appservice`.`freeplan`, ".
            "`appservice`.`freepaymentterm`, ".
            "`appservice`.`recurring`, ".
            "`appservice`.`IsNew`, ".
            "`appservice`.`IsQuantity`, ".
            "`appservice`.`whmcsappserviceid` whmcsappserviceid, ".
            "IFNULL(`appservice`.`whmcsappserviceid`, 0) whmcsappserviceid, ".
            "`appservice`.`price` ".
            "FROM `appservice` ".
            "JOIN `appservicedesc` ON `appservice`.`id` = `appservicedesc`.`appserviceid` AND `appservicedesc`.`lang`='" . $this->language->get('code') . "' ".
            "WHERE `appservice`.`type`=2 ORDER BY `appservice`.`order`";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getAvaliableFeatures() {
        $sql =  "SELECT `appservicedesc`.`Name`, ".
            "`appservicedesc`.`MiniDescription`, ".
            "`appservicedesc`.`image` ".
            "`appservice`.`id`, ".
            "`appservice`.`whmcsappserviceid` whmcsappserviceid, ".
            "`appservice`.`price` ".
            "FROM `appservice` ".
            "JOIN `appservicedesc` ON `appservice`.`id` = `appservicedesc`.`appserviceid` AND `appservicedesc`.`lang`='" . $this->language->get('code') . "' ".
            "WHERE `appservice`.`type`=3";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getAppService($appserviceId) {
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
            "`appservice`.`IsQuantity` ".
            "FROM `appservice` ".
            "LEFT JOIN `storeappservice` ON `appservice`.`id` = `storeappservice`.`appserviceid`  AND `storeappservice`.`storecode`='" . STORECODE . "' ".
            "LEFT JOIN `packageappservice` ON `appservice`.`id` = `packageappservice`.`AppServiceId` AND `packageappservice`.`PackageId` = ". PRODUCTID . " " .
            "JOIN `appservicedesc` ON `appservice`.`id` = `appservicedesc`.`appserviceid` AND `appservicedesc`.`lang`='" . $this->language->get('code') . "' ".
            "WHERE `appservice`.`id`=" . $appserviceId;

        $query = $this->db->query($sql);
        return $query->rows[0];
    }
}
