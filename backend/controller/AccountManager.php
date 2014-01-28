<?php


/**
 * \brief The AccountManager class manages Account information for the logged in user.
 * 
 * V1.05
 */
 
class AccountManager{
	/**
	 * Get account information from the given user
	 * @param $accountName Name of the target account
	 * @return Account Detail model populated with only account level detail, including account name, due balance, last invoice date, last payment date and last payment amount
	 */
	public static function getAccountDetail($accountName){
		$zapi;
		$accountDetail = new Summary_Account();
		try{
			$zapi = new zApi();
		} catch(Exception $e){
			$accountDetail->success = false;
			$accountDetail->error = 'INVALID_ZLOGIN';
			return $accountDetail;
		}

		//Check if account exists
		$accId = NULL;
		$accResult = $zapi->zQuery("SELECT Id,Name,Balance,LastInvoiceDate FROM Account WHERE Name='".$accountName."'");
		foreach($accResult->result->records as $acc){
			$accId = $acc->Id;
		}
		if($accId == NULL){
			$accountDetail->success = false;
			$accountDetail->error = 'USER_DOESNT_EXIST';
			return $accountDetail;
		}
		
		//Get Account Info
		foreach($accResult->result->records as $acc){
			$accountDetail->Name = $acc->Name;
			$accountDetail->Balance = $acc->Balance;
			$accountDetail->LastInvoiceDate = isset($acc->LastInvoiceDate) ? $acc->LastInvoiceDate : NULL;
			
			$paymentResult = $zapi->zQuery("SELECT Amount,EffectiveDate,CreatedDate FROM Payment WHERE AccountId='".$accId."'");
			if($paymentResult->result->size==0){
				$accountDetail->LastInvoiceAmount = null;
				$accountDetail->LastInvoiceDate = null;
			} else {
				usort($paymentResult->result->records, "AccountManager::cmpPayments");
				$accountDetail->LastPaymentAmount = $paymentResult->result->records[0]->Amount;
				$accountDetail->LastPaymentDate = $paymentResult->result->records[0]->EffectiveDate;
			}
		}

		$accountDetail->success = true;
		return $accountDetail;
	}

	/**
	 * Get contact information from the given user, including address information
	 * @param $accountName Name of the target account
	 * @return Contact Detail model populated with a single contact on this account
	 */
	public static function getContactDetail($accountName){
		$contactDetail = new Summary_Contact();
		try{
			$zapi = new zApi();
		} catch(Exception $e){
			$contactDetail->success = false;
			$contactDetail->error = 'INVALID_ZLOGIN';
			return $contactDetail;
		}

		//Get Contact with this email
		$accId = NULL;
		$accResult = $zapi->zQuery("SELECT Id FROM Account WHERE Name='".$accountName."'");
		foreach($accResult->result->records as $acc){
			$accId = $acc->Id;
		}
		if($accId == NULL){
			$contactDetail->success = false;
			$contactDetail->error = 'USER_DOESNT_EXIST';
			return $contactDetail;
		}
		
		//Get Contact with this email
		$conResult = $zapi->zQuery("SELECT FirstName,LastName,Address1,Address2,City,State,PostalCode,Country FROM Contact WHERE AccountId='".$accId."'");
		if(count($conResult)==0){
			$contactDetail->success = false;
			$contactDetail->error = 'CONTACT_DOESNT_EXIST';
			return $contactDetail;
		}
		foreach($conResult->result->records as $con){
			$contactDetail->FirstName = $con->FirstName;
			$contactDetail->LastName = $con->LastName;
			$contactDetail->Country = $con->Country;
			$contactDetail->State = isset($con->State) ? $con->State : "";
			$contactDetail->Address1 = isset($con->Address1) ? $con->Address1 : "";
			$contactDetail->Address2 = isset($con->Address2) ? $con->Address2 : "";
			$contactDetail->City = isset($con->City) ? $con->City : "";
			$contactDetail->PostalCode = isset($con->PostalCode) ? $con->PostalCode : "";
				
			$contactDetail->success = true;
			return $contactDetail;
		}
	}
	
