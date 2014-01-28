<?php
	session_start();
   	session_regenerate_id();
	$_SESSION = array();
	header('Location: ../login.html');
?>