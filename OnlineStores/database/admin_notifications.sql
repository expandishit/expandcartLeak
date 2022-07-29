CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case` enum('success','info','warning','danger') DEFAULT NULL,
  `source` enum('system_frontend','system_backend','ectools') DEFAULT NULL,
  `allow_read` tinyint(1) DEFAULT 1,
  `read_status` tinyint(4) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `icon` varchar(45) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `remote_id` int(10) DEFAULT 0,
  `code` varchar(45) DEFAULT NULL,
  `notification_module` varchar(45),
  `notification_module_code` varchar(45),
  `notification_module_id` int(11) NULL,
  `count` int(11) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
