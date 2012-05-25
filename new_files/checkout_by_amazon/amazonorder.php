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

require_once(DIR_FS_CBA . 'library/xml.php');
require_once(DIR_FS_CBA . 'library/AmazonMerchantClient.php');
require_once(DIR_FS_CBA . 'library/iopn_processor.php');
require_once(DIR_FS_CBA . 'zencart.php');
require_once(DIR_FS_CBA . 'zencartorder.php');

class AmazonOrder {

  /*
   *    Debug flag
   */

  var $debug = false;

  /*
   *    Amazon Seller Central Login
   */

  var $login;

  /*
   *    Amazon Seller Central Password
   */

  var $password;

  /*
   *    Amazon Seller Central Merchant Token
   */

  var $merchant_token;

  /*
   *    Amazon Seller Central Merchant Name 
   */

  var $merchant_name;

  /*
   *    Amazon Seller Central CURL Client
   */

  var $client;

  /*
   *	  @brief Constructor 
   */
  function AmazonOrder(){

    $this->login = MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTEMAIL;
    $this->password = MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTPASSWORD;
    $this->merchant_token = MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTTOKEN;  
    $this->merchant_name = MODULE_PAYMENT_CHECKOUTBYAMAZON_CBAMERCHANTNAME;

    /* Initialize the Amazon Merchant CURL Client */
    $this->client =  new AmazonMerchantClient($this->login, $this->password, $this->merchant_token, $this->merchant_name);

    if(defined("MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_DIAGNOSTIC_LOGGING") && MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_DIAGNOSTIC_LOGGING == "True"){
      $this->debug = true;
	  $this->client->debug = true;
    }

  }

  /**
   *		@brief Cache Orders from Amazon in TABLE_AMAZON_PAYMENTS table
   *
   */

  function cacheOrders(){

    $myorders = $this->client->getOrders();

	if(!$myorders){
		writelog("No Orders from Amazon");
		return false;
	}

    foreach($myorders as $amazon_order_id => $order){

      $order_exist = $this->orderExists($amazon_order_id);
      if(!$order_exist){

        $order_insert_sql = "INSERT INTO ".TABLE_AMAZON_PAYMENTS." (amazon_order_id, xml, xmltype, reference_id, reported_on) values('$amazon_order_id','".amazon_db_input($order['xml'])."','".amazon_db_input($order['type'])."','".amazon_db_input($order['reference_id'])."',now());";
        amazon_db_execute($order_insert_sql);

      }elseif(empty($order_exist->fields['xml'])){
	
        /* if order exists, update the report xml */
        $order_update_sql = "UPDATE ".TABLE_AMAZON_PAYMENTS." set xml = '".amazon_db_input($order['xml'])."', xmltype='".amazon_db_input($order['type'])."' , reference_id='".amazon_db_input($order['reference_id'])."', reported_on=now() WHERE amazon_order_id = '$amazon_order_id'";				
        amazon_db_execute($order_update_sql);

      }
    }

  }


  /**
   *		@brief Cache Orders from Amazon in TABLE_AMAZON_PAYMENTS table
   *
   */

