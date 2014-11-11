<?php 
/**
 * Gateway for Säästöpankki
 */

require "samlink.php";

class FpiapiGatewaySP extends FpiapiGatewaySamlink {
  
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