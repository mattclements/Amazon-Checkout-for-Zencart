<?php
/**
 * @brief Various constants for Checkout by Amazon code  
 * @catagory Zen Cart Checkout by Amazon Payment Module
 * @author Balachandar Muruganantham
 * @copyright 2007-2010 Amazon Technologies, Inc
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
 
    // constant tags for XML
define("INTEGRATOR_NAME", 'CBAZencart1.0');
define("INTEGRATOR_ID", 'A1JTR13ML1DA21');
define("XMLNS_VERSION_TAG", 'http://payments.amazon.com/checkout/2009-05-15/');

	// maximum lengths of string fields in XML, (including 0)
define("MAX_DESC_LEN", 1999);
define("MAX_TITLE_LEN", 79);
define("MAX_SKU_LEN", 39);
define("MAX_CATEGORY_LEN", 39);

	// types of fulfillment 
define("MERCHANT_FULFILLMENT", "MERCHANT");

	// button values                                          
define("STYLE0_NAME", 'tan');
define("STYLE1_NAME", 'orange');
define("STYLE1_DESC", 'Classic Amazon orange & blue.');
define("STYLE0_DESC", 'A sleek tan & blue combination.');

	//size of "order" tag 
define("LEN_ORDER_TAG", 5);
	
	//Endpoint URLs for orders
define("SANDBOX_ENDPOINT", 'https://payments-sandbox.amazon.com/checkout/');
define("PROD_ENDPOINT", 'https://payments.amazon.com/checkout/');

define("CBA_POPUP_STYLE_SHEET",'"https://images-na.ssl-images-amazon.com/images/G/01/cba/styles/AmazonPaymentsThankYou.css"');

       //URLS for Amazon 1-click/express, order summary popup scripts
define("PROD_POPUP_ORDER_SUMMARY", '"https://images-na.ssl-images-amazon.com/images/G/01/cba/js/widget/AmazonPaymentsThankYou.js"');
define("SANDBOX_POPUP_ORDER_SUMMARY", '"https://images-na.ssl-images-amazon.com/images/G/01/cba/js/widget/sandbox/AmazonPaymentsThankYou.js"');

define("SANDBOX_1_CLICK", '"https://images-na.ssl-images-amazon.com/images/G/01/cba/js/widget/sandbox/widget.js"');
define("PROD_1_CLICK", '"https://images-na.ssl-images-amazon.com/images/G/01/cba/js/widget/widget.js"');

    //Style sheet, jquery setup scripts
define("CBA_JQUERY_SETUP",'"https://images-na.ssl-images-amazon.com/images/G/01/cba/js/jquery.js"');
define("CBA_STYLE_SHEET", '"https://images-na.ssl-images-amazon.com/images/G/01/cba/styles/one-click.css"');

    //partial strings in the button URL
define("HTML_BUTTON_FORM_METHOD",'<form method="POST" name="checkout_by_amazon" action="');
define("HTML_BUTTON_INPUT_TYPES", '"><input type="hidden" name="order-input" value="type:');
define("HTML_BUTTON_MERCHANT_SIGNED_ORDER", 'merchant-signed-order/aws-accesskey/1');
define("HTML_BUTTON_MERCHANT_UNSIGNED_ORDER", 'unsigned-order');
define("HTML_BUTTON_BEGIN_ORDER", ';order:');
define("HTML_BUTTON_SIGNATURE",  ';signature:');
define("HTML_BUTTON_AWS_KEY_ID", ';aws-access-key-id:' );
define("HTML_BUTTON_MAIN_BUTTON_LINK", '"><input alt="Buy Now with Amazon Payments" src="https://payments.amazon.com/gp/cba/button?ie=UTF8&color=');
define("HTML_BUTTON_SIZE_TAG", '&size=');
define("HTML_BUTTON_END_IMAGE", '" type="image"></form><br />');
define("HTML_BUTTON_CLIENT_REQUEST_ID_CART_ID", 'cartId');

 //HTML string representing Sandbox environment warning
define("HTML_SANDBOX_WARNING_OPENING",'</div><div align="right"><div style="text-align: center; width: 160px; font-size:11px; color: red;">' );
define("HTML_SANDBOX_WARNING_CLOSING", '</div>');

define('MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_CBA_TEXT', 'Or');
define('MODULE_PAYMENT_CHECKOUTBYAMAZON_USING_SANDBOX', 'WARNING: Checkout by Amazon set to use Sandbox; orders will not be charged');
define('MODULE_PAYMENT_CHECKOUTBYAMAZON_INVALID_ORDER', 'Not available for these items');

/* Amazon Related Tables */
define('TABLE_AMAZON_PAYMENTS', DB_PREFIX . "amazon_payments");
define('TABLE_AMAZON_ORDER_HISTORY', DB_PREFIX . "amazon_order_history");

