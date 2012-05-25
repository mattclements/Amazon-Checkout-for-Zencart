<?php
  /**
   * @brief Amazon order functionality code for Zencart Order Admin
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
?>
<td>
<?php
global $shipping_carrier, $refund_reason, $order,$amazon_status_mapping,$zencart_order_status_amazon_status_mapping,$amazon_statuses;

/* Amazon Payments Code Starts Here */

$amazon_statuses = array();


$amazon_statuses[AMAZON_ORDER_STATUS_PROCESSING][0] = "The order placed by your customer is *under* review. Please *wait* for the review to complete.";
$amazon_statuses[AMAZON_ORDER_STATUS_PROCESSING][1] = "This order has been reviewed by Amazon. Please prepare to ship the order";

/* Unshipped 0 Initated 1 Success 2 Error 3 Timeout*/
$amazon_statuses[AMAZON_ORDER_STATUS_UNSHIPPED][1] = "This order has been reviewed by Amazon and is ready to ship. Please \"Confirm Shipment\" to charge the buyer's payment method and ship the order. You can also choose to \"Cancel Order\" also";

/* Shipment 0 Initated 1 Success 2 Error 3 Timeout*/
$amazon_statuses[AMAZON_ORDER_STATUS_SHIPMENT][0] = "Your \"Confirm Shipment\" request has been sent to Amazon. Please wait for the request to complete.";
$amazon_statuses[AMAZON_ORDER_STATUS_SHIPMENT][1] = "Your \"Confirm Shipment\" request succeeded and the buyer's payment method has been charged. ";
$amazon_statuses[AMAZON_ORDER_STATUS_SHIPMENT][2] = "We were unable to charge the customer's payment method.";

/* Cancel 0 Initated 1 Success 2 Error 3 Timeout*/
$amazon_statuses[AMAZON_ORDER_STATUS_CANCEL][0] = "Your \"Cancel Order\" request has been sent to Amazon. Please wait for the request to complete.";
$amazon_statuses[AMAZON_ORDER_STATUS_CANCEL][1] = "The order has been Canceled. Note that the cancel request might have been initiated by you or the buyer or by amazon.";
$amazon_statuses[AMAZON_ORDER_STATUS_CANCEL][2] = "We were unable to process your \"Cancel Order\" request. ";

/* Refund 0 Initated 1 Success 2 Error 3 Timeout*/
$amazon_statuses[AMAZON_ORDER_STATUS_REFUND][0] = "Your \"Refund\" request has been sent to Amazon. Please wait for the request to complete.";
$amazon_statuses[AMAZON_ORDER_STATUS_REFUND][1] = "Your \"Refund\" request has been processed and the money has been refunded to the customer's payment method.";
$amazon_statuses[AMAZON_ORDER_STATUS_REFUND][2] = "We were unable to process your \"Refund\" request";

$amazon_statuses[AMAZON_ORDER_STATUS_ERROR][1] = "This order cannot be managed from here. Please visit this (link) to update the status here. This could have happened if you updated the status of this order previously through Seller Central.";

/* retrieves amazon order id from amazon payment status table */
$amazon_order_id = 	amazon_order_id($zf_order_id);

?>
<!-- Amazon Code Starts Here -->
<script language="javascript">
  <!--
  function confirm_cancel(value){

  if(confirm("<?php echo ENTRY_AMAZON_CANCEL_CONFIRMATION_TEXT;?>")){
    setStatus(value);
    return true;
  }
	
  return false;
}
function refundreason(value){

  var refund_reason = document.amazon_payments.refund_reason;

  if(refund_reason.selectedIndex == 0){		
    alert("<?php echo ENTRY_AMAZON_SELECT_THE_REASON_FOR_REFUND; ?>");
    refund_reason.focus();
    return false;
  }
  setStatus(value);
}

