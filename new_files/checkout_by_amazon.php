<?php
  /**
   * @brief Single Endpoint for all request to zencart by amazon
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

require('includes/application_top.php');
require('checkout_by_amazon/checkout_by_amazon_constants.php');
require(DIR_FS_CBA . 'amazonorder.php');

$action = trim($_GET["action"]);

/* Log any request GET or POST */
requestlog();

/* default action */
$url = zen_href_link(FILENAME_DEFAULT,'','SSL');
$redirect = true;

/* for all POST actions */
if($_POST){

  /* Callback Request*/
  if($_POST['order-calculations-request']){

    require_once(DIR_FS_CBA . 'amazoncallback.php');

    $acb = new AmazonCallback();

    echo $acb->CallbackResponse();

    exit;	
  }

  /* IOPN Post */
  if($_POST['NotificationType']){
	
    // This is used for later release where we integrate with IOPN	

    require_once(DIR_FS_CBA . 'amazoniopn.php');

    /* cache orders since we got the amazon order id */
    $ao = new AmazonOrder();
    
	$aiopn = new AmazonIOPN();

	$orders = $aiopn->getOrders();
	
	if($_POST['NotificationType'] == "NewOrderNotification"){
		/* cache orders into amazon table */
		$ao->NewIOPNOrders($orders);
	}else{
		/* update amazon order based on notification type */
		$ao->updateAmazonOrder($orders);
	}

    exit;
  }
}

if($_GET){
	if(isset($action)){

	  /* ResetCart action */
	  if($action == "ResetCart"){

		/* cache orders since we got the amazon order id */
		$ao = new AmazonOrder();

		/* temporarily create order in amazon table */
		$ao->tempOrder();

		/* reset the cart using the session */
		$_SESSION['cart']->reset(true);

		/* send the query string to display the widget on home page*/
		$query_string = str_replace("action=ResetCart","",$_SERVER["QUERY_STRING"]);

		/* Return URL redirect */
		if(defined("MODULE_PAYMENT_CHECKOUTBYAMAZON_RETURN_URL") && MODULE_PAYMENT_CHECKOUTBYAMAZON_RETURN_URL !=""){
		  $url = MODULE_PAYMENT_CHECKOUTBYAMAZON_RETURN_URL;
		}else{
		  $url =  zen_href_link(FILENAME_DEFAULT,'','SSL') . $query_string;
		}
	  }

	  /* CancelCart action */
	  if($action == "CancelCart"){

		/* Cancel URL redirect */
		if(defined("MODULE_PAYMENT_CHECKOUTBYAMAZON_CANCEL_URL") && MODULE_PAYMENT_CHECKOUTBYAMAZON_CANCEL_URL !=""){
		  $url = MODULE_PAYMENT_CHECKOUTBYAMAZON_CANCEL_URL;
		}else{
		  $url = zen_href_link(FILENAME_SHOPPING_CART,'','SSL');
		}
	  }

	  /* GetOrders action */
	  if($action == "GetOrders"){

		$ao = new AmazonOrder();
			
		/* As this operation might require lot of time */
		set_time_limit(0);		

		/* cache orders into amazon table */
		$ao->cacheOrders();

		/* create orders */ 
		$ao->createOrders();

		/* acknowledge orders */
		$ao->acknowledgeOrders();

		/* no need for redirection */
		$redirect = false;
	  }

	  /* MonitorOrders action */
	  if($action == "monitorOrders"){

		/* As this operation might require lot of time */
		set_time_limit(0);

		$ao = new AmazonOrder();

		/* monitor orders */
		$ao->monitorOrder();

		/* no need for redirection */
		$redirect = false;
	  }

	  if($action == "Everything"){

		/* As this operation might require lot of time */
		set_time_limit(0);

		$ao = new AmazonOrder();

		/* cache orders from amazon table */
		$ao->cacheOrders();
			
		/* create orders */ 
		$ao->createOrders();		

		/* monitor orders */
		$ao->monitorOrder();

		/* acknowledge orders */
		$ao->acknowledgeOrders();

		/* no need for redirection */
		$redirect = false;
	  }

	  if($redirect){
		/* any action */
		zen_redirect($url);
	  }
	}
}
?>