<?php
/**
 *
 * FpiAPI main factory class
 *
 * TODO: Ability to give default settings to the gateways via factory methods
 *
 */

require_once "exception.php";
require_once "transaction.php";


class FpiapiFactory {


  private static $included = array(); // booleans for file inclusions

  /**
   * getGateway
   * Get geteway object by name
   * @param string $gatewayName
   * @throws Exception
   */
  static public function getGateway($gatewayName) {

    if (!isset(self::$included['gateway'])) {
      require "gateways/gateway.php";
      self::$included['gateway'] = true;
    }

    $dir = dirname(__FILE__)."/gateways/";
    $file = $dir . $gatewayName . ".php";

    $className = "FpiapiGateway" . ucwords($gatewayName);

    if (!isset(self::$included[$gatewayName])) {
      if (file_exists($file)) {
        require($file);
       } else {
        throw new Exception("No such gateway as " . $gatewayName);
      }
    }

    return new $className();

  }


}


