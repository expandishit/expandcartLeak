<?php

class ModelModuleYourService extends Model
{
    public function saveRequest($data)
    {
        $this->db->query("INSERT INTO `".DB_PREFIX."ys_requests` 
        (`service_id`, `requester_id`, `requester_name`, `requester_email`, `requester_telephone`, `description`, `attachment`)
        VALUES (
            ".(int) $this->db->escape($data['service_id']).",
            ".(int) $this->db->escape($data['requester_id']).",
            '".$this->db->escape($data['requester_name'])."',
            '".$this->db->escape($data['requester_email'])."',
            '".$this->db->escape($data['requester_telephone'])."',
            '".$this->db->escape($data['description'])."',
            '".$this->db->escape($data['attachment'])."'
        )");
        return $this->db->getLastId();
    }

    public function getServices($parent = 0)
    {
        return $this->db->query("
            SELECT ys.ys_service_id, ysd.name
            FROM ".DB_PREFIX."ys_services ys
            LEFT JOIN ys_service_description ysd
            ON ys.ys_service_id = ysd.service_id
            WHERE ys.parent = ".$parent."
            AND ysd.language_id = " . $this->config->get('config_language_id')
        )->rows;
    }

    public function getServiceRequests($data)
    {
        $fields = 'ysr.*, ysd.name AS service';

        $queryString = [];

        $queryString[] = "SELECT ".$fields." FROM " . DB_PREFIX . "ys_requests ysr";
        $queryString[] = "LEFT JOIN ys_service_description ysd ON ysr.service_id = ysd.service_id";
        $queryString[] = "WHERE ysr.service_id IN(".implode(',', $data['service_ids']).")";
        $queryString[] = "AND ysd.language_id = " . $this->config->get('config_language_id');

        if (!empty($data['search'])) {
            $queryString[] = "AND ysr.requester_name LIKE '%" . $this->db->escape($data['search']) . "%'";
            $queryString[] = "OR ysr.requester_email LIKE '%" . $this->db->escape($data['search']) . "%'";
            $queryString[] = "OR ysr.requester_telephone LIKE '%" . $this->db->escape($data['search']) . "%'";
            $queryString[] = "OR ysd.name LIKE '%" . $this->db->escape($data['search']) . "%'";
        }

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = [
            'ys_request_id',
            'requester_name',
            'requester_email',
            'requester_telephone',
            'service'
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY ysr.ys_request_id";
        }

        if (isset($data['order']) && ($data['order'] == 'ASC')) {
            $queryString[] = " ASC";
        } else {
            $queryString[] = " DESC";
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

    public function getServiceRequest($request_id)
    {
        return $this->db->query("
            SELECT ysr.*, ysd.name FROM ".DB_PREFIX."ys_requests ysr
            LEFT JOIN ys_service_description ysd
            ON ysr.service_id = ysd.service_id
            WHERE ysr.ys_request_id = ".(int) $this->db->escape($request_id)."
            AND ysd.language_id = " . $this->config->get('config_language_id'))->row;
    }

    public function getServicesWithSubServices()
    {
        $services = $this->getServices();
        foreach ($services as $index => $service)
        {
            $services[$index]['sub_services'] = $this->getServices($service['ys_service_id']);
        }
        return $services;
    }

    public function getServiceName($serviceId)
    {
        return $this->db->query("
            SELECT name FROM ".DB_PREFIX."ys_service_description
            WHERE service_id = ".(int) $this->db->escape($serviceId)."
            AND language_id = " . (int) $this->config->get('config_language_id')
        )->row['name'];
    }

    public function saveServiceSettings($sellerId, $serviceId)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "ys_seller_settings WHERE seller_id = " . (int) $sellerId);
        if(is_array($serviceId)){
            foreach ($serviceId as $service){
                $this->db->query("
        INSERT INTO " . DB_PREFIX . "ys_seller_settings (seller_id, service_id) 
        VALUES (".(int) $sellerId.",".(int) $service.")");
            }
        }else{
            $this->db->query("
        INSERT INTO " . DB_PREFIX . "ys_seller_settings (seller_id, service_id) 
        VALUES (".(int) $sellerId.",".(int) $serviceId.")");
        }

    }

    public function getServiceSettings($sellerId)
    {
        $serviceIds = [];
        $result = $this->db->query("SELECT service_id FROM " . DB_PREFIX . "ys_seller_settings WHERE seller_id = " . (int) $sellerId)->rows ?? [];
        if (!empty($result))
        {
            foreach ($result as $serviceId)
            {
                $serviceIds[] = $serviceId['service_id'];
            }
        }
        return $serviceIds;
    }

    public function getSellersMails($serviceId)
    {
        $emails = [];
        $results = $this->db->query("
            SELECT c.email FROM " . DB_PREFIX . "customer c
            LEFT JOIN ys_seller_settings yss
            ON c.customer_id = yss.seller_id
            WHERE yss.service_id = " . (int) $this->db->escape($serviceId)
        )->rows ?? [];

        if (!empty($results))
        {
            foreach($results as $result)
            {
                $emails[] = $result['email'];
            }
        }

        return $emails;
    }

    public function getSellerNotifications($sellerId)
    {
        $serviceSettings = $this->getServiceSettings($sellerId);
        if (count($serviceSettings) > 0)
        {
            return $this->getServiceRequests(['service_ids' => $serviceSettings, 'limit' => 35])['data'];
        }
        return [];
    }
}
