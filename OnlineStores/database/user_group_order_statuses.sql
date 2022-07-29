CREATE TABLE `user_group_order_statuses` (
  `user_group_order_statuses_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_group_id` int(11) NOT NULL,
  `from_order_status_id` int(11) NOT NULL,
  `to_order_status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
