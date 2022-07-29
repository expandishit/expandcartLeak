CREATE TABLE `accounts` (
  `id` int UNSIGNED NOT NULL,
  `storecode` varchar(100) NOT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `paypal_subscription_id` varchar(255) DEFAULT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `plan_type` varchar(255) NOT NULL DEFAULT 'free account',
  `payment_method` varchar(255) NOT NULL DEFAULT 'banktransfer',
  `service_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `accounts` ADD PRIMARY KEY (`id`);
ALTER TABLE `accounts` MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;
