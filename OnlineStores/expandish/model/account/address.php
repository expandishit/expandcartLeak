<?php
class ModelAccountAddress extends Model {
    
    /**
     * Add a new address to current customer 
     *
     * @param array $data
     * @return integer created address ID
     */
    public function addAddress(array $data) 
    {            
        $this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$this->customer->getId() . "', address_expand_id = '" .(int)$data['address_expand_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '" . $this->db->escape(isset($data['company'])?$data['company']:'') . "', company_id = '" . $this->db->escape(isset($data['company_id']) ? $data['company_id'] : '') . "', tax_id = '" . $this->db->escape(isset($data['tax_id']) ? $data['tax_id'] : '') . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape(isset($data['address_2'])?$data['address_2']:'') . "', postcode = '" . $this->db->escape($data['postcode']) . "', city = '" . $this->db->escape($data['city']) . "', zone_id = '" . (int)$data['zone_id'] . "', area_id = '" . (int)$data['area_id'] . "', location = '" . $this->db->escape($data['location']) . "', country_id = '" . (int)$data['country_id'] . "', telephone = '". $this->db->escape($data['telephone']) . "'");
        
        $address_id = $this->db->getLastId();
        
        if (!empty($data['default'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
        }
        return $address_id;
	}
	
    /**
     *  Edit the address of the current customer
     *
     * @param integer $address_id
     * @param array $data
     * @return void
     */
	public function editAddress($address_id, array $data) {
        
        $query = "UPDATE " . DB_PREFIX . "address SET";

		if($data['firstname'])
			$query .= " firstname = '" . $this->db->escape($data['firstname']) . "',";

		if($data['lastname'])
			$query .= "  lastname = '" . $this->db->escape($data['lastname']) . "',";

        $query .= " address_expand_id = '" .(int)$data['address_expand_id'] . "', company = '" . $this->db->escape($data['company']) . "', company_id = '" . $this->db->escape(isset($data['company_id']) ? $data['company_id'] : '') . "', tax_id = '" . $this->db->escape(isset($data['tax_id']) ? $data['tax_id'] : '') . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', city = '" . $this->db->escape($data['city']) . "', zone_id = '" . (int)$data['zone_id'] . "', area_id = '" . (int)$data['area_id'] ."', location = '" . $this->db->escape($data['location']) . "', country_id = '" . (int)$data['country_id'] . "', telephone = '" . $this->db->escape($data['telephone']) . "' WHERE address_id  = '" . (int)$address_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'";
        
		$this->db->query($query);
	
		if (!empty($data['default'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
        }
    }
    
	public function deleteAddress($address_id) {
        
        $address = $this->getAddress($address_id);
        if ($address) {
            // delete address from identity
            if (!empty($address['address_expand_id'])) {
                $this->identity->deleteAddress([
                    'id' => $address['address_expand_id'],
                    'customer_id' => $this->customer->getExpandId(),
                ]);
            }
            
            // default sql
            $this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");
        }
        
	}	
	
	public function getAddress($address_id, int $customer_id = null) {
		$address_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address_id . "' AND customer_id = '" . ($customer_id ?? (int)$this->customer->getId()) . "'");
        $lang = $this->config->get('config_language_id');
		
		if ($address_query->num_rows) {
			// $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$address_query->row['country_id'] . "'");
			
            $country_query = $this->db->query("SELECT c.*, cl.name as country_name FROM `" . DB_PREFIX . "country` c inner join `" . DB_PREFIX . "countries_locale` cl on c.country_id = cl.country_id WHERE c.country_id = '" . (int)$address_query->row['country_id'] . "' and cl.lang_id = '{$lang}'");
            
			if ($country_query->num_rows) {
				$country = $country_query->row['country_name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';	
				$address_format = '';
			}
			
			// $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$address_query->row['zone_id'] . "'");
            $zone_query = $this->db->query("SELECT z.code, zl.name FROM `" . DB_PREFIX . "zone` z inner join zones_locale zl on z.zone_id = zl.zone_id WHERE z.zone_id = '" . (int)$address_query->row['zone_id'] . "' and zl.lang_id = '{$lang}'");
			
			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}		
			
            // $area_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "geo_area` WHERE area_id = '" . (int)$address_query->row['area_id'] . "'");
			$area_query = $this->db->query("SELECT a.code, al.name FROM `" . DB_PREFIX . "geo_area` a inner join `" . DB_PREFIX . "geo_area_locale` al on a.area_id = al.area_id WHERE a.area_id = '" . (int)$address_query->row['area_id'] . "' and al.lang_id = '{$lang}'");
            
			if ($area_query->num_rows) {
				$area = $area_query->row['name'];
				$area_code = $area_query->row['code'];
			} else {
				$area = '';
				$area_code = '';
			}
            
			$address_data = array(
                'address_id'     => $address_query->row['address_id'],
                'firstname'      => $address_query->row['firstname'],
                'address_expand_id' => $address_query->row['address_expand_id'],
				'lastname'       => $address_query->row['lastname'],
				'company'        => $address_query->row['company'],
				'company_id'     => $address_query->row['company_id'],
				'tax_id'         => $address_query->row['tax_id'],
				'address_1'      => $address_query->row['address_1'],
				'address_2'      => $address_query->row['address_2'],
				'postcode'       => $address_query->row['postcode'],
				'city'           => $address_query->row['city'],
				'area_id'        => $address_query->row['area_id'],
				'area'           => $area,
				'area_code'      => $area_code,
				'zone_id'        => $address_query->row['zone_id'],
				'zone'           => str_replace('"', "", $zone),
				'zone_code'      => $zone_code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,	
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'location'        => $address_query->row['location'],
				'address_format' => $address_format,
				'telephone'	     => $address_query->row['telephone'],
                'default'        => $this->customer->getAddressId() == $address_query->row['address_id'],
                
			);
			
			return $address_data;
		} else {
			return false;	
		}
    }
    
    public function getAddressByExpandId(int $address_expand_id, int $customer_id)
    {
        $address_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "address WHERE address_expand_id = '" . (int)$address_expand_id . "' AND customer_id = '" . $customer_id . "'");

        if ($address_query->num_rows) {
            $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$address_query->row['country_id'] . "'");

            if ($country_query->num_rows) {
                $country = $country_query->row['name'];
                $iso_code_2 = $country_query->row['iso_code_2'];
                $iso_code_3 = $country_query->row['iso_code_3'];
                $address_format = $country_query->row['address_format'];
            } else {
                $country = '';
                $iso_code_2 = '';
                $iso_code_3 = '';
                $address_format = '';
            }

            $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$address_query->row['zone_id'] . "'");

            if ($zone_query->num_rows) {
                $zone = $zone_query->row['name'];
                $zone_code = $zone_query->row['code'];
            } else {
                $zone = '';
                $zone_code = '';
            }

            $address_data = array(
                'address_id'      => $address_query->row['address_id'],
                'firstname'      => $address_query->row['firstname'],
                'address_expand_id' => $address_query->row['address_expand_id'],
                'lastname'       => $address_query->row['lastname'],
                'company'        => $address_query->row['company'],
                'company_id'     => $address_query->row['company_id'],
                'tax_id'         => $address_query->row['tax_id'],
                'address_1'      => $address_query->row['address_1'],
                'address_2'      => $address_query->row['address_2'],
                'postcode'       => $address_query->row['postcode'],
                'city'           => $address_query->row['city'],
                'zone_id'        => $address_query->row['zone_id'],
                'area_id'        => $address_query->row['area_id'],
                'location'        => $address_query->row['location'],
                'zone'           => str_replace('"', "", $zone),
                'zone_code'      => $zone_code,
                'country_id'     => $address_query->row['country_id'],
                'country'        => $country,
                'iso_code_2'     => $iso_code_2,
                'iso_code_3'     => $iso_code_3,
                'address_format' => $address_format,
                'telephone'         => $address_query->row['telephone']
            );

            return $address_data;
        } else {
            return false;
        }
    }
	
	public function getAddresses(int $customer_id = null) {
        $customer_id = (int)($customer_id ?? $this->customer->getId());
        $lang = $this->config->get('config_language_id');
        $address_data = array();
        	
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE customer_id = '" . $customer_id . "'");
	
		foreach ($query->rows as $result) {
			// $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$result['country_id'] . "'");
            $country_query = $this->db->query("SELECT c.*, cl.name as country_name FROM `" . DB_PREFIX . "country` c inner join `" . DB_PREFIX . "countries_locale` cl on c.country_id = cl.country_id WHERE c.country_id = '" . (int)$result['country_id'] . "' and cl.lang_id = '{$lang}'");
            
			if ($country_query->num_rows) {
				$country = $country_query->row['country_name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';	
				$address_format = '';
			}
			
			$zone_query = $this->db->query("SELECT z.code, zl.name FROM `" . DB_PREFIX . "zone` z inner join zones_locale zl on z.zone_id = zl.zone_id WHERE z.zone_id = '" . (int)$result['zone_id'] . "' and zl.lang_id = '{$lang}'");
			
			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}	
			
            // $area_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "geo_area` WHERE area_id = '" . (int)$result['area_id'] . "'");
            
            $area_query = $this->db->query("SELECT a.code, al.name FROM `" . DB_PREFIX . "geo_area` a inner join `" . DB_PREFIX . "geo_area_locale` al on a.area_id = al.area_id WHERE a.area_id = '" . (int)$result['area_id'] . "' and al.lang_id = '{$lang}'");
			
			
			if ($area_query->num_rows) {
				$area = $area_query->row['name'];
				$area_code = $area_query->row['code'];
			} else {
				$area = '';
				$area_code = '';
			}
            	
			$address_data[$result['address_id']] = array(
                'address_id'     => $result['address_id'],
                'address_expand_id' => $result['address_expand_id'],
				'firstname'      => $result['firstname'],
				'lastname'       => $result['lastname'],
				'telephone'      => $result['telephone'],
				'company'        => $result['company'],
				'company_id'     => $result['company_id'],
				'tax_id'         => $result['tax_id'],				
				'address_1'      => $result['address_1'],
				'address_2'      => $result['address_2'],
				'postcode'       => $result['postcode'],
				'city'           => $result['city'],
				'area_id'        => $result['area_id'],
				'area'           => str_replace('"', "", $area),
				'area_code'      => $area_code,
				'zone_id'        => $result['zone_id'],
				'zone'           => str_replace('"', "", $zone),
				'zone_code'      => $zone_code,
				'country_id'     => $result['country_id'],
				'country'        => $country,	
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'location'       => $result['location'],
				'address_format' => $address_format,
                'default'        => $this->customer->getAddressId() == $result['address_id'],
			);
		}		
		
		return $address_data;
	}	
	
	public function getTotalAddresses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	
		return $query->row['total'];
    }
    
    public function setExpandId($address_id, $address_expand_id)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "address SET address_expand_id = '" . (int)$address_expand_id .  "' WHERE address_id  = '" . (int)$address_id . "'");
    }
}
?>
