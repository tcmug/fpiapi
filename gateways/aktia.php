<?php 
/**
 * Gateway for Aktia
 */

require_once "samlink.php";

class FpiapiGatewayAktia extends FpiapiGatewaySamlink {
  
  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->name = "Aktia";
    $this->postUrl = 'https://auth.aktia.fi/vm';
    $this->queryUrl = 'https://ebank.aktia.fi/vmapi/kysely.html'; 
  }
  

}