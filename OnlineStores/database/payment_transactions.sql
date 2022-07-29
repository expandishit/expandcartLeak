CREATE TABLE `payment_transactions` (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT ,
    `order_id` INT NULL ,
    `transaction_id` VARCHAR(255) NULL ,
    `payment_gateway_id` INT NULL ,
    `payment_method` VARCHAR(100) NULL ,
    `status` VARCHAR(100) NULL ,
    `amount` DOUBLE(8, 2) NULL ,
    `currency` VARCHAR(3) NULL ,
    `details` LONGTEXT NULL ,
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

ALTER TABLE `payment_transactions` ADD CONSTRAINT `payment_transactions_order_id_foreign`
    FOREIGN KEY (`order_id`) REFERENCES `order`(`order_id`)
ON DELETE CASCADE ON UPDATE CASCADE;