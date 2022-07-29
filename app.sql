SET @APPCODE = 'APPCODE';
INSERT INTO `appservice` (`id`,`name`,`description`,`whmcsappserviceid`,`link`,`type`,`IsQuantity`,`IsNew`,`price`,`AppImage`,`CoverImage`,`HomeImage`,`order`,`recurring`,`freeplan`,`freepaymentterm`,`category`, `published`, `supported_countries`, `recommended`, `provider_id`, `response_time`) VALUES (
    NULL,
    @APPCODE,
    @APPCODE,
    0,
    NULL,
    1,
    0,
    0,
    0,
    'view/image/marketplace/code.png',
    NULL,
    NULL,
    -1,
    0,
    8,
    'm',
    'product_management',
    1,
    '',
    0,
    '',
    ''
);
SET @APPID = LAST_INSERT_ID();
INSERT INTO `appservicedesc` VALUES (
    NULL,
    @APPID,
    @APPCODE,
    @APPCODE,
    @APPCODE,
    '',
    'en'
), (
    NULL,
    @APPID,
    @APPCODE,
    @APPCODE,
    @APPCODE,
    '',
    'ar'
);
INSERT INTO `packageappservice` (`id`, `PackageId`, `AppServiceId`) VALUES (NULL, 8, @APPID);