  function updateAmazonOrder($myorders){

	global $zencart_order_status_amazon_status_mapping;

	foreach($myorders as $amazon_order_id => $order){
		switch($order['iopn']){

			case "OrderReadyToShipNotification":

				$order_id = zencart_order_id($amazon_order_id);

				update_amazon_order_status_history($order_id, $amazon_order_id,AMAZON_ORDER_STATUS_PROCESSING, "1");

				$comments = "Amazon Order is now ready to be shipped";

		        /* insert into amazon order status history with unshipped status */
				insert_amazon_order_status_history($order_id, $amazon_order_id,$order['reference_id'],AMAZON_ORDER_STATUS_UNSHIPPED, $comments);

				$order_status_id = $zencart_order_status_amazon_status_mapping[AMAZON_ORDER_STATUS_UNSHIPPED];

				insert_order_status_history($order_id, $order_status_id,$comments);

				update_order_status($order_id, $order_status_id);

				break;
			case "OrderCancelledNotification":
				$order_id = zencart_order_id($amazon_order_id);

				// Customer Cancelled Order -  update the orders and orders status history table
				if(!merchant_requested_for_cancel($order_id)){

					$comments = "Amazon / Buyer has canceled the order.";

					/* insert into amazon order status history with cancel status */
					insert_amazon_order_status_history($order_id, $amazon_order_id,$order['reference_id'],AMAZON_ORDER_STATUS_CANCEL, $comments);

					$order_status_id = $zencart_order_status_amazon_status_mapping[AMAZON_ORDER_STATUS_CANCEL];

					insert_order_status_history($order_id, $order_status_id,$comments);

					update_order_status($order_id, $order_status_id);

					return true;
				}

				if(!is_order_canceled($order_id)){
					
					// Merchant Initiated Cancelled Order - update the orders and orders status history table
					update_amazon_order_status_history($order_id, $amazon_order_id,AMAZON_ORDER_STATUS_CANCEL, "1");

					$comments = "Amazon Order has been canceled.";

					$order_status_id = $zencart_order_status_amazon_status_mapping[AMAZON_ORDER_STATUS_CANCEL];

					insert_order_status_history($order_id, $order_status_id,$comments);

					update_order_status($order_id, $order_status_id);

					return true;
				}

				break;
			default:
				return;
		}
	}

  }

  /**
   *		@brief New IOPN Orders from Amazon in TABLE_AMAZON_PAYMENTS table
   *
   */

  function NewIOPNOrders($myorders){

    foreach($myorders as $amazon_order_id => $order){

      $order_exist = $this->orderExists($amazon_order_id);
      if(!$order_exist){

        $order_insert_sql = "INSERT INTO ".TABLE_AMAZON_PAYMENTS." (amazon_order_id, xml, xmltype, reference_id, reported_on) values('$amazon_order_id','".amazon_db_input($order['xml'])."','".amazon_db_input($order['type'])."','".amazon_db_input($order['reference_id'])."',now());";
        amazon_db_execute($order_insert_sql);

      }elseif(empty($order_exist->fields['xml'])){
	
        /* if order exists, update the report xml */
        $order_update_sql = "UPDATE ".TABLE_AMAZON_PAYMENTS." set xml = '".amazon_db_input($order['xml'])."', xmltype='".amazon_db_input($order['type'])."' , reference_id='".amazon_db_input($order['reference_id'])."', reported_on=now() WHERE amazon_order_id = '$amazon_order_id'";				
        amazon_db_execute($order_update_sql);

      }

	   /* created orders immediately for IOPN */
	   $this->createOrders($amazon_order_id);

    }

  }

  /**
   *		@brief Temporary Order being created in TABLE_AMAZON_PAYMENTS table
   *
   */

  function tempOrder(){

    $serialize_cart = serialize($_SESSION['cart']);
    $amazon_order_id = amazon_db_input($_GET['amznPmtsOrderIds']);
    $ip_address = $_SESSION['customers_ip_address'];

    if(!$this->orderExists($amazon_order_id)){
      $temp_order_insert_sql = "INSERT INTO ".TABLE_AMAZON_PAYMENTS." (amazon_order_id, cart, ip_address, created_on) values('$amazon_order_id','".amazon_db_input($serialize_cart)."','".amazon_db_input($ip_address)."',now());";
      amazon_db_execute($temp_order_insert_sql);
    }
		
  }

  /**
   *		@brief Check whether Amazon Order exists in TABLE_AMAZON_PAYMENTS table
   *
   *		@params $amazon_order_id Amazon Order ID
   *
   *		@return boolean true or false
   */

  function orderExists($amazon_order_id){

    $order_exists_query = "select id,xml from ".TABLE_AMAZON_PAYMENTS." where amazon_order_id='" . trim($amazon_order_id) . "'";

    $orders = amazon_db_execute($order_exists_query);

    if (amazon_recordcount($orders) > 0) {
      return $orders;
    } else {
      return false;
    }
  }


