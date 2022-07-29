CREATE TABLE `newsletter_subscriber`(
	`newsletter_subscriber_id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(100) NULL,
	`email` varchar(100) NOT NULL,
	`created_at` datetime NOT NULL DEFAULT current_timestamp(),
	`updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  	`status` tinyint(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`newsletter_subscriber_id`),
  	UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
