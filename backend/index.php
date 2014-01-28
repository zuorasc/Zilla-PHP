<?php

/**
 * \brief index.php is used as a REST layer to interface between the front end HTML files and backend controller methods.
 * Events can be triggered from this page, using "<Base URL>/backend/?type=<ActionName>"
 * 
 * V1.05
 */


function __autoload($class){
  @include('./model/' . $class . '.php');
  @include('./controller/' . $class . '.php');
  @include('./sfdc/' . $class . '.php');
}
session_start();

$debug = 1; //debug mode

$errors = array();
$messages = null;

//debug($client->__getFunctions());

isset($_REQUEST['type']) ? dispatcher($_REQUEST['type']) : '';

function addErrors($field,$msg){
	global $errors;
	$error['field']=$field;
	$error['msg']=$msg;
	$errors[] = $error;
}

function dispatcher($type){
	switch($type) {
		case 'GetInitialCart' : getInitialCart();
		break;
		case 'AddItemToCart' : addItemToCart();
		break;
		case 'RemoveItemFromCart' : removeItemFromCart();
		break;
		case 'EmptyCart' : emptyCart();
		break;
		case 'RefreshCatalog' : refreshCatalog();
		break;
		case 'ReadCatalog' : readCatalog();
		break;
		case 'GetLatestSubscription' : getLatestSubscription();
		break;
		case 'PreviewAddRatePlan' : previewAddRatePlan();
		break;
		case 'AddRatePlan' : addRatePlan();
		break;
		case 'PreviewRemoveRatePlan' : previewRemoveRatePlan();
		break;
		case 'RemoveRatePlan' : removeRatePlan();
		break;
		case 'PreviewUpdateRatePlan' : previewUpdateRatePlan();
		break;
		case 'GetAccountSummary' : getAccountSummary();
		break;
		case 'GetContactSummary' : getContactSummary();
		break;
		case 'GetPaymentMethodSummary' : getPaymentMethodSummary();
		break;
		case 'GetCompleteSummary' : getCompleteSummary();
		break;
		case 'UpdateContact' : updateContact();
		break;
		case 'CheckEmailAvailability' : checkEmailAvailability();
		break;
		case 'UpdatePaymentMethod' : updatePaymentMethod();
		break;
		case 'RemovePaymentMethod' : removePaymentMethod();
		break;
		case 'GetNewIframeSrc' : getNewIframeSrc();
		break;
		case 'GetExistingIframeSrc' : getExistingIframeSrc();
		break;
		case 'SubscribeWithCurrentCart' : subscribeWithCurrentCart();
		break;
		case 'PreviewCurrentCart' : previewCurrentCart();
		break;
		case 'IsUserLoggedIn' : isUserLoggedIn();
		break;
		default : addErrors(null,'no action specified');
	}
}

function emptyCart(){
	global $messages;
	
	$_SESSION['cart'] = new Cart();
	
	$messages = $_SESSION['cart'];
}

function getInitialCart(){
	global $messages;
	
	if(!isset($_SESSION['cart'])){
		emptyCart();
	}
	
	$messages = $_SESSION['cart'];
}

function addItemToCart(){
	global $messages;
	if(!isset($_SESSION['cart'])){
		emptyCart();
	}
	
	$ratePlanId = $_REQUEST['ratePlanId'];
	$quantity = 1;
	if(isset($_REQUEST['quantity']))
		$quantity = $_REQUEST['quantity'];


	if(isset($_SESSION['cart'])){
		$_SESSION['cart']->addCartItem($ratePlanId, $quantity);
	} else {
		addErrors(null,'Cart has not been set up.');
		return;	
	}

	$messages = $_SESSION['cart'];
}

function removeItemFromCart(){
	global $messages;

	$itemId;
	if(isset($_REQUEST['itemId'])){
		$itemId = $_REQUEST['itemId'];
	} else {
		addErrors(null,'Item Id not specified.');
		return;		
	}

	if(isset($_SESSION['cart'])){
		$removed = $_SESSION['cart']->removeCartItem($itemId);
		if(!$removed){
			addErrors(null,'Item no longer exists.');
		}
	} else {
		addErrors(null,'Cart has not been set up.');
		return;		
	}

	$messages = $_SESSION['cart'];
}

function refreshCatalog(){
	global $messages;
	$refreshResult = Catalog::refreshCache();

	$messages = $refreshResult;
}

function readCatalog(){
	global $messages;
	$messages = Catalog::readCache();
}

function getLatestSubscription(){
 	global $messages;

	$userSub = SubscriptionManager::getCurrentSubscription($_SESSION['email']);

	$messages = $userSub;
}

/* Retrieve the subtotal of the amendment being added
 *
 */
function previewAddRatePlan(){
	global $messages;
	
	$prpId = $_REQUEST['itemId'];
	$qty = $_REQUEST['itemQty'];
	
	$amRes = Amender::addRatePlan($_SESSION['email'], $prpId,$qty,true);
	
	$messages = $amRes;
}
function addRatePlan(){
	global $messages;
	
	$prpId = $_REQUEST['itemId'];
	$qty = $_REQUEST['itemQty'];
	
	$amRes = Amender::addRatePlan($_SESSION['email'], $prpId,$qty,false);
	
	if(!$amRes->results->Success){
		if($amRes->results->Errors->Code=='TRANSACTION_FAILED'){
			addErrors(null,$amRes->results->Errors->Message);
			return;
		}
	}
	
	$messages = $amRes;
}

