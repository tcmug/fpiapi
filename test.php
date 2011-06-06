<?php
/**
 * Test and demo case
 */


session_start();

include "fpiapi.php";

if ($_GET['gt'])
  $gatewayName = $_GET['gt'];
else
  $gatewayName = $_SESSION['gateway'];

$_SESSION['gateway'] = $gatewayName;
  
$banks = array(

  "sampopankki" => array(
    "publicKey" => "000000000000",
    "privateKey" => "jumCLB4T2ceZWGJ9ztjuhn5FaeZnTm5HpfDXWU2APRqfDcsrBs8mqkFARzm7uXKd",
  	"merchantId" => "00123456800",
    "contractNumber" => "123456"
  ),
  
  "aktiasppop" => array(
    "publicKey" => "0000000000",
    "privateKey" => "11111111111111111111"
  ),
  
  "osuuspankki" => array(
    "publicKey" => "Esittelymyyja",
    "privateKey" => "Esittelykauppiaansalainentunnus"
  ),
  
  "handelsbanken" => array(
    "publicKey" => "0000000000",
    "privateKey" => "11111111111111111111"
  ),
  
  "nordea" => array(
    "publicKey" => "12345678",
    "privateKey" => "LEHTI"
  ),
  
  "spankki" => array(
    "publicKey" => "SPANKKIESHOPID",
    "privateKey" => "SPANKKI",
    "accountNumber" => "393900-01002369",
    "accountName" => "Testailija"
  ),
    
  "alandsbanken" => array(
    "publicKey" => "AABESHOPID",
    "privateKey" => "PAPEGOJA",
    "accountNumber" => "660100-01130855",
    "accountName" => "Testailija"
  ),
  
  "tapiola" => array(
    "publicKey" => "TAPESHOPID",
    "privateKey" => "PAPUKAIJA",
    "accountNumber" => "363630-01652643",
    "accountName" => "Testailija"
  ),
  
  
  "luottokunta" => array(
    "publicKey" => "7778883",
    "privateKey" => "KTLDJ546GDS"
  )
  
);


foreach ($banks as $bankName => $o) {
  ?><a href="?gt=<?php print $bankName?>"><?php print $bankName; ?></a>&nbsp;<?php
}

?><br/><br/><?php  

if (!$gatewayName) {
  exit;
}

$t = new FpiapiTransaction();
$t->setSum("250,12");
$t->setUid("1");
$t->setDueDate(date("Y-m-d", strtotime("+5 days")));
$t->setReferenceBaseNumber(10000);
$t->setReferencePaddingLength(0);

$gateway = FpiapiFactory::getGateway($gatewayName);

$gateway->setCurrency("EUR");
$gateway->setConfiguration($banks[$gatewayName]);
$gateway->setTransaction($t);
$gateway->setReturnUrl("http://devvy/sites/all/modules/uc_finnish_payments/fpiapi/test.php?ok=1");
$gateway->setErrorUrl("http://devvy/sites/all/modules/uc_finnish_payments/fpiapi/test.php?error=1");

$gateway->setLanguage("fi");

try {
  
  if ($qr = $gateway->getQueryResult()) {
    var_dump($qr);
  }
  
  if ($gateway->isPaymentCompleted()) {
  
    ?><h1>Payment completed</h1><?php
    
  }

} catch (FpiapiException $e) {
  ?><h1><?php print $e->getMessage(); ?></h1><?php   
} 

?>
<form action="<?php print $gateway->getPaymentUrl(); ?>" method="POST">
<?php 
$fields = $gateway->getPaymentFields();
foreach ($fields as $name => $content) {
  ?><span style="width:200px;display:block;float:left;clear:left;text-align:right;padding-right:5px;"><?php print $name; ?></span><input style="display:block;float:left;width:320px" type="text" name="<?php print $name; ?>" value="<?php print $content; ?>"/><br/>
  <?php
}

?>
<div style="clear:both;"></div>
<input type="submit" value="Proceed to the bank"/>
</form>
<?php
if ($gateway->hasQueryAbility()) {
  ?>
  <h3>Query (in development)</h3>
  <form action="<?php print $gateway->getQueryUrl(); ?>" method="POST">
  <?php
  $fields = $gateway->getQueryFields();
  foreach ($fields as $name => $content) {
    ?><span style="width:200px;display:block;float:left;clear:left;text-align:right;padding-right:5px;"><?php print $name; ?></span><input style="display:block;float:left;width:320px" type="text" name="<?php print $name; ?>" value="<?php print $content; ?>"/><br/>
  <?php
  }
  ?>
  <div style="clear:both;"></div>
  <input type="submit" value="Proceed to the bank"/>
  </form>
<?php
}

if ($gateway->hasRefundAbility()) {
  ?>
  <h3>Refund (mostly unimplemented)</h3>
  <form action="<?php print $gateway->getRefundUrl(); ?>" method="POST">
  <?php
  $fields = $gateway->getRefundFields();
  foreach ($fields as $name => $content) {
    ?><span style="width:200px;display:block;float:left;clear:left;text-align:right;padding-right:5px;"><?php print $name; ?></span><input style="display:block;float:left;width:320px" type="text" name="<?php print $name; ?>" value="<?php print $content; ?>"/><br/>
  <?php
  }
  ?>
  <div style="clear:both;"></div>
  <input type="submit" value="Proceed to the bank"/>
  </form>
<?php
}
    
