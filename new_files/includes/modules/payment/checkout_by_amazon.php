<?php
/**
 * @brief Defines the class representing the Checkout by Amazon Module
 * @catagory ZenCart Checkout by Amazon Payment Module
 * @author Neil Corkum
 * @author Allison Naaktgeboren
 * @author Joshua Wong
 * @author Balachandar Muruganantham
 * @copyright Portions copyright 2007-2010 Amazon Technologies, Inc
 * @copyright portions copyright zencart, 2002-2008
 * @license GPL v2, please see LICENSE.txt
 * @access public
 * @version $Id: $
 * 
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

require_once(DIR_FS_CATALOG . 'checkout_by_amazon/checkout_by_amazon_constants.php');

class checkout_by_amazon extends base{

	/*
	*    Debug flag
	*/

	var $debug = false;
  
	/**
	* string repesenting the payment method
	*
	* @var string
	*/
  
	var $code;

	/**
	* $title is the displayed name for this payment method
	*
	* @var string
	*/

	var $title;

	/**
	* $description is a soft name for this payment method
	*
	* @var string
	*/

	var $description;

	/**
	* $enabled determines whether this module shows or not... in catalog.
	*
	* @var boolean
	*/

	var $enabled;

	/*
	 *    Default Currency Code
	 */

	var $currency = "USD";

/**
 * @brief creates a new instance of checkout_by_amazon
 * @post one instance of the class is created 
 */
    function checkout_by_amazon() {

		$this->code = 'checkout_by_amazon';
		$this->codeVersion = '1.3.9e';
        $this->title = MODULE_PAYMENT_CHECKOUTBYAMAZON_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_CHECKOUTBYAMAZON_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_PAYMENT_CHECKOUTBYAMAZON_SORT_ORDER;
	    $this->enabled = ((MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS == 'True') ? true : false);

		if(defined("MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_DIAGNOSTIC_LOGGING") && MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_DIAGNOSTIC_LOGGING == "True"){
			$this->debug = true;
		}

		// set error messages if misconfigured
		if ($this->enabled) {
			if (MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTID == '') {
				$this->title .= MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTID_MISSING;	
			}else{

				/* For Signing */
				if(MODULE_PAYMENT_CHECKOUTBYAMAZON_SIGNING == 'True'){
					if(MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSACCESSID == ''){
						$this->title .= MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSACCESSID_MISSING;
					}elseif(MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSSECRETKEY == ''){
						$this->title .= MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSSECRETKEY_MISSING;					
					}
					
				}
				
				/* For Order Management */
				if(MODULE_PAYMENT_CHECKOUTBYAMAZON_ORDER_MANAGEMENT == 'True'){
					if(MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTEMAIL == ''){
						$this->title .= MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTEMAIL_MISSING;
					}elseif(MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTPASSWORD == ''){
						$this->title .= MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTPASSWORD_MISSING;					
					}elseif(MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTTOKEN == ''){
						$this->title .= MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTTOKEN_MISSING;					
					}elseif(MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTNAME == ''){
						$this->title .= MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTNAME_MISSING;					
					}
					
				}

				/* to check whether curl module exists or not */
				if (!function_exists('curl_init')){
					$this->title .= MODULE_PAYMENT_CHECKOUTBYAMAZON_CURL_MISSING;
				}

			}
		}
    }

/**
 * @brief selects the code string & the title 
 * @return returns false so that checkout by amazon doesnt get displayed in the Alternate Payment page
 */
    function selection() {
		return false;
    }

/**
 * @brief addresses any calls which should take place during start up
 * @post ignores checkout by amazon module in the osCommer checkout page
 */
    function before_process() {
		zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
    }

/**
 * @brief determines if config table has been turned on
 * @return number of rows within the config table 
 */
    function check() {
		global $db;
		if (!isset($this->_check)) {
		  $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS'");
		  $this->_check = $check_query->RecordCount();
		}
		return $this->_check;
    }

/**
 * @brief stub function required of /payment files 
 */
    function update_status() {
    }

/**
 * @brief stub function required of /payment files
 */
    function javascript_validation() {
		return false;
    }
/**
 * @brief stub function required of /payment files
 */
    function pre_confirmation_check() {
		return false;
    }

/**
 * @brief stub function required of /payment files
 */
    function confirmation() {
		return false;
    }

/**
 * @brief stub function required of /payment files
 */
    function process_button() {
    }

/**
 * @brief stub function required of /payment files
 */
    function after_process() {
		return false;
    }