//Remove Product amendments generate no invoices, so this method will instead return the date on which the removal should take effect (End of term)
function previewRemoveRatePlan(){
	global $messages;
	
	$sub = SubscriptionManager::getCurrentSubscription($_SESSION['email']);
	
	$messages = $sub->endOfTermDate;
}
function removeRatePlan(){
	global $messages;
	
	$rpId = $_REQUEST['itemId'];
	
	$amRes = Amender::removeRatePlan($_SESSION['email'], $rpId,false);
	
	if(!$amRes->results->Success){
		addErrors(null,"Unable to remove Rate Plan");
		return;
	}
	
	$messages = $amRes;
}

function previewUpdateRatePlan(){
	global $messages;
	
	$rpId = $_REQUEST['itemId'];
	$qty = $_REQUEST['itemQty'];

	$amRes = new Amender_UpdatePreview();
	$amRes->type = "upgrade";
	$amRes->effectiveDate = date('Y-m-d\TH:i:s',time());
	$amRes->productName = "Zuora Enterprise";
	$amRes->ratePlanName = "Monthly";
	$amRes->newQty = 2;
	$amRes->oldQty = 1;
	$amRes->uom = "Licence";
	$amRes->deltaPrice = 5.00;

	$amRes->success = true;
	
	//$amRes = Amender::updateRatePlan($_SESSION['email'], $rpId,$qty,true);
	
	
	$messages = $amRes;	
}

function getAccountSummary(){
	global $messages;
	
	$accSum = AccountManager::getAccountDetail($_SESSION['email']);
	
	if($accSum=='INVALID_ZLOGIN'){
		addErrors(null,"INVALID_ZLOGIN");
		return;
	} else if($accSum=='USER_DOESNT_EXIST'){
		addErrors(null,"USER_DOESNT_EXIST");
		return;
	}
		
	
	$messages = $accSum;
}

function getContactSummary(){
	global $messages;

	$conSum = AccountManager::getContactDetail($_SESSION['email']);

	$messages = $conSum;
}

function updateContact(){
	global $messages;

	$firstName = $_REQUEST['firstName'];
	$lastName = $_REQUEST['lastName'];
	$address = $_REQUEST['address'];
	$city = $_REQUEST['city'];
	$state = $_REQUEST['state'];
	$postalCode = $_REQUEST['postalCode'];
	$country = $_REQUEST['country'];
	$updRes = AccountManager::updateContact($_SESSION['email'], $firstName, $lastName, $address, $city, $state, $postalCode, $country);

	$messages = $updRes;
}

function getPaymentMethodSummary(){
	global $messages;
	
	$accSum = AccountManager::getPaymentMethodDetail($_SESSION['email']);
	
	$pmSum = $accSum->paymentMethodSummaries;
	
	$messages = $pmSum;
}

function getCompleteSummary(){
	global $messages;
	
	$accSum = AccountManager::getCompleteDetail($_SESSION['email']);
	
	$messages = $accSum;
}

function updatePaymentMethod(){
	global $messages;
	
	$pmId = $_REQUEST['pmId'];
	
	$updRes = PaymentManager::changePaymentMethod($_SESSION['email'], $pmId);
	
	$messages = $updRes->result[0]->Success;
}

function removePaymentMethod(){
	global $messages;
	
	$pmId = $_REQUEST['pmId'];
	
	$delRes = PaymentManager::removePaymentMethod($pmId);
	
	$messages = $delRes;
}

function checkEmailAvailability(){
	global $messages;
	
	$uEmail = $_REQUEST['uEmail'];
	
	$check = AccountManager::checkEmailAvailability($uEmail);
	
	$messages = $check;
}

function getNewIframeSrc(){
	global $messages;
	
	$iframeSrc = PaymentManager::getNewAccountUrl();
	
	$messages = $iframeSrc;
}

function getExistingIframeSrc(){
	global $messages;
	
	$iframeSrc = PaymentManager::getExistingIframeSrc($_SESSION['email']);
	
	$messages = $iframeSrc;
}

function subscribeWithCurrentCart(){
	global $messages;
	
	$userEmail = $_REQUEST['userEmail'];
	$pmId = $_REQUEST['pmId'];
	
	$subRes = SubscriptionManager::subscribeWithCart($userEmail, $pmId, $_SESSION['cart']);

	if($subRes=='DUPLICATE_EMAIL'){
		addErrors(null,"This email address is already in use. Please choose another and re-submit.");
		return;
	}
	if($subRes=='INVALID_PMID'){
		addErrors(null,"There was an error processing this transaction. Please try again.");
		return;
	}
	$_SESSION['email'] = $userEmail;
	
	session_regenerate_id();

	$messages = $subRes;
}


function previewCurrentCart(){
	global $messages;
	
	$subscribePreview = new Subscribe_Preview();
	$subscribePreview = SubscriptionManager::previewCart($_SESSION['cart']);
		
	$messages = $subscribePreview;
	return;
}

function isUserLoggedIn(){
	global $messages;
	
	if(isset($_SESSION['email'])){
		$messages = true;
	} else {
		addErrors(null,"SESSION_NOT_SET");
		return;
	}
}

function debug($a) {
	global $debug ;
	if($debug) {
		echo "/*";
		var_dump($a);
		echo "*/";
	}
}

function output(){
	global $errors,$messages;
	$msg = array();
	
	if(count($errors)>0) {
		debug($errors);
		$msg['success'] = false;
		$msg['msg'] = $errors;
	}
	else {
		$msg['success'] = true;
		if(!is_array($messages)) $messages = array($messages);
		$msg['msg'] = $messages;
	}
	
	debug($msg);
	
	echo json_encode($msg);

}

output();
?>