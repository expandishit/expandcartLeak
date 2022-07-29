/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_image` (
  `product_image_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_image_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4348 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `product_image` (`product_image_id`, `product_id`, `image`, `sort_order`) VALUES
(4363, 191, 'data/DemoImages/Products/87.jpg', 0),
(4364, 191, 'data/DemoImages/Products/88.jpg', 1),
(4365, 191, 'data/DemoImages/Products/89.jpg', 2),
(4366, 137, 'data/DemoImages/Products/33.jpg', 0),
(4367, 137, 'data/DemoImages/Products/32.jpg', 1),
(4370, 124, 'data/DemoImages/Products/7.jpg', 0),
(4371, 124, 'data/DemoImages/Products/6.jpg', 1),
(4372, 161, 'data/DemoImages/Products/61.jpg', 0),
(4373, 161, 'data/DemoImages/Products/60.jpg', 1),
(4374, 161, 'data/DemoImages/Products/59.jpg', 2);