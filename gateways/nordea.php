<?php
/**
 * Gateway for Nordea
 *
 */

class FpiapiGatewayNordea extends FpiapiGateway {
  
  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->name = "Nordea e-maksu";
    $this->postUrl = 'https://solo3.nordea.fi/cgi-bin/SOLOPM01';
    $this->queryUrl = 'https://solo3.nordea.fi/cgi-bin/SOLOPM10';
    $this->hasPaymentAbility = true;
    $this->hasQueryAbility = true;
    $this->hasRefundAbility = true;
  }
  
  /**
   * getPaymentFields()
   * @see fpiapi/gateways/FpiapiGateway::getPaymentFields()
   */
  public function getPaymentFields() {
    
    // First fill in the field used to calculate mac
    $fields = array(
      'VERSION' => "0003",
      'STAMP'   => $this->transaction->getUid(),
      'RCV_ID'  => $this->configuration['publicKey'],
      'AMOUNT'  => $this->transaction->getSum(),
      'REF'     => $this->transaction->getReferenceNumber(),
      'DATE'    => "EXPRESS",
      'CUR'     => $this->getCurrency()
    );
    
    // calculate mac...
    $mac = implode('&', $fields)  ."&" . $this->configuration['privateKey'] . "&";
    $mac = strtoupper(md5($mac));
    
    
    $codes = array(
      "fi" => "1",
      "sv" => "2",
      "en" => "3"
    );
    
    // fill in the rest of the information
    $fields['MAC']         = $mac;
    $fields['RETURN']      = $this->getReturnUrl();
    $fields['REJECT']      = 
    $fields['CANCEL']      = $this->getErrorUrl();
    $fields['MSG']         = "";
    $fields['LANGUAGE']    = $codes[$this->getLanguage()];
    $fields['RCV_ACCOUNT'] = $this->configuration['accountNumber'];
    $fields['RCV_NAME']    = $this->configuration['accountName'];
    $fields['CONFIRM']     = 'YES';
    $fields['KEYVERS']     = '0001';

    //$fields['PMTTYPE'] = 'M';

    return $fields;
  }
  
  
  /**
   * isPaymentCompleted()
   * Returns true if payment has been completed and false if not
   */
  public function isPaymentCompleted() {
    
    $params = &$_REQUEST;
      
    $fields = array(
      isset($params['RETURN_VERSION']) ? $params['RETURN_VERSION'] : NULL,
      $this->transaction->getUid(),
      $this->transaction->getReferenceNumber(),
      isset($params['RETURN_PAID']) ? $params['RETURN_PAID'] : NULL
    );
 
    if (!$this->checkFields($fields)) {
      return false;
    } 
    
    $mac = implode('&', $fields) . "&" . $this->configuration['privateKey'] . "&";
    $mac = strtoupper(md5($mac));
     
    return strcmp($mac, $params['RETURN_MAC']) === 0;

  }
 

  /**
   * getQueryFields()
   * @see fpiapi/gateways/FpiapiGateway::getQueryFields()
   */
  public function getQueryFields() {
    
    $fields = array(
      'SOLOPMT_VERSION'  => '0001',
      'SOLOPMT_TIMESTMP' => $this->transaction->getUid(),
      'SOLOPMT_RCV_ID'   => $this->configuration['publicKey'],
      'SOLOPMT_LANGUAGE' => '1',
      'SOLOPMT_RESPTYPE' => 'html',
      'SOLOPMT_RESPDATA' => $this->getReturnUrl(),
      'SOLOPMT_RESPDETL' => 'N',
      'SOLOPMT_STAMP'    => $this->transaction->getUid(),
      'SOLOPMT_REF'      => $this->transaction->getReferenceNumber(),
      'SOLOPMT_AMOUNT'   => $this->transaction->getSum(),
      'SOLOPMT_CUR'      => $this->getCurrency(),
      'SOLOPMT_KEYVERS'  => '0001',
      'SOLOPMT_ALG'      => '01'
    );

    $fields = $this->filterEmptyFields($fields);
    
    // calculate mac...
    $mac = implode('&', $fields) . "&" . $this->configuration['privateKey'] . "&";
    $fields['SOLOPMT_MAC'] = strtoupper(md5($mac));
    
    return $fields;
    
  }
  
  
  /**
   * getQueryResult()
   * @see fpiapi/gateways/FpiapiGateway::getQueryResult()
   */
  public function getQueryResult() {
    
    $params = &$_REQUEST;
    
    $fields = array(
      isset($params["SOLOPMT_VERSION"]) ? $params["SOLOPMT_VERSION"] : NULL,
      isset($params["SOLOPMT_TIMESTMP"]) ? $params["SOLOPMT_TIMESTMP"] : NULL,
      isset($params["SOLOPMT_RCV_ID"]) ? $params["SOLOPMT_RCV_ID"] : NULL,
      isset($params["SOLOPMT_RESPCODE"]) ? $params["SOLOPMT_RESPCODE"] : NULL,
      isset($params["SOLOPMT_STAMP"]) ? $params["SOLOPMT_STAMP"] : NULL,
      isset($params["SOLOPMT_REF"]) ? $params["SOLOPMT_REF"] : NULL,
      isset($params["SOLOPMT_KEYVERS"]) ? $params["SOLOPMT_KEYVERS"] : NULL,
      isset($params["SOLOPMT_ALG"]) ? $params["SOLOPMT_ALG"] : NULL,
    );
    
    $fields = $this->filterEmptyFields($fields);
    
    $mac = implode('&', $fields) . "&" . $this->configuration['privateKey'] . "&";
    $mac = strtoupper(md5($mac));
    
    if (!isset($params['SOLOPMT_MAC']) || $mac != $params['SOLOPMT_MAC']) {
       throw new FpiapiException("MAC mismatch", FPIAPI_EXCEPTION_MAC_ERROR);
    }
    
    switch ($params['SOLOPMT_RESPCODE']) {
      case 'Notfound':
        throw new FpiapiException("Payment not found", FPIAPI_EXCEPTION_NOT_FOUND);
      case 'Error':
        throw new FpiapiException("Error", FPIAPI_EXCEPTION_ERROR);
    }
    
    $qr = new FpiapiQueryResult();
    
    $qr->setSum($params['SOLOPMT_AMOUNT']);
    $qr->setUid($params['SOLOPMT_STAMP']);
    $qr->setReferenceNumber($params['SOLOPMT_REF']);
    $qr->setDueDate($params['SOLOPMT_DATE']);
    
    return $qr;
  }
  
  
}