/* log path setting */
define('DIR_FS_CBA',DIR_FS_CATALOG . 'checkout_by_amazon/');
define('DIR_WS_CBA',DIR_WS_CATALOG . 'checkout_by_amazon/');

define("LOG_DIR", DIR_FS_CBA . 'log/');
define('LOG_FILE', LOG_DIR . 'checkout_by_amazon.log');


/* supported shipping carrier */
$shipping_carrier = array(
		array('id'=>'0','text'=>'Select a Carrier'),
		array('id'=>'USPS','text'=>'USPS'),
		array('id'=>'UPS','text'=>'UPS'),
		array('id'=>'FedEx','text'=>'FedEx'),
		array('id'=>'DHL','text'=>'DHL'),
		array('id'=>'NipponExpress','text'=>'NipponExpress')
);

/* refund reason array */
$refund_reason = array(
	array('id'=>'0','text'=>'Select a Reason'),
	array('id'=>'NoInventory','text'=>'No Inventory'),
	array('id'=>'CustomerReturn','text'=>'Customer Return'),
	array('id'=>'GeneralAdjustment','text'=>'General Adjustment'),
	array('id'=>'CouldNotShip','text'=>'Could Not Ship'),
	array('id'=>'DifferentItem','text'=>'Different Item'),
	array('id'=>'CustomerCancel','text'=>'Customer Cancel'),
	array('id'=>'ProductOutofStock','text'=>'Product Out of Stock'),
	array('id'=>'CustomerAddressIncorrect','text'=>'Customer Address Incorrect')
);

/* custom amazon order status for amazon tables */
define('AMAZON_ORDER_STATUS_PROCESSING','1000');
define('AMAZON_ORDER_STATUS_UNSHIPPED','1001');
define('AMAZON_ORDER_STATUS_SHIPMENT','1002');
define('AMAZON_ORDER_STATUS_CANCEL','1003');
define('AMAZON_ORDER_STATUS_REFUND','1004');
define('AMAZON_ORDER_STATUS_ERROR','1005');

/* amazon status mapping text */
$amazon_status_mapping = array(
			 AMAZON_ORDER_STATUS_PROCESSING => "OrderReview",
			 AMAZON_ORDER_STATUS_UNSHIPPED => "Unshipped",
			 AMAZON_ORDER_STATUS_SHIPMENT => "Shipment",
			 AMAZON_ORDER_STATUS_CANCEL => "Cancel",
			 AMAZON_ORDER_STATUS_REFUND => "Refund",
			 AMAZON_ORDER_STATUS_ERROR => "Error"
);

$zencart_order_status_amazon_status_mapping = array(
                                                    AMAZON_ORDER_STATUS_PROCESSING => MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_PROCESSING,
                                                    AMAZON_ORDER_STATUS_UNSHIPPED => MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_PENDING,
                                                    AMAZON_ORDER_STATUS_SHIPMENT => MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_DELIVERED,
                                                    AMAZON_ORDER_STATUS_CANCEL => MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_CANCEL,
                                                    AMAZON_ORDER_STATUS_REFUND => MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_REFUND,
                                                    AMAZON_ORDER_STATUS_ERROR => MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_ERROR
                                                    );

define('CALLBACK_HMAC_ALGORITHM','sha1');

/* Form keys sent to Checkout by Amazon in response. */
define("RESPONSE_KEY","order-calculations-response");
define("RESPONSE_AWS_KEY","aws-access-key-id");
define("RESPONSE_SIGNATURE_KEY","Signature");
?>