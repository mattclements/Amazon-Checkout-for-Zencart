<?php
/**
 * @brief Generates the button to be displayed on the shopping cart page
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

require_once('checkout_by_amazon_constants.php');
require_once('button_generator.php');
$button = new CheckoutByAmazonButton();
?>
<div align="right">
	<div style="text-align: center; width: 160px;"><b><? echo MODULE_PAYMENT_CHECKOUTBYAMAZON_USE_CBA_TEXT; ?></b></div>
</div>
<div align="right">
	<?php echo $button->CheckoutButtonHtml(); ?>
</div>