/**
 * @brief stub function required of /payment files
 */
    function output_error() {
		return false;
    }

/**
 * @brief stub function required of /payment files
 */
	function _doVoid($oID) {
	    global $db, $messageStack, $zencart_order_status_amazon_status_mapping;

	  require_once(DIR_FS_CBA . 'library/AmazonMerchantClient.php');
	  require_once(DIR_FS_CBA . 'zencart.php');

	  $login = MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTEMAIL;
	  $password = MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTPASSWORD;
	  $merchant_token = MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTTOKEN;  
	  $merchant_name = MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTNAME;
	  $client =  new AmazonMerchantClient($login, $password, $merchant_token, $merchant_name);

		/* Amazon Payments Code Starts Here */

		$amazon_order_id = trim(zen_db_input($_POST['amazon_order_id']));
		$status_code = trim(zen_db_input($_POST['status_code']));

		switch($status_code){
			case AMAZON_ORDER_STATUS_CANCEL:
				$reference_id = $client->cancelOrder($amazon_order_id,$oID);
				$messageStack->add_session('Order Cancellation Request is sent to Amazon', 'success');
				$comments = "Order Cancellation";
				break;
			case AMAZON_ORDER_STATUS_SHIPMENT:
				$shipping_carrier = trim(zen_db_input($_POST['shipping_carrier']));;
				$shipping_service = trim(zen_db_input($_POST['shipping_service']));;
				$tracking_id = trim(zen_db_input($_POST['tracking_id']));;
				if($shipping_carrier == '0' || empty($shipping_service) || empty($tracking_id)){
					 $messageStack->add_session('Please fill the required values for confirming shipment', 'warning');
					 zen_redirect(zen_href_link(FILENAME_AMAZON_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
				}else{
					$reference_id = $client->shipOrder($amazon_order_id,$shipping_carrier,$shipping_service,$tracking_id);
					$comments = ENTRY_AMAZON_SHIPPING_CARRIER . $shipping_carrier . ENTRY_AMAZON_SHIPPING_SERVICE . $shipping_service . ENTRY_AMAZON_SHIPPING_TRACKING_NUMBER . $tracking_id;
					$messageStack->add_session('Order Confirmation Request is sent to Amazon', 'success');
				}
				break;
			case AMAZON_ORDER_STATUS_REFUND:
				$refund_reason = zen_db_prepare_input($_POST['refund_reason']);
				if($refund_reason == '0'){
					 $messageStack->add_session('Please select the reason for Refund!', 'warning');
					 zen_redirect(zen_href_link(FILENAME_AMAZON_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
				}else{
					
				    $amazon_orders_query = "select xml, xmltype from ".TABLE_AMAZON_PAYMENTS." where amazon_order_id='$amazon_order_id'";
					$amazon_orders = $db->Execute($amazon_orders_query);
					$xmlparser = $amazon_orders->fields['xmltype'] . "XMLParser";

					if(!class_exists($xmlparser)){
						include_once(DIR_FS_CBA . "modules/order/".$xmlparser.".php");
					}

					$data = new $xmlparser($amazon_orders->fields['xml']);
					$reference_id = $client->refundOrder($amazon_order_id,$refund_reason,$data->getOrderItems());
					$comments = "Refund Reason: " . $refund_reason;
					$messageStack->add_session('Order Refund Request is sent to Amazon', 'success');
				}
				break;
			default:
				// do nothing as of now
		}

		if($this->debug){
			writelog("Type of Request : $status_code ; Request Reference Id: $reference_id");
		}


		/* insert into amazon order status history */
		$sql_data_array = array(
		  'amazon_order_id' => $amazon_order_id,
		  'orders_id' => $oID,
		  'reference_id' => $reference_id,
		  'created_on' => 'now()',
		  'status'=>'0', // default status for that txn
		  'operation' => $status_code, // callback status which is set
		  'comments' => $comments
		);
	
		zen_db_perform(TABLE_AMAZON_ORDER_HISTORY, $sql_data_array);

		/* Amazon Payments Code Ends Here */

	}

/**
 * @brief Admini notification
 */
	function admin_notification($zf_order_id) {
		global $db;
		
		require_once(DIR_FS_CBA . 'zencart.php');

		if(amazon_order_id($zf_order_id)){	
			include_once(DIR_FS_CBA . 'checkout_by_amazon_admin.php');
		}
	}

/**
 * @brief installs all the merchant settings in database 
 * @post entires are installed in database, with assiocated strings (will be visible in Admin UI)
 * @see keys() 
 * @note all entires in install must be entered into mySQL via keys()
 * @note this function required by this name for admin/modules.php. Allows it to be called in UI
 * @note the style types are hardcoded here, otherwise Button Style will not highlight
 *	default value in UI menu  
 */
    function install() {

		global $db;

		if(isset($_GET["subaction"])){
			$this->$_GET["subaction"]();
			$url =  zen_href_link(FILENAME_MODULES,'','SSL') . "?set=payment&module=checkout_by_amazon";
			zen_redirect($url);

		}

		if(!defined("MODULE_PAYMENT_CHECKOUTBYAMAZON_SCHEMA_VERSION")){

			$db->Execute("insert into ".TABLE_CONFIGURATION.
				 " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order,date_added) values ('', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_SCHEMA_VERSION', '1', '', '6', '3', now())");
		}

		 $mandatory_flag = "<font color=\'red\'><b> * </b></font>";
		// GENERAL OPTIONS

		$db->Execute("insert into ".TABLE_CONFIGURATION.
				 " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('<br/>GENERAL OPTIONS<br/><hr/><br/>Enable Checkout by Amazon', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS', 'True', '<br/>Allow customers to use Checkout by Amazon on your web store<br/><hr>".$mandatory_flag." Indicates mandatory parameters if \'Enable Checkout by Amazon\' is set to True.', '6', '3', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		$db->Execute("insert into ".TABLE_CONFIGURATION.
				 " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Checkout by Amazon merchantID".$mandatory_flag."', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTID', '', '<a href=\'https://sellercentral.amazon.com/gp/cba/seller/account/settings/user-settings-view.html/ref=sc_navbar_m1k_cba_order_pipe_settings\' target=\'_blank\'/>Click here to get your MerchantID</a>', '6', '4', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
				 " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Operating environment', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_OPERATING_ENVIRONMENT', 'Sandbox', 'Select whether Checkout by Amazon should operate in the test sandbox or the live production environment. <br><i>Note: Currently Post Order Management cannot be tested on Sandbox</i>', '6', '3', 'zen_cfg_select_option(array(\'Production\', \'Sandbox\'), ', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
				 " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Checkout Button Size', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_BUTTON_SIZE', 'Large', 'Creates either a large(151x27) or medium(126x24) Checkout By Amazon button.', '6', '3', 'zen_cfg_select_option(array(\'Large\',\'Medium\'), ', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
				 " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Button Style', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_BUTTON_STYLE', 'Orange', 'Choose from two styles of buttons', '6', '3', 'zen_cfg_select_option(array(\'Orange\', \'Tan\'), ', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
				 " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_SORT_ORDER', '0', 'Order in which different payment options you have enabled are displayed. Lowest is displayed first.', '6', '0', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
				 " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Cart expiration time (in minutes)', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_CART_EXPIRATION', '0', 'The number of minutes a cart is valid for (0 for no expiration)', '6', '4', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
				 " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Cancelation Return Page', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_CANCEL_URL', '', 'Please enter the complete URL of the page you would like your customers to return to if they abandon or cancel an order.  If you do not enter one, the default is the main catalog page', '6', '4', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
				 " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Success Return Page', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_RETURN_URL','', 'Please enter the complete URL of the page you would like your customers to return after a purchase.  If you choose not to specify one, the index page will be used', '6', '4', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
				 " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Diagnostic Logging', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_DIAGNOSTIC_LOGGING', 'False', 'Enables diagnostic logging for debugging this plugin.', '6', '3', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

		/* signing */
		$db->Execute("insert into ".TABLE_CONFIGURATION.
				" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('<br/>SIGNING OPTIONS<br/><hr/><br/>Enable Order Signing', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_SIGNING', 'True', '<i>Please note that Amazon recommends Signed carts. The signature helps to validate the cart is not manipulated between your website and Amazon.</i><br/><font color=\'red\'><b>* </b></font> Indicates mandatory params if \'Enable Order Signing\' is True', '6', '3', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
				" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('AWS Access ID".$mandatory_flag."', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSACCESSID', '', '<a href=\'https://sellercentral.amazon.com/gp/cba/seller/accesskey/showAccessKey.html/ref=sc_tab_home_cba_access_key\' target=\'_blank\'/>Click here to get your AWS Access ID</a>', '6', '4', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
				" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('AWS Secret Key<font color=\'red\'><b> *</b></font>', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSSECRETKEY', '', '<a href=\'https://sellercentral.amazon.com/gp/cba/seller/accesskey/showAccessKey.html/ref=sc_tab_home_cba_access_key\' target=\'_blank\'/>Click here to get your AWS Secret Key</a>', '6', '4', now())");

        /* post-order management information */
		$db->Execute("insert into ".TABLE_CONFIGURATION.
				" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('<br/><br/>ORDER MANAGEMENT OPTIONS<br/><hr/><br/>Enable Order Mgmt', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_ORDER_MANAGEMENT', 'False', '<br/>Manage orders placed through Checkout by Amazon within your admin UI<br/><hr>".$mandatory_flag." Indicates mandatory parameters if \'Enable Ord Mgmt\' is set to True.', '6', '3', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
				" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant Login Id".$mandatory_flag."', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTEMAIL', '', '', '6', '4', now())");
		$db->Execute("insert into ".TABLE_CONFIGURATION.
				" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant Password".$mandatory_flag."', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTPASSWORD', '', '', '6', '4', now())");
		$db->Execute("insert into ".TABLE_CONFIGURATION.
				" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant Token".$mandatory_flag."', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTTOKEN', '', '<a href=\'https://sellercentral.amazon.com/gp/seller/configuration/account-info-page.html/ref=sc_navbar_m1k_seller_cfg\' target=\'_blank\'/>Click here to get your Merchant Token</a>', '6', '4', now())");
		$db->Execute("insert into ".TABLE_CONFIGURATION.
				" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant Name".$mandatory_flag."', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTNAME', '', '<a href=\'https://sellercentral.amazon.com/gp/seller/configuration/account-info-page.html/ref=sc_navbar_m1k_seller_cfg\' target=\'_blank\'/>Click here to get your Merchant Name</a>', '6', '4', now())");
	    
		/* Order Status Mapping */
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, 
				configuration_group_id, sort_order, set_function, use_function, date_added) values ('<br/><br/>ORDER STATUS MAPPING<br/><hr/>New Order Status', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_PROCESSING', 1, 'What should be the order status when a new order placed by your customer is pending review from Amazon? This state will indicate that the order CANNOT be processed from your end.<br />Recommended: <strong>Processing</strong>', '6', 121, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, 
				configuration_group_id, sort_order, set_function, use_function, date_added) values ('ReadyToShip Order Status', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_PENDING', 1, 'What should be the order status after Amazon processes it? This state will indicate that the order is pending shipment from your end.<br />Recommended: <strong>Pending</strong>', '6', 121, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

	    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Delivered Order Status', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_DELIVERED', 1, 'What should be the order status after you deliver it? The order will move into this state when you click <img src=\'".DIR_WS_CBA."images/confirm_shipment.jpg\' align=\'absmiddle\'/> button.<br />Recommended: <strong>Delivered</strong>', '6', 121, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

	    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Refund Order Status', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_REFUND', 1, 'What should be the order status after you apply a refund on it? The order will move into this state when you click <img src=\'".DIR_WS_CBA."images/refund_order.jpg\' align=\'absmiddle\'/> button.<br />Recommended: <strong>Refund</strong>', '6', 121, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

	    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Canceled Order Status', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_CANCEL', 1, 'What should be the order status when the order gets canceled? The order will move into this state when you click <img src=\'".DIR_WS_CBA."images/cancel_order.jpg\' align=\'absmiddle\'/> button or when the buyer\amazon cancels it.<br />Recommended: <strong>Canceled</strong>', '6', 121, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

		// callbacks
		$db->Execute("insert into ".TABLE_CONFIGURATION.
		" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('<br/>CALLBACK OPTIONS<br/><hr/><br/>Enable Callbacks', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_CALLBACK','False', '<i>The Callback API lets you specify shipping and taxes using your own application logic at the time an order is placed when using Checkout by Amazon</i>', '6', '3', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");


		if (ENABLE_SSL_CATALOG == 'true') {
			$link = HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG;
		} else {
			$link = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
		}

		$callback_url = $link . 'checkout_by_amazon.php';

		$db->Execute("insert into ".TABLE_CONFIGURATION.
		" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Callback Page<font color=\'red\'><b> * </b></font>', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_CALLBACK_URL','$callback_url', 'Please enter the complete URL of the Callback page. use HTTPS if you are Operating environment is <b>Production</b> else use HTTP.  If you choose not to specify one, the index osCommerce page will be used', '6', '4', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
		" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Shipping Calculations', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_CALLBACK_SHIPPING','True', 'Should dynamic shipping calculations be enabled as part of Callbacks', '6', '3', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
		" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Tax Calculations', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_CALLBACK_TAXES','True', 'Should dynamic tax calculations be enabled as part of Callbacks', '6', '3', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

		$db->Execute("insert into ".TABLE_CONFIGURATION.
		" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Is Shipping and Handling Taxed', 'MODULE_PAYMENT_CHECKOUTBYAMAZON_CALLBACK_IS_SHIPPING_TAXED','True', 'Please specify whether the shipping amount should be taxed as part of Callbacks', '6', '3', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

		$this->update_schema();
		$this->notify('NOTIFY_PAYMENT_CHECKOUT_BY_AMAZON_INSTALLED');

    }

/**
 * @brief removes a configuration entry from the mySQL database 
 * @post a configuration entry has been removed 
 */
    function remove() {

		global $db;
		$db->Execute("delete from ".TABLE_CONFIGURATION." where configuration_key in ('".implode("', '",$this->keys())."')");
		$db->Execute("delete from ".TABLE_CONFIGURATION." where configuration_key='MODULE_PAYMENT_CHECKOUTBYAMAZON_SCHEMA_VERSION'");		
		$this->notify('NOTIFY_PAYMENT_CHECKOUT_BY_AMAZON_UNINSTALLED');

    }

/**
 * @brief returns the list of configuration keys for merchant configuration 
 * @return an array of the configuration keys associated with the install function for the mySQL database 
 * @see install()
 */
    function keys() {
        return array(
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTID',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_OPERATING_ENVIRONMENT',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_BUTTON_SIZE',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_BUTTON_STYLE',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_SORT_ORDER',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_CART_EXPIRATION',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_CANCEL_URL',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_RETURN_URL',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_DIAGNOSTIC_LOGGING',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_SIGNING',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSACCESSID',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSACCESSID',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_AWSSECRETKEY',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_ORDER_MANAGEMENT',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTEMAIL',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTPASSWORD',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTTOKEN',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTNAME',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_PROCESSING',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_PENDING',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_DELIVERED',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_REFUND',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS_CANCEL',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_CALLBACK',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_CALLBACK_URL',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_CALLBACK_SHIPPING',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_CALLBACK_TAXES',
				'MODULE_PAYMENT_CHECKOUTBYAMAZON_CALLBACK_IS_SHIPPING_TAXED'
			);
    }

	function update_schema(){
	    
		global $db,$sniffer;    // Now do database-setup:

		if (!$sniffer->table_exists(TABLE_AMAZON_PAYMENTS)) {
		  $sql = "CREATE TABLE " . TABLE_AMAZON_PAYMENTS . " (
					  `id` int(11) NOT NULL auto_increment,
					  `amazon_order_id` varchar(25) NOT NULL,
					  `orders_id` int(11) NOT NULL,
					  `xml` text NOT NULL,
					  `cart` text NOT NULL,
					  `xmltype` varchar(5) NOT NULL,
					  `reference_id` varchar(255) NOT NULL,
					  `ip_address` varchar(96) NOT NULL,
					  `is_ack` int(1) NOT NULL default '0',
					  `created_on` datetime NOT NULL,
					  `reported_on` datetime NOT NULL,
					  `modified_on` datetime NOT NULL,
					  PRIMARY KEY  (`id`))";
		  $db->Execute($sql);
		}

		if (!$sniffer->table_exists(TABLE_AMAZON_ORDER_HISTORY)) {
		  $sql = "CREATE TABLE " . TABLE_AMAZON_ORDER_HISTORY . " (
				  `id` int(11) NOT NULL auto_increment,
				  `amazon_order_id` varchar(25) NOT NULL,
				  `orders_id` int(11) NOT NULL,
				  `xml` text NOT NULL,
				  `reference_id` varchar(255) NOT NULL,
				  `status` int(10) NOT NULL default '0',
				  `operation` varchar(10) NOT NULL,
				  `comments` varchar(255) NOT NULL,
				  `created_on` datetime NOT NULL,
				  `modified_on` datetime NOT NULL,
				  PRIMARY KEY  (`id`),
				  UNIQUE KEY `amazon_order_reference_id_unique_key` (`amazon_order_id`,`reference_id`,`status`))";
		  $db->Execute($sql);
		}
	}

}
?>