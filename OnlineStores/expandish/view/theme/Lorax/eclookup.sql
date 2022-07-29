/*
 Navicat Premium Data Transfer

 Source Server         : Expand Solutions
 Source Server Type    : MySQL
 Source Server Version : 50551
 Source Host           : 192.185.73.98:3306
 Source Schema         : ashawqy_TEST1

 Target Server Type    : MySQL
 Target Server Version : 50551
 File Encoding         : 65001

 Date: 17/09/2017 20:21:52
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for eclookup
-- ----------------------------
DROP TABLE IF EXISTS `eclookup`;
CREATE TABLE `eclookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `LookUpKey` varchar(255) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Value` varchar(255) NOT NULL,
  `Lang` varchar(5) NOT NULL,
  `SortOrder` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of eclookup
-- ----------------------------
BEGIN;
INSERT INTO `eclookup`(LookUpKey, `Name`, `Value`, Lang, SortOrder) VALUES ('base-slides-textposition', 'Left', '1', 'en', 0),

/* Common Elements */
('FontAwesome_Icons', 'User', 'fa fa-user', 'en', 0),
('FontAwesome_Icons', 'المستخدم', 'fa fa-user', 'ar', 0),
('FontAwesome_Icons', 'Refresh', 'fa fa-refresh', 'en', 1),
('FontAwesome_Icons', 'تدوير', 'fa fa-refresh', 'ar', 1),
('FontAwesome_Icons', 'Box', 'fa fa-archive', 'en', 2),
('FontAwesome_Icons', 'صندوق', 'fa fa-archive', 'ar', 2),
('FontAwesome_Icons', 'Basket', 'fa fa-shopping-basket', 'en', 3),
('FontAwesome_Icons', 'السلة', 'fa fa-shopping-basket', 'ar', 3),
('FontAwesome_Icons', 'Shoping Bag', 'fa fa-shopping-bag', 'en', 4),
('FontAwesome_Icons', 'سلة التسوق', 'fa fa-shopping-bag', 'ar', 4),
('FontAwesome_Icons', 'Truck', 'fa fa-truck', 'en', 5),
('FontAwesome_Icons', 'الشاحنة', 'fa fa-truck', 'ar', 5),
('FontAwesome_Icons', 'StreetView', 'fa fa-street-view', 'en', 6),
('FontAwesome_Icons', 'شكل الشارع', 'fa fa-street-view', 'ar', 6),
('FontAwesome_Icons', 'Location', 'fa fa-location-arrow', 'en', 7),
('FontAwesome_Icons', 'الموقع', 'fa fa-location-arrow', 'ar', 7),
('FontAwesome_Icons', 'Paper Plane', 'fa fa-paper-plane', 'en', 8),
('FontAwesome_Icons', 'طائرة ورقية', 'fa fa-paper-plane', 'ar', 8),
('FontAwesome_Icons', 'Rotate Right', 'fa fa-rotate-right', 'en', 9),
('FontAwesome_Icons', 'تدوير لليمين', 'fa fa-rotate-right', 'ar', 9),
('FontAwesome_Icons', 'Umbrella', 'fa fa-umbrella', 'en', 10),
('FontAwesome_Icons', 'شمسية', 'fa fa-umbrella', 'ar', 10),
('FontAwesome_Icons', 'Tags', 'fa fa-tags', 'en', 11),
('FontAwesome_Icons', 'علامات', 'fa fa-tags', 'ar', 11),
('FontAwesome_Icons', 'Gift', 'fa fa-gift', 'en', 12),
('FontAwesome_Icons', 'هدية', 'fa fa-gift', 'ar', 12),
('FontAwesome_Icons', 'Calendar', 'fa fa-calendar-check-o', 'en', 12),
('FontAwesome_Icons', 'تقويم', 'fa fa-calendar-check-o', 'ar', 12),
('FontAwesome_Icons', 'Binoculars', 'fa fa-binoculars', 'en', 13),
('FontAwesome_Icons', 'منظار', 'fa fa-binoculars', 'ar', 13),
('FontAwesome_Icons', 'Building', 'fa fa-building', 'en', 14),
('FontAwesome_Icons', 'مبنى', 'fa fa-building', 'ar', 14),
('FontAwesome_Icons', 'bullhorn', 'fa fa-bullhorn', 'en', 15),
('FontAwesome_Icons', 'مكبر صوت', 'fa fa-bullhorn', 'ar', 15),
('FontAwesome_Icons', 'Cube', 'fa fa-cube', 'en', 16),
('FontAwesome_Icons', 'مكعب', 'fa fa-cube', 'ar', 16),
('FontAwesome_Icons', 'Database', 'fa fa-database', 'en', 17),
('FontAwesome_Icons', 'البيانات', 'fa fa-database', 'ar', 17),
('FontAwesome_Icons', 'Envelope', 'fa fa-envelope', 'en', 18),
('FontAwesome_Icons', 'ظرف', 'fa fa-envelope', 'ar', 18),

