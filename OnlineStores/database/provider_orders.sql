CREATE TABLE `provider_orders` (
                                   `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                   `order_id` int(11) NOT NULL,
                                   `provider_name` varchar(100) NOT NULL,
                                   `courier_name` varchar(100) NOT NULL,
                                   `paid_to_merchant` timestamp NULL DEFAULT NULL,
                                   INDEX (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
