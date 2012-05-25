<?php
  /**
   * @brief SOAP XML parser class for parsing Amazon SOAP messages
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


class SOAPXMLParser {

  var $data = array();

  function SOAPXMLParser($data){
		
    /* extract the SOAP body xml */
    preg_match('/.*\<SOAP\-ENV\:Body\>(.*)?\<\/SOAP-ENV:Body\>.*/',$data,$matches);
    $data = $matches[1];

    /* create the XML parser object which supports (PHP 4, PHP 5)*/
    $xml_parser = xml_parser_create();
    xml_set_object ( $xml_parser, $this );
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($xml_parser, $data, $vals, $index);
    xml_parser_free($xml_parser);
		
    $this->xml2array($vals);
  }

  /*
   * Credits for the structure of this function
   * http://in.php.net/manual/en/function.xml-parser-create.php#38739
   * 
   * Modified to parse amazon envelope xml properly by Balachandar Muruganantham - 10/16/2009
   * 
   */
	
  function xml2array($vals){

    $params = array();
    $level = array();
    $custom=false;
    $customarray=array();
    $customtext="";
    foreach ($vals as $xml_elem) {
			
      // remove the namespace in tags
      $tagarray = explode(":",$xml_elem['tag']);
      $tagname = $tagarray[1];

      if ($xml_elem['type'] == 'open') {	  
        if($tagname == "MerchantDocumentInfo"){
          $customtext=$tagname;
          $custom=true;
        }
        $level[$xml_elem['level']] = $tagname;			  
      }
      if ($xml_elem['type'] == 'complete') {
        $start_level = 1;
        $php_stmt = '$params';
        while($start_level < $xml_elem['level']) {
          $php_stmt .= '[$level['.$start_level.']]';
          $start_level++;
        }
        if($custom){
          $customarray[$tagname] = $xml_elem['value'];
        }else{
          $php_stmt .= '[$tagname] = $xml_elem[\'value\'];';
          eval($php_stmt);
        }
      }

      if($xml_elem['type'] == 'close'){
			  
        if($custom){
          $start_level = 1;
          $php_stmt = '$params';
          while($start_level < $xml_elem['level']) {
            $php_stmt .= '[$level['.$start_level.']]';
            $start_level++;
          }
          $php_stmt .= '[$customtext][] = $customarray;';
          //echo $php_stmt;
          eval($php_stmt);
          $customarray=array();
          $custom=false;
          $customtext="";
        }
      }
    }

    $this->data = $params;
  }

  /*
   *   Returns the Data which is an associative array
   *
   */

  function getData(){
    return $this->data;
  }
}
?>