function confirm_shipment(value){

  if(value == ''){
    return false;
  }

  var shipping_carrier = document.amazon_payments.shipping_carrier;

  if(shipping_carrier.selectedIndex == 0){		
    alert("<?php echo ENTRY_AMAZON_SHIPPING_CARRIER_TEXT; ?>");
    shipping_carrier.focus();
    return false;
  }

  var shipping_service = document.amazon_payments.shipping_service;

  if(shipping_service.value == ""){		
    alert("<?php echo ENTRY_AMAZON_SHIPPING_SERVICE_TEXT; ?>");
    shipping_service.focus();
    return false;
  }

  var tracking_id = document.amazon_payments.tracking_id;

  if(tracking_id.value == ""){		
    alert("<?php echo ENTRY_AMAZON_SHIPPING_TRACKING_TEXT; ?>");
    tracking_id.focus();
    return false;
  }

  setStatus(value);
}

function setStatus(value){
  var status = document.amazon_payments.status_code;
  status.value = value;
  document.amazon_payments.submit();
}

function ajaxFunction(){
  var xmlhttp;
  if (window.XMLHttpRequest){
    // code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  }else{
    // code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

  var url = '<?php echo HTTP_SERVER . DIR_WS_CATALOG . "checkout_by_amazon.php?action=monitorOrders"?>';
  xmlhttp.open("GET",url,true);
  xmlhttp.send(null);
  setInterval(ajaxFunction,1000 * 60 * 2);
}

//ajaxFunction();
// -->
</script>
<? echo zen_draw_form('amazon_payments', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doVoid', 'post', '', true) . zen_hide_session_id(); ?>
<table border="0" cellpadding="4" width="100%">
  <tr>
  <td class="main"><strong><?php echo ENTRY_AMAZON_ORDERS_ID; ?></strong> <?php echo $amazon_order_id; ?> ( <a href="https://sellercentral.amazon.com/gp/orders-v2/details?ie=UTF8&orderID=<?php echo $amazon_order_id; ?>" target="_blank">View in Seller Central</a> )</td>
  </tr>
  <tr>
  <td class="main">
  <b>Order Status History </b><a href="#" onclick="javascript:window.open('<?php echo DIR_WS_CBA;?>amazon_payments_what_this.html',
'mywindow','menubar=0,resizable=1,width=650,height=425');return false;"><img src="images/icon_info.gif" align="absmiddle" alt="What's this?" border="0"/></a><br/><br/>
  <?php

  $transaction_status_msg = array("Initiated", "<font color='green'><b>Success</b></font>", "<font color='red'><b>Error</b></font>","<font color='orange'><b>Timeout</b></font>");
$amazon_order_status_sql = "SELECT status as transaction_status, operation, xml, comments, created_on, modified_on FROM ". TABLE_AMAZON_ORDER_HISTORY ." WHERE orders_id='$zf_order_id' ORDER BY operation";
$amazon_order_status = 	amazon_db_execute($amazon_order_status_sql);

if(amazon_recordcount($amazon_order_status) > 0){

  $txn_table = '<table border="0" cellpadding="5" cellspacing="1" width="100%" bgcolor="#cccccc">';
  $txn_table .= '<tr bgcolor="#efefef"><th>Amazon Order Status</th><th>Zencart Order Status</th><th>Initiated On</th><th>Completed On</th><th>Comments</th></tr>';
  while(!$amazon_order_status->EOF){
					
    $transaction_status = $amazon_order_status->fields['transaction_status'];
    $operation = $amazon_order_status->fields['operation'];
    $created_on = $amazon_order_status->fields['created_on'];
    $modified_on = $amazon_order_status->fields['modified_on'];
    $amazon_status = $amazon_statuses[$operation];
    $comments = $amazon_statuses[$operation][$transaction_status];

    if($transaction_status == 1){
      $comments  = $comments  . ' <br/><br/>'. $amazon_order_status->fields['comments'];
    } 

    if($transaction_status == 2){
      $xml = $amazon_order_status->fields['xml'];
      preg_match('/.*\<ResultMessageCode>(.*?)\<\/ResultMessageCode>.*/',$xml,$matches);
      $error_code = $matches[1];
      $comments  = $comments  . ' <br/><br/>Error code: '.$error_code.' - <a href="https://sellercentral.amazon.com/gp/search/search-results.html/ref=cb_helpsearch_bnav_help?keywords='.$error_code.'" target="_blank">Details</a>';
    }

	$amazon_status_name = $amazon_status_mapping[$operation];
    $txn_table .= "<tr align='center' bgcolor='#FFFFFF'><td><b>$amazon_status_name</b>  ($transaction_status_msg[$transaction_status])</td><td>".zen_get_order_status_name($zencart_order_status_amazon_status_mapping[$operation])."</td><td>$created_on</td><td>$modified_on</td><td align='left' width='50%'>$comments</td></tr>";

    $amazon_order_status->MoveNext();
  }
  $txn_table .='<tr bgcolor="#FFFFFF"><td colspan="5">Note: The last row in this table reflects the latest update on this order.</td></tr>';
  $txn_table .= '</table>';
  echo $txn_table;
}

?>
			
</td>
</tr>
<?php

if(MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS == 'True'){

  /* enable this only if payment module is installed and configured properly */
  if(MODULE_PAYMENT_CHECKOUTBYAMAZON_ORDER_MANAGEMENT == 'True'){
	
	/* check if there is any merchant initiated action on the order. action can be shipment / cancel / refund */
	if(!any_merchant_action($zf_order_id)){

    ?>
    <tr>
      <td>
      <?php	

	/* 
	 *		Display the confirm shipment button only if order is not cancelled and order is not shipped. 
	 *		i.e only for new orders
	 */

	if(!is_order_canceled($zf_order_id) && !is_order_shipped($zf_order_id)){
        echo ENTRY_AMAZON_SHIPPING_CARRIER . zen_draw_pull_down_menu('shipping_carrier', $shipping_carrier, 'length="32"') . ENTRY_AMAZON_SHIPPING_SERVICE . zen_draw_input_field('shipping_service', '', 'length="32"') . ENTRY_AMAZON_SHIPPING_TRACKING_NUMBER . zen_draw_input_field('tracking_id', '', 'length="32"');
        ?> 
          <img src="<?php echo DIR_WS_CBA;?>images/confirm_shipment.jpg" align="absmiddle" style="cursor:pointer; cursor:hand;" onclick="javascript:confirm_shipment('<? echo AMAZON_ORDER_STATUS_SHIPMENT; ?>');return false;"/>
             <?php
             }

	/* 
	 *		Display the cancel button  if order is not cancelled and order is not shipped. 
	 *		i.e only for new orders. Shipped orders cannot be cancelled.
	 */

	if(!is_order_canceled($zf_order_id) && !is_order_shipped($zf_order_id)){
      ?>
      <b>or</b> <img src="<?php echo DIR_WS_CBA;?>images/cancel_order.jpg" align="absmiddle" style="cursor:pointer; cursor:hand;" onclick="javascript:confirm_cancel('<? echo AMAZON_ORDER_STATUS_CANCEL; ?>');return false;" />
        <?php
        }

	/* 
	 *		Display the refund button if order is shipped and order is not refunded already.
	 *		i.e only for shipped orders.
	 */

	if(is_order_shipped($zf_order_id) && !is_order_refunded($zf_order_id)){
      echo zen_draw_pull_down_menu('refund_reason', $refund_reason, 'length="32"');
      ?>
        <img src="<?php echo DIR_WS_CBA;?>images/refund_order.jpg" align="absmiddle" style="cursor:pointer; cursor:hand;" onclick="javascript:refundreason('<? echo AMAZON_ORDER_STATUS_REFUND; ?>');return false;" />

           <?php
           }
					
    echo zen_draw_hidden_field('amazon_order_id',$amazon_order_id);
    echo zen_draw_hidden_field('status_code','0');
    ?>
      </td>
          </tr>
          <?php
	}
          }else{
    echo ENABLE_AMAZON_ORDER_MANAGEMENT;
  }
}else{
  echo ENABLE_AMAZON_PAYMENTS_MODULE;
}
?>
</table>
</form>
<!-- Amazon Code Ends Here -->
</td>
