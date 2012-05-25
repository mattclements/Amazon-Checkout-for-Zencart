<?php
  /**
   * @brief Order Class for creating order, update order status history
   * @catagory Zen Cart Checkout by Amazon Payment Module
   * @author Balachandar Muruganantham
   * @copyright Portions Copyright 2007-2010 Amazon Technologies, Inc
   * @license GPL v2, please see LICENSE.txt
   * @access public
   * @version $Id: $
   */
  /*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
                                                                                                 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
                                                                                                 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
  */

require_once(DIR_WS_CLASSES . 'shipping.php');
require_once(DIR_WS_CLASSES . 'http_client.php');

require_once(DIR_FS_CBA . 'checkout_by_amazon_shopping_cart.php');
require_once(DIR_FS_CBA . 'library/xml.php');
require_once(DIR_FS_CBA . 'library/HMAC.php');
require_once(DIR_FS_CBA . 'library/order_callback_processor.php');

class AmazonCallback {

  /*
   *    Debug flag
   */

  var $debug = false;

  /*
   *    Default currency code 
   */

  var $CurrencyCode = 'USD';

  /*
   *    Pre-defined region for Shipping Rates calculation
   */

  var $PreDefinedRegion = 'WorldAll';

  /*
   *    constructor
   */
	
  function AmazonCallback(){
		
  }

  /*
   *    @brief Tax Calculation
   */
	
  function CalculateTax($items,$country_id,$zone_id){

    $taxTablesArray = array();
    $taxTableArray = array();
    $tax_rate = 0.0;
    foreach ($items as $myitem) {
      $item = $myitem['Item'];
      $sku = $item['SKU'];
      $taxRule = array();
      $taxRule['Rate'] =  zen_get_tax_rate(amazon_get_tax_class_id($sku), $country_id, $zone_id) / 100.00;

      if (MODULE_PAYMENT_CHECKOUTBYAMAZON_CALLBACK_IS_SHIPPING_TAXED == 'True') {
        $taxRule['IsShippingTaxed'] = "true";
      } else {
        $taxRule['IsShippingTaxed'] = "false";
      }

      // 223 -> US 
      if ($country_id == '223') {
        $taxRule["PredefinedRegion"] = 'USAll';
      }else{
        $taxRule["PredefinedRegion"] = 'WorldAll';
      }

      $taxTableArray[] = array (
                                'TaxTableId' => "Tax-for-SKU-".$sku,
                                'TaxRules' => array(
                                                    'TaxRule' => $taxRule
                                                    )
                                );
    }
		
    $taxTablesArray['TaxTable'] = $taxTableArray;
    return $taxTablesArray;
  }

  /*
   *    @brief Shipping Rates Calculation
   */
	
