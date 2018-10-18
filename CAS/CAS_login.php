<?php

/**
 *   Example for a simple cas 2.0 client
 *
 * PHP Version 5
 *
 * @file     example_simple.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

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

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
// phpCAS::setNoCasServerValidation();


// logout if desired
if (isset($_REQUEST['logout'])) {
	phpCAS::logout();
}


if(!isset($_SESSION["phpCAS"]["attributes"])) { //if user data hasn't been retrieved yet
	$auth = 0; //init to false
	$auth = phpCAS::checkAuthentication(); //check if authorized
	if($auth != 1) //not yet authorized
	{
		phpCAS::forceAuthentication(); //force CAS authentication
	}
	else
	{
		phpCAS::getAttributes(); //get CAS info from server
	}
}

//set local variables from CAS session
try {
	$CASbroncoNetID = $_SESSION["phpCAS"]["attributes"]["uid"];
	$CASemail = $_SESSION["phpCAS"]["attributes"]["mail"];
} catch (Exception $e) {
	echo "<h1>Session exception occured! Please refresh the page or try restarting your browser.</h1>";
	echo "<h1>".$e."</h1>";
	exit();
}

//make sure all variables aren't blank or null
if(!isset($CASbroncoNetID) || trim($CASbroncoNetID)==='' ||
	!isset($CASemail) || trim($CASemail)==='')
{
	echo "<h1>Error retrieving valid user data! Please refresh the page or try restarting your browser.</h1>";
	exit();
}

//print_r($_SESSION);
?>