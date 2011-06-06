<?php
/**
 * 
 * FpiAPI main factory class
 *
 * TODO: Ability to give default settings to the gateways via factory methods
 *
 */

require("exception.php");
require("transaction.php");


class FpiapiFactory {

  
  private static $included = array(); // booleans for file inclusions

  /**
   * getGateway
   * Get geteway object by name
   * @param string $gatewayName
   * @throws Exception
   */
  static public function getGateway($gatewayName) {
    
    if (!self::$included['gateway']) {
      require "gateways/gateway.php";
      self::$included['gateway'] = true;
    }
   
    $dir = dirname(__FILE__)."/gateways/";
    $file = $dir . $gatewayName . ".php";
    
    $className = "FpiapiGateway" . ucwords($gatewayName);
    
    if (!self::$included[$gatewayName]) {  
      if (file_exists($file)) {
        require($file);
       } else {
        throw new Exception("No such gateway as " . $gatewayName);
      }
    }
    
    return new $className();

  }
 
  
  
  
}


 