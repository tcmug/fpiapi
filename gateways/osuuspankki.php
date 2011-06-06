<?php
/**
 * Gateway for Osuuspankki
 *
 */

class FpiapiGatewayOsuuspankki extends FpiapiGateway {
  
  /**
   * Constructor
   */
  public function __construct() {
    $this->name = "Osuuspankki";
    $this->postUrl = 'https://kultaraha.op.fi/cgi-bin/krcgi';
    $this->queryUrl = 'https://kultaraha.op.fi/cgi-bin/krcgi';
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
      'VERSIO'          => "1",
      'MAKSUTUNNUS'     => $this->transaction->getUid(),
      'MYYJA'           => $this->configuration['publicKey'],
      'SUMMA'           => $this->transaction->getSum(),
      'VIITE'           => $this->transaction->getReferenceNumber(),
      'VALUUTTALAJI'    => $this->getCurrency(),
      'TARKISTE-VERSIO' => "1",
    );
    
    // calculate mac...
    $mac = implode('', $fields) . $this->configuration['privateKey'];
    $mac = strtoupper(md5($mac));
    
    // fill in the rest of the information
    $fields['TARKISTE']        = $mac;
    $fields['PALUU-LINKKI']    = $this->getReturnUrl();
    $fields['PERUUTUS-LINKKI'] = $this->getErrorUrl();
    $fields['VAHVISTUS']       = 'K';
    $fields['VERSIO']          = '1';
    $fields['action_id']       = '701';

    return $fields;
  }
  
  
  /**
   * isPaymentCompleted()
   * @see fpiapi/gateways/FpiapiGateway::isPaymentCompleted()
   */
  public function isPaymentCompleted() {
    
    $params = &$_REQUEST;
     
    $fields = array(
      $params['VERSIO'],
      $this->transaction->getUid(),
      $this->transaction->getReferenceNumber(),
      $params['ARKISTOINTITUNNUS'],
      $params['TARKISTE-VERSIO'],
    );

    if (!$this->checkFields($fields)) {
      return false;
    }    
    
    $mac = implode('', $fields) . $this->configuration['privateKey'];
    $mac = strtoupper(md5($mac));
     
    return $mac == $params['TARKISTE'];

  }
  
  
  /**
   * getQueryFields()
   * @see fpiapi/gateways/FpiapiGateway::getQueryFields()
   */
  public function getQueryFields() {
            
    $fields = array(
      'VERSIO' => '0006',
      'MYYJA' => $this->configuration['publicKey'],
      'KYSELYTUNNUS' => $this->transaction->getUid(),
      'MAKSUTUNNUS' => $this->transaction->getUid(),
      'VIITE' => $this->transaction->getReferenceNumber(),
      'TARKISTE-VERSIO' => "6"
    );
   
    // calculate mac...
    $mac = implode('', $fields) . $this->configuration['privateKey'];
    $mac = strtoupper(md5($mac));
    
    // fill in the rest of the information
    $fields['TARKISTE']        = $mac;
    $fields['PALUU-LINKKI']    = $this->getReturnUrl();
    $fields['PERUUTUS-LINKKI'] = $this->getErrorUrl();
    $fields['action_id']       = '708';

    return $fields;

  }

  
  /**
   * getQueryResult()
   * @see fpiapi/gateways/FpiapiGateway::getQueryResult()
   */
  public function getQueryResult() {
    
    $params = &$_REQUEST;
    
    if (!isset($params['VERSIO'])) {
      return null;
    }
      
    $fields = array(
      $params['VERSIO'], 
      $params['MYYJA'],
      $params['KYSELYTUNNUS'],
      $params['VASTAUSKOODI'],
      $params['MAKSUTUNNUS'],
      $params['VIITE'],
      $params['SUMMA'],
      $params['VALUUTTALAJI'],
      $params['ARKISTOINTITUNNUS'],
      $params['TARKISTE-VERSIO'],
      $this->configuration['privateKey']
    );

    $fields = $this->filterEmptyFields($fields);
    
    $mac = implode('', $fields);
    $mac = strtoupper(md5($mac));
    
    if ($mac != $params['TARKISTE']) {
      throw new FpiapiException("MAC mismatch", FPIAPI_EXCEPTION_MAC_ERROR);
    }
    
    switch ($params['VASTAUSKOODI']) {
      case '0000':
       break;
      case '0001':
        throw new FpiapiException("Payment not found", FPIAPI_EXCEPTION_NOT_FOUND);
      default;
        throw new FpiapiException("Error", FPIAPI_EXCEPTION_ERROR);
    }
    
    $qr = new FpiapiQueryResult();
    
    $qr->setSum($params['SUMMA']);
    $qr->setUid($params['KYSELYTUNNUS']);
    $qr->setReferenceNumber($params['MAKSUTUNNUS']);
    
    return $t;
  }
    
  
  
}