<?php
/**
 * @brief MerchantData Class for populating product info into item custom data
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

  class MerchantData {

	/* 
	 * @brief constrcutor
	 */
    function MerchantData() {

	}

	/* 
	 * @brief custom data is called in button generator 
	 * @return associative array 
	 */
    function custom_data() {		
	
	  $test_array = array("customer_ip_address" => $_SERVER['REMOTE_ADDR'], "cart" => serialize($_SESSION['cart']));

	  return $test_array;
    }

  }

?>