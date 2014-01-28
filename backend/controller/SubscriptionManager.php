<?php
/**
 * \brief The SubscriptionManager class contains methods to create and and view details of the logged in user's Subscription.
 * 
 * V1.05
 */

class SubscriptionManager{
	/**
	 * Retrieve all details of the current and removed rateplans on the given user's subscription. The Subscription summary that gets returned will contain a list of Active plans, and removed plans.
	 * @param $accountName Name of the target account
	 * @return Subscription details
	 */

	public static function getCurrentSubscription($accountName){
		$zapi = new zApi();
		//Get Account Id of current User
		$accountId = null;
		$accResult = $zapi->zQuery("SELECT Id FROM Account WHERE Name='".$accountName."'");
		foreach($accResult->result->records as $acc){
			$accountId = $acc->Id;
		}
		
		if($accountId==null){
			throw new Exception('ACCOUNT_DOES_NOT_EXIST');
		}

		//Get Active Subscription
		$activeSub = new Amender_Subscription();

		$subResult = $zapi->zQuery("SELECT Id,Name,Status,Version,PreviousSubscriptionId,ContractEffectiveDate,TermStartDate FROM Subscription WHERE AccountId='".$accountId."' AND Status='Active'");
		foreach($subResult->result->records as $sub){
			$activeSub->subId = $sub->Id;
			$activeSub->Version = $sub->Version;
		}
		
		if($subResult->result->size==0){
			throw new Exception('SUBSCRIPTION_DOES_NOT_EXIST');
		}

		date_default_timezone_set('America/Los_Angeles');
		$activeSub->userEmail = $accountName;
		$activeSub->endOfTermDate = date('Y-m-d\TH:i:s',time());
		$activeSub->startDate = $sub->TermStartDate;

		$curDate = date("Y-m-d") .'T00:00:00.000-08:00';
		//Get Existing Rate Plans
		$activeSub->active_plans = array();
		$activeSub->removed_plans = array();
		$rpResult = $zapi->zQuery("SELECT Id,Name,ProductRatePlanId FROM RatePlan WHERE SubscriptionId='".$activeSub->subId."'");	
		foreach($rpResult->result->records as $rp){
			$newPlan = new Amender_Plan();
			$newPlan->Id = $rp->Id;
			$newPlan->Name = $rp->Name;
			//Get Product Name
			$prpResult = $zapi->zQuery("SELECT Description,ProductId FROM ProductRatePlan WHERE Id='".$rp->ProductRatePlanId."'");
			$newPlan->Description = (isset($prpResult->result->records[0]->Description) ? $prpResult->result->records[0]->Description : '');
			$pResult = $zapi->zQuery("SELECT Name FROM Product WHERE Id='".$prpResult->result->records[0]->ProductId."'");
			$newPlan->ProductName = $pResult->result->records[0]->Name;

			//Get all charges
			$newPlan->amender_charges = array();
			$rpcResult = $zapi->zQuery("SELECT Id,Name,ProductRatePlanChargeId,ChargeModel,ChargeType,UOM,Quantity,ChargedThroughDate FROM RatePlanCharge WHERE RatePlanId='".$rp->Id."'");
			foreach($rpcResult->result->records as $rpc){
				$newCharge = new Amender_Charge();
				$newCharge->Id = $rpc->Id;
				$newCharge->Name = $rpc->Name;
				$newCharge->ChargeModel = $rpc->ChargeModel;
				$newCharge->ProductRatePlanChargeId = $rpc->ProductRatePlanChargeId;
				if($rpc->ChargeModel!='Flat Fee Pricing'){
					$newPlan->uom = $rpc->UOM;
					$newPlan->quantity = $rpc->Quantity;
					$newCharge->Uom = $rpc->UOM;
					$newCharge->Quantity = $rpc->Quantity;
				}
				//For all charges, find maximum ChargedThroughDate					
				if(isset($rpc->ChargedThroughDate)){
					if($rpc->ChargedThroughDate > $activeSub->endOfTermDate){
						$activeSub->endOfTermDate = $rpc->ChargedThroughDate;
					}
				}
				array_push($newPlan->amender_charges, $newCharge);
			}
			array_push($activeSub->active_plans, $newPlan);
		}
		//Get Removed Rate Plans
		$rpResult = $zapi->zQuery("SELECT Id,Name,AmendmentType,AmendmentId,ProductRatePlanId FROM RatePlan WHERE SubscriptionId='".$activeSub->subId."' AND AmendmentType='RemoveProduct'");	
		foreach($rpResult->result->records as $rp){
			$newPlan = new Amender_Plan();
			$newPlan->Id = $rp->Id;
			$newPlan->Name = $rp->Name;

			//Get Product Name
			$prpResult = $zapi->zQuery("SELECT Description,ProductId FROM ProductRatePlan WHERE Id='".$rp->ProductRatePlanId."'");
			$newPlan->Description = (isset($prpResult->result->records[0]->Description) ? $prpResult->result->records[0]->Description : '');
			$pResult = $zapi->zQuery("SELECT Name FROM Product WHERE Id='".$prpResult->result->records[0]->ProductId."'");
			$newPlan->ProductName = $pResult->result->records[0]->Name;
			
			$newPlan->AmendmentId = $rp->AmendmentId;
			$newPlan->AmendmentType = $rp->AmendmentType;
			$newPlan->effectiveDate = 'end of current billing period.';
			
			//Query Amendment for this rate plan to get Effective Removal Date
			$amdResult = $zapi->zQuery("SELECT Id,ContractEffectiveDate FROM Amendment WHERE Id='".$newPlan->AmendmentId."'");		
			foreach($amdResult->result->records as $amd){
				$newPlan->effectiveDate = $amd->ContractEffectiveDate;
			}

			//Get all charges
			$newPlan->amender_charges = array();
			$rpcResult = $zapi->zQuery("SELECT Id,Name,ProductRatePlanChargeId,ChargeModel,ChargeType,UOM,Quantity,ChargedThroughDate FROM RatePlanCharge WHERE RatePlanId='".$rp->Id."'");
			foreach($rpcResult->result->records as $rpc){
				$newCharge = new Amender_Charge();
				$newCharge->Id = $rpc->Id;
				$newCharge->Name = $rpc->Name;
				$newCharge->ChargeModel = $rpc->ChargeModel;
				$newCharge->ProductRatePlanChargeId = $rpc->ProductRatePlanChargeId;
				if(isset($rpc->UOM)){
					$newPlan->uom = $rpc->UOM;
					$newPlan->quantity = $rpc->Quantity;
					$newCharge->Uom = $rpc->UOM;
					$newCharge->Quantity = $rpc->Quantity;
				}
				//For all charges, find maximum ChargedThroughDate					
				if(isset($rpc->ChargedThroughDate)){
					if($rpc->ChargedThroughDate > $activeSub->endOfTermDate){
						$activeSub->endOfTermDate = $rpc->ChargedThroughDate;
					}
				}
				array_push($newPlan->amender_charges, $newCharge);
			}
			array_push($activeSub->removed_plans, $newPlan);
		}
		return $activeSub;
	}

