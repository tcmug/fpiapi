<?php 
/**
 * Gateway for Handelsbanken
 * 
 */

require_once "samlink.php";

class FpiapiGatewayHandelsbanken extends FpiapiGatewaySamlink {
  
  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->name = "Handelsbanken";
    $this->postUrl = 'https://verkkomaksu.handelsbanken.fi/vm/login.html';
    $this->queryUrl = 'https://verkkomaksu.handelsbanken.fi/vm/kysely.html'; 
    
  }
  
}

