CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_code` varchar(50) NOT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `plan_type` enum('monthly','annually') DEFAULT 'monthly',
  `amount` double(8,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `status` int(11) DEFAULT 0,
  `transaction_id` varchar(255) DEFAULT NULL,
  `capture_id` varchar(255) DEFAULT NULL,
  `payment_method` enum('stripe','paypal') DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `transaction_status` enum('0','1') NOT NULL DEFAULT '0',
  `arguments` text DEFAULT NULL,
  `auth_token` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `account_id` int(11) NOT NULL,
  `debug_id` varchar(256) DEFAULT NULL,
   PRIMARY KEY (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=latin1;