  /**
   *		@brief Acknowledges the order
   *
   *		@return 
   */

  function acknowledgeOrders(){

    /* do now acknowledge orders if debug is true*/
    if($this->debug){
      return;
    }

    /* is_ack  0 - not acknowledged 1 - acknowledged */
    $orders_query = "SELECT distinct(reference_id) FROM ".TABLE_AMAZON_PAYMENTS." WHERE orders_id != 0 and is_ack=0 and xmltype='MFA'";

    $orders = amazon_db_execute($orders_query);

    while (!$orders->EOF) {
			
      $documentId = $orders->fields['reference_id'];

      $status = $this->client->postDocumentDownloadAck($documentId);

      writelog("Document ID: " . $documentId . "  ; Amazon Order ID ".$amazon_order_id ."  Zencart Order Id: " .$orders_id . "; Status: ". $status );

      if($status){
        $order_acknowldege_update_sql = "Update ".TABLE_AMAZON_PAYMENTS." SET is_ack=1 WHERE reference_id='$documentId'";
        amazon_db_execute($order_acknowldege_update_sql);
      }
			
      $orders->MoveNext();
    }
  }

  /**
   *		@brief Creates Zencart Order with data from TABLE_AMAZON_PAYMENTS table
   *
   *		@return boolean true or false
   */

