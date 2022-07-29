<?php

use ExpandCart\Foundation\String\Slugify;

class ModelCatalogInformation extends Model
{
    public function addInformation($data)
    {

        $this->db->execute("
        INSERT INTO " . DB_PREFIX . "information SET
        bottom =?,
        status =?,
        sort_order=?
       ", [
        (isset($data['bottom']) ? (int)$data['bottom'] : 0),
        (int)$data['status'],
          (int)$data['sort_order']
          ]
      );

        $information_id = $this->db->getLastId();

        foreach ($data['information_description'] as $language_id => $value) {
  
            $this->db->execute("INSERT INTO " . DB_PREFIX . "information_description SET
            information_id =?,
            language_id =?, 
            title =?, 
            slug =?, 
            description =?", 
                [
                    (int)$information_id ,
                    (int)$language_id ,
                   $value['title'] ,
                   (new Slugify)->slug($value['title']) ,
                    $value['description']
                ]);
        }

        if (isset($data['information_store']) && $data['information_store'] != 0) {
            foreach ($data['information_store'] as $store_id) {
         
                $this->db->execute("INSERT INTO " . DB_PREFIX . "information_to_store SET
				information_id =?, store_id =?", 
					[
						(int)$information_id,
						(int)$store_id
					]);
        
            }
        }else{
     
                $this->db->execute("INSERT INTO " . DB_PREFIX . "information_to_store SET
				information_id =?, store_id =?", 
					[
						(int)$information_id,
					0
					]);
        }

        if (isset($data['information_layout'])) {
            foreach ($data['information_layout'] as $store_id => $layout) {
                if ($layout) {
           
                    $this->db->execute("INSERT INTO " . DB_PREFIX . "information_to_layout SET
                    information_id =?, store_id =?, layout_id =?", 
                        [
                            (int)$information_id,
                            (int)$store_id,
                            (int)$layout['layout_id'] 
                        ]);
                }
            }
        }

        if ($data['keyword']) {
    
            $this->db->execute("INSERT INTO " . DB_PREFIX . "url_alias SET
            query =?, keyword =?", 
                [
                    "information_id=" . (int)$information_id ,
                    $data['keyword']
                ]);
        }

