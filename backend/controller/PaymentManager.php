<?php
/**
 * \brief The PaymentManager class manages existing PaymentMethods, and create Source URLs to generate Iframes to enter new credit cards.
 *
 * V1.05
 */
class PaymentManager{

	/**
	 * Generates a URL for a new subscriber to enter credit card and contact information.
	 * @return New PaymentMethod URL
	 */
	public static function getNewAccountUrl(){
		$URL = PaymentManager::generateUrl() . "&retainValues=true";
		return $URL;
	}
	
	/**
	 * Generates a URL for an existing subscriber to enter an additional credit card. Uses the given User's accountId to attach the paymentmethod to their account upon submission.
	 * @param $accountName Name of the target account
	 * @return Existing PaymentMethod URL
	 */
	public static function getExistingIframeSrc($accountName){
		$zapi;
		try{
			$zapi = new zApi();
		} catch(Exception $e){
			throw new Exception('INVALID_ZLOGIN');
		}

		//Get Account with this name
		$accId = NULL;
		$accResult = $zapi->zQuery("SELECT Id FROM Account WHERE Name='".$accountName."'");
		//Get Account Information
		foreach($accResult->result->records as $acc){
			$accId = $acc->Id;
		}
		if($accId == NULL){
			throw new Exception('USER_DOESNT_EXIST');
		}
		$conResult = $zapi->zQuery("SELECT AccountId,Country,Address1,Address2,City,State,PostalCode,WorkPhone FROM Contact WHERE AccountId='".$accId."'");
		if($conResult->result->size==0){
			return null;
		}
		$con;
		foreach($conResult->result->records as $ccon){
			$con = $ccon;
		}
		$URL = PaymentManager::generateUrl();

		$URL = $URL . '&field_accountId=' . $con->AccountId;

		if(isset($con->Country)){
			if(strtolower($con->Country)=='united states'){
				$URL .= '&field_creditCardCountry=USA';
			} else if(strtolower($con->Country)=='canada'){
				$URL .= '&field_creditCardCountry=CAN';
			}
		}
		$URL .= isset($con->State) ? ('&field_creditCardState=' . $con->State) : '';
		$URL .= isset($con->City) ? ('&field_creditCardCity=' . $con->City) : '';
		$URL .= isset($con->PostalCode) ? ('&field_creditCardPostalCode=' . $con->PostalCode) : '';
		$URL .= $con->Address1!=null ? ('&field_creditCardAddress1=' . $con->Address1) : '';
		$URL .= isset($con->Address2) ? ('&field_creditCardAddress2=' . $con->Address2) : '';
		$URL .= isset($con->WorkPhone) ? ('&field_phone=' . $con->WorkPhone) : '';
		$URL .= isset($con->WorkEmail) ? ('&field_email=' . $con->WorkEmail) : '';

		return $URL;
	}

	/**
	 * Used to generate the Base URL for both Existing and New accounts, using the configured Z-Payments Page information
	 * @return Base HPM URL
	 */
	public static function generateUrl(){		
		include("./config.php");

		//generate random token
		$token_length = 32;
		$token_bound = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$token = "";
		while(strlen($token) < $token_length) {
			$token .= $token_bound{mt_rand(0, strlen($token_bound)-1)};
		}

		//get current time in utc milliseconds
		list($usec, $sec) = explode(" ", microtime());
		$timestamp = (float)$sec - 2;
		$queryString = 'id=' . $pageId . '&tenantId=' . $tenantId . '&timestamp=' . $timestamp . '000&token=' . $token;

		//concatenate API security key with query string
		$queryStringToHash = $queryString . $apiSecurityKey;
		//get UTF8 bytes
		$queryStringToHash = utf8_encode($queryStringToHash);
		//create MD5 hash
		$hashedQueryString = md5($queryStringToHash);
		//encode to Base64 URL safe
		$hashedQueryStringBase64ed = strtr(base64_encode($hashedQueryString), '+/', '-_');
		//formulate the url
		$iframeUrl = $appUrl . "/apps/PublicHostedPaymentMethodPage.do?" .
			"method=requestPage&" .
			$queryString . "&" .
			"signature=" . $hashedQueryStringBase64ed;
		return $iframeUrl;
	}

	/**
	 * Sets the default payment method of the given user to a different paymentmethod on their account
	 * @param $accountName Name of the target account
	 * @param $pmId ID of new active payment method
	 */
	public static function changePaymentMethod($accountName, $pmId){
		$zapi;
		try{
			$zapi = new zApi();
		} catch(Exception $e){
			throw new Exception('INVALID_ZLOGIN');
		}

		//Get Account with this name
		$accId = NULL;
		$accRes = $zapi->zQuery("SELECT Id FROM Account WHERE Name='".$accountName."'");
		//Get Account Information
		foreach($accRes->result->records as $acc){
			$accId = $acc->Id;
		}
		if($accId == NULL){
			throw new Exception('ACCOUNT_DOESNT_EXIST');
		}

		$uAcc = array(
			"Id"=>$accId,
			"DefaultPaymentMethodId"=>$pmId
		);
		$objs = array($uAcc);

		$updResult = $zapi->zCreateUpdate('update',$objs,'Account');

		return $updResult;
	}

	/**
	 * Deletes the selected payment method from the logged in user's account
	 * @param $pmId ID of payment method to be removed
	 */
	public static function removePaymentMethod($pmId){
		$zapi = new zApi();

		$deleteResult = $zapi->zDelete(array($pmId),'PaymentMethod');

		return $deleteResult;
	}
}

?>