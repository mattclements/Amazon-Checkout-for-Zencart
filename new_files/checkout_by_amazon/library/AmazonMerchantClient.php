<?php
  /**
   * @brief Class which handles post order management
   * @catagory Checkout by Amazon SDK with PHP CURL for PHP 4 & PHP 5
   * @author Balachandar Muruganantham
   * @copyright Portions copyright 2007-2010 Amazon Technologies, Inc
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

require_once("SOAPXMLParser.php");
require_once("xml.php");

/* document processing complete status */
define('DOCUMENT_PROCESSING_STATUS_COMPLETE', '_DONE_');

/* times out after ten minutes */
define('DOCUMENT_PROCESSING_STATUS_POLL_TIMEOUT', 600);

/* poll every minute to see if the document has completed */
define('DOCUMENT_PROCESSING_STATUS_POLL_INTERVAL', 60);

/**
 * This AmazonMerchantClient API offers the following APIs:
 *
 * getOrders
 * shipOrder
 * refundOrder
 * cancelOrder
 *
 *
 * Copyright 2008-2010 Amazon.com, Inc., or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the “License”).
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *    http://aws.amazon.com/apache2.0/
 *
 * or in the “license” file accompanying this file.
 * This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific language governing permissions and limitations under the License.
 */

class AmazonMerchantClient {

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
   *    Default Currency Code
   */

  var $currency = "USD";

  /*
   *    Get Orders Message Type
   */

  var $_msgtype_get_orders = "_GET_ORDERS_DATA_";
	
  /*
   *    cancel Order Message type
   */

  var $_msgtype_order_ack = "_POST_ORDER_ACKNOWLEDGEMENT_DATA_";

  /*
   *    Ship Orders Message Type
   */

  var $_msgtype_order_fulfillment = "_POST_ORDER_FULFILLMENT_DATA_";

  /*
   *    Refund Orders Message Type
   */

  var $_msgtype_payment_adjustment = "_POST_PAYMENT_ADJUSTMENT_DATA_"; 
	
  /**
   *	Constructor
   *
   *	@params $login
   *	@params $password
   *	@params $merchanttoken
   *	@params $merchantname
   *
   */
  function AmazonMerchantClient($login, $password, $merchanttoken, $merchantname){
	  
    $this->login = $login;
    $this->password = $password;
    $this->merchant_token = $merchanttoken;
    $this->merchant_name = $merchantname;

  }

  /*
   * set the currency code
   */
  function setCurrency($str){
    if($str !=""){
      $this->currency = $str;
    }
  }

  /*
   *  you can set the _GET_FLAT_FILE_ORDERS_DATA_ or leave blank
   */
  function setGetOrders($str){
    if($str !=""){
      $this->_msgtype_get_orders = $str;
    }
  }

  /**
   * Get all documents that have yet to have been acknowledged by the merchant.
   *
   * @returns $pending_document_ids Pending document associative array with document id and generated date time
   */

  function getAllPendingDocumentInfo() {
    $this->error = null;

    $header = array ("SOAPAction: http://www.amazon.com/merchants/merchant-interface/MerchantInterface#getAllPendingDocumentInfo#KEx3YXNwY1NlcnZlci9BbXpJU0EvTWVyY2hhbnQ7TGphdmEvbGFuZy9TdHJpbmc7KVtMd2FzcGNTZXJ2ZXIvQW16SVNBL01lcmNoYW50RG9jdW1lbnRJbmZvOw==");

    $xml = '<?xml version="1.0" encoding="UTF-8"?>
					<SOAP-ENV:Envelope 
					xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
					xmlns:ns1="http://www.amazon.com/merchants/merchant-interface/" 
					xmlns:ns2="http://systinet.com/xsd/SchemaTypes/">
						<SOAP-ENV:Body>
							<ns2:merchant>
								<ns1:merchantIdentifier>'.$this->merchant_token.'</ns1:merchantIdentifier>
								<ns1:merchantName>'.$this->merchant_name.'</ns1:merchantName>
							</ns2:merchant>
							<ns2:messageType>'.$this->_msgtype_get_orders.'</ns2:messageType>
						</SOAP-ENV:Body>
					</SOAP-ENV:Envelope>';


    $result = $this->_call_curl($xml,$header);

    $xml = new SOAPXMLParser($result);

    $data = $xml->getData();
	
    $result = $data['ArrayOfMerchantDocumentInfo_Response'];
	
	if(count($result) == 0){
		return false;
	}

    $pending_document_ids = array();

    foreach($result['MerchantDocumentInfo'] as $key){
      $documentID =  (string) $key['documentID'];
      $pending_document_ids[$documentID] = (string) $key['generatedDateTime'];
    }

    return $pending_document_ids;
  }


