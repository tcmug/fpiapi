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
    $this->postUrl = 'https://verkkopankki.sampopankki.fi/SP/vemaha/VemahaApp';
    $this->queryUrl = 'https://netbank.danskebank.dk/HB';
    $this->hasPaymentAbility = true;
    $this->hasQueryAbility = false; //true;
    $this->configuration['version'] = 3;
  }
  
  
  /**
   * getPaymentFields()
   * @see fpiapi/gateways/FpiapiGateway::getPaymentFields()
   */
  public function getPaymentFields() {
    
    // First fill in the field used to calculate mac
    $fields = array(
      'SUMMA'    => $this->transaction->getSum(),
      'VIITE'    => $this->transaction->getReferenceNumber(),
      'KNRO'     => $this->configuration['publicKey'],
      'VERSIO'   => $this->configuration['version'],
      'VALUUTTA' => $this->getCurrency(),
      'OKURL'    => $this->getReturnUrl(),
      'VIRHEURL' => $this->getErrorUrl()
    );
    
    // Calculate mac accordingly...
    switch ($params['VERSIO']) {
      case 3:
        $mac = $this->configuration['privateKey'] . implode('', $fields);
        $mac = strtoupper(md5($mac));
      break;
      case 4:
        $mac = $this->configuration['privateKey'] . '&' . implode('&', $fields) . '&';
        $mac = strtolower(hash('sha256', $mac));
      break;
    }
    
    $fields['TARKISTE'] = $mac;
    
    $due = $this->transaction->getDueDate();
    
    if (!empty($due))
      $fields['ERAPAIVA'] = date("d.m.Y", strtotime($due));

    $codes = array(
      "fi" => "1",
      "sv" => "2",
      "en" => "3"
    );
    
    $fields['lng'] = $codes[$this->getLanguage()];
      
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
      isset($params['VALUUTTA']) ? $params['VALUUTTA'] : NULL
    );
    
    if (!$this->checkFields($fields)) {
      return false;
    }
    
    if ($this->getCurrency() != $params['VALUUTTA']) {
      return false;
    }

    switch ($params['VERSIO']) {
      case 3:
        $mac = $this->configuration['privateKey'] . implode('', $fields);
        $mac = strtoupper(md5($mac));
      break;
      case 4:
        $mac = $this->configuration['privateKey'] . '&' . implode('&', $fields) . '&';
        $mac = strtolower(hash('sha256', $mac));
      break;
    }
    
//    $mac = $this->configuration['privateKey'] . implode('', $fields);
//    $mac = strtoupper(md5($mac));
     
    return $mac == $params['TARKISTE'];

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
