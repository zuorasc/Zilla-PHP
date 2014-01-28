<link href="css/structure.css" rel="stylesheet" type="text/css"/>
<script>
			
			function callback() {
<?	

if ($_REQUEST['success'] != NULL && $_REQUEST['success'] == 'true') {
?>
				parent.hostedpagecallback_success('<?php echo $_REQUEST['refId']; ?>');
<?
} else if ($_REQUEST['success']==NULL || $_REQUEST['success'] == 'false'){
	$errorCode = isset($_REQUEST['errorCode']) ? $_REQUEST['errorCode'] : '';
	$errorMessage = isset($_REQUEST['errorMessage']) ? $_REQUEST['errorMessage'] : '';
	$errorField_creditCardType = isset($_REQUEST['errorField_creditCardType']) ? $_REQUEST['errorField_creditCardType'] : '';
	$errorField_creditCardNumber = isset($_REQUEST['errorField_creditCardNumber']) ? $_REQUEST['errorField_creditCardNumber'] : '';
	$errorField_creditCardExpirationMonth = isset($_REQUEST['errorField_creditCardExpirationMonth']) ? $_REQUEST['errorField_creditCardExpirationMonth'] : '';
	$errorField_creditCardExpirationYear = isset($_REQUEST['errorField_creditCardExpirationYear']) ? $_REQUEST['errorField_creditCardExpirationYear'] : '';
	$errorField_cardSecurityCode = isset($_REQUEST['errorField_cardSecurityCode']) ? $_REQUEST['errorField_cardSecurityCode'] : '';
	$errorField_creditCardHolderName = isset($_REQUEST['errorField_creditCardHolderName']) ? $_REQUEST['errorField_creditCardHolderName'] : '';
?>
				parent.hostedpagecallback_failure(
					'<?php echo $errorCode; ?>',
					'<?php echo $errorMessage; ?>',
					'<?php echo $errorField_creditCardType; ?>',
					'<?php echo $errorField_creditCardNumber; ?>',
					'<?php echo $errorField_creditCardExpirationMonth; ?>',
					'<?php echo $errorField_creditCardExpirationYear; ?>',
					'<?php echo $errorField_cardSecurityCode; ?>',
					'<?php echo $errorField_creditCardHolderName; ?>');
<?
}
?>
			}
</script><body onload="callback();"/>

<div align="center">
<br/><br/><br/>

<?php
	if(!isset($_REQUEST['success']) || $_REQUEST['success']=='false'){
		echo "
			<div class='error_message'>
				There was an error while processing your card.
				You will be redirected shortly.
			</div>";
	} else {
		echo "
			<div class='success_message'>
				Credit Card validated.
				You will be redirected shortly.
			</div>";
	}
?>

</div>