	/**
	 * Get all payment method information tied to the given user
	 * @param $accountName Name of the target account
	 * @return Account Detail model populated with a list of Payment Method Detail records, including Zuora ID, Card Holder Name, Masked Card Number, Expiration Year, Expiration Month, Card Type, and whether it is currently set as the Account's default payment method
	 */
	public static function getPaymentMethodDetail($accountName){
		$zapi;
		$accountDetail = new Summary_Account();
		try{
			$zapi = new zApi();
		} catch(Exception $e){
			$accountDetail->success = false;
			$accountDetail->error = 'INVALID_ZLOGIN';
			return $accountDetail;
		}

		//Get Account with this name
		$accId = NULL;
		$accResult = $zapi->zQuery("SELECT Id FROM Account WHERE Name='".$accountName."'");
		foreach($accResult->result->records as $acc){
			$accId = $acc->Id;
		}
		if($accId == NULL){
			$accountDetail->success = false;
			$accountDetail->error = 'USER_DOESNT_EXIST';
			return $accountDetail;
		}

		//Get Default Payment Method Id for this accountl
		$defaultPmId;
		$accResult = $zapi->zQuery("SELECT DefaultPaymentMethodId FROM Account WHERE Id='".$accId."'");
		foreach($accResult->result->records as $acc){
			$defaultPmId = $acc->DefaultPaymentMethodId;
		}

		//Get PaymentMethods with this Account Id
		$pmResult = $zapi->zQuery("	SELECT Id,CreditCardHolderName,CreditCardMaskNumber,CreditCardExpirationYear,CreditCardExpirationMonth,CreditCardType from PaymentMethod WHERE AccountId='".$accId."'");

		$pmArray = array();
		foreach($pmResult->result->records as $pm){
			$pmDetail = new Summary_PaymentMethod();
			$pmDetail->Id = $pm->Id;
			$pmDetail->CardHolderName = htmlentities($pm->CreditCardHolderName);
			$pmDetail->MaskedNumber = $pm->CreditCardMaskNumber;
			$pmDetail->ExpirationYear = $pm->CreditCardExpirationYear;
			$pmDetail->ExpirationMonth = $pm->CreditCardExpirationMonth;
			$pmDetail->CardType = $pm->CreditCardType;
			$pmDetail->isDefault = ($pm->Id==$defaultPmId);
			
			array_push($pmArray, $pmDetail);
		}
		$accountDetail->paymentMethodSummaries = $pmArray;
		$accountDetail->success = true;
		return $accountDetail;
	}

	/**
	 * Get all account, contact and payment method information tied to the given user
	 * @param $accountName Name of the target account
	 * @return Account Detail model populated with all information returned by getAccountDetail, getContactDetail and getPaymentMethodDetail
	 */
	public static function getCompleteDetail($accountName){
		$zapi;
		$accountDetail = new Summary_Account();
		try{
			$zapi = new zApi();
		} catch(Exception $e){
			$accountDetail->success = false;
			$accountDetail->error = 'INVALID_ZLOGIN';
			return $accountDetail;
		}

		//Get Account with this name
		$accId = NULL;
		$accResult = $zapi->zQuery("SELECT Id,Name,Balance,LastInvoiceDate,DefaultPaymentMethodId  FROM Account WHERE Name='".$accountName."'");
		//Get Account Information
		foreach($accResult->result->records as $acc){
			$accId = $acc->Id;
		}
		if($accId == NULL){
			$accountDetail->success = false;
			$accountDetail->error = 'USER_DOESNT_EXIST';
			return $accountDetail;
		}
		
		//Get Account Information
		$defaultPmId;
		foreach($accResult->result->records as $acc){
			$accountDetail->Name = $acc->Name;
			$accountDetail->Balance = $acc->Balance;
			$accountDetail->LastInvoiceDate = isset($acc->LastInvoiceDate) ? $acc->LastInvoiceDate : NULL;
			$defaultPmId = isset($acc->DefaultPaymentMethodId) ? $acc->DefaultPaymentMethodId : null;
			
			$paymentResult = $zapi->zQuery("SELECT Amount,EffectiveDate,CreatedDate FROM Payment WHERE AccountId='".$accId."'");
			if($paymentResult->result->size==0){
				$accountDetail->LastPaymentAmount = null;
				$accountDetail->LastInvoiceDate = null;
			} else {
				usort($paymentResult->result->records, "AccountManager::cmpPayments");
				$accountDetail->LastPaymentAmount = $paymentResult->result->records[0]->Amount;
				$accountDetail->LastPaymentDate = $paymentResult->result->records[0]->EffectiveDate;
			}
		}

		//Get Contact with this email
		$contactDetail = new Summary_Contact();
		$conResult = $zapi->zQuery("SELECT FirstName,LastName,Address1,Address2,City,State,PostalCode,Country FROM Contact WHERE AccountId='".$accId."'");
		if(count($conResult)==0){
			$contactDetail->success = false;
			$contactDetail->error = 'CONTACT_DOESNT_EXIST';
		}
		foreach($conResult->result->records as $con){
			$contactDetail->FirstName = $con->FirstName;
			$contactDetail->LastName = $con->LastName;
			$contactDetail->Country = $con->Country;
			$contactDetail->State = isset($con->State) ? $con->State : "";
			$contactDetail->Address1 = isset($con->Address1) ? $con->Address1 : "";
			$contactDetail->Address2 = isset($con->Address2) ? $con->Address2 : "";
			$contactDetail->City = isset($con->City) ? $con->City : "";
			$contactDetail->PostalCode = isset($con->PostalCode) ? $con->PostalCode : "";
				
			$contactDetail->success = true;
		}
		$accountDetail->contactSummary = $contactDetail;

		//Get PaymentMethods with this Account Id
		$pmResult = $zapi->zQuery("	SELECT Id,CreditCardHolderName,CreditCardMaskNumber,CreditCardExpirationYear,CreditCardExpirationMonth,CreditCardType from PaymentMethod WHERE AccountId='".$accId."'");

		$pmArray = array();
		foreach($pmResult->result->records as $pm){
			$pmDetail = new Summary_PaymentMethod();
			$pmDetail->Id = $pm->Id;
			$pmDetail->CardHolderName = htmlentities($pm->CreditCardHolderName);
			$pmDetail->MaskedNumber = $pm->CreditCardMaskNumber;
			$pmDetail->ExpirationYear = $pm->CreditCardExpirationYear;
			$pmDetail->ExpirationMonth = $pm->CreditCardExpirationMonth;
			$pmDetail->CardType = $pm->CreditCardType;
			$pmDetail->isDefault = ($pm->Id==$defaultPmId);
			
			array_push($pmArray, $pmDetail);
		}
		$accountDetail->paymentMethodSummaries = $pmArray;

		$accountDetail->success = true;
		return $accountDetail;
	}


