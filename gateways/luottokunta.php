<?php
/**
 * Gateway for Luottokunta
 * 
 * UNTESTED
 *
 */

class FpiapiGatewayLuottokunta extends FpiapiGateway {
  

  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->name = "Luottokunta";
    $this->postUrl = 'https://dmp2.luottokunta.fi/dmp/html_payments';
    $this->hasPaymentAbility = true;
  }
  
  /**
   * getPaymentFields()
   * @see fpiapi/gateways/FpiapiGateway::getPaymentFields()
   */
  public function getPaymentFields() {
    
    // First fill in the field used to calculate mac
    $fields = array(
      'Merchant_Number'  => $this->configuration['publicKey'],
      'Order_ID'         => $this->transaction->getUid(),
      'Amount'           => round($this->transaction->getSum()*100),
      'Transaction_Type' => '1'
    );
    
    // Calculate mac
    $mac = implode('', $fields) . $this->configuration['privateKey'];
    $mac = strtolower(md5($mac));
    
    // Convert currency to numeric code
    switch($this->getCurrency()) {
      case 'EUR': 
        $currency = 978;
        break;
    }
    
    $fields['Authentication_Mac']    = $mac;
    $fields['Success_Url']           = $this->getReturnUrl();
    $fields['Failure_Url']           = 
    $fields['Cancel_Url']            = $this->getErrorUrl();
    $fields['Device_Category']       = '1';
    $fields['Card_Details_Transmit'] = '0'; // Ask card details at the other end
    $fields['Currency_Code']         = $currency;
   

    return $fields;
  }
  
  
  /**
   * isPaymentCompleted()
   * @see fpiapi/gateways/FpiapiGateway::isPaymentCompleted()
   */
  public function isPaymentCompleted() {
    
    $params = &$_REQUEST;
    
    if (!isset($params['LKMAC']))
      return false;

    if (!isset($params['LKPRC']))
      return false;
      
    // First fill in the field used to calculate mac
    $fields = array(
      'Merchant_Number'  => $this->configuration['publicKey'],
      'Order_ID'         => $this->transaction->getUid(),
      'Amount'           => $this->transaction->getSum(),
      'Transaction_Type' => '1'
    );
    
    if (!$this->checkFields($fields)) {
      return false;
    }
    
    // reverse array to calculate return mac
    $fields = array_reverse($fields);
    
    // Calculate mac
    $mac = implode('', $fields) . $this->configuration['privateKey'];
    $mac = strtolower(md5($mac));
    
    return $mac == $params['LKMAC'];

  }
  
  

}
