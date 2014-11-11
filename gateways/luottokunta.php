<?php

/**
 * Gateway for Luottokunta
 * 
 * Refactored gateway to be compatible with the new rules of Luottokunta
 * Uses hash('sha256', $mac) instead of md5($mac) which is the new infosec-policy
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

        $mac_fields = $this->getFieldArrayForRequest();

        $mac_str = implode("&", $mac_fields);
        $mac = hash('sha256', $mac_str);

        $fields = array(
            'Authentication_Mac'     => $mac,
            'Success_Url'            => $this->getReturnUrl(),
            'Failure_Url'            => $this->getErrorUrl(),
            'Cancel_Url'             => $this->getErrorUrl(),
            'Device_Category'        => $this->getDeviceCategory(),
            'Card_Details_Transmit'  => $this->getCardDetailsTransmit(),
            'Currency_Code'          => $this->getCurrencyCode(),
            'Merchant_Number'        => $this->configuration['publicKey'],
            'Order_ID'               => $this->transaction->getUid(),
            'Amount'                 => $this->getFormattedSum(),
            'Transaction_Type'       => $this->getTransactionType(),
        );

        return $fields;
    }

    /**
     * isPaymentCompleted()
     * @see fpiapi/gateways/FpiapiGateway::isPaymentCompleted()
     */
    public function isPaymentCompleted() {

        $params = &$_REQUEST;

        if (!isset($params['LKMAC'])) {
            return false;
        }

        $fields = $this->getFieldArrayForResponse();

        if (!$this->checkFields($fields)) {
            return false;
        }

        $mac_str = implode("&", $fields);
        $mac     = hash('sha256', $mac_str);

        return strcmp(strtolower($mac), strtolower($params['LKMAC'])) === 0;
    }

    protected function getFieldArrayForResponse() {

        $fields = array(
            'Private_key'      => $this->configuration['privateKey'],
            'Transaction_Type' => $this->getTransactionType(),
            'Currency_Code'    => $this->getCurrencyCode(),
            'Amount'           => $this->getFormattedSum(),
            'Order_ID'         => $this->transaction->getUid(),
            'Merchant_Number'  => $this->configuration['publicKey'],
        );

        $LB_fields = array(
            'LKBINCOUNTRY',
            'LKIPCOUNTRY',
            'LKECI',
        );

        // Add LB-fields if they exist in reponse
        foreach ($LB_fields as $LB_field) {
            if (isset($_REQUEST[$LB_field])) {
                $fields[$LB_field] = $_REQUEST[$LB_field];
            }
        }

        return $fields;
    }

    protected function getFieldArrayForRequest() {

        $fields = array(
            'Merchant_Number'  => $this->configuration['publicKey'], 
            'Order_ID'         => $this->transaction->getUid(),            
            'Amount'           => $this->getFormattedSum(),
            'Currency_Code'    => $this->getCurrencyCode(),
            'Transaction_Type' => $this->getTransactionType(),
            'Private_key'      => $this->configuration['privateKey'],
        );

        return $fields;
    }

    protected function getFilteredSum() {
        return str_replace(",", ".", $this->transaction->getSum());
    }

    protected function getFormattedSum() {
        return round($this->getFilteredSum() * 100);
    }

    protected function getTransactionType() {
        return '1';
    }

    protected function getCurrencyCode() {
        return '978';
    }

    protected function getDeviceCategory() {
        return '1';
    }

    protected function getCardDetailsTransmit() {
        return '0';
    }

}