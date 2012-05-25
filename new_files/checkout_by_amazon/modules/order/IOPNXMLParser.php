<?php
/**
 * @brief IOPNXMLParser Class for parsing IOPN XML
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
class IOPNXMLParser{

  var $RequestXML;
  var $orderItems;
  var $data;
  var $item;

  function IOPNXMLParser($xml){
		$this->data = XML_unserialize($xml);
		$this->RequestXML = $this->data['ProcessedOrder'];
		$this->ProcessOrderItems();
  }

  function getAmazonOrderID() {
		return $this->RequestXML['AmazonOrderID'];
  }

  function getOrderItems(){
	    return $this->item; 
  }

  function ProcessOrderItems() {
      $items = $this->RequestXML['ProcessedOrderItems']['ProcessedOrderItem']; 

	  /* converting the item to array if there is only one item */
	  $items = $items['AmazonOrderItemCode'] != NULL ? array($items) : $items;


	  foreach($items as $myitem){
	  
		$myitemid = $myitem['SKU'];

		$this->item[$myitemid] = array();

		$this->item[$myitemid]['AmazonOrderItemCode'] = $myitem['AmazonOrderItemCode'];
		$this->item[$myitemid]['Title'] = $myitem['Title'];
		$this->item[$myitemid]['Quantity'] = $myitem['Quantity'];

		$this->setItemPriceValue($myitem['ItemCharges'],$myitemid);
		
		$this->setPromotionValue($myitem['ItemCharges'],$myitemid);

	  }

  }

    /**
     *		Sets the ItemCharges component
     */

	  function setItemPriceValue($ItemPrice_array,$myitemid){

		$promo = array("PrincipalPromo","ShippingPromo");

		foreach($ItemPrice_array['Component'] as $value){

			if(in_array($value['Type'],$promo)){
				continue;
			}

			
			/* IOPN sends the information as ItemCharges. we process it as ItemPrice*/
			$this->item[$myitemid]['ItemPrice'][$value['Type']] = $value['Charge']['Amount'];

		}

	  }


  function setPromotionValue($mypromotion,$myitemid){

	$promo = array("PrincipalPromo" => "Principal","ShippingPromo" => "Shipping");

    foreach($mypromotion['Component'] as $Promotion_array){
		
		/* Look for only PrincipalPromo or ShippingPromo */
		if(!array_key_exists($Promotion_array['Type'],$promo)){
			continue;
		}

		/* do not generate promotion if the amount is 0.0 */
		if($Promotion_array['Charge']['Amount'] == 0.0){
			continue;
		}

		/* IOPN has +ve promotion value. converting it to negative */
		if($Promotion_array['Charge']['Amount'] > 0 ){
			$Promotion_array['Charge']['Amount'] = "-" . $Promotion_array['Charge']['Amount'];
		}

		$this->item[$myitemid]['Promotion'][0][$promo[$Promotion_array['Type']]] = $Promotion_array['Charge']['Amount'];

    }

  }

    /**
     *		Gets Service Level name
     */

	  function getOrderFulfillmentServiceLevel() {
		return $this->RequestXML['ShippingServiceLevel'];	
	  }


    /**
     *		Get the Customer Selected Shipping Service
     */

	  function getOrderFulfillmentShippingLabel() {
		return $this->RequestXML['DisplayableShippingLabel'];	
	  }

    /**
     *		Gets name from IOPN order report
     */
    function getFulfillmentAddressName() {
        return $this->RequestXML['ShippingAddress']['Name'];
    }

    /**
     *		Gets first name from IOPN order report
     */
    function getFulfillmentAddressFirstName() {
        $name = $this->RequestXML['ShippingAddress']['Name'];
        $i = strrpos($name, ' ');

        $firstName = substr($name, 0, $i);
        return $firstName;
    }

    /**
     *		Gets last name from IOPN order report
     */
    function getFulfillmentAddressLastName() {
        $name = $this->RequestXML['ShippingAddress']['Name'];
        $i = strrpos($name, ' ');

        $lastName = substr($name, $i + 1);
        return $lastName;
    }

    /**
     *		Gets address field one from IOPN order report
     */
    function getFulfillmentAddressFieldOne() {
        return $this->RequestXML['ShippingAddress']['AddressFieldOne'];
    }

    /**
     * Gets address field two from IOPN order report
     */
    function getFulfillmentAddressFieldTwo() {
        return $this->RequestXML['ShippingAddress']['AddressFieldTwo'];
    }

    /**
     * Gets address city from IOPN order report
     */
    function getFulfillmentAddressCity() {
        return $this->RequestXML['ShippingAddress']['City'];
    }

    /**
     * Gets state or region from IOPN order report
     */
    function getFulfillmentAddressStateOrRegion() {
        return $this->RequestXML['ShippingAddress']['State'];
    }

    /**
     * Gets postal code from IOPN order report
     */
    function getFulfillmentAddressPostalCode() {
        return $this->RequestXML['ShippingAddress']['PostalCode'];
    }

    /**
     * Gets country code IOPN order report
     *
     */
    function getFulfillmentAddressCountryCode() {
        return $this->RequestXML['ShippingAddress']['CountryCode'];
    }


    /**
     * Gets buyer email address from IOPN order report
     *
     */
    function getBuyerEmail() {
        return $this->RequestXML['BuyerInfo']['BuyerEmailAddress'];
    }

    /**
     * returns the now date function ( for IOPN only )
     *
     */

    function getOrderDate() {

		
		return 'now()';

		/*
		$date = $this->RequestXML['OrderDate'];

        $i = strpos($date, 'T');
        $yyyyMMdd = substr($date, 0, $i);
        $hhmmss = substr($date, $i+1, 8);

        return $yyyyMMdd . ' ' . $hhmmss;
		*/
    }

    /**
     * Gets buyer first name from IOPN order report
     *
     */
    function getBuyerFirstName() {
        $firstName = explode(" ",$this->RequestXML['BuyerInfo']['BuyerName']);

        return $firstName[0];
    }

    /**
     * Gets buyer last name from IOPN order report
     */
	function getBuyerLastName() {
        $lastName = explode(" ",$this->RequestXML['BuyerInfo']['BuyerName']);
		
        return $lastName[1];
    }

	/*
     * Gets buyer name from IOPN order report
     */
	function getBuyerName() {	
        return $this->RequestXML['BuyerInfo']['BuyerName'];
    }

	/*
     * Gets buyer Phone Number from IOPN order report
     */
	function getBuyerPhone() {
        return $this->RequestXML['BuyerInfo']['BuyerPhoneNumber'];
    }

	/*
     * Gets buyer last name from IOPN order report
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
  
  	/*
     * Gets the order channel
     */
	function getNewOrderOperation() {
        return constant("AMAZON_ORDER_STATUS_PROCESSING");
    }

	/*
	* Gets the the new order status - transaction
	*/
	function getNewOrderOperationStatus() {
		return "0";
	}
  
	/*
	* Gets the the new order modified time
	*/
	function getNewOrderModifiedTime() {
		return "";
	}

}
?>
