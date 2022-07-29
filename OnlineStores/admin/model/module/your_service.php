<?php

class ModelModuleYourService extends Model 
{
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."ys_requests` (
            `ys_request_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `service_id` INT UNSIGNED NOT NULL,
            `requester_id` INT UNSIGNED NOT NULL,
            `requester_name` VARCHAR(255) DEFAULT NULL,
            `requester_email` VARCHAR(255) DEFAULT NULL,
            `requester_telephone` VARCHAR(255) DEFAULT NULL,
            `description` LONGTEXT DEFAULT NULL,
            `attachment` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`ys_request_id`)
        )");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."ys_services` (
            `ys_service_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `parent` INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`ys_service_id`)
        )");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."ys_service_description` (
            `service_id` INT UNSIGNED NOT NULL,
             `name` VARCHAR(255) DEFAULT NULL,
            `language_id` TINYINT NOT NULL
        )");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."ys_seller_settings` (
            `seller_id` INT UNSIGNED NOT NULL,
            `service_id` INT UNSIGNED NOT NULL
        )");

        mkdir(DIR_DOWNLOAD . 'ys_attachments');
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."ys_requests`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."ys_services`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."ys_service_description`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."ys_seller_settings`");
    }

    public function getRequests($data)
    {
        $fields = 'ysr.*, ysd.name AS service';

        $queryString = [];

        $queryString[] = "SELECT ".$fields." FROM " . DB_PREFIX . "ys_requests ysr";
        $queryString[] = "LEFT JOIN ys_service_description ysd ON ysr.service_id = ysd.service_id";
        $queryString[] = "WHERE ysd.language_id = " . $this->config->get('config_language_id');

        if (!empty($data['search'])) {
            $queryString[] = "AND (ysr.requester_name LIKE '%" . $this->db->escape($data['search']) . "%'";
            $queryString[] = "OR ysr.requester_email LIKE '%" . $this->db->escape($data['search']) . "%')";
            $queryString[] = "OR ysr.requester_telephone LIKE '%" . $this->db->escape($data['search']) . "%')";
            $queryString[] = "OR ysd.name LIKE '%" . $this->db->escape($data['search']) . "%')";
        }

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = [
            'ysr.requester_email',
            'ysr.requester_telephone'
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY ysr.ys_request_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $queryData = $this->db->query(implode(' ', $queryString));

        $data = [
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $queryData->num_rows
        ];

        return $data;
    }

    public function getRequest($request_id)
    {
        return $this->db->query("
            SELECT ysr.*, ysd.name as service
            FROM ".DB_PREFIX."ys_requests ysr 
            LEFT JOIN ys_service_description ysd ON ysr.service_id = ysd.service_id
            WHERE ysr.ys_request_id = ".(int) $this->db->escape($request_id)."
            AND ysd.language_id = " . $this->config->get('config_language_id'))->row;
    }

    public function getService($service_id)
    {
        $data = $this->db->query("
            SELECT ys.*, ysd.* FROM ".DB_PREFIX."ys_services ys
            LEFT JOIN ys_service_description ysd
            ON ys.ys_service_id = ysd.service_id
            WHERE ys.ys_service_id = ".(int) $this->db->escape($service_id));
        $service = [
            'parent' => $data->row['parent'],
            'name' => []
        ];
        foreach ($data->rows as $row)
        {
            $service['name'][$row['language_id']] = $row['name'];
        }
        if ($data->row['parent'] == '0')
        {
            $service['sub_services'] = [];
            $subServices = $this->db->query("SELECT `ys_service_id` FROM ".DB_PREFIX."ys_services WHERE `parent` = ".(int) $this->db->escape($service_id))->rows;
            
            foreach ($subServices as $subService)
            {
                $service['sub_services'][] = $subService['ys_service_id'];
            }
        }
        return $service;
    }

    public function getServices($data)
    {
        $queryString = [];

        $fields = 'ys.ys_service_id, ysd.name';

        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "ys_services ys";
        $queryString[] = "LEFT JOIN ys_service_description ysd";
        $queryString[] = "ON ys.ys_service_id = ysd.service_id";
        $queryString[] = "WHERE ysd.language_id = " . $this->config->get('config_language_id');

        if (!empty($data['search'])) {
            $queryString[] = "AND (ysd.name LIKE '%" . $this->db->escape($data['search']) . "%')";
        }

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = [
            'ys.ys_service_id',
            'ys.name'
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY ys.ys_service_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $queryData = $this->db->query(implode(' ', $queryString));

        $data = [
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $queryData->num_rows
        ];

        return $data;
    }

    public function getServicesForDropdown($exclude = null)
    {
        $q = "
        SELECT ys.ys_service_id, ysd.name, ys.parent
        FROM ".DB_PREFIX."ys_services ys
        LEFT JOIN ys_service_description ysd
        ON ys.ys_service_id = ysd.service_id
        WHERE ys.parent = 0 
        AND (SELECT COUNT(ys_sub.ys_service_id) FROM ys_services ys_sub WHERE parent = ys.ys_service_id) = 0
        AND ysd.language_id = " . $this->config->get('config_language_id');

        if ($exclude > 0)
        {
            $q .= " AND ys.ys_service_id <> " . $exclude;
        }
        
        return $this->db->query($q)->rows;
    }

    public function addService($data)
    {
        $this->db->query("INSERT INTO `".DB_PREFIX."ys_services` (`parent`) VALUES (0)");
        $insertId = $this->db->getLastId();
        foreach ($data['service']['name'] as $key => $value)
        {
            $this->db->query("
                INSERT INTO `".DB_PREFIX."ys_service_description` (`service_id`, `name`, `language_id`)
                VALUES (".$insertId.", '".$value."', ".$key.")
            ");
        }
        if (!empty($data['service']['sub_services']))
        {
            $this->db->query("
                UPDATE `".DB_PREFIX."ys_services` SET `parent` = " . $insertId . " 
                WHERE `ys_service_id` IN(".implode(',', $data['service']['sub_services']).")");
        }
    }

    public function updateService($data)
    {
        $this->db->query("
            UPDATE `".DB_PREFIX."ys_services` 
            SET `parent` = 0 WHERE `ys_service_id` = " . (int) $data['service_id']);
                
        foreach ($data['service']['name'] as $key => $value)
        {
            $this->db->query("
                UPDATE `".DB_PREFIX."ys_service_description`
                SET `name` = '".$value."'
                WHERE `service_id` = " . (int) $data['service_id'] . " 
                AND `language_id` = ".$key);
        }

        if (!empty($data['service']['sub_services']))
        {
            $this->db->query("
                UPDATE `".DB_PREFIX."ys_services` SET `parent` = " . (int) $data['service_id'] . " 
                WHERE `ys_service_id` IN(".implode(',', $data['service']['sub_services']).")");
        }
    }

    public function deleteRequest($request_id)
    {
        $this->db->query("DELETE FROM `".DB_PREFIX."ys_requests` WHERE `ys_request_id` = " . (int) $this->db->escape($request_id));
    }

    public function deleteService($service_id)
    {
        $this->db->query("DELETE FROM `".DB_PREFIX."ys_services` WHERE `ys_service_id` = " . (int) $this->db->escape($service_id));
        $this->db->query("DELETE FROM `".DB_PREFIX."ys_service_description` WHERE `service_id` = " . (int) $this->db->escape($service_id));
        $this->db->query("DELETE FROM `".DB_PREFIX."ys_requests` WHERE `service_id` = " . (int) $this->db->escape($service_id));
        $this->db->query("UPDATE `".DB_PREFIX."ys_services` SET `parent` = 0 WHERE `parent` = " . (int) $this->db->escape($service_id));
    }

}