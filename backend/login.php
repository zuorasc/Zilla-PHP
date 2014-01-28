<?php
	function __autoload($class){
	  @include('controller/' . $class . '.php');
	}
	session_start();

	try{
		$username = $_POST['username'];
		$password = $_POST['passwd'];
	} catch (Exception $e){
		loginFailure('Must supply email and password');
		return;
	}
	
	if($username==''){
		loginFailure('Must enter an email address');
		return;
	}

	$loginSuccess = validate($username, $password);

	if($loginSuccess){
		loginSuccess($username);
	} else {
		loginFailure('Invalid email');
		return;
	}

	//Set session header and redirect to account detail page
	function loginSuccess($username){
   		session_regenerate_id();
		$_SESSION['email'] = $username;
		header('Location: ../account_view.html');
	}

	function loginFailure($error){
		echo $error;
	}

	function validate($username, $password){
		if(!AccountManager::checkEmailAvailability($username))
			return true;
		else
			return false;
	}

?>