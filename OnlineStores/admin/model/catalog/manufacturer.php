    <?php

use ExpandCart\Foundation\String\Slugify;

class ModelCatalogManufacturer extends Model
{  public function addManufacturer($data)
    {
        $this->db->execute("
            INSERT INTO " . DB_PREFIX . "manufacturer SET
            name =?,
            slug =?,
            sort_order=?
      	 ", [
      	    $data['name'],
      	    (new Slugify)->slug($data['name']),
      	    (int)$data['sort_order']
      	    ]
        );
        
        $manufacturer_id = $this->db->getLastId();

        if (isset($data['image'])) {
			$this->db->execute("UPDATE " . DB_PREFIX . "manufacturer SET
			image=?
			WHERE manufacturer_id =?",
				[
					html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'),
					(int)$manufacturer_id
				]);
        }

        if (isset($data['manufacturer_store'])) {
            foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->execute("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET
				manufacturer_id =?, store_id =?", 
					[
						(int)$manufacturer_id,
						(int)$store_id
					]);
            }
        }

        if ($data['keyword']) {
			$this->db->execute("INSERT INTO " . DB_PREFIX . "url_alias SET
			query = ?,
			keyword =?", 
			[
				'manufacturer_id='.(int)$manufacturer_id,
				$data['keyword']
			]);
        }

        $this->cache->delete('manufacturer');
        $this->cache->delete('manufacturer.' . (int)$this->config->get('config_store_id'));

        $manufacturer['name'] = $this->db->escape($data['name']);
        $manufacturer['id'] = $manufacturer_id;
        return $manufacturer;
    }

    public function getManufacturerByName($name) {
        $stmt = "SELECT * FROM  manufacturer WHERE `name`='" . trim($this->db->escape($name)) . "'";
        return $this->db->query($stmt)->row;
    }
	
  public function editManufacturer($manufacturer_id, $data)
    {
        $this->db->execute("
            UPDATE " . DB_PREFIX . "manufacturer SET
            name =?,
            slug =?,
            sort_order=?
            WHERE manufacturer_id =?
      	 ", [
      	     $data['name'],
      	     (new Slugify)->slug($data['name']),
      	     (int)$data['sort_order'],
      	     (int)$manufacturer_id
      	 ]
            );
        if (isset($data['image'])) {
			$this->db->execute("UPDATE " . DB_PREFIX . "manufacturer SET
			image =?
			WHERE manufacturer_id=?", [
				html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'), 
				(int)$manufacturer_id
				]);
        }
		
		$this->db->execute("DELETE FROM " . DB_PREFIX . "manufacturer_to_store
		WHERE manufacturer_id =?", [(int)$manufacturer_id]);

        if (isset($data['manufacturer_store'])) {
            foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->execute("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET
				manufacturer_id=?, store_id=?", [(int)$manufacturer_id, (int)$store_id]);
            }
        }
		$this->db->execute("DELETE FROM " . DB_PREFIX . "url_alias
		WHERE query =?", ['manufacturer_id='.(int)$manufacturer_id]);

        if ($data['keyword']) {
			$this->db->execute("INSERT INTO " . DB_PREFIX . "url_alias
			SET query =?,
			keyword =?", ['manufacturer_id=' . (int)$manufacturer_id, $data['keyword']]);
        }

        $this->cache->delete('manufacturer');
    }
	
    public function deleteManufacturer($manufacturer_id)
    {
        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'"
        );
        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "manufacturer_to_store
		    WHERE manufacturer_id = '" . (int)$manufacturer_id . "'"
        );
        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'"
        );

        $this->cache->delete('manufacturer');
    }

    public function getManufacturer($manufacturer_id)
    {
        $queryString = $subQuery = [];

        $subQuery[] = 'SELECT keyword FROM ' . DB_PREFIX . 'url_alias';
        $subQuery[] = 'WHERE query = "manufacturer_id=' . (int)$manufacturer_id . '"';

        $queryString[] = 'SELECT DISTINCT *, (%s) AS keyword';
        $queryString[] = 'FROM ' . DB_PREFIX . 'manufacturer';
        $queryString[] = 'WHERE manufacturer_id = "' . (int)$manufacturer_id . '"';

        $query = $this->db->query(sprintf(implode(' ', $queryString), implode(' ', $subQuery)));

        return $query->row;
    }

    public function getManufacturers($data = array())
    {
        $queryString = [];
        $queryString[] = "SELECT * FROM " . DB_PREFIX . "manufacturer";

        if (!empty($data['filter_name'])) {
            $queryString[] = " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'name',
            'sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY name";
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
    }

    public function getManufacturerStores($manufacturer_id)
    {
        $manufacturer_store_data = $queryString = [];

        $queryString[] = 'SELECT * FROM ' . DB_PREFIX . 'manufacturer_to_store';
        $queryString[] = 'WHERE manufacturer_id = "' . (int)$manufacturer_id . '"';

        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            $manufacturer_store_data[] = $result['store_id'];
        }

        return $manufacturer_store_data;
    }

    public function getTotalManufacturersByImageId($image_id)
    {
        $queryString = [];

        $queryString[] = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . 'manufacturer';
        $queryString[] = 'WHERE image_id = "' . (int)$image_id . '"';

        $query = $this->db->query();

        return $query->row['total'];
    }

    public function getTotalManufacturers()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer");

        return $query->row['total'];
    }

    public function dtHandler($data)
    {
        $queryString = [];

        $fields = '*';

        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "manufacturer";

        if (!empty($data['filter_name'])) {
            $queryString[] = " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = array(
            'name',
            'sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY name";
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

        $data = array(
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $queryData->num_rows
        );

        return $data;
    }
}
