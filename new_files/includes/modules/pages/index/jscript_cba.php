<?php
/////////////////////////////////////////////////////////////////////////////////////
//
// AMAZON CODE -> START
// Produces the javascript popup from amazon with order summary
//
/////////////////////////////////////////////////////////////////////////////////////
require_once("checkout_by_amazon/checkout_by_amazon_constants.php");

#load only when there is a amazon order
if(isset($_GET["amznPmtsOrderIds"])){
?>
	<script src=<?php echo(CBA_JQUERY_SETUP); ?> type="text/javascript"></script>
	<link href= <?php echo(CBA_STYLE_SHEET); ?> media="screen" rel="stylesheet" type="text/css"/>
	<link type="text/css" rel="stylesheet" media="screen" href=<?php echo(CBA_POPUP_STYLE_SHEET); ?>/>
	<script src=<?php if(MODULE_PAYMENT_CHECKOUTBYAMAZON_OPERATING_ENVIRONMENT == 'Production'){echo(PROD_POPUP_ORDER_SUMMARY);} else {echo(SANDBOX_POPUP_ORDER_SUMMARY);}  ?> type="text/javascript"></script>
<?php
}
/////////////////////////////////////////////////////////////////////////////////////
//
// AMAZON CODE -> END
//
/////////////////////////////////////////////////////////////////////////////////////
?>