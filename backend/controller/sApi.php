<?php

/**
* \brief The sApi class is used to interface with Salesforce API
* 
* V1.05
*/
class sApi{
	/**
	 * Creates an account in Salesforce with the given name
	 * @param $accountName Account Name of new Salesforce Account
	 * @return a response result containing a 'success' result and an 'id' of the created account if successful.
	 */
	static function createSfdcAccount($accountName){
		include("./config.php");
	
		$mySforceConnection = new SforceEnterpriseClient();
		$mySforceConnection->createConnection($SfdcWsdl);
		$mySforceConnection->login($SfdcUsername, $SfdcPassword.$SfdcSecurityToken);

		$records = array();
	
		$records[0] = new stdclass();
		$records[0]->Name = $accountName;
	
		$response = $mySforceConnection->create($records, 'Account');
	
		return $response[0];
	}
}

?>