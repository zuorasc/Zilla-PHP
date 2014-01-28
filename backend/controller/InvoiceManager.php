<?php


/**
 * \brief The InvoiceManager class deals with operations pertaining to a user's invoices.
 * 
 * V1.05
 */
 
class InvoiceManager{

	/**
	 * Retrieves the latest invoice on the given user's account as a PDF body to be rendered by the user's browser.
	 * @param $accountName Name of the target account
	 * @return PDF Body of invoice
	 */
	public static function getLastInvoicePdf($accountName){
		$zapi;
		try{
			$zapi = new zApi();
		} catch(Exception $e){
			return null;
		}
		
		//Get Contact with this email
		$accId;
		$accResult = $zapi->zQuery("SELECT Id FROM Account WHERE Name='".$accountName."'");
		foreach($accResult->result->records as $acc){
			$accId = $acc->Id;
		}

		//Get all Invoices with this AccountId
		$invsResult = $zapi->zQuery("SELECT Id,CreatedDate FROM Invoice WHERE AccountId='".$accId."'");
		
		if($invsResult->result->size>0){	
			//Sort invoices by invoice date
			usort($invsResult->result->records, "InvoiceManager::cmpInvoices");
			
			//Use the first invoice and return the body
			$invResult = $zapi->zQuery("SELECT Body FROM Invoice WHERE Id='".$invsResult->result->records[0]->Id."'");			
			return $invResult->result->records[0]->Body;

		} else {
			return null;
		}
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