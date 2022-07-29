SET @CODE = 'GATEWAYCODE';
INSERT INTO `payment_methods` (`id`, `code`, `image`, `status`, `type`) VALUES (
    NULL,
    @CODE,
    'view/image/payment/code.png',
    1,
    'type'
);
SET @PAYMENTMETHODID = LAST_INSERT_ID();
INSERT INTO `payment_methods_description` VALUES (
    NULL,
    @CODE,
    @CODE,
    @CODE,
    @PAYMENTMETHODID,
    '1',
    'en'
), (
    NULL,
    @CODE,
    @CODE,
    @CODE,
    @PAYMENTMETHODID,
    '2',
    'ar'
);
INSERT INTO `extension` (`extension_id`, `type`, `code`) VALUES (NULL, 'payment', @CODE);