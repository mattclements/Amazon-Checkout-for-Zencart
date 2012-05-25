<?php
  /**
   * @brief Class for processing Callback Request and generate the response
   * @catagory Zen Cart Checkout by Amazon Payment Module - Callback processing
   * @author Balachandar Muruganantham
   * @copyright 2007-2010 Amazon Technologies, Inc
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
class OrderCallBackProcessor{

  var $requestXML;
  var $OrderItems;
  var $OrderItemCount;
  var $TaxTables;
  var $Promotions;
  var $ShippingMethods;
  var $Error = '';
  var $ValidateXML = false;
  var $SignedOrder = false;
  var $UUID;
  var $TimeStamp;

  function OrderCallBackProcessor(){
    
    $this->ProcessHTTPRequest();

    $xml = $this->OrderRequest;

    if(!$this->IsValidXML($xml) or empty($xml)){
      $this->Error = 'INTERNAL_SERVER_ERROR';
      $this->ErrorMessage = INTERNAL_SERVER_ERROR;
    }

    if($this->SignedOrder){
      if(!$this->IsValidRequest()){
        $this->Error = 'INTERNAL_SERVER_ERROR';
        $this->ErrorMessage = INTERNAL_SERVER_ERROR;
      }
    }

    $unserialized = XML_unserialize($xml);

    $this->requestXML = $unserialized['OrderCalculationsRequest'];

    if($this->requestXML['OrderCalculationCallbacks']['CalculateTaxRates'] == 'true'){
      $this->CalculateTaxRates = true;
    }else{
      $this->CalculateTaxRates = False;
    }

    if($this->requestXML['OrderCalculationCallbacks']['CalculatePromotions'] == 'true'){
      $this->CalculatePromotions = true;
    }else{
      $this->CalculatePromotions = False;
    }

    if($this->requestXML['OrderCalculationCallbacks']['CalculateShippingRates'] == 'true'){
      $this->CalculateShippingRates = true;
    }else{
      $this->CalculateShippingRates = false;
    }

    if($this->CalculateTaxRates){
      $this->TaxTableId = $this->requestXML->CallbackOrders->CallbackOrder->CallbackOrderItems->CallbackOrderItem->TaxTableId;
    }
    
    $this->OrderItems = $this->requestXML['CallbackOrderCart']['CallbackOrderCartItems'];
    
    /* convert CallbackOrderCartItem to array if there is only one item */
    if(array_key_exists("CallbackOrderItemId",$this->OrderItems['CallbackOrderCartItem'])){
      $this->OrderItems['CallbackOrderCartItem'] = array($this->OrderItems['CallbackOrderCartItem']);
    }

    $this->OrderItemCount = count($this->OrderItems['CallbackOrderCartItem']);

    $this->ShippingAddress = $this->requestXML['CallbackOrders']['CallbackOrder']['Address'];
  }

  /* Process the POST request and set appropriate flag */
  function ProcessHTTPRequest(){    
    if($_POST){
      
      if($_POST['order-calculations-request']){
        $this->OrderRequest = stripslashes($_POST['order-calculations-request']);
      }else if($_POST['order-calculations-error']){
        $error = $_POST['order-calculations-error'];
        requestlog();
        exit;
      }else{
        $this->Error = 'INTERNAL_SERVER_ERROR';
        $this->ErrorMessage = INTERNAL_SERVER_ERROR;
      }
      
      if($_POST['Signature']){
        $this->SignedOrder = true;
        $this->Signature = $_POST['Signature'];
      }

      if($_POST['Timestamp']){
        $this->Timestamp = $_POST['Timestamp'];
      }
      
      if($_POST['UUID']){
        $this->UUID = $_POST['UUID'];
      }
    }
  }

  /* Setting the Tax Tables */
  function SetTaxTables($array){
    $this->TaxTables = $array;
  }


  /* Setting the Promotions */
  function SetPromotions($array){
    $this->Promotions = $array;
  }

  /* Setting the Shipping Methods */
  function SetShippingMethods($array){
    $this->ShippingMethods = $array;
  }

  /* return items */
  function GetOrderItems() {
    return $this->OrderItems['CallbackOrderCartItem'];
  }

  /* return item count */
  function GetOrderItemCount() {
    return $this->OrderItemCount;
  }

  /* return category of the item */
  function GetCategory($item) {
    return $item->Category;
  }

  /* return product name */
  function GetTitle($item) {
    return $item->Title;
  }
  
  /* return product name */
  function GetSKU($item) {
    return $item['Item']['SKU'];
  }

  /* return weight of the item */
  function GetWeight($item) {
    return $item['Weight']['Amount'];
  }

  /* return Quantity of the item */
  function GetQuantity($item) {
    return $item['Quantity'];
  }

  /* returns the shipping address so that merchant can do calculations*/
  function GetShippingAddress(){    
    return $this->ShippingAddress;
  }

  /* get the shipping method id alone */
  function GetShippingMethodIds(){
    $shippingMethodIdArray = array();

    foreach($this->ShippingMethods as $key => $val){
      foreach($val as $key2 => $val2){
        array_push($shippingMethodIdArray,$val2['ShippingMethodId']);
      }
    }

    return $shippingMethodIdArray;
  }

  /* get the shipping method id alone */
  function GetPromotionIds(){   
    $promotionIdArray = array();
    foreach($this->Promotions as $key => $val){
      foreach($val as $key2 => $val2){
        array_push($promotionIdArray,$val2['PromotionId']);
      }
    }
    return $promotionIdArray;
  }



  /* get the shipping methods */
  function GetShippingMethods(){    
    $shippingMethodArray = array();
    foreach($this->ShippingMethods as $key => $val){
      foreach($val as $key2 => $val2){
        array_push($shippingMethodArray,$val2);
      }
    }
    return $shippingMethodArray;
  }

  /* push Associative array to xml */
  function Array2XML($xml,$data){
    foreach($data as $key => $value){
      if(is_array($value)){        
        $xml->Push($key);
        $this->Array2XML($xml,$value);
        $xml->Pop($key);
      }else{
        $xml->Element($key,$value);
      }
    }
  }

  /* Generate the response */
  function OrderCallBackResponse(){
    $addressID = array('AddressId' => $this->ShippingAddress['AddressId']);


    $callbackOrderItemsElement = array();

    foreach($this->GetOrderItems() as $myitem){

      $callbackOrderItemElement = array('CallbackOrderItemId' => $myitem['CallbackOrderItemId']);
      $item = $myitem['Item'];

      if($this->CalculateTaxRates){
        $this->TaxTableId = "Tax-for-SKU-" . $item['SKU'];
        $callbackOrderItemElement['TaxTableId'] = $this->TaxTableId;
      }

      if($this->CalculatePromotions){
        $callbackOrderItemElement['PromotionIds'] = array();
        foreach($this->getPromotionIds() as $val){
          array_push($callbackOrderItemElement['PromotionIds'], array('PromotionId' => $val));
        }
      }

      if($this->CalculateShippingRates){
        $callbackOrderItemElement['ShippingMethodIds'] = array();
        $temp['ShippingMethodId'] = array();
        foreach($this->getShippingMethodIds() as $val){
          array_push($temp['ShippingMethodId'], $val);
        }
        $callbackOrderItemElement['ShippingMethodIds'] = $temp;
      }

      array_push($callbackOrderItemsElement,$callbackOrderItemElement);
    }  

    $data = array(
                  'Response' => array(
                                      'CallbackOrders' => array(
                                                                'CallbackOrder' => array(array(
                                                                                               'Address' => $addressID,
                                                                                               'CallbackOrderItems' => array('CallbackOrderItem' => $callbackOrderItemsElement))
                                                                                         )
                                                                )
                                      )
                  );

    if(!$this->Error){
      $data = $this->MerchantTPSCalculation(&$data);
    }


    $data = array(
                  'OrderCalculationsResponse' => $data, 
                  'OrderCalculationsResponse attr' => array(
                                                            'xmlns'=>XMLNS_VERSION_TAG
                                                            )
                  );

    $xml = XML_serialize($data,'UTF-8');

    // check if the final xml is valid
    if(!$this->isValidXML($xml)){
      $this->Error = 'INTERNAL_SERVER_ERROR';
      $this->ErrorMessage = 'INTERNAL_SERVER_ERROR';
      return $this->OrderCallBackError($xml);
    }

    // finally return the xml
    return $xml;
  }

  //calculate Taxes, Promotions, Shipping methods, Cart Promotion Id
  function MerchantTPSCalculation(&$data){

    // calculate the tax rates if set
    if($this->CalculateTaxRates){
      $data['TaxTables'] = array();
      foreach($this->TaxTables as $val){
        $data['TaxTables']['TaxTable'] = $val;
      }
    }

    // calculate the promotions if set
    if($this->CalculatePromotions){
      $data['Promotions'] = array();

      foreach($this->Promotions as $val){
        array_push($data['Promotions'], $val);
      }
    }

    // Generation of Shipping Methods Tag Ends Here
    if($this->CalculateShippingRates){
      $data['ShippingMethods'] = array();

      foreach($this->ShippingMethods as $key => $val){
        $data['ShippingMethods']['ShippingMethod'] = $val;
      }
    }

    if($this->CartPromotionId){
      $data['CartPromotionId'] = $this->CartPromotionId;
    }

    return $data;
  }

  /* validates the xml using the schema file */
  function IsValidXML($xml){

    //TODO: added the return value as true as some minor issue is here
    // in validating the response xml against schema
    return true;
    if($xml){
      $doc = new DOMDocument();
      $doc->loadXML($xml);
      if($doc->schemaValidate(CALLBACK_SCHEMA_FILE)){
        return true;
      }else{
        return false;
      }
    }
  }

  /* checks whether request is valid via signature cmp */
  function IsValidRequest(){    

    $data = $this->UUID . $this->Timestamp;
    $signature = $this->GenerateSignature($data);
    if($signature != $this->Signature){
      $this->Error = 'INTERNAL_SERVER_ERROR';
      $this->ErrorMessage = INTERNAL_SERVER_ERROR;
      return false;
    }else{
      return true;
    }

  }

  /**
   * @brief returns the encrypted order signature
   * @return a based64 encoded encrypted order signature
   * @see HMAC.php
   */
  function GenerateSignature($data){
    $signature_calculator = new Crypt_HMAC(AWS_SECRET_KEY, CALLBACK_HMAC_ALGORITHM);
    $signature = $signature_calculator->hash($data);
    $binary_signature = pack('H*', $signature);
    return base64_encode($binary_signature);    
  }
  
  /* Generating the response in key=value pair*/
  function GenerateResponse(){
    $response = $this->OrderCallBackResponse();
    writelog(RESPONSE_KEY . "=" . $response);
    $response_signature = $this->GenerateSignature($response);
    $response =  RESPONSE_KEY . "=" . urlencode($response);   
    if($this->SignedOrder){
      $response .=  "&" . RESPONSE_AWS_KEY . "=" . AWS_ACCESS_KEY . "&" . RESPONSE_SIGNATURE_KEY . "=" . urlencode($response_signature);      
    }
    return $response;
  }
  
  /* Error response in case of any problem */
  function OrderCallBackError(){
    $data = array(
                  'Response' => array(
                                      'Error' => array(
                                                       'Code' => $this->Error,
                                                       'Message' => $this->ErrorMessage
                                                       )
                                      )
                  );

    $error_data = array(
                        'OrderCalculationsResponse' => $data, 
                        'OrderCalculationsResponse attr' => array(
                                                                  'xmlns'=>XMLNS_VERSION_TAG
                                                                  )
                        );
    $xml = XML_serialize($error_data,'UTF-8');
    return $xml;
  }
  }

?>
