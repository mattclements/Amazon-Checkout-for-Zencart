<?php
/**
 * @brief Class which generates the CBA button in the shopping Cart page 
 * @catagory Zen Cart Checkout by Amazon Payment Module
 * @author Balachandar Muruganantham
 * @copyright Portions Copyright 2007-2008 Amazon Technologies, Inc
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


require_once('library/XmlBuilder.php');
require_once('library/xml.php');
require_once('library/HMAC.php');
require_once('checkout_by_amazon_constants.php');
require_once('custom_data.php');
require_once('zencart.php');

error_reporting(E_ALL ^ E_NOTICE);
class CheckoutByAmazonButton {

	var $debug = false;
	var $currency = 'USD';
	var $callback = false;

	function CheckoutByAmazonButton(){
		global $products;

		if(defined("MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_DIAGNOSTIC_LOGGING") && MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_DIAGNOSTIC_LOGGING == "True"){
			$this->debug = true;
		}

		if(defined("MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_CALLBACK") && MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_CALLBACK == "True"){
			$this->callback = true;
		}

		$this->item_array = $products;
		$this->zen_cart_id = HTML_BUTTON_CLIENT_REQUEST_ID_CART_ID . ':' .$_SESSION["cart"]->cartID;
		$this->GetOrderXml();
	}


	/**
	* @brief creates an XML order for Checkout by Amazon based on the current cart
	* @return the Amazon cart converted into an XML string 
	* @note the string fields, ie SKU, Title, Category,  Description, are truncated 
	*	to meet Checkout by Amazon schema limits
	* @see checkout_by_amazon_constants.php for the limits
	* @note Checkout by Amazon does not honor weights which are 0, however digital products
	*      legitmately have a weight of 0.  So if the weight of an item is 0,
	*      its weight will be excluded from the cart
	*/
    function GetOrderXml() {

		$xmlBuilder = new XmlBuilder();
		$cd = new CustomData();

		$xmlBuilder->Push('Order', array('xmlns'=>XMLNS_VERSION_TAG));

		// clientrequest id, ie cart identifier 
		$xmlBuilder->Element('ClientRequestId', $this->zen_cart_id);

		//cart expiration date if set
		$expiration_date = $this->GetCartExpirationDate();
		if ($expiration_date) {
			$xmlBuilder->Element('ExpirationDate', $expiration_date);
		}

		$xmlBuilder->Push('Cart');

		// add each cart item & its attributes 
		$xmlBuilder->Push('Items');

		foreach($this->item_array as $item) {
			$xmlBuilder->Push('Item');

			$xmlBuilder->Element('SKU', substr($item[id], 0, MAX_SKU_LEN));
			$xmlBuilder->Element('MerchantId',MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTID);
			$xmlBuilder->Element('Title', substr($item[name], 0, MAX_TITLE_LEN));
			$xmlBuilder->Element('Description', substr(zen_get_products_description($item[id],1), 0, MAX_DESC_LEN));

			// Price
			$xmlBuilder->Push('Price');
			$xmlBuilder->Element('Amount', $item[final_price]);
			$xmlBuilder->Element('CurrencyCode', $this->currency);
			$xmlBuilder->Pop('Price');	

			$xmlBuilder->Element('Quantity', $item[quantity]);

			// ignoring tag if weight is 0, otws, adding weight tag
			if($item[weight] != 0) {
				$xmlBuilder->Push('Weight');
				$xmlBuilder->Element('Amount', $item[weight]);
	
				// only lb is currently accepted
				if(TEXT_PRODUCT_WEIGHT_UNIT == "lbs"){
					$weight = "lb";
				}
				$xmlBuilder->Element('Unit', $weight);
				$xmlBuilder->Pop('Weight');
			}

			// item Category
            if($item[category] == "") {
				$item[category] = "Uncategorised";
            }else{
				$item[category] = substr(zen_get_categories_name_from_product($item[id]), 0, MAX_CATEGORY_LEN);
			}
			$xmlBuilder->Element('Category', $item[category]);

			// FulfillmentNetwork settings - Hardcoded for merchant fulfillment as we do not support FBA at present
			$xmlBuilder->Element('FulfillmentNetwork',	 MERCHANT_FULFILLMENT);

			/* Loads the Item custom xml */
			$itemcustom = $cd->GetItemCustomXml($item);

			if(count($itemcustom) > 0){
				$xmlBuilder->Push('ItemCustomData');
				$xmlBuilder->xml .= $this->array2xml($itemcustom,$xmlBuilder);
				$xmlBuilder->Pop('ItemCustomData');
			}

			$xmlBuilder->Pop('Item');
		}		

		$xmlBuilder->Pop('Items');

		/* Loads the Item custom xml */
		$cartcustom = $cd->GetCartCustomXml();
		if(count($cartcustom) > 0){
			$xmlBuilder->Push('CartCustomData');
			$xmlBuilder->xml .= $this->array2xml($cartcustom,$xmlBuilder);
			$xmlBuilder->Pop('CartCustomData');
		}

		$xmlBuilder->Pop('Cart');

		// integrator name and ID
		$xmlBuilder->Element('IntegratorId', INTEGRATOR_ID);
		$xmlBuilder->Element('IntegratorName', INTEGRATOR_NAME);

		// Optional post checkout URLs. This is to reset the cart and then redirect to the Merchant's return URL
		$return_url = zen_href_link('checkout_by_amazon.php', 'action=ResetCart', 'SSL',false, false, true, true);
		$cancel_url = zen_href_link('checkout_by_amazon.php', 'action=CancelCart', 'SSL',false, false, true, true);

		// return/success url & cancel url
		$xmlBuilder->Element('ReturnUrl', $return_url);
		$xmlBuilder->Element('CancelUrl', $cancel_url);

		/* callback functionality */
		if($this->callback){

			$xmlBuilder->Push('OrderCalculationCallbacks');
				$xmlBuilder->Element('CalculateTaxRates', strtolower(MODULE_PAYMENT_CHECKOUTBYAMAZON_CALLBACK_TAXES));
				$xmlBuilder->Element('CalculatePromotions','false');
				$xmlBuilder->Element('CalculateShippingRates',strtolower(MODULE_PAYMENT_CHECKOUTBYAMAZON_CALLBACK_SHIPPING));
				$xmlBuilder->Element('OrderCallbackEndpoint', MODULE_PAYMENT_CHECKOUTBYAMAZON_CALLBACK_URL);
				$xmlBuilder->Element('ProcessOrderOnCallbackFailure', 'false');
			$xmlBuilder->Pop('OrderCalculationCallbacks');

		}

		$xmlBuilder->Pop('Order');	// Order 
		$xml = $xmlBuilder->GetXml();

		if($this->debug){
			writelog("Cart XML : \n ".$xml);
			echo $xml;
		}
		
		$this->OrderXml = utf8_encode($xml);
    }

	/**
	* @brief converts the associative array into XML
	*/	
	function array2xml($data,&$xb){
		foreach($data as $key => $val){	
			if(is_array($val)){
				$xb->Push(ucfirst($key));
				$this->array2xml($val,$xb);
				$xb->Pop(ucfirst($key));
			}else{
				if(trim($val) !="")
					$xb->Element(ucfirst($key),$val);
			}
		}
	}

	/**
	* @brief encodes the var xml as a base64 string 
	* @return encoded string 
	*/
    function GetEncodedOrderXml() {		
		return base64_encode($this->OrderXml);
    }

	/**
	* @brief returns the encrypted order signature
	* @return a based64 encoded encrypted order signature 
	* @see HMAC.php  
	*/
    function GetOrderSignature() {
	
		$signature_calculator = new Crypt_HMAC(MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSSECRETKEY, 'sha1');
		$signature = $signature_calculator->hash($this->OrderXml);
		$binary_signature = pack('H*', $signature);
		return base64_encode($binary_signature);
    }

	/**
	* @brief returns the expiration date for the current cart, based on
	*	the merchant time limit & current time 
	* @return the expiration date as a string with preceding header 
	*/
    function GetCartExpirationDate() {
		$cart_expiration = MODULE_PAYMENT_CHECKOUTBYAMAZON_CART_EXPIRATION;
		$expiration_date = null;

		if ($cart_expiration > 0) {
			$current_time = time();
			$expiration_time = $current_time + (60 * $cart_expiration) - date('Z', $current_time);	// in UTC
			$expiration_date = date('Y-m-d\TH:i:s\Z', $expiration_time);
		}
		return $expiration_date;
    }

	
	/**
	* @brief generates the HTML checkout by amazon button, containing the order in XML
	* @return a string of code representing the button in HTML
	* @todo optimization to save on database accesses   
	*/
    function CheckoutButtonHtml() {
		$prod = false;		

		if (MODULE_PAYMENT_CHECKOUTBYAMAZON_OPERATING_ENVIRONMENT == 'Production') {
			$prod = true;
		}

		$code = HTML_BUTTON_FORM_METHOD;
		if($prod){
			$code.= PROD_ENDPOINT . MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTID;
		}else{
			$code.= SANDBOX_ENDPOINT . MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTID;
		}

		$code.= HTML_BUTTON_INPUT_TYPES;
		$code.= (MODULE_PAYMENT_CHECKOUTBYAMAZON_SIGNING == 'True') ?  HTML_BUTTON_MERCHANT_SIGNED_ORDER : HTML_BUTTON_MERCHANT_UNSIGNED_ORDER;
		$code.= HTML_BUTTON_BEGIN_ORDER;                                                                                               
		
		$code.= $this->GetEncodedOrderXml();
							
		// do signing if enabled
		if (defined("MODULE_PAYMENT_CHECKOUTBYAMAZON_SIGNING") && MODULE_PAYMENT_CHECKOUTBYAMAZON_SIGNING == 'True') {
			$code.= HTML_BUTTON_SIGNATURE;
			$code.= $this->GetOrderSignature();
			$code.= HTML_BUTTON_AWS_KEY_ID;
			$code.= MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSACCESSID;
		}
                                                                                                 
        $code.= HTML_BUTTON_MAIN_BUTTON_LINK . strtolower(MODULE_PAYMENT_CHECKOUTBYAMAZON_BUTTON_STYLE) . HTML_BUTTON_SIZE_TAG. strtolower(MODULE_PAYMENT_CHECKOUTBYAMAZON_BUTTON_SIZE). HTML_BUTTON_END_IMAGE;

		if (!$prod) {
		  $code.= HTML_SANDBOX_WARNING_OPENING;
		  $code.= MODULE_PAYMENT_CHECKOUTBYAMAZON_USING_SANDBOX;
		  $code.= HTML_SANDBOX_WARNING_CLOSING;
		}

		return $code;
    }

}
?>