<?php
/**
 * Gateway for S-Pankki
 * 
 */

require_once "crosskey.php";

class FpiapiGatewaySpankki extends FpiapiGatewayCrosskey {
  
  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->name = "S-Pankki";
    $this->postUrl = 'https://online.s-pankki.fi/service/paybutton';
    $this->queryUrl = 'https://online.s-pankki.fi/service/paymentquery';
  }
  
}
