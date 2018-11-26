<?php

/* This file is used simply to keep track of a BroncoNetID IF a CAS session was already started. 
the $CASbroncoNetID will be made available if the session for it was started with CAS_login.php. Otherwise, this code has no effect. */

// Load the settings from the central config file
require_once 'config.php';
// Load the CAS lib
require_once $phpcas_path . '/CAS.php';

// Enable debugging
phpCAS::setDebug();
// Enable verbose error messages. Disable in production!
phpCAS::setVerbose(true);

// Initialize phpCAS
phpCAS::client(SAML_VERSION_1_1, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
phpCAS::setCasServerCACert($cas_server_ca_cert_path);


// logout if desired
if (isset($_REQUEST['logout'])) {
	phpCAS::logout();
}

// login if desired
if (isset($_REQUEST['login'])) {
	$auth = 0; //init to false
	$auth = phpCAS::checkAuthentication(); //check if authorized
	if($auth != 1) //not yet authorized
	{
		phpCAS::forceAuthentication(); //force CAS authentication
	}
}

if(isset($_SESSION["phpCAS"]["attributes"])) {
    try {
        $CASbroncoNetID = $_SESSION["phpCAS"]["attributes"]["uid"];
    } catch (Exception $e) {
        echo "<h1>Session exception occured! Please refresh the page or try restarting your browser.</h1>";
        echo "<h1>".$e."</h1>";
        exit();
    }
}
?>