	/**
	 * Update the given user's information
	 * @param $accountName Name of the target account
	 * @param $firstName user's new first name
	 * @param $lastName user's new last name
	 * @param $address user's new address
	 * @param $city user's new first city
	 * @param $state user's new first state
	 * @param $postalCode user's new postalCode
	 * @param $country user's new country
	 * @return SaveResult
	 */
	public static function updateContact($accountName, $firstName, $lastName, $address, $city, $state, $postalCode, $country){
		$zapi;
		try{
			$zapi = new zApi();
		} catch(Exception $e){
			return 'INVALID_ZLOGIN';
		}

		//Get Account with this name
		$accId = NULL;
		$accResult = $zapi->zQuery("SELECT Id,Name,Balance,LastInvoiceDate,DefaultPaymentMethodId  FROM Account WHERE Name='".$accountName."'");
		//Get Account Information
		foreach($accResult->result->records as $acc){
			$accId = $acc->Id;
		}
		if($accId == NULL){
			return 'USER_DOESNT_EXIST';
		}
		
		//Get Contact with this email
		$conResult = $zapi->zQuery("SELECT Id,FirstName,LastName,Country,Address1,City,State,PostalCode FROM Contact WHERE AccountId='".$accId."'");
		if(count($conResult)==0){
			return 'CONTACT_DOESNT_EXIST';
		}
		$con = NULL;
		foreach($conResult->result->records as $icon){
			$con = $icon;
		}
		
		//Create a Contact record with this ID, and all parameters that were passed in.
		$updCon = array(
			"Id"=>$con->Id
		);
		
		if($firstName != NULL && $con->FirstName!=$firstName) $updCon["FirstName"] = $firstName;
		if($lastName != NULL && $con->LastName!=$lastName) $updCon["LastName"] = $lastName;
		if($country != NULL && (!isset($con->Country)  || $con->Country!=$country)) $updCon["Country"] = $country;
		if($address != NULL && (!isset($con->Address1) || $con->Address1!=$address)) $updCon["Address1"] = $address;
		if($postalCode != NULL && (!isset($con->postalCode) || $con->PostalCode!=$postalCode)) $updCon["PostalCode"] = $postalCode;
		if($city != NULL && (!isset($con->city) || $con->City!=$city)) $updCon["City"] = $city;
		if($state != NULL && (!isset($con->state) || $con->State!=$state)) $updCon["State"] = $state;
		
		$updCons = array($updCon);

		$updRes = $zapi->zCreateUpdate('update',$updCons,'Contact');
		
		return $updRes->result;
	}
	
	/**
	 * Determines whether there is already a Contact in Zuora with the given email address
	 * @param $targetEmail
	 * @return true if unique, false if not unique
	 */
	public static function checkEmailAvailability($targetEmail){
		$zapi;
		try{
			$zapi = new zApi();
		} catch(Exception $e){
			return null;
		}
		
		//Disallow apostrophes
		if (strpos($targetEmail, "'") !== false) {
 		   return false;
		}

		//Get Contact with this email
		$accResult = $zapi->zQuery("SELECT Id FROM Account WHERE Name='".$targetEmail."'");

		foreach($accResult->result->records as $acc){
			return false;
		}
		return true;
	}

	/**
	 * Comparator to sort payments by effective date
	 */
	private static function cmpPayments($a, $b)
	{
		if ($a->CreatedDate == $b->CreatedDate) {
			return 0;
		}
		return ($a->CreatedDate > $b->CreatedDate) ? -1 : 1;
	}
	/**
	 * Comparator to sort invoices by invoice date
	 */
	private static function cmpInvoices($a, $b)
	{
		if ($a->CreatedDate == $b->CreatedDate) {
			return 0;
		}
		return ($a->CreatedDate > $b->CreatedDate) ? -1 : 1;
	}
}

?>