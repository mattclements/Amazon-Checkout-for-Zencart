<?php
/**
 * @brief Zencart Wrapper Functions
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

/* Table lookup functions */

/**
 *  search country by ISO code 2
 */
  function amazon_get_country_by_ISOCode2($iso_code_2) {

		$countries = "select countries_id ,countries_name, countries_iso_code_3
				  from " . TABLE_COUNTRIES . "
				  where countries_iso_code_2 = '" . trim($iso_code_2) . "'
				  order by countries_name";

		$countries_values = amazon_db_execute($countries);

		$country = array();
		$country['countries_id'] = $countries_values->fields['countries_id'];
		$country['countries_name'] = $countries_values->fields['countries_name'];
		$country['countries_iso_code_3'] = $countries_values->fields['countries_iso_code_3'];

		return $country;
  }


/**
 *  search country by ISO code 2
 */
  function amazon_get_zone_id($country_id, $zone_code) {

		$zones = "select zone_id, zone_name from " . TABLE_ZONES . " where zone_code = '" . trim($zone_code) . "' and zone_country_id = '" . trim($country_id) . "'	  order by zone_name";

		$zones_values = amazon_db_execute($zones);

		$zone = array();
		$zone['zone_id'] = $zones_values->fields['zone_id'];
		$zone['zone_name'] = $zones_values->fields['zone_name'];

		return $zone;
  }


/**
 *  get tax class for a product
 */
  function amazon_get_tax_class_id($products_id) {

		$product_info = "select products_tax_class_id, products_price, products_priced_by_attribute, product_is_free, product_is_call, products_type from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'" . " limit 1";

		$product_info_values = amazon_db_execute($product_info);

		$tax_class_id = $product_info_values->fields['products_tax_class_id'];
		return $tax_class_id;

  }

/**
 *		get amazon order id for zencart order id
 */
function amazon_order_id($oID){
	global $db;

	$amazon_order_query = "select amazon_order_id from ".TABLE_AMAZON_PAYMENTS." where orders_id='" . trim($oID) . "'";

	$amazon_order = $db->Execute($amazon_order_query);

	if ($amazon_order->RecordCount() > 0) {
		$amazon_order_id = $amazon_order->fields['amazon_order_id'];
		return $amazon_order_id;
	} else {
	  return null;
	}
}

/**
 *		get zencart order id for amazon order id
 */
function zencart_order_id($amazon_order_id){
	global $db;

	$amazon_order_query = "select orders_id from ".TABLE_AMAZON_PAYMENTS." where amazon_order_id='" . trim($amazon_order_id) . "'";

	$amazon_order = $db->Execute($amazon_order_query);

	if ($amazon_order->RecordCount() > 0) {
		$orders_id = $amazon_order->fields['orders_id'];
		return $orders_id;
	} else {
	  return null;
	}
}

/**
 *		amazon order id hidden field generator
 */
function amazon_order_id_hidden_field($oID){
	global $db;

	$amazon_order_query = "select amazon_order_id from ".TABLE_AMAZON_PAYMENTS." where orders_id='" . trim($oID) . "'";

	$amazon_order = $db->Execute($amazon_order_query);

	if ($amazon_order->RecordCount() > 0) {

		$amazon_order_id = $amazon_order->fields['amazon_order_id'];
		return zen_draw_hidden_field("amazon_order_id",$amazon_order_id);
	} else {
	  return '';
	}
}

/**
 *		Check for any inprogress.
 */
function any_merchant_action($oID){

	global $db;

	$amazon_order_action_query = "select status from ".TABLE_AMAZON_ORDER_HISTORY." where orders_id='" . trim($oID) . "' and status='0'";

	$amazon_order_action = amazon_db_execute($amazon_order_action_query);

	if ($amazon_order_action->RecordCount() > 0) {
		return true;
	}else{
		return false;
	}

}

/**
 *		Check for cancel request initiated by merchant..
 */
function merchant_requested_for_cancel($oID){

	global $db;

	$amazon_order_merchant_cancel_request_query = "SELECT status FROM ".TABLE_AMAZON_ORDER_HISTORY." WHERE orders_id='" . trim($oID) . "' AND status='0' AND operation='".AMAZON_ORDER_STATUS_CANCEL."' ";

	$amazon_order_merchant_cancel_request = amazon_db_execute($amazon_order_merchant_cancel_request_query);

	if ($amazon_order_merchant_cancel_request->RecordCount() > 0) {
		return true;
	}else{
		return false;
	}

}

/**
 *		Order shipment check
 */
function is_order_shipped($oID){

	global $db;

	$amazon_order_shipment_query = "select status from ".TABLE_AMAZON_ORDER_HISTORY." where orders_id='" . trim($oID) . "' and operation='".AMAZON_ORDER_STATUS_SHIPMENT."' and status='1'";

	$amazon_order_shipment = amazon_db_execute($amazon_order_shipment_query);

	if ($amazon_order_shipment->RecordCount() > 0) {
		return true;
	}else{
		return false;
	}

}

/**
 *		Order Refund check
 */
