<?php
  /**
   * @brief Class used to parse xml data
   * @catagory Zencart Checkout by Amazon Payment Module - Callback processing.
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

class OrderXMLParser {

	var $data = array();

	function OrderXMLParser($data){
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
	 * Modified to parse amazon envelope xml properly by Balachandar Muruganantham 
	 * 
	 */
	
	function xml2array($vals){
		//print_r($vals);
		$params = array();
		$level = array();
		$custom=false;
		$customarray=array();
		$customtext="";
		foreach ($vals as $xml_elem) {
		  if ($xml_elem['type'] == 'open') {
			  if($xml_elem['tag']== "CustomizationInfo" || $xml_elem['tag']== "Component" || $xml_elem['tag']== "Fee"){
				$customtext=$xml_elem['tag'];
 				$custom=true;
			  }
			  $level[$xml_elem['level']] = $xml_elem['tag'];			  
		  }
		  if ($xml_elem['type'] == 'complete') {
			$start_level = 1;
			$php_stmt = '$params';
			while($start_level < $xml_elem['level']) {
			  $php_stmt .= '[$level['.$start_level.']]';
			  $start_level++;
			}
			  if($custom){
				if(array_key_exists("attributes",$xml_elem)){
					$customattributes = $xml_elem["attributes"];
					$customattributes[$xml_elem['tag']] = $xml_elem['value'];
					array_push($customarray,$customattributes);
				}else{
					array_push($customarray,$xml_elem['value']);
				}
			  }else{
					$php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
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
				$php_stmt .= '[$customtext][$customarray[0]] = $customarray[1];';
				//echo $php_stmt;
				eval($php_stmt);
				$customarray=array();
				$custom=false;
				$customtext="";
			  }
		  }
		}

		//print_r($params);
		$this->data = $this->parseArrayToObject($params);
//		$this->data = $params;
	}

	/*
	 * Credits for this function
	 * http://forum.weblivehelp.net/web-development/php-convert-array-object-and-vice-versa-t2.html#p2
	 * 
	 * Modified to recursively converty everything to object by Balachandar Muruganantham - 10/16/2009
	 * 
	 */

	function parseArrayToObject($array) {
	   $object = new stdClass();
	   if (is_array($array) && count($array) > 0) {
		  foreach ($array as $name=>$value) {
			 $name = trim($name);
			 if (!empty($name)) {
				//$object->$name = $value;
				if(is_array($value)){
					$object->$name = $this->parseArrayToObject($value);
				}else{
					$object->$name = $value;
				}
			 }
		  }
	   }
	   return $object;
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