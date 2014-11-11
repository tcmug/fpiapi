<?php 
/**
 * Gateway for POP Pankki
 */

require_once "samlink.php";

class FpiapiGatewayPOP extends FpiapiGatewaySamlink {
  
  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->name = "POP Pankki";
    $this->postUrl = 'https://verkkomaksu.poppankki.fi/vm/login.html';
    $this->queryUrl = 'https://verkkomaksu.poppankki.fi/vm/vm/kysely.html'; 
  }
  
}