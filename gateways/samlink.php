<?php
/**
 * Samlink based web payment:
 * 	Säästöpankki
 * 	Handelsbanken
 * 
 * Samlink base payment systems offer no support for languages or due dates.
 */


class FpiapiGatewaySamlink extends FpiapiGateway {
  
  /**
   * Constructor
   */
  public function __construct() {
    $this->postUrl = ''; 
    $this->queryUrl = '';
    $this->hasPaymentAbility = true;
    $this->hasQueryAbility = true;
    $this->hasRefundAbility = false;
  }

  /**
   * getPaymentFields()
   * @see fpiapi/gateways/FpiapiGateway::getPaymentFields()
   */
  public function getPaymentFields() {
            
    switch ($this->configuration['version']) {
      case '002':
        $fields = array(
          'NET_VERSION'   => "002",
          'NET_STAMP'     => $this->transaction->getUid(),
          'NET_SELLER_ID' => $this->configuration['publicKey'],
          'NET_AMOUNT'    => $this->transaction->getSum(),
          'NET_REF'       => $this->transaction->getReferenceNumber(),
          'NET_DATE'      => 'EXPRESS',
          'NET_CUR'       => $this->getCurrency(),
          'NET_RETURN'    => $this->getReturnUrl(),
          'NET_CANCEL'    => $this->getErrorUrl(),
          'NET_REJECT'    => $this->getErrorUrl()
        );
        $algorithm = 'md5';
      break;

      case '010': // Specific to aktia
        $fields = array(
          'NET_VERSION'   => "010",
          'NET_STAMP'     => $this->transaction->getUid(),
          'NET_SELLER_ID' => $this->configuration['publicKey'],
          'NET_AMOUNT'    => $this->transaction->getSum(),
          'NET_REF'       => $this->transaction->getReferenceNumber(),
          'NET_DATE'      => 'EXPRESS',
          'NET_CUR'       => $this->getCurrency(),
          'NET_RETURN'    => $this->getReturnUrl(),
          'NET_CANCEL'    => $this->getErrorUrl(),
          'NET_REJECT'    => $this->getErrorUrl(),
          'NET_ALG'       => '03',
          'NET_KEYVERS'   => '0001'
        );
        $algorithm = 'sha256';
      break;

      case '003': // Also the default.
      default:
        $fields = array(
          'NET_VERSION'   => "003",
          'NET_STAMP'     => $this->transaction->getUid(),
          'NET_SELLER_ID' => $this->configuration['publicKey'],
          'NET_AMOUNT'    => $this->transaction->getSum(),
          'NET_REF'       => $this->transaction->getReferenceNumber(),
          'NET_DATE'      => 'EXPRESS',
          'NET_CUR'       => $this->getCurrency(),
          'NET_RETURN'    => $this->getReturnUrl(),
          'NET_CANCEL'    => $this->getErrorUrl(),
          'NET_REJECT'    => $this->getErrorUrl(),
          'NET_ALG'       => '03',
        );
        $algorithm = 'sha256';
      break;
    }

    // Calculate mac accordingly...
    $mac = implode('&', $fields) . "&" . $this->configuration['privateKey'] . "&";
    $mac = strtoupper(hash($algorithm, $mac, FALSE));
    
    if (isset($this->configuration['accountNumber'])) {
      $fields['NET_SELLER_ACC'] = $this->configuration['accountNumber'];
    }

    if (isset($this->configuration['accountName'])) {
      $fields['NET_NAME'] = $this->configuration['accountName'];
    }

    $fields['NET_MAC']        = $mac;
    $fields['NET_CONFIRM']    = "YES";
   
    return $fields;

  }
  
  
  /**
   * isPaymentCompleted()
   * @see fpiapi/gateways/FpiapiGateway::isPaymentCompleted()
   */
  public function isPaymentCompleted() {
    
    $params = &$_REQUEST;
      
    if (!isset($params['NET_RETURN_VERSION'])) {
      return FALSE;
    }

    switch ($params['NET_RETURN_VERSION']) {
      case '002':
        $fields = array(
          isset($params['NET_RETURN_VERSION']) ? $params['NET_RETURN_VERSION'] : NULL,
          $this->transaction->getUid(),
          $this->transaction->getReferenceNumber(),
          isset($params['NET_RETURN_PAID']) ? $params['NET_RETURN_PAID'] : NULL,
          $this->configuration['privateKey']
        );
        $algorithm = 'md5';
      break;

      case '010': // Specific to Aktia
        $fields = array(
          isset($params['NET_RETURN_VERSION']) ? $params['NET_RETURN_VERSION'] : NULL,
          isset($params['NET_ALG']) ? $params['NET_ALG'] : NULL,
          $this->transaction->getUid(),
          $this->transaction->getReferenceNumber(),
          isset($params['NET_RETURN_PAID']) ? $params['NET_RETURN_PAID'] : NULL,
          isset($params['NET_KEYVERS']) ? $params['NET_KEYVERS'] : NULL,
          $this->configuration['privateKey']
        );      
        $algorithm = 'sha256';
      break;

      case '003':
        $fields = array(
          isset($params['NET_RETURN_VERSION']) ? $params['NET_RETURN_VERSION'] : NULL,
          $this->transaction->getUid(),
          $this->transaction->getReferenceNumber(),
          isset($params['NET_RETURN_PAID']) ? $params['NET_RETURN_PAID'] : NULL,
          isset($params['NET_ALG']) ? $params['NET_ALG'] : NULL,
          $this->configuration['privateKey']
        );      
        $algorithm = 'sha256';
      break;

      default:
        return FALSE;
    }
    
    if (!$this->checkFields($fields)) {
      return false;
    }   

    $mac = implode('&', $fields) . '&';
    $mac = strtoupper(hash($algorithm, $mac, FALSE));

    return strcmp($mac, $params['NET_RETURN_MAC']) === 0;

  }
  
  
  /**
   * getQueryFields()
   * @see fpiapi/gateways/FpiapiGateway::getQueryFields()
   */
  public function getQueryFields() {
            
    $fields = array(
      'NET_VERSION'   => "001",
      'NET_SELLER_ID' => $this->configuration['publicKey'],
      'NET_STAMP'     => $this->transaction->getUid(),
      'NET_REF'       => $this->transaction->getReferenceNumber(),
    );
    
    // Calculate mac accordingly...
    $mac = implode('&', $fields) . "&" . $this->configuration['privateKey'] . "&";
    $mac = strtoupper(md5($mac));
    
    $fields['NET_MAC']     = $mac;
    
    $fields['NET_RETURN']  = $this->getReturnUrl();
    $fields['NET_KEYVERS'] = "";

    return $fields;

  }
  