  function createOrders($amazon_order_id=null){
    global $order, $order_total_modules, $checkout_by_amazon,$zencart_order_status_amazon_status_mapping;


    /* checkout by amazon payment module */
    if(!class_exists('checkout_by_amazon')){				
      require (DIR_WS_CLASSES . 'payment.php');
      $payments = new payment('checkout_by_amazon');
    }

    /* Currency Creation */
    if(!class_exists('currencies')){
      require (DIR_WS_CLASSES . 'currencies.php');
    }

    /* Rows with order_id  as 0 are retrieved to create order in zencart system */
	if(isset($amazon_order_id)){
		$orders_query = "SELECT amazon_order_id, xml, xmltype, cart, ip_address, reference_id,created_on,reported_on FROM ".TABLE_AMAZON_PAYMENTS." WHERE amazon_order_id='".$amazon_order_id."' AND orders_id=0";
	}else{
		$orders_query = "SELECT amazon_order_id, xml, xmltype, cart, ip_address, reference_id,created_on,reported_on FROM ".TABLE_AMAZON_PAYMENTS." WHERE orders_id=0";
	}

    /* Use this query if you want to debug something - uncomment and use it. */
	//$orders_query = "SELECT amazon_order_id, xml, xmltype, cart, ip_address, reference_id,created_on FROM ".TABLE_AMAZON_PAYMENTS." WHERE amazon_order_id='105-3210088-5857028'";

    $orders = amazon_db_execute($orders_query);

    while (!$orders->EOF) {
			
      $amazon_order_id = $orders->fields['amazon_order_id'];

	  /* if there are any data in session, destroy it */
	  //session_destroy();


      if(!empty($orders->fields['cart'])){
        $_SESSION['cart'] = unserialize($orders->fields['cart']);
        $_SESSION['customers_ip_address'] = $orders->fields['ip_address'];
      }

      $xmlparser = $orders->fields['xmltype'] . "XMLParser";

      if($this->debug){
        writelog("Amazon Order ID: ".$amazon_order_id."; XML Type: " . $xmlparser);
      }

      if(!class_exists($xmlparser)){
        include_once(DIR_FS_CBA . "modules/order/".$xmlparser.".php");
      }

      $data = new $xmlparser($orders->fields['xml']);

      /* customer account creation */
      $sql_data_array = array('customers_firstname' => $data->getBuyerFirstName(),
                              'customers_lastname' => $data->getBuyerLastName(),
                              'customers_email_address' => $data->getBuyerEmail(),
                              'customers_nick' => $data->getBuyerName(),
                              'customers_telephone' => $data->getBuyerPhone(),
                              'customers_fax' => $data->getBuyerFax(),
                              'customers_newsletter' => 0,
                              'customers_email_format' => 2,
                              'customers_default_address_id' => 0,
                              'customers_password' => zen_encrypt_password($password),
                              'customers_authorization' => (int)CUSTOMERS_APPROVAL_AUTHORIZATION
                              );

      if ((CUSTOMERS_REFERRAL_STATUS == '2' and $customers_referral != '')) $sql_data_array['customers_referral'] = $checkout_by_amazon->title;
      if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = '0001-01-01 00:00:00';

      $customer_exists = amazon_db_execute("select customers_id from ".TABLE_CUSTOMERS." where customers_email_address='".amazon_db_input($data->getBuyerEmail())."'");

      if(amazon_recordcount($customer_exists) == 0){

        amazon_db_perform(TABLE_CUSTOMERS, $sql_data_array);
        $_SESSION['customer_id'] = amazon_insert_id();

        if($this->debug){
          writelog("New Customer: " . $_SESSION['customer_id'] . "; Customer Name: " . $data->getBuyerName());
        }

      }else{
        $_SESSION['customer_id'] = $customer_exists->fields['customers_id'];

        if($this->debug){
          writelog("Existing Customer: " . $_SESSION['customer_id'] . "; Customer Name: " . $data->getBuyerName());
        }
      }

      //   amazon_db_execute("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int) $_SESSION['customer_id'] . "', '0', now())");

      /* customer address creation */
      $address_book = amazon_db_execute("SELECT address_book_id, entry_country_id, entry_zone_id FROM " . TABLE_ADDRESS_BOOK . "
																WHERE customers_id = '" . $_SESSION['customer_id'] . "'
																AND entry_street_address = '" . amazon_db_input($data->getFulfillmentAddressFieldOne()) . "'
																AND entry_suburb = '" . amazon_db_input($data->getFulfillmentAddressFieldTwo()) . "'
																AND entry_postcode = '" . amazon_db_input($data->getFulfillmentAddressPostalCode()) . "'
																AND entry_city = '" . amazon_db_input($data->getFulfillmentAddressCity()) . "'");

      if (amazon_recordcount($address_book) == 0) {
				
        $zone = amazon_db_execute("SELECT zone_id, zone_country_id FROM " .TABLE_ZONES . " WHERE zone_code = '" . amazon_db_input($data->getFulfillmentAddressStateOrRegion()) . "'");

        list ($firstname, $lastname) =	explode(' ', amazon_db_input($data->getFulfillmentAddressName()), 2);

        $sql_data_array = array (
                                 'customers_id' => $_SESSION['customer_id'],
                                 'entry_gender' => '',
                                 'entry_company' => '',
                                 'entry_firstname' => $firstname,
                                 'entry_lastname' => $lastname,
                                 'entry_street_address' => amazon_db_input($data->getFulfillmentAddressFieldOne()),
                                 'entry_suburb' => amazon_db_input($data->getFulfillmentAddressFieldTwo()),
                                 'entry_postcode' => amazon_db_input($data->getFulfillmentAddressPostalCode()),
                                 'entry_city' => amazon_db_input($data->getFulfillmentAddressCity()),
                                 'entry_state' => amazon_db_input($data->getFulfillmentAddressStateOrRegion()),
                                 'entry_country_id' => $zone->fields['zone_country_id'],
                                 'entry_zone_id' => $zone->fields['zone_id']
                                 );

        amazon_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

        $address_id = amazon_insert_id();

        amazon_db_execute("UPDATE " . TABLE_CUSTOMERS . " SET customers_default_address_id = '" . (int) $address_id . "'	WHERE customers_id = '" . (int) $_SESSION['customer_id'] . "'");

        $_SESSION['customer_default_address_id'] = $address_id;
        $_SESSION['customer_country_id'] = $zone_answer->fields['zone_country_id'];
        $_SESSION['customer_zone_id'] = $zone_answer->fields['zone_id'];
        $_SESSION['sendto'] = $address_id;

        if($this->debug){
          writelog("New Address ID: " . $_SESSION['customer_default_address_id']);
        }
      } else {

        $_SESSION['customer_default_address_id'] = $address_book->fields['address_book_id'];
        $_SESSION['customer_country_id'] = $address_book->fields['entry_country_id'];
        $_SESSION['customer_zone_id'] = $address_book->fields['entry_zone_id'];
        $_SESSION['sendto'] = $address_id;

        if($this->debug){
          writelog("Old Address ID: " . $_SESSION['customer_default_address_id']);
        }
      }

      /* Order Creation in Zencart System */

      $order = new zencartorder();

      /* Order Total calculation */
      $order->products = array();
      $promo_code = array();
      $promotion_order_totals = array();
      $all_promotion_code = "";
			

      $total = 0.0;
      $subtotal = 0.0;
      $shipping_cost = 0.0;
      $total_tax = 0.0;
      $promotion = 0.0;
      $promotion_code = '';
      $shipping_promotion = 0.0;

      foreach ($data->getOrderItems() as $sku => $item) {

        $order->products[] = array (
                                    'qty' => $item['Quantity'],
                                    'name' => $item['Title'],
                                    'tax' => $item['ItemPrice']['Tax'],
                                    'tax_description' => '',
                                    'price' => $item['ItemPrice']['Principal'] / $item['Quantity'],
                                    'final_price' => $item['ItemPrice']['Principal'] / $item['Quantity'],
                                    'onetime_charges' => 0,
                                    'weight' => $item['Weight'],
                                    'products_priced_by_attribute' => 0,
                                    'product_is_free' => 0,
                                    'products_discount_type' => 0,
                                    'products_discount_type_from' => 0,
                                    'id' => $sku
                                    );				

        $shipping_label = $item['CBAShipLabel'];


        $total += $item['ItemPrice']['Principal'] + $item['ItemPrice']['Shipping'] + $item['ItemPrice']['Tax'] + $item['ItemPrice']['ShippingTax'];
        $subtotal +=$item['ItemPrice']['Principal'];
        $shipping_cost += $item['ItemPrice']['Shipping'];
        $total_tax += $item['ItemPrice']['Tax'] + $item['ItemPrice']['ShippingTax'];


        /* calculate promotions for MFA XML*/
        if(array_key_exists("Promotion",$item)){	
          $total_promotion = 0.0;
          $total_shipping_promotion = 0.0;
          foreach($item['Promotion'] as $promotion_code => $promotion_value){
            $total_promotion += $promotion_value['Principal'];
            $total_shipping_promotion += $promotion_value['Shipping'];
            $promotion = $promotion_value['Principal'];
            $shipping_promotion = $promotion_value['Shipping'];
            if(!array_key_exists($promotion_code,$promo_code)){
              $promo_code[$promotion_code] = $promotion + $shipping_promotion;
            }else{
              $promo_code[$promotion_code] = $promo_code[$promotion_code] + $promotion + $shipping_promotion;
            }							
          }					

          /* add the promotion as promotion value is always in -ve */
          $total += $total_promotion + $total_shipping_promotion;
        }

      }

	  /* overwrite the shipping label for IOPN*/
	  if(!isset($shipping_label)){
		$shipping_label = $data->getOrderFulfillmentShippingLabel();
	  }

      /* prepare promotions array for order totals merge */
      if(count($promo_code) > 0){

        /* for currency - display format - only needed here */
        $currency = new currencies();

        foreach($promo_code as $promotion_code => $promotion){
          $all_promotion_code .= $promotion_code . "," ;
          $promotion_array = array(
                                   'code' => 'ot_amazon_promotion',
//                                   'title'=>'<b> Promotion: ' . $promotion_code . ' : </b>',    ------> IOPN doesnt have promotion code. MFA has promotion code. hence disabling promotion code
                                   'title'=>'<b> Promotion: </b>',
                                   'text' => "(" . $currency->format(substr($promotion, 1)) . ")",  // promotion value is always -ve. remove minus symbol
                                   'value' => $promotion,
                                   'sort_order' => 500
                                   );
          array_push($promotion_order_totals,$promotion_array);
        }

        if($this->debug){
          ob_writelog("Promotion: ", $promotion_order_totals);
        }
      }



      if(isset($_SESSION['cart'])){
        $order->cart();
        ob_writelog("Session[cart] ", $_SESSION['cart']);
      }

      list ($order->customer['firstname'], $order->customer['lastname']) = explode(' ', amazon_db_input($data->getBuyerName()), 2);
      $order->customer['company'] = '';
      $order->customer['street_address'] = '';
      $order->customer['suburb'] = '';
      $order->customer['city'] = '';
      $order->customer['postcode'] = '';
      $order->customer['state'] = '';
      $order->customer['country']['title'] = '';
      $order->customer['telephone'] = amazon_db_input($data->getBuyerPhone());
      $order->customer['email_address'] = amazon_db_input($data->getBuyerEmail());
      $order->customer['format_id'] = 2;

      list ($order->delivery['firstname'], $order->delivery['lastname']) = explode(' ', $data->getFulfillmentAddressName(), 2);
      $order->delivery['company'] = '';
      $order->delivery['street_address'] =  amazon_db_input($data->getFulfillmentAddressFieldOne());
      $order->delivery['suburb'] = amazon_db_input($data->getFulfillmentAddressFieldTwo());
      $order->delivery['city'] =  amazon_db_input($data->getFulfillmentAddressCity());
      $order->delivery['postcode'] = amazon_db_input($data->getFulfillmentAddressPostalCode());
      $order->delivery['state'] = amazon_db_input($data->getFulfillmentAddressStateOrRegion());
      $order->delivery['country']['title'] =  amazon_db_input($data->getFulfillmentAddressCountryCode());
      $order->delivery['format_id'] = 2;

      list ($order->billing['firstname'], $order->billing['lastname']) = explode(' ', amazon_db_input($data->getBuyerName()), 2);
      $order->billing['company'] = '';
      $order->billing['street_address'] = '';
      $order->billing['suburb'] = '';
      $order->billing['city'] = '';
      $order->billing['postcode'] = '';
      $order->billing['state'] = '';
      $order->billing['country']['title'] = '';
      $order->billing['format_id'] = 2;

      /* Update values so that order_total modules get the correct values */			
      $order->info['payment_method'] = $checkout_by_amazon->title;
      $order->info['payment_module_code'] = $checkout_by_amazon->code;
      $order->info['cc_type'] = '';
      $order->info['cc_owner'] = '';
      $order->info['cc_number'] = '';
      $order->info['cc_expires'] = '';
      $order->info['order_status'] = $zencart_order_status_amazon_status_mapping[$data->getNewOrderOperation()];
      $order->info['tax'] = $total_tax;
      $order->info['currency'] = $checkout_by_amazon->currency;
      $order->info['currency_value'] = 1;
      $order->info['comments'] = "Amazon Order ID: " . $amazon_order_id;
      $order->info['total'] = $total;
      $order->info['subtotal'] = $subtotal;
      $order->info['coupon_code'] = $all_promotion_code;
      $order->info['shipping_method'] = "Shipping - " . $shipping_label; // as of now we overwrite this if there are more items
	  $order->info['date_purchased'] = $data->getOrderDate();
	  $order->info['ip_address'] = zen_db_input($_SESSION['customers_ip_address']);
      $order->info['shipping_cost'] = $shipping_cost;
      $order->info['tax_groups']['tax'] = $total_tax;

      if($this->debug){
        writelog("Total: " . $total . "; Sub-total: " . $subtotal . "; Promotion Code: " . $promotion_code . "; Shipping Cost" . $shipping_cost . "; Tax: " . $total_tax);
      }

      /* Order Total Creation */
      if(!class_exists('order_total')){
        require (DIR_WS_CLASSES . 'order_total.php');
      }

      $order_total_modules = new order_total();
      $order_totals = $order_total_modules->process();

      /* if there is a promotion code applied, then generate the promotion array to insert into order modules */
      if($all_promotion_code){
        $order_totals = array_merge($order_totals,$promotion_order_totals);
      }

      if($this->debug){
        ob_writelog("Order Total Modules calculations: ",$order_totals);
      }

      $amazon_order_history_check_sql = "SELECT orders_id FROM " . TABLE_AMAZON_ORDER_HISTORY . " where amazon_order_id= '$amazon_order_id'";
      $amazon_order_history_check = amazon_db_execute($amazon_order_history_check_sql);

      if(amazon_recordcount($amazon_order_history_check) == 0){

        /* order creation */
        $insert_id = $order->create($order_totals,2);

        /* store the product info to the order */
        $order->create_add_products($insert_id);

        if($this->debug){
          writelog("Order Id: ".$insert_id);
        }

        $sql_amazon_payment_update = "UPDATE ".TABLE_AMAZON_PAYMENTS." set orders_id = '$insert_id', modified_on = 'now()' where amazon_order_id = '$amazon_order_id'";
        amazon_db_execute($sql_amazon_payment_update);

        /* insert into amazon order status history with pending transaction */
        $sql_data_array = array (
                                 'amazon_order_id' => $amazon_order_id,
                                 'orders_id' => $insert_id,
                                 'reference_id' => $orders->fields['reference_id'],
                                 'created_on' => $data->getOrderDate(),	 // importing the order create time
                                 'modified_on' => $data->getNewOrderModifiedTime(),
                                 'status'=>$data->getNewOrderOperationStatus(), // default status for that txn
                                 'operation' => $data->getNewOrderOperation(), // callback status which is set
                                 'comments' => $comments
                                 );
			
        zen_db_perform(TABLE_AMAZON_ORDER_HISTORY, $sql_data_array);

		/* update the purchased date from order report */
        $sql_order_date_update = "UPDATE ".TABLE_ORDERS." set date_purchased = '".$data->getOrderDate()."' where orders_id = '$insert_id'";
        amazon_db_execute($sql_order_date_update);

      }

      if(isset($_SESSION['cart'])){
        $_SESSION['cart']->reset(true);
      }

	  /* unregistering all session we set for calculating */
	//  session_destroy();

      $orders->MoveNext();
    }

  }

  /*
   *			Monitor and update status of waiting transactions
   */ 

  function monitorOrder(){
    global $success_message, $failed_message, $zencart_order_status_amazon_status_mapping;

    /* checkout by amazon payment module */
    if(!class_exists('checkout_by_amazon')){				
      require (DIR_WS_CLASSES . 'payment.php');
      $payments = new payment('checkout_by_amazon');
    }

    /* Currency Creation */
    if(!class_exists('currencies')){
      require (DIR_WS_CLASSES . 'currencies.php');
    }

    $currency = new currencies();

    /* Status  0 =  default, 1 = Success, 2 = Error 3 = Timeout*/
    $order_docs_sql = "SELECT id,ao.orders_id,reference_id,operation,comments,o.order_total FROM ".TABLE_AMAZON_ORDER_HISTORY." ao LEFT JOIN ".TABLE_ORDERS." o on ao.orders_id=o.orders_id WHERE status='0' AND operation > ".AMAZON_ORDER_STATUS_PROCESSING;

    /* Use this query if you want to debug something - uncomment and use it. */
    /* $order_docs_sql = "SELECT id,ao.orders_id,reference_id,operation,comments,o.order_total FROM ".TABLE_AMAZON_ORDER_HISTORY." ao LEFT JOIN ".TABLE_ORDERS." o on ao.orders_id=o.orders_id WHERE reference_id='2743985778'"; */ 

    $order_docs = amazon_db_execute($order_docs_sql);
    $reverse_order_total_sql = "";

	while (!$order_docs->EOF) {
			
      $id = $order_docs->fields['id'];
      $orders_id = $order_docs->fields['orders_id'];
      $order_total = $order_docs->fields['order_total']; // used for refund
      $operation = $order_docs->fields['operation'];
      $documentTransactionID = $order_docs->fields['reference_id'];
      $saved_comments = $order_docs->fields['comments'];
      $status = $this->client->getTransactionStatus($documentTransactionID);
      $report_xml = $status['xml'];

      if($this->debug){
        $log="Doc Txn Id: " . $documentTransactionID . "; Orders ID: " . $orders_id . "; Row Id: " . $id;
        ob_writelog($log . "; \n Txn Status: ",$status);
      }

      if($status['MessagesSuccessful'] > 0 && $status['MessagesWithError'] <= 0 ){							 
        $amazon_order_status_sql = "UPDATE ".TABLE_AMAZON_ORDER_HISTORY." SET status='1', modified_on=now(), xml='".amazon_db_input($report_xml)."'   WHERE id='$id'";
        $amazon_order_status = amazon_db_execute($amazon_order_status_sql);	
        $comments = "Amazon Order Update Successful! \n\n" . $saved_comments;
        $reverse_order_total_sql = "";

        /* update the order total with refund entry :) this is a good user experience */
        if($operation == AMAZON_ORDER_STATUS_REFUND){
          $refund_array = array(
                                'orders_id' => $orders_id,
                                'class' => 'ot_amazon_refund',
                                'title'=>'<b> <font color="red">Refund:</font></b>',
                                'text' => "<font color='red'>(" . $currency->format($order_total) . ")</font>",
                                'value' => $order_total ,
                                'sort_order' => 501
                                );

          amazon_db_perform(TABLE_ORDERS_TOTAL, $refund_array);

          $reverse_order_total_sql = ",order_total='0.00', order_tax='0.00'";

          /* update ot_total */
          $order_total_revert_sql = "UPDATE ".TABLE_ORDERS_TOTAL." SET text='".$currency->format(0.00)."',value='0.00'	WHERE orders_id='$orders_id' and class='ot_total'";

          amazon_db_execute($order_total_revert_sql);

        }

		$order_status_id = $zencart_order_status_amazon_status_mapping[$operation];

        /* insert into order status history */
        $sql_data_array = array (
                                 'orders_id' => $orders_id,
                                 'orders_status_id' => $order_status_id,
                                 'date_added' => 'now()',
                                 'customer_notified' => '0',
                                 'comments' => $comments
                                 );
			
        amazon_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        /* update order status */
        $order_status_sql = "UPDATE ".TABLE_ORDERS." SET orders_status='$order_status_id' $reverse_order_total_sql WHERE orders_id='$orders_id'";

        amazon_db_execute($order_status_sql);

      }elseif($status['MessagesWithError'] > 0){		
        $amazon_order_status_sql = "UPDATE ".TABLE_AMAZON_ORDER_HISTORY." SET status='2',modified_on=now(), xml='".amazon_db_input($report_xml)."'  WHERE id='$id'";
        $amazon_order_status = amazon_db_execute($amazon_order_status_sql);
        $comments = "Amazon Order Update Failed!!! \n\n" . $saved_comments;
      }else{
        $amazon_order_status_sql = "UPDATE ".TABLE_AMAZON_ORDER_HISTORY." SET status='3',modified_on=now(), xml='".amazon_db_input($report_xml)."'  WHERE id='$id'";
        $amazon_order_status = amazon_db_execute($amazon_order_status_sql);
        $comments = "Amazon Order Update Timeout!!! \n\n You can retry!";
      }

      $order_docs->MoveNext();
    }

  }
}
?>