  /**
   *	Retrieves the actual document from Amazon Seller Central 
   * 
   *	@params $documentID	Document Id 
   *
   *	@return $xml OrderReport XML document
   */

  function getDocument($documentID) {
    $this->error = null;

    $header = array ("SOAPAction: http://www.amazon.com/merchants/merchant-interface/MerchantInterface#getDocument#KEx3YXNwY1NlcnZlci9BbXpJU0EvTWVyY2hhbnQ7TGphdmEvbGFuZy9TdHJpbmc7TG9yZy9pZG9veC93YXNwL3R5cGVzL1Jlc3BvbnNlTWVzc2FnZUF0dGFjaG1lbnQ7KUxqYXZhL2xhbmcvU3RyaW5nOw==");

    $xml = '<?xml version="1.0" encoding="UTF-8"?>
					<SOAP-ENV:Envelope 
					xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
					xmlns:ns1="http://www.amazon.com/merchants/merchant-interface/" 
					xmlns:ns2="http://systinet.com/xsd/SchemaTypes/">
						<SOAP-ENV:Body>
							<ns2:merchant>
								<ns1:merchantIdentifier>'.$this->merchant_token.'</ns1:merchantIdentifier>
								<ns1:merchantName>'.$this->merchant_name.'</ns1:merchantName>
							</ns2:merchant>
							<ns2:documentIdentifier>'.$documentID.'</ns2:documentIdentifier>
						</SOAP-ENV:Body>
					</SOAP-ENV:Envelope>';

    $result = $this->_call_curl($xml,$header);

    $xml = $this->getAmazonEnvelopeXML($result);

    return $xml;
  }

  /**
   *	Acknowledges that pending document has been downloaded, and remove it from the pending list.
   *	
   *	@params $documentID	Document Id 
   *
   *	@return $result array of document download acknowledge status response 
   */

  function postDocumentDownloadAck($documentID) {
    $this->error = null;

    $header = array ("SOAPAction: http://www.amazon.com/merchants/merchant-interface/MerchantInterface#postDocumentDownloadAck#KEx3YXNwY1NlcnZlci9BbXpJU0EvTWVyY2hhbnQ7W0xqYXZhL2xhbmcvU3RyaW5nOylbTHdhc3BjU2VydmVyL0FteklTQS9Eb2N1bWVudERvd25sb2FkQWNrU3RhdHVzOw==");

    $xml = '<?xml version="1.0" encoding="UTF-8"?>
					<SOAP-ENV:Envelope 
					xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
					xmlns:ns1="http://www.amazon.com/merchants/merchant-interface/" 
					xmlns:ns2="http://systinet.com/xsd/SchemaTypes/"
					xmlns:ns3="http://systinet.com/wsdl/java/lang/">
						<SOAP-ENV:Body>
							<ns2:merchant>
								<ns1:merchantIdentifier>'.$this->merchant_token.'</ns1:merchantIdentifier>
								<ns1:merchantName>'.$this->merchant_name.'</ns1:merchantName>
							</ns2:merchant>
							<ns2:documentIdentifierArray>
								<ns3:string>'.$documentID.'</ns3:string>
							</ns2:documentIdentifierArray>
						</SOAP-ENV:Body>
					</SOAP-ENV:Envelope>';

    $result = $this->_call_curl($xml,$header);

    $xml = new SOAPXMLParser($result);

    $data = $xml->getData();
		
    $result = $data['ArrayOfDocumentDownloadAckStatus_Response']['DocumentDownloadAckStatus']['documentDownloadAckProcessingStatus'];

    if($result == "_SUCCESSFUL_"){
      return true;
    }else{
      return false;
    }
  }

