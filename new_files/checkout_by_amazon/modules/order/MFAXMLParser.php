<?php
  /**
   * @brief MFAXMLParser Class for parsing the MFA XML report
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
class MFAXMLParser{

  var $RequestXML;
  var $orderItems;
  var $data;
  var $item;

  function MFAXMLParser($xml){
    $this->data = XML_unserialize($xml);
    $this->RequestXML = $this->data['OrderReport'];
    $this->ProcessOrderItems();
  }

  function getAmazonOrderID() {
    return $this->RequestXML['AmazonOrderID'];
  }

  function getOrderItems(){  
    return $this->item;  
  }

  function ProcessOrderItems() {
    $items = $this->RequestXML['Item'];	  

    /* converting the item to array if there is only one item */
    $items = $items['AmazonOrderItemCode'] != NULL ? array($items) : $items;

    foreach($items as $myitem){
	  
      $myitemid = $myitem['SKU'];

      $this->item[$myitemid] = array();

      $this->item[$myitemid]['AmazonOrderItemCode'] = $myitem['AmazonOrderItemCode'];
      $this->item[$myitemid]['Title'] = $myitem['Title'];
      $this->item[$myitemid]['Quantity'] = $myitem['Quantity'];

      $this->setCustomizationInfoValue($myitem['CustomizationInfo'],$myitemid);
      $this->setItemPriceValue($myitem['ItemPrice'],$myitemid);

      if(array_key_exists("Promotion",$myitem)){
        $this->setPromotionValue($myitem['Promotion'],$myitemid);
      }
    }
  }

  function setPromotionValue($Promotion,$myitemid){

    if(isset($Promotion['PromotionClaimCode'])){
      $mypromotion = array($Promotion);
    }else{
      $mypromotion = $Promotion;
    }	

    foreach($mypromotion as $Promotion_array){
      $this->item[$myitemid]['Promotion'][$Promotion_array['PromotionClaimCode']]['MerchantPromotionID'] = $Promotion_array['MerchantPromotionID'];


      foreach($Promotion_array['Component'] as $value){
			
        $this->item[$myitemid]['Promotion'][$Promotion_array['PromotionClaimCode']][$value['Type']] = $value['Amount'];

      }
    }

  }

  function setItemPriceValue($ItemPrice_array,$myitemid){

    foreach($ItemPrice_array['Component'] as $value){
		
      $this->item[$myitemid]['ItemPrice'][$value['Type']] = $value['Amount'];

    }

  }

  function setCustomizationInfoValue($customization_array,$myitemid){
  
    foreach($customization_array as $value){
	
      $this->item[$myitemid][$value['Type']] = $value['Data'];

    }

  }

  function getOrderFulfillmentServiceLevel() {
    return $this->RequestXML['FulfillmentData']['FulfillmentServiceLevel'];	
  }

  /**
   * Gets name from MFA order report
   */
  function getFulfillmentAddressName() {
    return $this->RequestXML['FulfillmentData']['Address']['Name'];
  }

  /**
   * Gets first name from MFA order report
   */
  function getFulfillmentAddressFirstName() {
    $name = $this->RequestXML['FulfillmentData']['Address']['Name'];
    $i = strrpos($name, ' ');

    $firstName = substr($name, 0, $i);
    return $firstName;
  }

  /**
   * Gets last name from MFA order report
   */
  function getFulfillmentAddressLastName() {
    $name = $this->RequestXML['FulfillmentData']['Address']['Name'];
    $i = strrpos($name, ' ');

    $lastName = substr($name, $i + 1);
    return $lastName;
  }

  /**
   * Gets address field one from MFA order report
   */
  function getFulfillmentAddressFieldOne() {
    return $this->RequestXML['FulfillmentData']['Address']['AddressFieldOne'];
  }

  /**
   * Gets address field two from MFA order report
   */
  function getFulfillmentAddressFieldTwo() {
    return $this->RequestXML['FulfillmentData']['Address']['AddressFieldTwo'];
  }

  /**
   * Gets address city from MFA order report
   */
  function getFulfillmentAddressCity() {
    return $this->RequestXML['FulfillmentData']['Address']['City'];
  }

  /**
   * Gets state or region from MFA order report
   */
  function getFulfillmentAddressStateOrRegion() {
    return $this->RequestXML['FulfillmentData']['Address']['StateOrRegion'];
  }

  /**
   * Gets postal code from MFA order report
   */
  function getFulfillmentAddressPostalCode() {
    return $this->RequestXML['FulfillmentData']['Address']['PostalCode'];
  }

  /**
   * Gets country code MFA order report
   *
   */
  function getFulfillmentAddressCountryCode() {
    return $this->RequestXML['FulfillmentData']['Address']['CountryCode'];
  }


  /**
   * Gets buyer email address from MFA order report
   *
   */
  function getBuyerEmail() {
    return $this->RequestXML['BillingData']['BuyerEmailAddress'];
  }
  /**
   * Parse string in the format:
   *
   * 2008-07-23T14:33:02-07:00
   *
   * and converts it to yyyyMMdd hhmmss (OSCommerce formatted)
   */

  function getOrderDate() {
    $date = $this->RequestXML['OrderDate'];

    $i = strpos($date, 'T');
    $yyyyMMdd = substr($date, 0, $i);
    $hhmmss = substr($date, $i+1, 8);

    return $yyyyMMdd . ' ' . $hhmmss;
  }

  /**
   * Gets buyer first name from MFA order report
   *
   */
  function getBuyerFirstName() {
    $firstName = explode(" ",$this->RequestXML['BillingData']['BuyerName']);

    return $firstName[0];
  }

  /**
   * Gets buyer last name from MFA order report
   */
  function getBuyerLastName() {
    $lastName = explode(" ",$this->RequestXML['BillingData']['BuyerName']);
		
    return $lastName[1];
  }

  /*
   * Gets buyer name from MFA order report
   */
  function getBuyerName() {	
    return $this->RequestXML['BillingData']['BuyerName'];
  }

  /*
   * Gets buyer Phone Number from MFA order report
   */
  function getBuyerPhone() {
    return $this->RequestXML['BillingData']['BuyerPhoneNumber'];
  }

  /*
   * Gets buyer last name from MFA order report
   */
  function getBuyerFax() {
    return NULL;
  }
  
  /*
   * Gets Item Weight
   */
  function getItemWeight($itemid) {		
    return $this->Item[$itemid]['Weight'];
  }
  
  /*
   * Gets the order channel
   */
  function getOrderChannel($itemid) {
    return $this->Item[$itemid]['orderChannel'];
  }
  

  /*
   * Gets the order channel
   */
  function getPromotion($itemid) {
    return $this->Item[$itemid]['Promotion'];
  }
  
  /*
   * Gets the order channel
   */
  function getShipLabel($itemid) {
    return $this->Item[$itemid]['CBAShipLabel'];
  }


    /**
     *		Get the Customer Selected Shipping Service
     */

	  function getOrderFulfillmentShippingLabel() {
		return $this->RequestXML['DisplayableShippingLabel'];	
	  }
  
	/* New Order Operation and Status and Modified Time. These are called in create orders */

	/*
	* Gets the new order operation
	*/
	function getNewOrderOperation() {
		return constant("AMAZON_ORDER_STATUS_UNSHIPPED");
	}

	/*
	* Gets the the new order status - transaction
	*/
	function getNewOrderOperationStatus() {
		return "1";
	}
  
	/*
	* Gets the the new order modified time
	*/
	function getNewOrderModifiedTime() {
		return "now()";
	}

}
?>
