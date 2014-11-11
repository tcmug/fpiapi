<?php
/**
 * Crosskey powered based web payment:
 *  Ålandsbanken
 * 	S-Pankki
 * 	Tapiola
 *
 * Crosskey based payment systems offer only fi and sv languages.
 */


class FpiapiGatewayCrosskey extends FpiapiGateway {
  
  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->postUrl = ''; 
    $this->queryUrl = '';
    $this->hasPaymentAbility = true;
    $this->hasQueryAbility = true;
  }
  
  /**
   * getPaymentFields()
   * @see fpiapi/gateways/FpiapiGateway::getPaymentFields()
   */
  public function getPaymentFields() {
    
    // First fill in the field used to calculate mac
    $fields = array(
      'AAB_VERSION' => "0002",
      'AAB_STAMP'   => $this->transaction->getUid(),
      'AAB_RCV_ID'  => $this->configuration['publicKey'],
      'AAB_AMOUNT'  => $this->transaction->getSum(),
      'AAB_REF'     => $this->transaction->getReferenceNumber(),
      'AAB_DATE'    => "EXPRESS",
      'AAB_CUR'     => $this->getCurrency(),
    );
    
    // calculate mac...
    $mac = implode('&', $fields) . "&" . $this->configuration['privateKey'] . "&";
    $mac = strtoupper(md5($mac));
     
    $codes = array(
      "fi" => "1",
      "sv" => "2",
      "en" => "1", // Theres no english
    );
    
    // fill in the rest of the information
    $fields['AAB_MAC']         = $mac;
    $fields['AAB_RETURN']      = $this->getReturnUrl();
    $fields['AAB_REJECT']      = 
    $fields['AAB_CANCEL']      = $this->getErrorUrl();
    $fields['AAB_LANGUAGE']    = $codes[$this->getLanguage()];
    $fields['AAB_RCV_ACCOUNT'] = $this->configuration['accountNumber']; 
    $fields['AAB_RCV_NAME']    = $this->configuration['accountName'];
    $fields['AAB_CONFIRM']     = 'YES';
    $fields['AAB_KEYVERS']     = '0001';
    // BV_UseBVCookie
    return $fields;
  }
  
  
  /**
   * isPaymentCompleted()
   * @see fpiapi/gateways/FpiapiGateway::isPaymentCompleted()
   */
  public function isPaymentCompleted() {

    $params = &$_REQUEST;
     
    $fields = array(
      isset($params['AAB-RETURN-VERSION']) ? $params['AAB-RETURN-VERSION'] : NULL,
      $this->transaction->getUid(),
      $this->transaction->getReferenceNumber(),
      isset($params['AAB-RETURN-PAID']) ? $params['AAB-RETURN-PAID'] : NULL,
    );
    
    if (!$this->checkFields($fields)) {
      return false;
    }    
    
    $mac = implode('&', $fields) . "&" . $this->configuration['privateKey'] . "&";
    $mac = strtoupper(md5($mac));
     
    return strcmp($mac, $params['AAB-RETURN-MAC']) === 0;

  }
  
  
  /**
   * getQueryFields()
   * @see fpiapi/gateways/FpiapiGateway::getQueryFields()
   */
  public function getQueryFields() {
    
    $codes = array(
      "fi" => "1",
      "sv" => "2",
      "en" => "1", // Theres no english
    );
    
    // First fill in the field used to calculate mac
    $fields = array(
      'CBS_VERSION'	 => "0001",
      'CBS_TIMESTMP' => date("Ymdhmi").$this->transaction->getUid(),
      'CBS_RCV_ID' 	 => $this->configuration['publicKey'],
      'CBS_LANGUAGE' => $codes[$this->getLanguage()],
      'CBS_RESPTYPE' => "html",
      'CBS_RESPDATA' => $this->getReturnUrl(),
      'CBS_STAMP'    => "EXPRESS",
      'CBS_REF'      => $this->transaction->getReferenceNumber(),
      'CBS_ALG'      => '01',
    );
    
    // calculate mac...
    $mac = implode('&', $fields) . "&" . $this->configuration['privateKey'] . "&";
    $mac = strtoupper(md5($mac));
    
    // fill in the rest of the information
    $fields['CBS_MAC']     = $mac;
    $fields['CBS_AMOUNT']  = $this->transaction->getSum();
    $fields['CBS_CUR']     = $this->getCurrency();
    $fields['CBS_KEYVERS'] = '0001';
    // BV_UseBVCookie
    return $fields;
  }
  
  
  
  /**
   * getQueryResult()
   * @see fpiapi/gateways/FpiapiGateway::getQueryResult()
   */
  public function getQueryResult() {
    
    $params = &$_REQUEST;
    
    $fields = array(
      isset($params["CBS_VERSION"]) ? $params["CBS_VERSION"] : NULL,
      isset($params["CBS_TIMESTMP"]) ? $params["CBS_TIMESTMP"] : NULL,
      isset($params["CBS_RCV_ID"]) ? $params["CBS_RCV_ID"] : NULL,
      isset($params["CBS_RESPCODE"]) ? $params["CBS_RESPCODE"] : NULL,
      isset($params["CBS_STAMP"]) ? $params["CBS_STAMP"] : NULL,
      isset($params["CBS_REF"]) ? $params["CBS_REF"] : NULL,
      isset($params["CBS_AMOUNT"]) ? $params["CBS_AMOUNT"] : NULL,
      isset($params["CBS_CUR"]) ? $params["CBS_CUR"] : NULL,
      isset($params["CBS_PAID"]) ? $params["CBS_PAID"] : NULL,
      isset($params["CBS_ALG"]) ? $params["CBS_ALG"] : NULL,
    );
   
    $mac = implode('&', $fields) . "&" . $this->configuration['privateKey'] . "&";
    $mac = strtoupper(md5($mac));
    
    if (!isset($params['CBS_MAC']) || $mac != $params['CBS_MAC']) {
       throw new FpiapiException("MAC mismatch", FPIAPI_EXCEPTION_MAC_ERROR);
    }
    
    
    switch ($params['CBS_RESPCODE']) {
      case 'NotFound':
        throw new FpiapiException("Payment not found", FPIAPI_EXCEPTION_NOT_FOUND);
      case 'Error':
        throw new FpiapiException("Error", FPIAPI_EXCEPTION_ERROR);
    }
    
    $qr = new FpiapiQueryResult();
    
    $qr->setSum($params['CBS_AMOUNT']);
    $qr->setUid($params['CBS_STAMP']);
    $qr->setReferenceNumber($params['CBS_REF']);
    
    
    return $qr;
  }
    
  
  
}