function is_order_refunded($oID){

	global $db;

	$amazon_order_refund_query = "select status from ".TABLE_AMAZON_ORDER_HISTORY." where orders_id='" . trim($oID) . "' and operation='".AMAZON_ORDER_STATUS_REFUND."' and status='1'";

	$amazon_order_refund = amazon_db_execute($amazon_order_refund_query);

	if ($amazon_order_refund->RecordCount() > 0) {
		return true;
	}else{
		return false;
	}

}

/**
 *		Order cancel check
 */
function is_order_canceled($oID){

	global $db;

	$amazon_order_cancel_query = "select status from ".TABLE_AMAZON_ORDER_HISTORY." where orders_id='" . trim($oID) . "' and operation='".AMAZON_ORDER_STATUS_CANCEL."' and status='1'";

	$amazon_order_cancel = amazon_db_execute($amazon_order_cancel_query);

	if ($amazon_order_cancel->RecordCount() > 0) {
		return true;
	}else{
		return false;
	}

}

/**
 *		Order status history table insert
 */
function insert_order_status_history($order_id, $order_status_id,$comments){

	/* insert into order status history */
	$sql_data_array = array (
							 'orders_id' => $order_id,
							 'orders_status_id' => $order_status_id,
							 'date_added' => 'now()',
							 'customer_notified' => '0',
							 'comments' => $comments
							 );
		
	amazon_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

}

/**
 *		Amazon Order status history table insert
 */
function insert_amazon_order_status_history($order_id, $amazon_order_id,$reference_id,$operation, $comments){

		/* insert into amazon order status history with unshipped transaction */
		$sql_data_array = array (
								 'amazon_order_id' => $amazon_order_id,
								 'orders_id' => $order_id,
								 'reference_id' => $reference_id,
								 'created_on' => 'now()',
								 'modified_on' => 'now()',
								 'status'=>'1', // default status for that txn
								 'operation' => $operation, // callback status which is set
								 'comments' => $comments
								 );
			
		zen_db_perform(TABLE_AMAZON_ORDER_HISTORY, $sql_data_array);

}

/**
 *		Amazon Order status history table update
 */
function update_amazon_order_status_history($orders_id,$amazon_order_id,$operation, $status){

		$amazon_order_status_sql = "UPDATE ".TABLE_AMAZON_ORDER_HISTORY." SET status='$status', modified_on=now() WHERE operation='".$operation."' AND status='0' AND amazon_order_id='".$amazon_order_id."' AND orders_id ='".$orders_id."'";

		$amazon_order_status = amazon_db_execute($amazon_order_status_sql);	

}


/**
 *		Orders table -> status update
 */
function update_order_status($orders_id, $order_status_id){

	$order_status_sql = "UPDATE ".TABLE_ORDERS." SET orders_status='$order_status_id' WHERE orders_id='$orders_id'";

	amazon_db_execute($order_status_sql);

}



/* DB related functions */

/*
 *	  Wrapper function for Zen_db_input
 */
function amazon_db_input($str){

	return zen_db_input($str);	

}

/*
 *	  Wrapper function for $db->Execute
 */

function amazon_db_execute($sql){
	global $db;

	$result = $db->Execute($sql);

	return $result;
}

/*
 *	  Wrapper function for RecordCount
 */

function amazon_recordcount($obj){

	return $obj->RecordCount();

}

/*
 *	  Wrapper function for INSERT, UPDATE, DELETE operation
 */

function amazon_db_perform($tablename, $sql_data_array, $action = 'insert', $parameters = '', $link = 'db_link'){

	zen_db_perform($tablename, $sql_data_array, $action,$parameters, $link);

}

/*
 *	  Wrapper function for returning last insert id
 */

function amazon_insert_id(){
	global $db;

	return $db->Insert_ID();
}

/* Logger functions */

/*
 *		@brief function to dump objects
 *
 *		@params $str String
 *		@params $obj object or array
 *		
 */

function ob_writelog($str,$obj){
	ob_start();
	var_dump($obj);
	$content=ob_get_contents();
	ob_end_clean();
	writelog($str ." -> ". $content);
}

/*
 *		@brief Generic function to write the log
 *
 *		@params $content String
 *		
 */

function writelog($content){

	if(MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_DIAGNOSTIC_LOGGING == 'True') {

		if(!file_exists(LOG_FILE)){
		  $handle = fopen(LOG_FILE,"w");
		}else{
		  $handle = fopen(LOG_FILE,"a+");
		}

		if (is_writable(LOG_FILE)) {
		  if (!$handle) {
			return;
		  }

		  $somecontent .= date("D M j G:i:s T Y") ." :- " . $content . "\n";
		  $somecontent .= "-----------------------------------------------------\n";

		  if (fwrite($handle, $somecontent) === FALSE) {
			return;
		  }
		  fclose($handle);
		}
	}
}

/*
 *		@brief Logs the POST request
 *		
 */

function requestlog(){
	if($_REQUEST){
	foreach ($_REQUEST as $k => $v) {
	  $somecontent .= "$k = ".str_replace("\\\"","\"",$v)."\n";
	}
	writelog($somecontent);
	}
}
?>