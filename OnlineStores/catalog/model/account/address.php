<?php
class ModelAccountAddress extends Model {
    public function addAddress(array $data) 
    {
        $customer_id = (int)(isset($data['customer_id']) ? $data['customer_id'] : $this->customer->getId());
        
        if (!$customer_id) return false;
                    
        $this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . $customer_id . "', address_expand_id = '" .(int)$data['address_expand_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '" . $this->db->escape(isset($data['company'])?$data['company']:'') . "', company_id = '" . $this->db->escape(isset($data['company_id']) ? $data['company_id'] : '') . "', tax_id = '" . $this->db->escape(isset($data['tax_id']) ? $data['tax_id'] : '') . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape(isset($data['address_2'])?$data['address_2']:'') . "', postcode = '" . $this->db->escape(isset($data['postcode'])?$data['postcode']:'') . "', city = '" . $this->db->escape($data['city']) . "', zone_id = '" . (int)$data['zone_id'] . "', area_id = '" . $this->db->escape(isset($data['area_id'])?$data['area_id']:'') . "', location = '" . $this->db->escape($data['location']) . "', country_id = '" . (int)$data['country_id'] . "', telephone = '". $this->db->escape($data['telephone']) . "'");
        
        $address_id = $this->db->getLastId();
        
        if (!empty($data['default'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . $customer_id . "'");
        }
        return $address_id;
	}
	
	public function editAddress($address_id, $data) {
		$customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : (int)$this->customer->getId();
		$this->db->query("UPDATE " . DB_PREFIX . "address SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '" . $this->db->escape($data['company']) . "', company_id = '" . $this->db->escape(isset($data['company_id']) ? $data['company_id'] : '') . "', tax_id = '" . $this->db->escape(isset($data['tax_id']) ? $data['tax_id'] : '') . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', postcode = '" . $this->db->escape(isset($data['postcode'])?$data['postcode']:'') . "', city = '" . $this->db->escape($data['city']) . "', zone_id = '" . (int)$data['zone_id'] . "', country_id = '" . (int)$data['country_id'] . "', telephone = '" . $data['telephone'] . "', area_id = '" . (int)$data['area_id'] . "' WHERE address_id  = '" . (int)$address_id . "' AND customer_id = '" . $customer_id . "'");
	
		if (!empty($data['default']) && $data['default']) {
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . $customer_id . "'");
		}
	}
	
	public function deleteAddress($address_id, $customer_id = null) {
		$customer_id =  $customer_id ? (int)$customer_id : (int)$this->customer->getId();
		$this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address_id . "' AND customer_id = '" .  $customer_id . "'");
	}	
	
	public function getAddress($address_id) {
		$address_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");
		$default_address_query = $this->db->query("SELECT address_id FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$this->customer->getId() . "'")->row['address_id'];
		
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
				'firstname'      => $address_query->row['firstname'],
				'lastname'       => $address_query->row['lastname'],
				'company'        => $address_query->row['company'],
				'company_id'     => $address_query->row['company_id'],
				'tax_id'         => $address_query->row['tax_id'],
				'address_1'      => $address_query->row['address_1'],
				'address_2'      => $address_query->row['address_2'],
				'postcode'       => $address_query->row['postcode'],
				'telephone'       => $address_query->row['telephone'],
				'city'           => $address_query->row['city'],
				'zone_id'        => $address_query->row['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,	
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
				'default'        => $default_address_query == $address_id
			);
			
			return $address_data;
		} else {
			return false;	
		}
	}
	
	public function getAddresses($customer_id = null) {
		$customer_id =  $customer_id ? (int)$customer_id : (int)$this->customer->getId();
		$address_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE customer_id = '" . $customer_id . "'");

		$default_address_query = $this->db->query("SELECT address_id FROM " . DB_PREFIX . "customer WHERE customer_id = '" . $customer_id . "'")->row['address_id'];

		foreach ($query->rows as $result) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$result['country_id'] . "'");
			
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
			
			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$result['zone_id'] . "'");
			
			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}
			
			$area_query = $this->db->query("SELECT `name`, `code` FROM geo_area WHERE area_id = '" . (int)$result['area_id'] . "'");
			
			if ($area_query->num_rows) {
				$area = $area_query->row['name'];
				$area_code = $area_query->row['code'];
			} else {
				$area = '';
				$area_code = '';
			}	
		
			$address_data[$result['address_id']] = array(
				'address_id'     => $result['address_id'],
				'firstname'      => $result['firstname'],
				'lastname'       => $result['lastname'],
				'company'        => $result['company'],
				'company_id'     => $result['company_id'],
				'tax_id'         => $result['tax_id'],				
				'address_1'      => $result['address_1'],
				'address_2'      => $result['address_2'],
				'postcode'       => $result['postcode'],
				'telephone'      => $result['telephone'],
				'city'           => $result['city'],
				'zone_id'        => $result['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'area_id'        => $result['area_id'],
				'area'           => $area,
				'area_code'      => $area_code,
				'country_id'     => $result['country_id'],
				'country'        => $country,	
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
				'default'        => $default_address_query == $result['address_id']
			);
		}		
		
		return $address_data;
	}	
	
	public function getTotalAddresses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	
		return $query->row['total'];
	}
}
?>
