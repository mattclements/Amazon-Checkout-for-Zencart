<?php
/////////////////////////////////////////////////////////////////////////////////////
//
// AMAZON CODE -> START
// Produces the javascript popup from amazon with order summary
//
/////////////////////////////////////////////////////////////////////////////////////
require_once("checkout_by_amazon/checkout_by_amazon_constants.php");

#load only when there is a amazon order
if(defined("MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS") && MODULE_PAYMENT_CHECKOUTBYAMAZON_STATUS == "True"){
?>
<!--BEGIN CHECKOUT BY AMAZON SCRIPTS, 1click & express checkout  -->
    <script src=<?php echo(CBA_JQUERY_SETUP); ?> type="text/javascript"></script>

    <script src=<?php if(MODULE_PAYMENT_CHECKOUTBYAMAZON_OPERATING_ENVIRONMENT == 'Production'){echo(PROD_1_CLICK);} else {echo(SANDBOX_1_CLICK);}   ?> type="text/javascript"></script>

   <link href=<?php echo(CBA_STYLE_SHEET); ?> media="screen" rel="stylesheet" type="text/css"/>

   <!--END CHECKOUT BY AMAZON SCRIPTS, 1click & express checkout  -->
<?php
}
/////////////////////////////////////////////////////////////////////////////////////
//
// AMAZON CODE -> END
//
/////////////////////////////////////////////////////////////////////////////////////
?>