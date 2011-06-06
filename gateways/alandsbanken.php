<?php
/**
 * Gateway for Ålandsbanken
 */

require_once "crosskey.php";

class FpiapiGatewayAlandsbanken extends FpiapiGatewayCrosskey {
  
  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->name = "Ålandsbanken";
    $this->postUrl = 'https://online.alandsbanken.fi/service/paybutton';
    $this->queryUrl = 'https://online.alandsbanken.fi/service/paymentquery';
  }
  
}