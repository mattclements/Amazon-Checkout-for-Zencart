<?php
  /**
   * @brief CustomData Class for use by merchant for adding custom data to cart
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

class CustomData {

  var $debug = false;
  var $stack = array();

  /*
   *	@brief Based on module directory and item id, calls the appropriate module and puts the custom data in stack array
   * @returns nothing
   *
   */
  function GetCustomData($module_dir,$item = ""){

    $module_directory = DIR_FS_CBA . "modules/custom_data/".$module_dir."/";

    $directory_array = $this->getModules($module_dir);

    if($this->debug){
      ob_writelog("[Custom Data] GetCustomData Directory Array: " , $directory_array);
    }
		
    /* reset the stack before using it */
    $this->stack = array();
    $installed_modules = array();

    for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {

      $file = $directory_array[$i];
			
      include_once($module_directory . $file);
      $class = substr($file, 0, strrpos($file, '.'));

      if (class_exists($class)) {			  
        $module = new $class;
        $data = $module->custom_data($item);
        if(isset($data)){
          $this->stack[$class] = $data;
        }
      }else{			
        /* class not found */
        writelog("[Custom Data] ".$class . " class not found");
      }

    }
  }

  /*
   *	@brief returns the list of modules for a given module directory
   * @returns modules array
   *
   */
  function getModules($module_dir){
	
    $module_directory = DIR_FS_CBA . "modules/custom_data/" . $module_dir . "/";
    $directory_array = array();
    if ($dir = dir($module_directory)) {
      while ($file = $dir->read()) {
				
        if (!is_dir($module_directory . $file)) {
          $directory_array[] = $file;
        }
      }
      sort($directory_array);
      $dir->close();
    }
    if($this->debug){
      ob_writelog("[Custom Data] getModules Directory Array: " , $directory_array);
    }
    return $directory_array;
  }

  /*
   *	@brief calls the item modules
   * @returns item custom data array
   *
   */
  function GetItemCustomXml($item){
    $this->GetCustomData("item", $item);
    if($this->debug){
      ob_writelog("[Custom Data] GetItemCustomXml Stack: " , $this->stack);
    }
    return $this->stack;		 
  }

  /*
   *	@brief calls the cart modules
   * @returns cart custom data array
   *
   */
  function GetCartCustomXml(){	
    $this->GetCustomData("cart");
    if($this->debug){
      ob_writelog("[Custom Data] GetCartCustomXml Stack: " , $this->stack);
    }
    return $this->stack;		 
  }
  }
?>