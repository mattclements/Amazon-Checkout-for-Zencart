<?php
  /**
   * @brief IOPN class to parse IOPN XML from Amazon
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
require_once(DIR_FS_CBA . 'library/HMAC.php');
require_once(DIR_FS_CBA . 'library/iopn_processor.php');

class AmazonIOPN {

  /*
   *    Debug flag
   */

  var $debug = false;

  /*
   *    Default currency code 
   */

  var $CurrencyCode = 'USD';

  /*
   *    Pre-defined region for Shipping Rates calculation
   */

  var $PreDefinedRegion = 'WorldAll';

  var $iopn;

  /*
   *    constructor
   */
	
  function AmazonIOPN(){
		$this->iopn = new IOPNProcessor();
  }

  /**
   *	Get the Orders from the IOPN
   *
   *   @return $document_array
   */
	
  function getOrders(){
	 
	$the_document = $this->iopn->getData();

    $document_array = array();

	preg_match("/\<NotificationReferenceId\>(.*?)\<\/NotificationReferenceId\>/msU",$the_document,$NotificationReferenceId);
	$documentID = $NotificationReferenceId[1];

    preg_match_all("/\<ProcessedOrder\>(.*?)\<\/ProcessedOrder\>/msU",$the_document,$matches,PREG_PATTERN_ORDER);

	// matches[0] contains orders with OrderReport as a root element
	foreach($matches[0] as $order){
		preg_match('/\<AmazonOrderID\>(.*?)\<\/AmazonOrderID\>/',$order,$orderid);
		$amazon_order_id = trim($orderid[1]);
		$document_array[$amazon_order_id] = array();
		$document_array[$amazon_order_id]['xml'] = $order;
		$document_array[$amazon_order_id]['reference_id'] = $documentID; // this will contain document ID from MFA or notification reference ID
		$document_array[$amazon_order_id]['type'] = 'IOPN'; 
		$document_array[$amazon_order_id]['iopn'] = $this->iopn->getDataType(); 
	}
    
    return $document_array;
  }

}