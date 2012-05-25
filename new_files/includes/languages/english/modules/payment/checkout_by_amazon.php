<?php
/**
 * @brief the defines associated with the install panel in the Admin UI
 * @catagory zencart Checkout by Amazon Payment Module
 * @author Balachandar Muruganantham
 * @copyright 2007-2010 Amazon Technologies, Inc
 * @license GPL v2, please see LICENSE.txt
 * @access public
 * @version $Id: $
 * @note this location is specific to language, each supported language should have one 
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

define('MODULE_PAYMENT_CHECKOUTBYAMAZON_TEXT_TITLE', 'Checkout by Amazon 1.0');

define('MODULE_PAYMENT_CHECKOUTBYAMAZON_UPDATE_TEXT',' <br/> <br/> <a href="modules.php?set=payment&module=checkout_by_amazon&action=install&subaction=update_schema">Click Here</a> to update the configuration after you have uploaded the files' . MODULE_PAYMENT_CHECKOUTBYAMAZON_SCHEMA_VERSION);

  if (MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS == 'True') {

	if(MODULE_PAYMENT_CHECKOUTBYAMAZON_SCHEMA_VERSION == 0){
	    define('MODULE_PAYMENT_CHECKOUTBYAMAZON_TEXT_DESCRIPTION', '<img src="https://payments.amazon.com/gp/cba/button?ie=UTF8&color='.strtolower(MODULE_PAYMENT_CHECKOUTBYAMAZON_BUTTON_STYLE).'&size=large"/>' . MODULE_PAYMENT_CHECKOUTBYAMAZON_UPDATE_TEXT);
	}else{
	    define('MODULE_PAYMENT_CHECKOUTBYAMAZON_TEXT_DESCRIPTION', '<img src="https://payments.amazon.com/gp/cba/button?ie=UTF8&color='.strtolower(MODULE_PAYMENT_CHECKOUTBYAMAZON_BUTTON_STYLE).'&size=large"/>');
	}

  } else { 
 define('MODULE_PAYMENT_CHECKOUTBYAMAZON_TEXT_DESCRIPTION', '<a target="_blank" href="https://payments.amazon.com/sdui/sdui/offer?apaysccid=AP_ZENCART">Click Here to Sign Up for Checkout By Amazon Merchant Account</a><br /><br /><a target="_blank" href="https://sellercentral.amazon.com/">Click to Login to the Seller Central</a><hr/>Checkout by Amazon&trade; is a complete e-commerce checkout solution that provides your customers with the same secure and trusted checkout experience available on Amazon.com today. It offers unique features including Amazon\'s 1-Click&reg; and tools for businesses to manage shipping charges, sales tax, promotions, and post-sale activities including refunds, cancellations, and chargebacks.');
  }

define('MODULE_PAYMENT_CHECKOUTBYAMAZON_TEXT_DESCRIPTION', 'Checkout by Amazon');


/* Mandatory alert */
define('MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTID_MISSING', '<span class="alert">&nbsp; Merchant ID is empty</span>');
define('MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSACCESSID_MISSING', '<span class="alert">&nbsp; AWS Access ID is empty</span>');
define('MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSSECRETKEY_MISSING', '<span class="alert">&nbsp; AWS Secret Key is empty</span>');
define('MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTEMAIL_MISSING', '<span class="alert">&nbsp; Merchant Email is empty</span>');
define('MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTPASSWORD_MISSING', '<span class="alert">&nbsp; Merchant Password is empty</span>');
define('MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTTOKEN_MISSING', '<span class="alert">&nbsp; Merchant token is empty</span>');
define('MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTNAME_MISSING', '<span class="alert">&nbsp; Merchant name is empty</span>');
define('MODULE_PAYMENT_CHECKOUTBYAMAZON_CURL_MISSING', '<span class="alert">&nbsp; CURL Not Found. Cannot use.</span>');


/* Needed for Orders Admin notification page */
define('ENTRY_AMAZON_ORDERS_ID', 'Amazon Order ID:');
define('ENTRY_AMAZON_SHIPPING_CARRIER', ' Carrier: ');
define('ENTRY_AMAZON_SHIPPING_SERVICE', ' Shipping Service: ');
define('ENTRY_AMAZON_SHIPPING_TRACKING_NUMBER', ' Shipment Tracking ID: ');

/* admin notification javascript alert messages */
define('ENTRY_AMAZON_CANCEL_CONFIRMATION_TEXT', ' Are you sure you want to Cancel this Order? ');
define('ENTRY_AMAZON_SELECT_THE_REASON_FOR_REFUND', ' Please select the reason for the Refund! ');
define('ENTRY_AMAZON_SHIPPING_SERVICE_TEXT', ' Please enter the Shipping Service! ');
define('ENTRY_AMAZON_SHIPPING_CARRIER_TEXT', ' Please select the Shipping Carrier! ');
define('ENTRY_AMAZON_SHIPPING_TRACKING_TEXT', ' Please enter the Shipment Tracking ID! ');

define('ENABLE_AMAZON_ORDER_MANAGEMENT', '<tr>	<td style="border:1px solid #ccc;background-color:#F75D59;color: #fff">Please set "Enable Order Mgmt" to "true"!</td></tr>');
define('ENABLE_AMAZON_PAYMENTS_MODULE', '<tr>	<td style="border:1px solid #ccc;background-color:#F75D59;color: #fff">Please enable "Checkout by Amazon" module!</td></tr>');
?>
