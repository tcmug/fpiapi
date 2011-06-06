<?php
/**
 * 
 * Gateway for Tapiola
 *
 */

require_once "crosskey.php";

class FpiapiGatewayTapiola extends FpiapiGatewayCrosskey {
  
  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->name = "Tapiola";
    $this->postUrl = 'https://pankki.tapiola.fi/service/paybutton';
    $this->queryUrl = 'https://pankki.tapiola.fi/service/paymentquery';
  }
  
}