  function CalculateShippingRates($weight, $country_code,$postal_code,$weight,$country_id,$zone_id){
    global $order, $shipping_weight, $shipping_num_boxes, $total_weight, $shipping, $cart;
    $shipping_weight = $weight;
    $total_weight = $weight;
    $shipping_num_boxes = 1;

    $order = new zencartorder();
    $order->delivery['country']['iso_code_2'] = (string)$country_code;
    $order->delivery['country']['id'] = $country_id;
    $order->delivery['postcode'] = $postal_code;
    $order->delivery['zone_id'] = $zone_id;

    if((int)SHIPPING_ORIGIN_COUNTRY != (int)$country_id){
      $this->PreDefinedRegion="WorldAll";
    }else{
      $this->PreDefinedRegion="USAll";
    }
    writelog("PreDefinedRegion -> " . $this->PreDefinedRegion);

    $shipping = new shipping();
    $quotes_all = $shipping->quote();

    /* Setting the shipping method */
    $costArray = array();
    $quoteArray = array();
    $cnt = 0;
    for ($j = 0; $j < count($quotes_all); $j++) {
      $quotes=$quotes_all[$j];
      $cnt = $cnt +  count($quotes['methods']);
      for ($i = 0; $i < count($quotes['methods']); $i++) {
        $method = $quotes['methods'][$i];
        $cost = (float)$method['cost'];
        $shipping_id = $method['id'];
        array_push($costArray,$cost);
        array_push($quoteArray,  strtoupper($quotes['id']) . " - " .  $method['title'] . " - " . $cost);		
      }
    }
		
    /* sort the cost in values */
    asort($costArray);

    $shippingMethodsArray = array();
    $shippingMethodArray = array();
    $shippingMethodsArray['ShippingMethod'] = array();

    $MAX_SHIPPING_METHODS = 24;
    $standard = 8;
    $expedited = 15;
    $oneday = 20;
    $twoday = 25;
    $cnt = count($costArray);
    $id = 1;
    if($cnt > $MAX_SHIPPING_METHODS) {
      $standard = round(7 * $cnt / $MAX_SHIPPING_METHODS) + 1;
      $expedited = round(7 * $cnt / $MAX_SHIPPING_METHODS) + 1 + $standard;
      $oneday = round(5 * $cnt / $MAX_SHIPPING_METHODS) + 1 + $expedited;
      $twoday = $cnt - ($standard + $expedited + $oneday) + 1;
    }
    $servicelevel = "Standard";
    foreach ($costArray as $key => $cost) {
      switch($id) {
      case ($id < $standard): 
        $servicelevel = "Standard";
        break;
      case ($id < $expedited): 
        $servicelevel = "Expedited";
        break;
      case ($id < $oneday):
        $servicelevel = "OneDay";
        break;
      case ($id < $twoday):
        $servicelevel = "TwoDay";
        break;
      }

      $shippingMethodArray['ShippingMethodId'] = "ship-method-" . $id;
      $shippingMethodArray['ServiceLevel'] = $servicelevel;
      $shippingMethodArray['Rate']['ShipmentBased']['Amount'] = $cost;
      $shippingMethodArray['Rate']['ShipmentBased']['CurrencyCode'] = $this->CurrencyCode;
      $shippingMethodArray['IncludedRegions']['PredefinedRegion'] = $this->PreDefinedRegion;
      $shippingMethodArray['DisplayableShippingLabel'] = $quoteArray[$key];

      array_push($shippingMethodsArray['ShippingMethod'],$shippingMethodArray);

      $id = $id + 1;
    }	

    if($shippingMethodsArray){
      ob_writelog("Got shipping amount from shipping carrier: ", $shippingMethodsArray);
      return $shippingMethodsArray;
    }else{
      writelog("Shipping Carrier and Shipping Override are None. Please change in Checkout by Amazon 2.0 Payment module");
    }		
  }

  /*
   *    @brief Tax Calculation
   */
  function CallbackResponse(){
    global $cart;

    define('AWS_SECRET_KEY',MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSSECRETKEY);
    define('AWS_ACCESS_KEY',MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSACCESSID);

    $cb = new OrderCallbackProcessor();
    $items = $cb->GetOrderItems();
    $shippingAddress = $cb->GetShippingAddress();
		
    $cart = new shoppingCartAmazon();
    $cart->generate_cart($items);
    $_SESSION['cart'] = $cart;

    $totals = $cart->show_total();
    $weight = $cart->show_weight();
    $total_count=$cart->count_contents(); // total count of product quantity
		
    $postal_code = $shippingAddress['PostalCode'];
    $country_code = $shippingAddress['CountryCode'];
    $zone_code = $shippingAddress['State'];

    $country=amazon_get_country_by_ISOCode2($country_code);
    $zone = amazon_get_zone_id($country['countries_id'],$zone_code);

    $shippingMethodsArray = $this->CalculateShippingRates($weight,$country_code,$postal_code,$weight,$country['countries_id'],$zone['zone_id']);
    $cb->SetShippingMethods($shippingMethodsArray);

    /* Set Tax Tables Array */
    $taxTablesArray = $this->CalculateTax($items,$country['countries_id'],$zone['zone_id']);
    $cb->SetTaxTables($taxTablesArray);

    $xml = $cb->GenerateResponse();
		
    unset($_SESSION['cart']);
    return $xml;
  }
}