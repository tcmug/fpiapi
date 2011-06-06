<?php
/**
 * 
 * Transaction class
 *
 * Used by gateway to create payment form
 *
 */

class FpiapiTransaction {

  private $uid; // Unique numeric ID that identifies this transaction
  private $sum; // Amount to pay
  private $dueDate; // Due date (only a handful of banks support this)
  private $referenceNumber; // Reference number calculated from the uid
  
   // Reference padding length; pad reference number with zeroes up to given number of characters
  static protected $referencePaddingLength;
  
  // Reference base number; added to uid before calculated ref.num.
  static protected $referenceBaseNumber; 

  
  /**
   * Constructor
   */
  public function __construct() {
    
  }  
 
  /**
   * setSum
   * Set sum for the transaction
   * @param string $s
   */
  public function setSum($s) {
    $this->sum = $s;
  }
  
  /**
   * getSum
   * Get the sum for the transaction
   */
  public function getSum() {
    if (is_float($this->sum))
      return number_format($this->sum, 2, ',', '');
    return str_replace(".", ",", $this->sum);
  }
  
  /**
   * setUid
   * Set the transactions unique indentifier
   * This value is used to calculate the reference number
   * @param string $uid
   */
  public function setUid($uid) {
    $this->uid = $uid;
  } 
  
  /**
   * getUid
   * Get the transactions unique identifier
   * Enter description here ...
   */
  public function getUid() {
    return $this->uid;
  } 
  
  /**
   * setDueDate
   * Set due date for the transaction
   * @param string with Y-m-d date $date
   */
  public function setDueDate($date) {
    $this->dueDate = $date;
  } 
  
  /**
   * getDueDate
   * Get due date
   */
  public function getDueDate() {
    return $this->dueDate;
  }   
  
  
  /**
   * _calculateCheckSum()
   * Enter description here ...
   * @param $reference
   */
  private function _calculateCheckSum($reference) {
    
    // from http://pastebin.com/f69d0ce0f
    
    $multipliers = array(7,3,1);
    $length = strlen($reference);
    $reference = str_split($reference);
  
    $sum = 0;
  
    for ($i = $length - 1; $i >= 0; --$i) {
      $sum += $reference[$i] * $multipliers[($length - 1 - $i) % 3];
    }
  
    return (10 - $sum % 10) % 10;
  }

  
  /**
   * getReferenceNumber()
   * Calculate reference number for the given number
   * @param $reference
   */
  public function getReferenceNumber() {
    $reference = $this->uid;
    $reference += $this->getReferenceBaseNumber();
    $str = $reference.$this->_calculateCheckSum($reference);
    $padding = $this->getReferencePaddingLength();
    if ($padding > 0) 
      return str_pad($str, $padding, "0", STR_PAD_LEFT);
    return $str;
  }
    
  
  /**
   * setReferencePaddingLength
   * Set the length of the reference number zero padding
   * Example: if set to 4 and reference number is 11, it becomes 0011
   * @param unknown_type $n
   */
  public function setReferencePaddingLength($n) {
    self::$referencePaddingLength = $n;
  }
  
  /**
   * getReferencePaddingLength
   * Returns the reference padding length
   */
  public function getReferencePaddingLength() {
    return self::$referencePaddingLength;
  }
  
  /**
   * setReferenceBaseNumber
   * Set the reference base number. This number will be added to the UID of the transactions
   * @param $n
   */
  public function setReferenceBaseNumber($n) {
    self::$referenceBaseNumber = $n;
  }
  
  /**
   * getReferenceBaseNumber
   * Returns the reference base number
   */
  public function getReferenceBaseNumber() {
    return self::$referenceBaseNumber;
  }    
  
}

