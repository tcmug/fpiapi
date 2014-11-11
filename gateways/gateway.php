<?php
/**
 * Baseclass for all gateways
 * 
 * TODO: Classes should get default values from the factory
 * 
 */

// TODO: more comments

class FpiapiGateway {
  
  protected $errorUrl; // Cancellation and error url
  protected $returnUrl; // Return url
  protected $currency; // Used currency (Support depends on banks)
  protected $language; // Used language (eg. "fi","en","sv")
  
  protected $postUrl; // This gateways post url (where to post the payment form)
  protected $queryUrl; // Payment query url (mostly untested)
  protected $refundUrl; // Refund url (unimplemented)

  protected $transaction; // The transaction object (FpiapiTransaction
  protected $configuration; // Gateways configuration
  protected $name; // Gateways name (Visible name)
  
  protected $paymentAbility; // Gateway can do payments (should be always true for real gateways)
  protected $queryAbility; // Gateway can do queries about payments
  protected $refundAbility; // Gateway can do refunds


  /**
   * Constructor
   * Set some defaults; inheriting classes must always call parents constructor
   */
  public function __construct() {
    $this->hasPaymentAbility = false;
    $this->hasQueryAbility = false;
    $this->hasRefundAbility = false;
    
    $this->transaction = null;
    $this->name = null;
    $this->currency = "EUR";
    $this->language = "fi";
   }
  
  /**
   * getPaymentUrl
   * retreive the forms post URL
   */
  public function getPaymentUrl() {
    return $this->postUrl;
  }
  
  /**
   * setPaymentUrl
   * set the forms post URL
   */
  public function setPaymentUrl($url) {
    $this->postUrl = $url;
  }

  /**
   * getPaymentUrl
   * retreive the query url 
   */
  public function getQueryUrl() {
    return $this->queryUrl;
  }
  
  /**
   * getPaymentUrl
   * retreive the query url 
   */
  public function getRefundUrl() {
    return $this->refundUrl;
  }  
    
  /**
   * getPaymentFields
   * retreive the (hidden) fields for the form
   */  
  public function getPaymentFields() {
    $fields = array(
        "RETURN_URL" => $this->getReturnUrl(),
        "ERROR_URL" => $this->getErrorUrl()
    );
    $str = implode("&", $fields);
    $fields['MAC'] = md5(strtoupper($str));
    return $fields;
  }
  
  /**
   * getQueryFields
   * Return query specific fields
   */
  public function getQueryFields() {
    return array();
  }
  
  /**
   * getQueryResult
   * Get the result of a payment query
   */
  public function getQueryResult() {
    return null;
  }
  
  /**
   * getRefundFields
   * Get the fields for a refund action
   */
  public function getRefundFields() {
    return array();
  }  

  /**
   * Fetch an image for the site
   */
  public function getImageUrl() {
    return 'images/' . get_class($this) . '.gif';
  }
  
  /**
   * getName
   * retreive the name of the gateway
   */
  public function getName() {
    return $this->name;
  }
  


  /**
   * setTransaction
   * Set the fpiapi_transaction for the gateway
   * @param fpiapi_transaction $t
   */
  public function setTransaction(FpiapiTransaction $t) {
    $this->transaction = $t;
  }

  /**
   * getConfiguration
   * Get configuration for the bank
   * @param array $config
   */
  public function getConfiguration($config) {
    return $this->configuration;
  }  
  
  /**
   * setConfiguration
   * Set configuration for the bank
   * @param array $config
   */
  public function setConfiguration($config) {
    $this->configuration = $config;
  }

  
  /**
   * isPaymentCompleted
   * Returns true if payment has been completed and false if not
   */
  public function isPaymentCompleted() {
    return false;
  }
 
  
  
  /**
   * setReturnUrl
   * Set the return URL for the gateways
   * @param $url
   */
  public function setReturnUrl($url) {
    $this->returnUrl = $url;
  }
  
  /**
   * getReturnUrl
   * Get the return URL for the gateways
   * Enter description here ...
   */
  public function getReturnUrl() {
    return $this->returnUrl;
  }
  
  
  /**
   * setErrorUrl
   * Set the error URL for the gateways
   * @param unknown_type $url
   */
  public function setErrorUrl($url) {
    $this->errorUrl = $url;
  }
    
  /**
   * getErrorUrl
   * Get the error URL for the gateways
   */
  public function getErrorUrl() {
    return $this->errorUrl;
  }
  
  /**
   * setCurrency
   * Set the used currency
   */
  public function setCurrency($c) {
    $this->currency = $c;
  }
  
  /**
   * getCurrency
   * Get the used currency
   */
  public function getCurrency() {
    return $this->currency;
  }
  
  /**
   * checkFields
   * Verifies that the rows in $fields have values
   */
  public function checkFields(array $fields) {
  	foreach ($fields as $k => $v) {
  	  if (!isset($v) || $v == "") {
  	  	return FALSE;
  	  }
  	}
  	return TRUE;
  } 

  /**
   * setLanguage
   * Set the language (fi, sv, en) 
   * Note that not all banks support languages
   * @param string $lang
   */
  public function setLanguage($lang) {
    $this->language = $lang;
  }
  
  /**
   * getLanguage
   * Get the set language
   */
  public function getLanguage() {
    return $this->language;
  }
    

  /**
   * hasPaymentAbility
   * Does the gateway support payments? (We don't really need this function or do we?)
   */
  public function hasPaymentAbility() {
    return $this->hasPaymentAbility;
  }

  /**
   * hasQueryAbility
   * Does the gateway support making queries about payments?
   */
  public function hasQueryAbility() {
    return $this->hasQueryAbility;
  }
  
  /**
   * hasRefundAbility
   * Does the gateway support making refunds
   */
  public function hasRefundAbility() {
    return $this->hasRefundAbility;
  }
    
  
  /**
   * filterEmptyFields
   * Remove empty fields from an array
   * @param array $fields
   */
  public function filterEmptyFields($fields) {
    foreach ($fields as $k => $value) {
      if (empty($value)) {
        unset($fields[$k]);
      }
    }
    return $fields;
  }
  
  
  
} 



