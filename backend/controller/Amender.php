<?php


/**
 * \brief The Amender class manages Amendments for the logged in user's subscription.
 * 
 * V1.05
 */
class Amender {

	/**
	 * \brief Adds a new ratePlan to the current user's subscription. 
	 * 
	 * New products added to the user's subscription are effective immediately. 
	 * A quantity can also be supplied, that will apply to all recurring and one-time charges on the rate plan that do not use flat fee pricing.
	 * @param $accountName Name of the target account
	 * @param $prpId Product Rate Plan of the amendment to be added.
	 * @param $qty Amount of UOM for the RatePlan being added. A null value can be passed for product rate plans that use flat fee pricing
	 * @param $preview Flag to determine whether this function will be used to create an amendment, or preview an invoice
	 * @return Amend Result
	 */
	public static function addRatePlan($accountName, $prpId, $qty, $preview) {
		$zapi;
		try {
			$zapi = new zApi();
		} catch (Exception $e) {
			throw new Exception("INVALID_ZLOGIN");
		}

		$sub = SubscriptionManager :: getCurrentSubscription($accountName);

		date_default_timezone_set('America/Los_Angeles');
		$date = date('Y-m-d\TH:i:s');
		$amendment = array (
			'EffectiveDate' => $date,
			'Name' => 'Add Rate Plan' . time(),
			'Description' => 'New Rate Plan',
			'Status' => 'Completed',
			'SubscriptionId' => $sub->subId,
			'Type' => 'NewProduct',
			'ContractEffectiveDate' => $date,
			'ServiceActivationDate' => $date,
			'ContractAcceptanceDate' => $date,
			'EffectiveDate' => $date,
			'RatePlanData' => array (
				'RatePlan' => array (
					'ProductRatePlanId' => $prpId,
				)
			)
		);

		//If a quantity has been passed, specify charge data to cover all quantifiable charges on the rate plan being added
		if ($qty != null) {
			$ratePlanChargeData = array ();
			$charges = Catalog :: getRatePlan($prpId)->charges;
			foreach ($charges as $charge) {
				if ($charge->ChargeModel == "Per Unit Pricing" || $charge->ChargeModel == "Tiered Pricing" || $charge->ChargeModel == "Volume Pricing") {
					array_push($ratePlanChargeData, array (
						"RatePlanCharge" => array (
							"ProductRatePlanChargeId" => $charge->Id,
							"Quantity" => $qty
						)
					));
				}
			}
			$amendment['RatePlanData']['RatePlanChargeData'] = $ratePlanChargeData;
		}

		$amendOptions = array (
			"GenerateInvoice" => true,
			"ProcessPayments" => true,
			
		);
		$previewOptions = array (
			"EnablePreviewMode" => $preview
		);

		$amendResult = $zapi->zAmend($amendment, $amendOptions, $previewOptions);
		return $amendResult;
	}

	/**
	 * \brief Remove an existing Rate Plan from the give user's subscription. 
	 * 
	 * Rate Plans removed will take effect at the end of the user's current billing cycle to avoid prorations and credit back.
	 * @param $accountName Name of the target account
	 * @param $rpId Rate Plan ID of the rate plan to be removed
	 * @param $preview Flag to determine whether this function will be used to create an amendment, or preview an invoice
	 * @return Amend Result
	 */
	public static function removeRatePlan($accountName, $rpId, $preview) {
		$zapi;
		try {
			$zapi = new zApi();
		} catch (Exception $e) {
			throw new Exception("INVALID_ZLOGIN");
		}

		$sub = SubscriptionManager :: getCurrentSubscription($accountName);

		date_default_timezone_set('America/Los_Angeles');
		$date = date('Y-m-d\TH:i:s');
		$amendment = array (
			'Name' => 'Remove Rate Plan' . time(),
			'Description' => 'Remove Rate Plan',
			'Status' => 'Completed',
			'SubscriptionId' => $sub->subId,
			'Type' => 'RemoveProduct',
			'ContractEffectiveDate' => $sub->endOfTermDate,
			'ServiceActivationDate' => $sub->endOfTermDate,
			'ContractAcceptanceDate' => $sub->endOfTermDate,
			'EffectiveDate' => $sub->endOfTermDate,
			'RatePlanData' => array (
				'RatePlan' => array (
					'AmendmentSubscriptionRatePlanId' => $rpId
				)
			)
		);
		$amendOptions = array (
			"GenerateInvoice" => false,
			"ProcessPayments" => false,
			
		);
		$previewOptions = array (
			"EnablePreviewMode" => $preview,
			"NumberOfPeriods" => 1
		);

		$amendResult = $zapi->zAmend($amendment, $amendOptions, $previewOptions);
		return $amendResult;
	}
	
}
?>