  /**
   *	Get the status of a transaction, such as postDocument.
   *	
   *	@params $documentTransactionID Document Transaction ID
   *
   *	@return $result Document Processing Info Response
   */

  function getDocumentProcessingStatus($documentTransactionID) {
    $this->error = null;

    $header = array ("SOAPAction: http://www.amazon.com/merchants/merchant-interface/MerchantInterface#getDocumentProcessingStatus#KEx3YXNwY1NlcnZlci9BbXpJU0EvTWVyY2hhbnQ7SilMd2FzcGNTZXJ2ZXIvQW16SVNBL0RvY3VtZW50UHJvY2Vzc2luZ0luZm87");

    $xml = '<?xml version="1.0" encoding="UTF-8"?>
					<SOAP-ENV:Envelope 
					xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
					xmlns:ns1="http://www.amazon.com/merchants/merchant-interface/" 
					xmlns:ns2="http://systinet.com/xsd/SchemaTypes/">
						<SOAP-ENV:Body>
							<ns2:merchant>
								<ns1:merchantIdentifier>'.$this->merchant_token.'</ns1:merchantIdentifier>
								<ns1:merchantName>'.$this->merchant_name.'</ns1:merchantName>
							</ns2:merchant>
							<ns2:documentTransactionIdentifier>'.$documentTransactionID.'</ns2:documentTransactionIdentifier>
						</SOAP-ENV:Body>
					</SOAP-ENV:Envelope>';

    $soap_xml = $this->_call_curl($xml,$header);
 
    $xml = new SOAPXMLParser($soap_xml);

    $data = $xml->getData();

    $result = $data['DocumentProcessingInfo_Response'];
        
    return $result;
  }

  /**
   *	Gets the requested  status of the documents. if its _DONE_, then returns the document ID. 
   *	otherwise, it waits and again looks for the requested report status
   *
   *	@params $documentTransactionID
   *
   *	@returns documentID
   */

  function waitForDocumentProcessingComplete($documentTransactionID) {

    $processingTime = 0;

    $status = $this->getDocumentProcessingStatus($documentTransactionID);

    while (DOCUMENT_PROCESSING_STATUS_COMPLETE != $status['documentProcessingStatus']) {

      if ($processingTime >= DOCUMENT_PROCESSING_STATUS_POLL_TIMEOUT) {
        $this->error = "Error: request timed out in " . DOCUMENT_PROCESSING_STATUS_POLL_TIMEOUT . " seconds.";
        return $this->error;
      }

      $processingTime += DOCUMENT_PROCESSING_STATUS_POLL_INTERVAL;

      sleep(DOCUMENT_PROCESSING_STATUS_POLL_INTERVAL);

      $status = $this->getDocumentProcessingStatus($documentTransactionID);
    }

    return $status['processingReport']['documentID'];
  }

  /**
   *	Get Transaction Status of a document 
   *
   *  @params $documentTransactionID
   *
   *  @return $result Message with Success and Failure error count
   */

  function getTransactionStatus($documentTransactionID){

    $documentID = $this->waitForDocumentProcessingComplete($documentTransactionID);

    $final_document = $this->getDocument($documentID);

    $result = array();
    preg_match('/.*\<MessagesSuccessful>(.*?)\<\/MessagesSuccessful>.*/',$final_document,$matches);
    $result['MessagesSuccessful'] = $matches[1];
    preg_match('/.*\<MessagesWithError>(.*?)\<\/MessagesWithError>.*/',$final_document,$matches);
    $result['MessagesWithError'] = $matches[1];
    $result['xml'] = $final_document;

    return $result;
  }

