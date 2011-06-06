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
    $this->postUrl = 'https://verkkomaksu.inetpankki.samlink.fi/vm/SHBlogin.html';
    $this->queryUrl = 'https://verkkomaksu.inetpankki.samlink.fi/vm/SHBkysely.html'; 
    
  }
  
}

