<?php
/**
 * Gateway for Danskebank
 */

class FpiapiGatewayDanskebank extends FpiapiGateway {
  
  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->name = "Sampo Pankki";
    $this->postUrl = 'https://verkkopankki.danskebank.fi/SP/vemaha/VemahaApp';
    $this->queryUrl = 'https://netbank.danskebank.dk/HB';
    $this->hasPaymentAbility = true;
    $this->hasQueryAbility = false; //true;
  }
  
  
  /**
   * getPaymentFields()
   * @see fpiapi/gateways/FpiapiGateway::getPaymentFields()
   */
  public function getPaymentFields() {

    $fields = array(
      'SUMMA'    => $this->transaction->getSum(),
      'VIITE'    => $this->transaction->getReferenceNumber(),
      'KNRO'     => $this->configuration['publicKey'],
      'VERSIO'   => '4',
      'VALUUTTA' => $this->getCurrency(),
      'OKURL'    => $this->getReturnUrl(),
      'VIRHEURL' => $this->getErrorUrl(),
    );

    $due = $this->transaction->getDueDate();
    
    if (!empty($due)) {
      $fields['ERAPAIVA'] = date("d.m.Y", strtotime($due));
    }

    $mac = $this->configuration['privateKey'] . '&' . implode('&', $fields) . '&';
    $mac = strtolower(hash('sha256', $mac));
    
    $fields['TARKISTE'] = $mac;

    $codes = array(
      "fi" => "1",
      "sv" => "2",
      "en" => "3"
    );
    
    $fields['lng'] = $codes[$this->getLanguage()];

    $fields['ALG'] = '03';

    return $fields;
  }
  
  
  /**
   * isPaymentCompleted()
   * @see fpiapi/gateways/FpiapiGateway::isPaymentCompleted()
   */
  public function isPaymentCompleted() {
    
    $params = &$_REQUEST;
    
    $fields = array(
      $this->transaction->getReferenceNumber(),
      isset($params['SUMMA']) ? $params['SUMMA'] : NULL,
      isset($params['STATUS']) ? $params['STATUS'] : NULL,
      $this->configuration['publicKey'],
      isset($params['VERSIO']) ? $params['VERSIO'] : NULL,
      isset($params['VALUUTTA']) ? $params['VALUUTTA'] : NULL,
      isset($params['ERAPAIVA']) ? $params['ERAPAIVA'] : NULL,
    );
    
    if (!$this->checkFields($fields)) {
      return false;
    }
    
    if ($this->getCurrency() != $params['VALUUTTA']) {
      return false;
    }

    $mac = $this->configuration['privateKey'] . '&' . implode('&', $fields) . '&';
    $mac = strtolower(hash('sha256', $mac));

    return strcmp($mac, $params['TARKISTE']) === 0;

  }
  
  
  /**
   * getQueryFields()
   * @see fpiapi/gateways/FpiapiGateway::getQueryFields()
   */
  public function getQueryFields() {
    
    // technically a very different implementation that with the other banks
    // nordeas XML call would be something in line with this one...
    // Curl is the way to go here
    
    // First fill in the field used to calculate mac
    $fields = array(
      'Refno'      => $this->transaction->getReferenceNumber(),
      'MerchantID' => $this->configuration['merchantId'],
      'gsAftlnr'   => $this->configuration['contractNumber'],
      'gsSprog'    => 'EN',
      'gsProdukt'  => 'IBV',
      'gsNextObj'  => 'InetPayV',
      'gsNextAkt'  => 'InetPaySt',
      'Version'    => '0001',
      'gsResp'     => 'S'
    );
    
    // Calculate mac accordingly...
    $mac = $this->configuration['privateKey'] . $this->configuration['publicKey'] . $fields['Refno'];

    $fields['VerifyCode'] = strtolower(md5($mac));

    $fields['Refno'] = $this->transaction->getReferenceNumber();
    
    return $fields;
  }
  

}