  /**
   *	Post to Amazon End Point
   *
   *  @params $msgtype Message Type like _GET_ORDERS_DATA_
   *  @params $xml AmazonEnvelope XML
   *
   *  @return $data Data returned by CURL
   */

  function _post_to_amazon($msgtype , $xml){

    $data = $this->_prepare_MIME_data($msgtype,$xml);

    $header = array ("Content-Type: Multipart/Related; boundary=MIME_boundary; type=text/xml; charset=utf-8",
                     "SOAPAction: http://www.amazon.com/merchants/merchant-interface/MerchantInterface#postDocument#KEx3YXNwY1NlcnZlci9BbXpJU0EvTWVyY2hhbnQ7TGphdmEvbGFuZy9TdHJpbmc7TG9yZy9pZG9veC93YXNwL3R5cGVzL1JlcXVlc3RNZXNzYWdlQXR0YWNobWVudDspTHdhc3BjU2VydmVyL0FteklTQS9Eb2N1bWVudFN1Ym1pc3Npb25SZXNwb25zZTs=");

    $data = $this->_call_curl($data,$header);

    return $data;
  }

  /**
   *	Calls the Merchant API end point with prepared MIME based SOAP XML with Amazon XML as attachments as Data 
   *	and HTTP Header with content-type and SOAP Action URL from WSDL
   *
   *	@params $data MIME based SOAP XML Data with Amazon Envelope as Attachments
   *	@params $header Amazon Envelope XML
   *
   *	@return $data complete header to be sent via curl
   */

  function _call_curl($data,$header){

	if($this->debug){
		writelog("SOAP XML Request Data :\n" . $data);
	}

    $curl_url = "https://merchant-api.amazon.com:443/gateway/merchant-interface-mime";

    if (function_exists('curl_init')){

      /* works on PHP 4 >= 4.0.2, PHP 5 */
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $curl_url);
      curl_setopt($ch, CURLOPT_HEADER, 1); // Get the header
      //curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/cookie");
      curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); 
      curl_setopt($ch, CURLOPT_VERBOSE, 1); 
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($ch, CURLOPT_USERPWD, "$this->login:$this->password"); 
      curl_setopt($ch, CURLOPT_TIMEOUT, 180);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
      curl_setopt($ch,CURLOPT_HTTPHEADER,$header);

      $data = curl_exec($ch);

