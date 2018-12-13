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

//find the number of pending profiles to alert the administrator about
$numberOfPendingProfiles = $database->getNumberOfPendingProfiles();

//only send out a message if there is at least 1 pending profile
if($numberOfPendingProfiles > 0){
    $emailHelper->pendingProfilesEmail($numberOfPendingProfiles, 'automatic pending profiles emailer');
}

?>