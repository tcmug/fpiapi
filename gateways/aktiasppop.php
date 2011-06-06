<?php 
/**
 * Gateway for Aktia, Säästöpankki and Paikallisosuuspankki
 */

require "samlink.php";

class FpiapiGatewayAktiasppop extends FpiapiGatewaySamlink {
  
  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->name = "Aktia/Sp/Pop-maksu";
    $this->postUrl = 'https://verkkomaksu.inetpankki.samlink.fi/vm/login.html';
    $this->queryUrl = 'https://verkkomaksu.inetpankki.samlink.fi/vm/kysely.html'; 
  }
  
}