      if (curl_errno($ch)) {

        $this->error=curl_error($ch);

		if($this->debug){
		  writelog("CURL Error :" . $this->error);
		}

        return false;
      } else {
        curl_close($ch);
      }
		
    }else{
			
      /* lets use the command line curl */
      if (getenv("OS") == "Windows_NT")
        $cpath = "c:\\curl\\curl.exe";
      else
        $cpath = "/usr/bin/curl";

      $args = "-m 300 -s -S";		// default curl args; 5 min. timeout in Silent (-s) mode

      $data = exec("$cpath $args -u $this->login:$this->password -d '$data' -H '$header' -k $curl_url");
		
    }

	if($this->debug){
		writelog("SOAP XML Response Data :\n" . $data);
	}

    return $data;
  }

  /**
   *	Prepares MIME based SOAP XML with Amazon XML as attachments
   *
   *	@params $msgtype Message Type like _GET_ORDERS_DATA_
   *	@params $binary_data Amazon Envelope XML
   *
   *	@return $data complete header to be sent via curl
   */

  function _prepare_MIME_data($msgtype,$binary_data){

    $cid = md5(uniqid(time()));
    $soap_envelope = $this->_prepare_soap_envelope($msgtype,$cid);

    $data = "--MIME_boundary\r\n";
    $data .= "Content-Type: text/xml; charset=\"UTF-8\"\r\n\r\n";
    $data .= $soap_envelope . "\r\n\r\n";
    $data .= "--MIME_boundary\r\n";
    $data .= "Content-Transfer-Encoding: 8bit\r\n";
    $data .= "Content-Type: application/binary\r\n";
    $data .= "Content-ID: <".$cid.">\r\n";
    $data .= "Content-Disposition:\r\n\r\n";	   
    $data .= $binary_data . "\r\n\r\n";
    $data .= "--MIME_boundary--\r\n\r\n";

    return $data;
  }

  /**
   *	Prepares SOAP envelope
   *
   *	@params $msgtype Message Type like _GET_ORDERS_DATA_
   *	@params $cid unique document identifier
   *
   *	@return $document SOAP XML
   */

  function _prepare_soap_envelope($msgtype,$cid){

    $document='<?xml version="1.0" encoding="UTF-8"?>
						<SOAP-ENV:Envelope 
						xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
						xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
						xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
						xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" 
						xmlns:ns4="http://systinet.com/xsd/SchemaTypes/">
							<SOAP-ENV:Body>
								<ns4:merchant>
									<ns4:merchantIdentifier>'.$this->merchant_token.'</ns4:merchantIdentifier>
									<ns4:merchantName>'.$this->merchant_name.'</ns4:merchantName>
								</ns4:merchant>
								<ns4:messageType>'.$msgtype.'</ns4:messageType>
								<doc href="cid:'.$cid.'"/>
							</SOAP-ENV:Body>
						</SOAP-ENV:Envelope>';

    return $document;
  }
   
  /**
   *	Retrieves Amazon Order envelope XML data from Header 
   *	@params $fp header data
   *
   *	@return $xml AmazonEnvelope XML
   */

  function getAmazonEnvelopeXML($fp){
		
    $xml = "";
    $fp = explode("\n", $fp);
    $start="";

    foreach($fp as $val){
      if(preg_match('/^--xxx-WASP-CPP-MIME-Boundary-xxx-.*-xxx-END-xxx--/',$val)){
        $start=false;
      }
      if($start){
        $xml .= trim($val);
      }
      if(trim($val) == "Content-Type: application/binary"){
        $start=true;  
      }
      if(trim($val)== "</AmazonEnvelope>"){
        $start=false;
      }
    }
    return $xml;
  }

  /************************************************************
   *																											*
   *		Following are the Exposed API Functions to be called from outside.			*
   *																											*
   ************************************************************/

  /**
   *	Get All pending documents and return as array 
   *
   *  @return $document_array
   */

  function getOrders(){
   
    $pending_document_ids = $this->getAllPendingDocumentInfo();

	if(!$pending_document_ids){
		return false;
	}

    $document_array = array();
    foreach($pending_document_ids as $documentID => $time){		
      $the_document = $this->getDocument($documentID);
      preg_match_all('/\<OrderReport\>(.*?)\<\/OrderReport\>/',$the_document,$matches, PREG_PATTERN_ORDER);

      // matches[0] contains orders with OrderReport as a root element
      foreach($matches[0] as $order){
        preg_match('/\<AmazonOrderID\>(.*?)\<\/AmazonOrderID\>/',$order,$orderid);
        $amazon_order_id = trim($orderid[1]);
        $document_array[$amazon_order_id] = array();
        $document_array[$amazon_order_id]['xml'] = $order;
        $document_array[$amazon_order_id]['reference_id'] = $documentID; // this will contain document ID from MFA or notification reference ID
        $document_array[$amazon_order_id]['type'] = 'MFA'; // this is hardcoded to notify about its type
      }
    }

    return $document_array;
  }

  /**
   *	Refund Order - Note: No Partial refund supported
   *
   *  @params $orders_id amazon order ID
   *  @params $refundReason Enumerated type of Refund Reason as specified in XSD
   *  @params $items array of items in the order report.
   *
   *  @return $documentTransactionID
   */

  function refundOrder($orders_id, $refundReason, $items){
    $count = 0;

    $refundxml = '<?xml version="1.0" encoding="UTF-8"?>
						<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
						<Header>
							<DocumentVersion>1.01</DocumentVersion>
							<MerchantIdentifier>'.$this->merchant_token.'</MerchantIdentifier>
						</Header>
						<MessageType>OrderAdjustment</MessageType>';

    foreach($items as $item){
      $itemprice = '';
      $promotionxml='';
      $count++;

      /* generates the promotion related adjustments only if it is present in the original report */
      if(array_key_exists("Promotion",$item)){
        foreach($item['Promotion'] as $promotion_code => $promotion_info){

		  /* do not generate promotions if Principal & Shipping are 0.0 */
		  if($promotion_info['Principal'] == 0.0 && $promotion_info['Shipping'] == 0.0){
			continue;
		  }

          $promotionxml .= '<PromotionAdjustments>';

		  /* IOPN as of now doesnt have promotion code or merchant promotion id. MFA provides that*/
		  if($promotion_code){
			$promotionxml .= "\n".'<PromotionClaimCode>'.$promotion_code.'</PromotionClaimCode>';
		  }

		  if(isset($promotion_info['MerchantPromotionID'])){
			$promotionxml .= "\n".'<MerchantPromotionID>'.$promotion_info['MerchantPromotionID'].'</MerchantPromotionID>';
		  }

		   if(array_key_exists("Principal",$promotion_info)){
				$promotionxml .= "\n".'<Component>
										<Type>Principal</Type>
										<Amount currency="'.$this->currency.'">'.$promotion_info['Principal'].'</Amount>
									</Component>';
		   }
		    if(array_key_exists("Shipping",$promotion_info)){
				$promotionxml .= "\n".'<Component>
										<Type>Shipping</Type>
										<Amount currency="'.$this->currency.'">'.$promotion_info['Shipping'].'</Amount>
									</Component>';
			
			}
          $promotionxml .= '</PromotionAdjustments>';
        }
      }

      /* generates the itemprice adjustment as it is in the original report */
      if(array_key_exists("ItemPrice",$item)){
        foreach($item['ItemPrice'] as $itemprice_component => $itemprice_value){
          $itemprice .= '<Component>
												 <Type>'.$itemprice_component.'</Type>
												 <Amount currency="'.$this->currency.'">'.$itemprice_value.'</Amount>
											  </Component>
											  ';
        }
      }

      /* refund - order adjustment xml*/
      $refundxml .= 	'<Message>
									<MessageID>'.$count.'</MessageID>
									 <OrderAdjustment>
										<AmazonOrderID>'.$orders_id.'</AmazonOrderID>
										<AdjustedItem>
										   <AmazonOrderItemCode>'.$item['AmazonOrderItemCode'].'</AmazonOrderItemCode>
										   <AdjustmentReason>'.$refundReason.'</AdjustmentReason>
										   <ItemPriceAdjustments>
											'.$itemprice.'
										   </ItemPriceAdjustments>
										   '.$promotionxml.'
										</AdjustedItem>
									 </OrderAdjustment>
								</Message>';
    }

    $refundxml .= '</AmazonEnvelope>';

    $result = $this->_post_to_amazon($this->_msgtype_payment_adjustment,$refundxml);

    preg_match('/.*\<ns0\:documentTransactionID.*>(.*?)<\/ns0:documentTransactionID>.*/',$result,$matches);

    $documentTransactionID = trim($matches[1]);

    return $documentTransactionID;
  }

  /**
   *	Ship Order i.e from Unshipped to Shipped status in seller central
   *
   *  @params $orders_id amazon order ID
   *  @params $carrier_code Enumerated type of Shipping Carrier code as specified in XSD e.g. UPS
   *  @params $shipping_method Shipping method name e.g. Parcel Post
   *  @params $tracking_number Shipping Tracking Number
   *
   *  @return $documentTransactionID
   */

  function shipOrder($amazon_order_id,$carrier_code,$shipping_method,$tracking_number){

    $shipxml='<?xml version="1.0" encoding="UTF-8"?>
					<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
						<Header>
							<DocumentVersion>1.01</DocumentVersion>
							<MerchantIdentifier>'.$this->merchant_token.'</MerchantIdentifier>
						</Header>
						<MessageType>OrderFulfillment</MessageType>
						<Message>
							<MessageID>1</MessageID>
							<OrderFulfillment>
								<AmazonOrderID>'.$amazon_order_id.'</AmazonOrderID>
								<FulfillmentDate>'.gmdate('Y-m-d\TH:i:s').'</FulfillmentDate>
								<FulfillmentData>
									<CarrierCode>'.$carrier_code.'</CarrierCode>
									<ShippingMethod>'.$shipping_method.'</ShippingMethod>
									<ShipperTrackingNumber>'.$tracking_number.'</ShipperTrackingNumber>
								</FulfillmentData>
							</OrderFulfillment>
						</Message>
					</AmazonEnvelope>';

    $result = $this->_post_to_amazon($this->_msgtype_order_fulfillment,$shipxml);

    preg_match('/.*\<ns0\:documentTransactionID.*>(.*?)<\/ns0:documentTransactionID>.*/',$result,$matches);

    $documentTransactionID = trim($matches[1]);

    return $documentTransactionID;
  }


  /**
   *	Cancel Order i.e from Unshipped to Cancelled status in seller central
   *
   *  @params $orders_id amazon order ID
   *
   *  @return $documentTransactionID
   */

  function cancelOrder($amazon_order_id,$merchant_order_id = ''){
   
    if(trim($merchant_order_id) !=""){
      $merchant_order_id_tag = "
								<MerchantOrderID>$merchant_order_id</MerchantOrderID>";
    }

    $cancelxml='<?xml version="1.0" encoding="UTF-8"?>
					<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
						<Header>
							<DocumentVersion>1.01</DocumentVersion>
							<MerchantIdentifier>'.$this->merchant_token.'</MerchantIdentifier>
						</Header>
						<MessageType>OrderAcknowledgement</MessageType>
						<Message>
							<MessageID>1</MessageID>
							<OperationType>Update</OperationType>
							<OrderAcknowledgement>
								<AmazonOrderID>'.$amazon_order_id.'</AmazonOrderID>'.$merchant_order_id_tag.'
								<StatusCode>Failure</StatusCode>
							</OrderAcknowledgement>
						</Message>
					</AmazonEnvelope>';

    $result = $this->_post_to_amazon($this->_msgtype_order_ack,$cancelxml);

    preg_match('/.*\<ns0\:documentTransactionID.*>(.*?)<\/ns0:documentTransactionID>.*/',$result,$matches);

    $documentTransactionID = trim($matches[1]);

    return $documentTransactionID;
  }

  /**
   *	Acknowledge Order 
   *
   *  @params $orders_id amazon order ID
   *
   *  @return $documentTransactionID
   */

  function acknowledgeOrder($amazon_order_id,$merchant_order_id){
   
    if(trim($merchant_order_id) !=""){
      $merchant_order_id_tag = "
								<MerchantOrderID>$merchant_order_id</MerchantOrderID>";
    }else{
      return false;
    }

    $ackxml='<?xml version="1.0" encoding="UTF-8"?>
					<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
						<Header>
							<DocumentVersion>1.01</DocumentVersion>
							<MerchantIdentifier>'.$this->merchant_token.'</MerchantIdentifier>
						</Header>
						<MessageType>OrderAcknowledgement</MessageType>
						<Message>
							<MessageID>1</MessageID>
							<OrderAcknowledgement>
								<AmazonOrderID>'.$amazon_order_id.'</AmazonOrderID>'.$merchant_order_id_tag.'
								<StatusCode>Success</StatusCode>
							</OrderAcknowledgement>
						</Message>
					</AmazonEnvelope>';

    $result = $this->_post_to_amazon($this->_msgtype_order_ack,$ackxml);

    preg_match('/.*\<ns0\:documentTransactionID.*>(.*?)<\/ns0:documentTransactionID>.*/',$result,$matches);

    $documentTransactionID = trim($matches[1]);

    return $documentTransactionID;
  }
}
?>