        $this->cache->delete('information');
    }

    public function editInformation($information_id, $data)
    {
  

        $this->db->execute("
        UPDATE " . DB_PREFIX . "information SET
        bottom =?,
        status =?,
        sort_order=?
        WHERE information_id =?
       ", [
        (isset($data['bottom']) ? (int)$data['bottom'] : 0),
        (int)$data['status'],
          (int)$data['sort_order'],
           (int)$information_id
       ]
        );

      
        $this->db->execute("DELETE FROM " . DB_PREFIX . "information_description
		WHERE information_id =?", [(int)$information_id]);


        foreach ($data['information_description'] as $language_id => $value) {
  
            $this->db->execute("INSERT INTO " . DB_PREFIX . "information_description SET
            information_id =?,
            language_id =?, 
            title =?, 
            slug =?, 
            description =?", 
                [
                    (int)$information_id ,
                    (int)$language_id ,
                   $value['title'],
                   (new Slugify)->slug($value['title']),
                   $value['description']
                ]);
        }

  
        $this->db->execute("DELETE FROM " . DB_PREFIX . "information_to_store
		WHERE information_id =?", [(int)$information_id]);

        if (isset($data['information_store']) && $data['information_store'] != 0) {
            foreach ($data['information_store'] as $store_id) {
         
                $this->db->execute("INSERT INTO " . DB_PREFIX . "information_to_store SET
				information_id =?, store_id =?", 
					[
						(int)$information_id,
						(int)$store_id
					]);
        
            }
        }else{
     
                $this->db->execute("INSERT INTO " . DB_PREFIX . "information_to_store SET
				information_id =?, store_id =?", 
					[
						(int)$information_id,
					0
					]);
        }



    $this->db->execute("DELETE FROM " . DB_PREFIX . "information_to_layout
		WHERE information_id =?", [(int)$information_id]);


 
        if (isset($data['information_layout'])) {
            foreach ($data['information_layout'] as $store_id => $layout) {
                if ($layout) {
           
                    $this->db->execute("INSERT INTO " . DB_PREFIX . "information_to_layout SET
                    information_id =?, store_id =?, layout_id =?", 
                        [
                            (int)$information_id,
                            (int)$store_id,
                            (int)$layout['layout_id'] 
                        ]);
                }
            }
        }


        $this->db->execute("DELETE FROM " . DB_PREFIX . "url_alias
		WHERE information_id =?", [(int)$information_id]);
    

        if ($data['keyword']) {
    
            $this->db->execute("INSERT INTO " . DB_PREFIX . "url_alias SET
            query =?, keyword =?", 
                [
                    "information_id=" . (int)$information_id ,
                   $data['keyword']
                ]);
        }

        $this->cache->delete('information');
    }

    public function deleteInformation($information_id)
    {
        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "information
            WHERE information_id = '" . (int)$information_id . "'"
        );

        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "information_description
            WHERE information_id = '" . (int)$information_id . "'"
        );

        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "information_to_store
            WHERE information_id = '" . (int)$information_id . "'"
        );

        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "information_to_layout
            WHERE information_id = '" . (int)$information_id . "'"
        );

        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "url_alias
            WHERE query = 'information_id=" . (int)$information_id . "'"
        );

        $this->cache->delete('information');
    }

    public function getInformation($information_id)
    {
        $queryString = $subQuery = [];

        $subQuery[] = "SELECT keyword FROM " . DB_PREFIX . "url_alias";
        $subQuery[] = "WHERE query = 'information_id=" . (int)$information_id . "'";

        $queryString[] = "SELECT DISTINCT *, (%s) AS keyword FROM " . DB_PREFIX . "information";
        $queryString[] = "WHERE information_id = '" . (int)$information_id . "'";
        $query = $this->db->query(sprintf(implode(' ', $queryString), implode(' ', $subQuery)));

        return $query->row;
    }

    public function getInformations($data = array())
    {
        if ($data) {

            $queryString = [];

            $queryString[] = "SELECT * FROM " . DB_PREFIX . "information i";
            $queryString[] = "LEFT JOIN " . DB_PREFIX . "information_description id";
            $queryString[] = "ON (i.information_id = id.information_id)";
            $queryString[] = "WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";

            $sort_data = array(
                'id.title',
                'i.sort_order'
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $queryString[] = " ORDER BY " . $data['sort'];
            } else {
                $queryString[] = " ORDER BY id.title";
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

            $query = $this->db->query(implode(' ', $queryString));

            return $query->rows;
        } else {
            $information_data = $this->cache->get('information.' . (int)$this->config->get('config_language_id'));

            if (!$information_data) {
                $queryString = [];

                $queryString[] = "SELECT * FROM " . DB_PREFIX . "information i";
                $queryString[] = "LEFT JOIN " . DB_PREFIX . "information_description id";
                $queryString[] = "ON (i.information_id = id.information_id)";
                $queryString[] = "WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";

                $sort_data = array(
                    'id.title',
                    'i.sort_order'
                );

                if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                    $queryString[] = " ORDER BY " . $data['sort'];
                } else {
                    $queryString[] = " ORDER BY id.title";
                }

                if (isset($data['order']) && ($data['order'] == 'DESC')) {
                    $queryString[] = " DESC";
                } else {
                    $queryString[] = " ASC";
                }

                $query = $this->db->query(implode(' ', $queryString));

                $information_data = $query->rows;

                $this->cache->set('information.' . (int)$this->config->get('config_language_id'), $information_data);
            }

            return $information_data;
        }
    }

    public function getInformationDescriptions($information_id)
    {
        $information_description_data = array();

        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "information_description
            WHERE information_id = '" . (int)$information_id . "'"
        );

        foreach ($query->rows as $result) {
            $information_description_data[$result['language_id']] = array(
                'title' => $result['title'],
                'description' => $result['description']
            );
        }

        return $information_description_data;
    }

    public function getInformationStores($information_id)
    {
        $information_store_data = array();

        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "information_to_store
            WHERE information_id = '" . (int)$information_id . "'"
        );

        foreach ($query->rows as $result) {
            $information_store_data[] = $result['store_id'];
        }

        return $information_store_data;
    }

    public function getInformationLayouts($information_id)
    {
        $information_layout_data = array();

        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "information_to_layout
            WHERE information_id = '" . (int)$information_id . "'"
        );

        foreach ($query->rows as $result) {
            $information_layout_data[$result['store_id']] = $result['layout_id'];
        }

        return $information_layout_data;
    }

    public function getTotalInformations()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information");

        return $query->row['total'];
    }

    public function getTotalInformationsByLayoutId($layout_id)
    {
        $query = $this->db->query(
            "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information_to_layout
            WHERE layout_id = '" . (int)$layout_id . "'"
        );

        return $query->row['total'];
    }

    public function dtHandler($data)
    {
        $queryString = [];

        $fields = '*';

        $queryString[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'information i';
        $queryString[] = 'LEFT JOIN ' . DB_PREFIX . 'information_description id';
        $queryString[] = 'ON (i.information_id = id.information_id)';
        $queryString[] = 'WHERE id.language_id = "' . (int)$this->config->get('config_language_id') . '"';

        if (!empty($data['filter_name'])) {
            $queryString[] = "AND id.title LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = array(
            'title',
            'sort_order',
            'status',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY id.title";
        }

        if (isset($data['order']) && ($data['order'] == 'desc')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] != -1) {
                $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
        }

        $queryData = $this->db->query(implode(' ', $queryString));

        $data = array(
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $queryData->num_rows
        );

        return $data;
    }
}