	/**
	 * Creates a subscription after a user has successfully submitted their payment information. Subscribes using email address as account name, contact information from the created payment method, and rate plan data from the given Cart
	 * @param $userEmail User's given Email address
	 * @param $cart An instance of a Cart object that contains all rate plans and quantities that will be used in this subscription.
	 * @param $pmId PaymentMethod ID that was created in Zuora by the Z-Payments Page
	 * @return SubscribeResult. If the email has already been used in this tenant, returns the error string, 'DUPLICATE_EMAIL', If the Payment Method ID passed doesn't exist, returns the error string, 'INVALID_PMID'
	 */
	
	static function subscribeWithCart($userEmail, $pmId, $cart){
		$zapi = new zApi();
		
		if(!AccountManager::checkEmailAvailability($userEmail)){
			return 'DUPLICATE_EMAIL';
		}

		if($cart==null || !isset($cart->cart_items)){
			return 'CART_NOT_INITIALIZED';
		}
		
		//Get Contact information from newly created user
		$pmResult = $zapi->zQuery("SELECT CreditCardAddress1,CreditCardAddress2,CreditCardCity,CreditCardCountry,CreditCardHolderName,CreditCardPostalCode,CreditCardState,Phone FROM PaymentMethod WHERE Id='".$pmId."'");
		if($pmResult->result->size==0){
			return 'INVALID_PMID';
		}
		$pm = $pmResult->result->records[0];

		$HolderName = isset($pm->CreditCardHolderName) ? $pm->CreditCardHolderName : ''; 
		$firstName;
		$lastName;
		
		//Derive first and last name from CardHolderName
		$pos = strpos($HolderName, ' ');
		if ($pos === false) {
			$firstName = $HolderName;
			$lastName = '-';
		} else {
			list($firstName, $lastName) = explode(' ', $HolderName,2);
		}

		$Address1 = isset($pm->CreditCardAddress1) ? $pm->CreditCardAddress1 : ''; 
		$Address2 = isset($pm->CreditCardAddress2) ? $pm->CreditCardAddress2 : ''; 
		$City = isset($pm->CreditCardCity) ? $pm->CreditCardCity : ''; 
		$Country = isset($pm->CreditCardCountry) ? $pm->CreditCardCountry : ''; 
		$PostalCode = isset($pm->CreditCardPostalCode) ? $pm->CreditCardPostalCode : ''; 
		$State = isset($pm->CreditCardState) ? $pm->CreditCardState : '';
		$Phone = isset($pm->Phone) ? $pm->Phone : '';
		
		date_default_timezone_set('America/Los_Angeles');
		$date = date('Y-m-d\TH:i:s',time());
		$today = getdate();
		$mday = $today['mday'];
		
		include("./config.php");
		
		//Set up account
		$account = array(
			"AutoPay" => $defaultAutopay,
			"Currency" => $defaultCurrency,
			"Name" => $userEmail,
			"PaymentTerm" => $defaultPaymentTerm,
			"Batch" => $defaultBatch,
			"BillCycleDay" => $mday,
			"Status" => "Active"
		);
		
		if($makeSfdcAccount==true){
			try{
				//Integrate with Salesforce
				$sfdcResponse = sApi::createSfdcAccount($userEmail);
				if($sfdcResponse->success){
					$account["CrmId"] = $sfdcResponse->id;
				}
			} catch (Exception $e){
				error_log('Account '.$userEmail.' could not be created in Salesforce: ' . $e->getMessage());
			}
		}
	
		//Set up Payment Method
		$pm = array(
			"Id"=> $pmId
		);
	
		//Set up contact
		$bcontact = array(
			"Address1" => $Address1,
			"Address2" => $Address2,
			"City" => $City,
			"Country" => $Country,
			"FirstName" => $firstName,
			"LastName" => $lastName,
			"PostalCode" => $PostalCode,
			"State" => $State,
			"WorkEmail" => $userEmail,
			"WorkPhone" => $Phone
		);
	
		$subscribeOptions = array(
			"GenerateInvoice"=>true,
			"ProcessPayments"=>true,
		);
		$previewOptions = array(
			"EnablePreviewMode"=>false
		);
	
		//Set up subscription
		$subscription = array(
			"ContractEffectiveDate" => $date,
			"ServiceActivationDate" => $date,
			"ContractAcceptanceDate" => $date,
			"TermStartDate" => $date,
			"TermType" => "EVERGREEN",
			"Status" => "Active",
		);
	
		$ratePlanData = SubscriptionManager::getRatePlanDataFromCart($cart);
		
		$subscriptionData = array(
			"Subscription" => $subscription,
			"RatePlanData" => $ratePlanData
		);
		
		$subscribeRequest = array(
			"Account"=>$account,
			"BillToContact"=>$bcontact,
			"PaymentMethod"=>$pm,
			"SubscribeOptions"=>$subscribeOptions,
			"SubscriptionData"=>$subscriptionData
		);

		$subResult = $zapi->zSubscribe($subscribeRequest);
		
		return $subResult;
	}

