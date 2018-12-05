<?php

/*Get DB connection*/
include_once(dirname(__FILE__) . "/../server/DatabaseHelper.php");

/*for sending emails*/
include_once(dirname(__FILE__) . "/../server/EmailHelper.php");

/*Logger*/
include_once(dirname(__FILE__) . "/../server/Logger.php");

$logger = new Logger(); //for logging to files
$database = new DatabaseHelper($logger); //database helper object used for some verification and insertion
$emailHelper = new EmailHelper($logger); //email helper for sending custom emails

//get an array of every primary contact email address for each approved profile in the database
$allEmails = $database->getAllPrimaryEmailAddresses();
$totalEmails = count($allEmails);

//loop through the array of each address, sending out an email to each one
for($i = 0; $i < $totalEmails; $i++){
    //echo $allEmails[$i].PHP_EOL;
}

$database->saveReminderEmailsLastSentTime(); //save the timestamp to the database

?>