  /**
   * getQueryResult()
   * @see fpiapi/gateways/FpiapiGateway::getQueryResult()
   */
  public function getQueryResult() {
    
    $params = &$_REQUEST;
    
    if (!isset($params['NET_VERSION'])) {
      return null;
    }
      
    $fields = array(
      $params['NET_VERSION'], 
      $params['NET_SELLER_ID'],
      $params['NET_RESPCODE'],
      $params['NET_STAMP'],
      $params['NET_REF'],
      $params['NET_DATE'],
      $params['NET_AMOUNT'],
      $params['NET_CUR'],
      $params['NET_PAID'],
      $this->configuration['privateKey']
    );
    
   
    $fields = $this->filterEmptyFields($fields);
    //print_r($fields);
    
    $mac = implode('&', $fields) . '&';
    $mac = strtoupper(md5($mac));
    
    if ($mac != $params['NET_RETURN_MAC']) {
      throw new FpiapiException("MAC mismatch", FPIAPI_EXCEPTION_MAC_ERROR);
    }
    switch ($params['NET_RESPCODE']) {
      case 'NOTFOUND':
        throw new FpiapiException("Payment not found", FPIAPI_EXCEPTION_NOT_FOUND);
      case 'ERROR':
        throw new FpiapiException("Error", FPIAPI_EXCEPTION_ERROR);
    }
    
    $qr = new FpiapiQueryResult();
    
    $qr->setSum($params['NET_AMOUNT']);
    $qr->setUid($params['NET_STAMP']);
    $qr->setReferenceNumber($params['NET_REF']);
    $qr->setDueDate($params['NET_DATE']);
    
    return $t;
  }
  
  
}



