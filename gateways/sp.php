<?php 
/**
 * Gateway for Säästöpankki
 */

require_once "samlink.php";

class FpiapiGatewaySp extends FpiapiGatewaySamlink {
  
  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->name = "Säästöpankki";
    $this->postUrl = 'https://verkkomaksu.saastopankki.fi/vm/login.html';
    $this->queryUrl = 'https://verkkomaksu.saastopankki.fi/vm/kysely.html'; 
  }
  
}