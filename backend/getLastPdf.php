<?php

	function __autoload($class){
	  @include('controller/' . $class . '.php');
	}
	session_start();

	$body = InvoiceManager::getLastInvoicePdf($_SESSION['email']);
	header("Content-type: application/pdf");
	echo (base64_decode($body));

?>