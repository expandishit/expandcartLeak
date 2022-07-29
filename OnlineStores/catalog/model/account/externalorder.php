<?php
/*TRUNCATE TABLE externalordercategory;
INSERT INTO externalordercategory(`text`,`value`,`language`)
VALUES
('Bluetooth device', 'Bluetooth device', 'en'),
('Book', 'Book', 'en'),
('Car spare part / accessory', 'Car spare part / accessory', 'en'),
('Clothes / Bag / Case / Shoes / Textiles', 'Clothes / Bag / Case / Shoes / Textiles', 'en'),
('Computer / Printer / Laptop', 'Computer / Printer / Laptop', 'en'),
('Electronics', 'Electronics', 'en'),
('Game Consoles', 'Game Consoles', 'en'),
('General use product', 'General use product', 'en'),
('Kitchenware/Homeware/Appliance/Audio', 'Kitchenware/Homeware/Appliance/Audio', 'en'),
('Makeup / Cosmetics', 'Makeup / Cosmetics', 'en'),
('Mobile / Tablet / Wireless device', 'Mobile / Tablet / Wireless device', 'en'),
('Mobile Accessory', 'Mobile Accessory', 'en'),
('Online service / Digital product', 'Online service / Digital product', 'en'),
('Supplement / Agricultural / Medical equipment', 'Supplement / Agricultural / Medical equipment', 'en'),
('Toy (Non-electronic)', 'Toy (Non-electronic)', 'en'),
('TV / LCD / Monitor', 'TV / LCD / Monitor', 'en'),
('Watch / Sunglasses / Eyeglasses', 'Watch / Sunglasses / Eyeglasses', 'en');
*/
class ModelAccountExternalOrder extends Model {
	public function getCategories() {
		$categories = $this->db->query("SELECT * FROM `" . DB_PREFIX . "externalordercategory` WHERE `language` = '" . $this->language->get('code') . "'");
		return $categories->rows;
	}

	public function addExternalOrder($data) {
		$this->db->query("INSERT INTO externalorder(url, name, quantity, price, categoryvalue, notes, customerid,statusvalue) VALUES
					('" . $data['url'] . "', '" . $data['name'] . "'," . (int)$data['quantity']  . " , " . $data['price']  . " , '" . $data['category']  . "', '" . $data['notes']  . "'," . (int)$this->customer->getId() . " , '" . $this->config->get('config_order_status_id')  . "')");
	}

	public function getCustomerExternalOrders() {
		$customerExternalOrders = $this->db->query("SELECT externalorder.*, order_status.name statusname
													FROM externalorder
													JOIN order_status ON externalorder.statusvalue = order_status.order_status_id
													WHERE externalorder.customerid=" . (int)$this->customer->getId() . "
													AND order_status.language_id = " . (int)$this->config->get('config_language_id') . "
													ORDER BY externalorder.createdon DESC");
		return $customerExternalOrders->rows;
	}
}
?>