	/**
	 * Creates dummy subscription with given cart, used to determine the value of the first invoice. Error codes are as follows:
	 * 		EMPTY_CART: No items in the cart
	 * 		RATE_PLAN_DOESNT_EXIST: No match was found for a rate plan
	 * 		RATE_PLAN_EXPIRED: Rate Plan is outside of its effective period
	 * @param $cart An instance of a Cart object that contains all rate plans and quantities that will be used in this subscription.
	 * @return Subscribe_Preview Object with fields for invoice success result, invoiceAmount if successful, and error code if unsuccessful.
	 */
	
	static function previewCart($cart){
		
		//Initialize Subscribe_Preview model
		$subscribePreview = new Subscribe_Preview();

		//If Cart is empty, return an empty cart message
		if(count($cart->cart_items)==0){
			$subscribePreview->invoiceAmount = 0;
			$subscribePreview->success = false;
			$subscribePreview->error = "EMPTY_CART";
			return $subscribePreview;
		}
	
		//Preview with SubscribeRequest
		$zapi = new zApi();
		
		date_default_timezone_set('America/Los_Angeles');
//		$date = date('Y-m-d',time()) . 'T00:00:00';
		$date = date('Y-m-d\T00:00:00',time());

		$today = getdate();
		$mday = $today['mday'];

		include("./config.php");

		//Set up account
		$account = array(
			"AutoPay" => 0,
			"Currency" => $defaultCurrency,
			"Name" => 'TestName',
			"PaymentTerm" => "Net 30",
			"Batch" => "Batch1",
			"BillCycleDay" => $mday,
			"Status" => "Active"
		);

		//Set up contact
		$bcontact = array(
			"Country" => 'United States',
			"FirstName" => 'TestFirst',
			"LastName" => 'TestLast',
			"State" => 'California'
		);

		$subscribeOptions = array(
			"GenerateInvoice"=>true,
			"ProcessPayments"=>false,
		);
		$previewOptions = array(
			"EnablePreviewMode"=>true,
			"NumberOfPeriods"=>1
		);

		//Set up subscription
		$subscription = array(
			"ContractEffectiveDate" => $date,
			"ServiceActivationDate" => $date,
			"ContractAcceptanceDate" => $date,
			"TermStartDate" => $date,
			"TermType" => "EVERGREEN",
			"Status" => "Active",
		);
	
		$ratePlanData = SubscriptionManager::getRatePlanDataFromCart($cart);

		$subscriptionData = array(
			"Subscription" => $subscription,
			"RatePlanData" => $ratePlanData
		);

		$subscribeRequest = array(
			"Account"=>$account,
			"BillToContact"=>$bcontact,
			"SubscribeOptions"=>$subscribeOptions,
			"PreviewOptions"=>$previewOptions,
			"SubscriptionData"=>$subscriptionData
		);

		$subResult = $zapi->zSubscribe($subscribeRequest);

		if($subResult->result->Success==true){
			if(isset($subResult->result->InvoiceData)){
				$subscribePreview->invoiceAmount = $subResult->result->InvoiceData->Invoice->Amount;
				$subscribePreview->success = true;
			} else {
				$subscribePreview->invoiceAmount = number_format((float)0, 2, '.', '');
				$subscribePreview->success = true;
			}
		} else {
			$subscribePreview->success = false;
			if(count($subResult->result->Errors)==1) $subResult->result->Errors = array($subResult->result->Errors);
			$errorResponse = $subResult->result->Errors[0]->Message;
			if(strpos($errorResponse, 'ProductRatePlanId is invalid.')){
				$subscribePreview->error = "RATE_PLAN_DOESNT_EXIST";
			} else if(strpos($errorResponse, 'RatePlan is out of date.')){
				$subscribePreview->error = "RATE_PLAN_EXPIRED";
			} else {
				$subscribePreview->error = $errorResponse;	
			}
		}

		return $subscribePreview;
	}

