<?php
/**
 * Gateway for Paytrail payment processor
 *
 * TODO: Language selection (CULTURE) accorting to user's current language.
 * Available locales on Paytrail: fi_FI, sv_SE, en_US
 * TODO: Support for Paytrail notification for successful payment, 
 * see NOTIFY_ADDRESS in API documentation
 */

class FpiapiGatewayPaytrail extends FpiapiGateway {
  
  /**
   * Constructor
   */
  public function __construct() {
    $this->name = "Paytrail";
    $this->postUrl = 'https://payment.paytrail.com/';
    $this->queryUrl = '';
    $this->hasPaymentAbility = true;
    $this->hasQueryAbility = false;
    $this->hasRefundAbility = false;
  }

  public function getDotFormatSum() {
    return str_replace(",", ".", $this->transaction->getSum());
  }
  
  /**
   * getPaymentFields()
   * @see fpiapi/gateways/FpiapiGateway::getPaymentFields()
   */
  public function getPaymentFields() {
    
    // Specify all fields for MAC calculation. Optional fields are left empty.
    $fields = array(
      'MERCHANT_ID'      => $this->configuration['publicKey'],
      'AMOUNT'           => $this->getDotFormatSum(),
      'ORDER_NUMBER'     => $this->transaction->getUid(),
      'REFERENCE_NUMBER' => $this->transaction->getReferenceNumber(),
      'ORDER_DESCRIPTION' => '',
      'CURRENCY'         => 'EUR',
      'RETURN_ADDRESS'   => $this->getReturnUrl(),
      'CANCEL_ADDRESS'   => $this->getErrorUrl(),
      'PENDING_ADDRESS'  => '',
      'NOTIFY_ADDRESS'   => '',
      'TYPE'             => 'S1',
      'CULTURE'          => 'fi_FI',
      'PRESELECTED_METHOD' => '',
      'MODE'             => '',
      'VISIBLE_METHODS'  => '',
      'GROUP'            => '',
    );
    
    // calculate mac and add it to the field array
    $mac = $this->configuration['privateKey'] . '|' . implode('|', $fields);
    $mac = strtoupper(md5($mac));
    
    $fields['AUTHCODE'] = $mac;

    return $fields;
  }
  
  
  /**
   * isPaymentCompleted()
   * @see fpiapi/gateways/FpiapiGateway::isPaymentCompleted()
   */
  public function isPaymentCompleted() {
    
    $params = &$_REQUEST;
    
    $fields = array(
      isset($params['ORDER_NUMBER']) ? $params['ORDER_NUMBER'] : NULL,
      isset($params['TIMESTAMP']) ? $params['TIMESTAMP'] : NULL,
      isset($params['PAID']) ? $params['PAID'] : NULL,
      isset($params['METHOD']) ? $params['METHOD'] : NULL,
    );

    if (!$this->checkFields($fields)) {
      return false;
    }
    
    $mac = implode('|', $fields) . '|' . $this->configuration['privateKey'];
    $mac = strtoupper(md5($mac));
     
    return strcmp($mac, $params['RETURN_AUTHCODE']) === 0;
  }
  
}