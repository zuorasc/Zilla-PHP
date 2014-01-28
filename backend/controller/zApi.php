<?php

/**
 * \brief The zApi Class implements the six basic SOAP API calls supported by Zuora.
 * 
 * V1.005
 * The methods called in this class closely resemble the standard Zuora SOAP calls, and take in the same objects and fields. Instantiating the class will call the Login call using the Zuora credentials set up in config.php. Methods are also provided to cover all types of query, create, update, delete, subscribe, and amend, and take in the standard Zuora object and complex object types. Each of the 'objects' passed into these functions are stored as associative arrays that map each field name to a value. These calls generally return a response object, and any errors and results will be contained within the objects returned by the methods. Complete documentation for Zuora's API calls and objects is maintained at http://knowledgecenter.zuora.com/D_Zuora_API_Developers_Guide
 */

class zApi{
	private $client;
	private $header;

	/**
	 * Creating a new zApi instance will log in using a SOAP login call, and will generate a session header which can be used for subsequent API calls. If the credentials set up in config.php are invalid, the instantiation of the class will throw an exception.
	 */
	public function __construct(){
		include("./config.php");
		if($wsdl==null){
			throw new Exception("Configuration file could not be read.");
		}
		$this->client = new SoapClient($wsdl,array('trace'=>true));
		$this->client->__setLocation($endpoint);

	   # assemble login components
	   $login = array(
			"username"=>$username,
			"password"=>$password
	   );
	
	   # make login call
	   $result = $this->client->login($login);
	   $session = $result->result->Session;	
	   $endpoint = $result->result->ServerUrl;
	
	   # store authentication header
	   $sessionVal = array('session'=>$session);
	   $this->header = new SoapHeader('http://api.zuora.com/',
			'SessionHeader',
			$sessionVal);
	}

	/**
	 * Query() call
	 * @param $q Query string
	 * @return QueryResult
	 */
 	function zQuery($q){
		global $header,$client;
		
		$zoql = array(
			"queryString"=>$q
		);
		
		$queryWrapper = array(
			"query"=>$zoql
		);
		$result = $this->client->__soapCall("query", $queryWrapper, null, $this->header);
		if($result->result->size==1) $result->result->records = array($result->result->records);
		if($result->result->size==0) $result->result->records = array();
		return $result;
	}
	
	/**
	 * zCreateUpdate() combines the Create() and Update() call into one function. 
	 * @param $action 'create' or 'update'
	 * @param $objs A list of 'zobject' arrays with all fields defined for the objects to be inserted/updated
	 * @param $ztype Object Type: Account, Product, etc.
	 * @return SaveResults
	 */
	function zCreateUpdate($action,$objs,$ztype) {
		global $header,$client;

		for($i=0;$i<count($objs);$i++) {
			$zObjs[] = new SoapVar($objs[$i], SOAP_ENC_OBJECT, $ztype, "http://object.api.zuora.com/");
		}
		$zo = array(
			"zObjects"=>$zObjs 
		);
		$cuResult = $this->client->__soapCall($action, $zo, null, $this->header);

		if(isset($cuResult->result) && count($cuResult->result)==1){
			$cuResult->result = array($cuResult->result);
		}
		foreach($cuResult->result as $result){
			if(isset($result->Errors) && count($result->Errors)==1){
				$result->Errors = array($result->Errors);
			}
		}

		return $cuResult;
	}

	/**
	 * Delete() call
	 * @param $ids Zuora IDs of records to be deleted
	 * @param $ztype Object Type: Account, Product, etc.
	 * @return DeleteResults
	 */
	function zDelete($ids,$ztype) {
		global $header,$client;

        $delete = array(
			"type"=>$ztype,
			"ids"=>$ids
        );
        $deleteWrapper = array(
	   		"delete"=>$delete
        );

		$delResult = $this->client->__soapCall('delete', $deleteWrapper, null, $this->header);

		if(isset($delResult->result) && count($delResult->result)==1){
			$delResult->result = array($delResult->result);
		}
		foreach($delResult->result as $result){
			if(isset($result->errors) && count($result->errors)==1){
				$result->errors = array($result->errors);
			}
		}
		return $delResult;
	}
	
	/**
	 * Subscribe() call
	 * @param $zSubReq a SubscriptionRequest object that has been populated with all required fields
	 * @return SubscribeResult
	 */
	function zSubscribe($zSubReq) {
		$subWrapper = array("subscribes"=>$zSubReq);
		$subWrapper = array("subscribe"=>$subWrapper);

		$subResult;
		try{
			$subResult = $this->client->__soapCall("subscribe", $subWrapper, null, $this->header);
			return $subResult;
		} catch (Exception $e){
			echo "zApi Exception: " . $e->getMessage();
			return;
		}
	}
	
	/**
	 * Amend() call
	 * @param $zAmendment Amendment to be created
	 * @param $zAmendOptions Override of default amendment options
	 * @param $zPreviewOptions Override of default preview options
	 * @return AmendResults
	 */
	function zAmend($zAmendment, $zAmendOptions, $zPreviewOptions) {
		global $header,$client;

		# Set up Default amend options and preview options
		if($zAmendOptions==NULL){
			$zAmendOptions = array(
				"GenerateInvoice"=>false,
				"ProcessPayments"=>false
			);
		}
		if($zPreviewOptions==NULL){
			$zPreviewOptions = array(
				"EnablePreviewMode"=>false,
				"NumberOfPeriods"=>1
			);
		}

		# construct amend components
		$amendRequest = array(
			'Amendments'=>$zAmendment,
			'AmendOptions'=>$zAmendOptions,
			'PreviewOptions'=>$zPreviewOptions
		);

		$amendWrapper = array("requests"=>$amendRequest);
		$amendWrapper = array("amend"=>$amendWrapper);

		$amendResult;
		try{
			# Make amend request
			$amendResult = $this->client->__soapCall("amend", $amendWrapper, null, $this->header);
			return $amendResult;
		} catch (Exception $e){
			echo "zApi Exception: " . $e->getMessage();
			return;
		}
	}
	
	/**
	 * When called, prints out the XML Request and Response of the last API call called on this instance
	 */
	public function printXml(){
		$soap_request = $this->client->__getLastRequest(); 
		$soap_response = $this->client->__getLastResponse(); 
		echo("<pre>");
		echo "SOAP request:<br>" . htmlentities($soap_request) . "<br><br>"; 
		echo "SOAP response:<br>" . htmlentities($soap_response) . "<br><br>";
		echo("</pre>");
		echo "<br>";
	}
}

?>