('Linear_Icons', 'Earth', 'lnr lnr-earth', 'en', 1),
('Linear_Icons', 'الكرة الارضية', 'lnr lnr-earth', 'ar', 1),
('Linear_Icons', 'User', 'lnr lnr-users', 'en', 2),
('Linear_Icons', 'المستخدم', 'lnr lnr-users', 'ar', 2),
('Linear_Icons', 'Check', 'lnr lnr-checkmark-circle', 'en', 3),
('Linear_Icons', 'ترقيم', 'lnr lnr-checkmark-circle', 'ar', 3),
('Linear_Icons', 'Bicycle', 'lnr lnr-bicycle', 'en', 4),
('Linear_Icons', 'دراجة ', 'lnr lnr-bicycle', 'ar', 4),

('SMedia_Icons', 'Facebook', 'fa fa-facebook', 'en', 0),
('SMedia_Icons', 'Facebook', 'fa fa-facebook', 'ar', 0),
('SMedia_Icons', 'Twitter', 'fa fa-twitter', 'en', 1),
('SMedia_Icons', 'Twitter', 'fa fa-twitter', 'ar', 1),
('SMedia_Icons', 'Instagram', 'fa fa-instagram', 'en', 2),
('SMedia_Icons', 'Instagram', 'fa fa-instagram', 'ar', 2),
('SMedia_Icons', 'Youtube', 'fa fa-youtube', 'en', 3),
('SMedia_Icons', 'Youtube', 'fa fa-youtube', 'ar', 3),
('SMedia_Icons', 'Gogle+', 'fa fa-google-plus', 'en', 4),
('SMedia_Icons', 'Gogle+', 'fa fa-google-plus', 'ar', 4),
('SMedia_Icons', 'Vine', 'fa fa-vine', 'en', 5),
('SMedia_Icons', 'Vine', 'fa fa-vine', 'ar', 5),
('SMedia_Icons', 'Vimeo', 'fa fa-vimeo', 'en', 6),
('SMedia_Icons', 'Vimeo', 'fa fa-vimeo', 'ar', 6),
('SMedia_Icons', 'Telegram', 'fa fa-telegram', 'en', 7),
('SMedia_Icons', 'Telegram', 'fa fa-telegram', 'ar', 7),
('SMedia_Icons', 'Whatsapp', 'fa fa-whatsapp', 'en', 8),
('SMedia_Icons', 'Whatsapp', 'fa fa-whatsapp', 'ar', 8),
('SMedia_Icons', 'SnapChat', 'fa fa-snapchat-ghost', 'en', 9),
('SMedia_Icons', 'SnapChat', 'fa fa-snapchat-ghost', 'ar', 9),
('SMedia_Icons', 'Skype', 'fa fa-skype', 'en', 10),
('SMedia_Icons', 'Skype', 'fa fa-skype', 'ar', 10),
('SMedia_Icons', 'Pinterest', 'fa fa-pinterest-p', 'en', 11),
('SMedia_Icons', 'Pinterest', 'fa fa-pinterest-p', 'ar', 11),
('SMedia_Icons', 'LinkedIN', 'fa fa-linkedin', 'en', 12),
('SMedia_Icons', 'LinkedIN', 'fa fa-linkedin', 'ar', 12),
('SMedia_Icons', 'Android', 'fa fa-android', 'en', 13),
('SMedia_Icons', 'Android', 'fa fa-android', 'ar', 13),
('SMedia_Icons', 'Apple', 'fa fa-apple', 'en', 14),
('SMedia_Icons', 'Apple', 'fa fa-apple', 'ar', 14),

('ArabicFonts', 'Cairo Font', 'Cairo', 'en', 1),
('ArabicFonts', 'Changa Font', 'Changa', 'en', 2),
('ArabicFonts', 'Lateef Font', 'Lateef', 'en', 3),
('ArabicFonts', 'Amiri Font', 'Amiri', 'en', 4),
('ArabicFonts', 'Scheherazade Font', 'Scheherazade', 'en', 5),
('ArabicFonts', 'Next Font', 'Next', 'en', 6),

