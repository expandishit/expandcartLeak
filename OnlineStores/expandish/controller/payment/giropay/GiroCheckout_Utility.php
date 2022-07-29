<?php

/**
  /**
 * Payment Modul fur girosolution.de.
 *
 * @version   4.5.2
 * @author    OCS
 * @copyright 2011-2015 GiroSolution AG
 * @link      http://www.girosolution.de
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

/**
 * Helper class which manages texts and plugin and shop versions.
 *
 * @package GiroCheckout
 */
class GiroCheckout_Utility {

  /**
   * Returns plugin version.
   *
   * @author GiroSolution AG
   * @package GiroCheckout
   * @copyright Copyright (c) 2015, GiroSolution AG
   * @return string
   */
  public static function getVersion() {
    return '4.5.3';
  }

  /**
   * Returns plugin and shop version.
   *
   * @author GiroSolution AG
   * @package GiroCheckout
   * @copyright Copyright (c) 2015, GiroSolution AG
   * @return string
   */
  public static function getGcSource() {
    return "OpenCart " . VERSION . ";OpenCart Plugin " . self::getVersion();
  }


/**
   * Get the payment purpose.
   *
   * @author GiroSolution AG
   * @package GiroCheckout
   * @copyright Copyright (c) 2014, GiroSolution AG
   * @return int
   */
  public static function getPurpose($purpose, $order) {
    $strPurpose = $purpose;
    $strLastName = "";
    $strFirstName = "";
    $strShopName = "";

    $iCustomerNr = $order['customer_id'];
    $strLastName = $order['lastname'];
    $strFirstName = $order['firstname'];
    $strName = $order['firstname'] . ", " . $order['lastname'];

    $strShopName = $order['store_name'];
    if (empty($strPurpose)) {
        $strPurpose = "{CUSTOMERID}, {SHOPNAME}";
    }

    $strPurpose = str_replace("{ORDERID}", $order['order_id'], $strPurpose);
    $strPurpose = str_replace("{CUSTOMERID}", $iCustomerNr, $strPurpose);
    $strPurpose = str_replace("{SHOPNAME}", $strShopName, $strPurpose);
    $strPurpose = str_replace("{CUSTOMERNAME}", $strName, $strPurpose);
    $strPurpose = str_replace("{CUSTOMERFIRSTNAME}", $strFirstName, $strPurpose);
    $strPurpose = str_replace("{CUSTOMERLASTNAME}", $strLastName, $strPurpose);
    
    if(empty($strPurpose)){
        if(!empty($strFirstName) || !empty($strLastName)){
            $strPurpose = "Kunde: ".$strFirstName." ".$strLastName;
        }else if(!empty($strShopName)){
            $strPurpose = "Bestellung: ".$strShopName;
        }else{
            $strPurpose = "Bestellung: OpenCart";
        }
    }

    return utf8_substr($strPurpose, 0, 27, 'UTF-8');
  }

}
