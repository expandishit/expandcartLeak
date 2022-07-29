CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `store_code` varchar(50) NOT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `plan_type` enum('monthly','annually') DEFAULT 'monthly',
  `amount` double(8,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `status` int(11) DEFAULT '0',
  `transaction_id` varchar(255) DEFAULT NULL,
  `capture_id` varchar(255) DEFAULT NULL,
  `payment_method` enum('stripe','paypal') DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `transaction_status` enum('0','1') NOT NULL DEFAULT '0',
  `arguments` text,
  `auth_token` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;