('EnglishFonts', 'Roboto Font', 'Roboto', 'en', 1),
('EnglishFonts', 'Open Sans Font', 'Open Sans', 'en', 2),
('EnglishFonts', 'Raleway Font', 'Raleway', 'en', 3),
('EnglishFonts', 'Oxygen Font', 'Oxygen', 'en', 4),
('EnglishFonts', 'Work Sans Font', 'Work Sans', 'en', 5),
('EnglishFonts', 'Hind Font', 'Hind', 'en', 6),
('EnglishFonts', 'Karla Font', 'Karla', 'en', 7),
('EnglishFonts', 'Montserrat Font', 'Montserrat', 'en', 8),
('EnglishFonts', 'Zilla Font', 'Zilla Slab', 'en', 9),
('EnglishFonts', 'Poppins Font', 'Poppins', 'en', 10),

('ProductsByStatus_Type', 'Latest Products', 'LatestProducts', 'en', 1),
('ProductsByStatus_Type', 'احدث المنتجات', 'LatestProducts', 'ar', 1),
('ProductsByStatus_Type', 'BestSeller Products', 'BestSellerProducts', 'en', 2),
('ProductsByStatus_Type', 'المنتجات الاكثر مبيعاً', 'BestSellerProducts', 'ar', 2),
('ProductsByStatus_Type', 'المنتجات المميزة', 'SpecialProducts', 'ar', 3),
('ProductsByStatus_Type', 'Special Products', 'SpecialProducts', 'en', 3),

('ProductsByCategory_Type', 'Latest Products', 'LatestProducts', 'en', 1),
('ProductsByCategory_Type', 'احدث المنتجات', 'LatestProducts', 'ar', 1),
('ProductsByCategory_Type', 'BestSeller Products', 'BestSellerProducts', 'en', 2),
('ProductsByCategory_Type', 'المنتجات الاكثر مبيعاً', 'BestSellerProducts', 'ar', 2),
('ProductsByCategory_Type', 'Special Products', 'SpecialProducts', 'en', 3),
('ProductsByCategory_Type', 'المنتجات المميزة', 'SpecialProducts', 'ar', 3),

('Products_Count', '5', '5', 'en', 1),
('Products_Count', '5', '5', 'ar', 1),
('Products_Count', '4', '4', 'en', 2),
('Products_Count', '4', '4', 'ar', 2),
('Products_Count', '3', '3', 'en', 3),
('Products_Count', '3', '3', 'ar', 3),

('Text_Position', 'Left', 'Pos_Left', 'en', 1),
('Text_Position', 'يسار', 'Pos_Left', 'ar', 1),
('Text_Position', 'Center', 'Pos_Center', 'en', 2),
('Text_Position', 'فى المنتصف', 'Pos_Center', 'ar', 2),
('Text_Position', 'Right', 'Pos_Right', 'en', 3),
('Text_Position', 'يمين', 'Pos_Right', 'ar', 3),

('Object_Width', '1/4', 'col-sm-3', 'en', 1),
('Object_Width', '1/4', 'col-sm-3', 'ar', 1),
('Object_Width', '1/3', 'col-sm-4', 'en', 1),
('Object_Width', '1/3', 'col-sm-4', 'ar', 1),
('Object_Width', '1/2', 'col-sm-6', 'en', 2),
('Object_Width', '1/2', 'col-sm-6', 'ar', 2),
('Object_Width', '3/4', 'col-sm-9', 'en', 3),
('Object_Width', '3/4', 'col-sm-9', 'ar', 3),
('Object_Width', 'Full', 'col-sm-12', 'en', 4),
('Object_Width', 'الكل', 'col-sm-12', 'ar', 4),

('Object_Position', 'Left', 'Left', 'en', 1),
('Object_Position', 'اليسار', 'Left', 'ar', 1),
('Object_Position', 'Center', 'Center', 'en', 2),
('Object_Position', 'فى المنتصف', 'Center', 'ar', 2),
('Object_Position', 'Right', 'Right', 'en', 3),
('Object_Position', 'اليمين', 'Right', 'ar', 3),

('Object_PositionMini', 'Left', 'Left', 'en', 1),
('Object_PositionMini', 'اليسار', 'Left', 'ar', 1),
('Object_PositionMini', 'Right', 'Right', 'en', 2),
('Object_PositionMini', 'اليمين', 'Right', 'ar', 2);

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