	/**
	 * Assembles RatePlan information by pulling all rate plans in the user's current cart. This rate plan information is formatted in a way that is understood by the SubscribeRequest object.
	 * @param $cart An instance of a Cart object that contains all rate plans and quantities that will be used in this subscription.
	 * @return RatePlanData for subscribe
	 */
	
	static function getRatePlanDataFromCart($cart){

		$cartItems = $cart->cart_items;

		$ratePlanDatas = array();

		foreach($cartItems as $ci){
			$ratePlanData = array("RatePlan" => array("ProductRatePlanId" => $ci->ratePlanId));
			if($ci->quantity!=null){
				$ratePlanChargeData = array();
				$qty = $ci->quantity;
				$catalogRp = Catalog::getRatePlan($ci->ratePlanId);
				$charges = $catalogRp->charges;
				foreach($charges as $charge){
					if(($charge->ChargeModel== "Per Unit Pricing" ||
							$charge->ChargeModel== "Tiered Pricing" ||
							$charge->ChargeModel== "Volume Pricing") 
							&& $charge->ChargeType!='Usage'){
						array_push($ratePlanChargeData, array("RatePlanCharge"=>array("ProductRatePlanChargeId"=> $charge->Id, "Quantity"=>$qty )));
					}
				}
				$ratePlanData["RatePlanChargeData"] = $ratePlanChargeData;
			}
			array_push($ratePlanDatas, $ratePlanData);
		}
		return $ratePlanDatas;
	}
	
}

?>