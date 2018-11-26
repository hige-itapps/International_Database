<?php
/*This file serves as the project's RESTful API. Simply send a get request to this file with a specified function name, with additional POST data when necessary.*/

/*Admin validation*/
include_once(dirname(__FILE__) . "/../CAS/CAS_session.php");

/*Get DB connection*/
include_once(dirname(__FILE__) . "/../server/DatabaseHelper.php");

/*for sending emails*/
include_once(dirname(__FILE__) . "/../server/EmailHelper.php");

/*Logger*/
include_once(dirname(__FILE__) . "/../server/Logger.php");

$logger = new Logger(); //for logging to files
$database = new DatabaseHelper($logger); //database helper object used for some verification and insertion
$emailHelper = new EmailHelper($logger); //email helper for sending custom emails

$returnVal = []; //initialize return value as empty. If there is an error, it is expected to be set as $returnVal["error"].

//For creating a new profile or editing an existing one. If editing, pass in 'editing' as a GET parameter
if (array_key_exists('create_profile', $_GET)) {
    $returnVal["success"] = false; //set to true if there are no errors after validation & running
    $returnVal["errors"] = []; //push errors to this array if any arise

    //define error messages
    $requiredError = "This field is required";
    $tooLongError = "Field exceeds maximum character limit";
    $phoneDigitsError = "Only digits (0-9) are allowed, please do not include any other formatting";
    $wmichEmailOnlyError = "This address must be a wmich.edu address";
    $nonWmichEmailOnlyError = "This address must not be a wmich.edu address";
    $emailFormatError = "This address is not a valid email address";
    $nonUniqueEmailError = "This address is already in use";

    //find out if editing
    $editing = isset($_GET["editing"]) ? true : false;

    //get the login email first to verify that it doesn't already exist in the database and that there is a pending code for it
    $login_email = isset($_POST["login_email"]) ? trim(json_decode($_POST["login_email"], true)) : null; //login email
    $primaryEmail = $login_email; //by default, set the primary email to the login email address

    //get the original alternate email if there was one
    $alternate_email_original = isset($_POST["alternate_email_original"]) && !empty(trim($_POST["alternate_email_original"])) ? trim(json_decode($_POST["alternate_email_original"], true)) : null; //original alternate email

    if(!$editing){ //if creating, make sure email address is unique, and that there is a pending code for it
        if($database->doesLoginEmailExist($login_email)){
            $returnVal["errors"]["other"] = "This address is already in use with another profile!";
        }
        else if(!boolval($database->isCodePending($login_email))){ //make sure there is a pending code for it
            $returnVal["errors"]["other"] = "There is currently no pending code for this profile.";
        }
    }
    else{ //if editing, make sure the primary email address has a pending code
        if(!empty($alternate_email_original)){$primaryEmail = $alternate_email_original;}  //set the primary address to the original alternate email if there was one

        if(!boolval($database->isCodePending($primaryEmail))){ //make sure there is a pending code for it
            $returnVal["errors"]["other"] = "There is currently no pending code for this profile.";
        }
    }
    

    //If no errors yet, then proceed with data validation
    if(empty($returnVal["errors"])){
        //set vars if possible (also trim excess whitespace off strings where possible)
        $firstname = isset($_POST["firstname"]) ? trim(json_decode($_POST["firstname"], true)) : null; //first name
        $lastname = isset($_POST["lastname"]) ? trim(json_decode($_POST["lastname"], true)) : null; //last name
        $affiliations = isset($_POST["affiliations"]) ? trim(json_decode($_POST["affiliations"], true)) : null; //affiliations
        $alternate_email = isset($_POST["alternate_email"]) && !empty(trim(json_decode($_POST["alternate_email"]))) ? trim(json_decode($_POST["alternate_email"], true)) : null; //alternate email
        $phone = isset($_POST["phone"]) ? trim(json_decode($_POST["phone"], true)) : null; //phone number
        $issues_expertise_other = isset($_POST["issues_expertise_other"]) ? trim(json_decode($_POST["issues_expertise_other"], true)) : null; //other issues
        $regions_expertise_other = isset($_POST["regions_expertise_other"]) ? trim(json_decode($_POST["regions_expertise_other"], true)) : null; //other regions
        $countries_expertise_other = isset($_POST["countries_expertise_other"]) ? trim(json_decode($_POST["countries_expertise_other"], true)) : null; //other countries of expertise 
        $social_link = isset($_POST["social_link"]) ? trim(json_decode($_POST["social_link"], true)) : null; //social link
        $issues_expertise = isset($_POST["issues_expertise"]) ? json_decode($_POST["issues_expertise"], true) : null; //issues
        $countries_expertise = isset($_POST["countries_expertise"]) ? json_decode($_POST["countries_expertise"], true) : null; //countries of expertise
        $regions_expertise = isset($_POST["regions_expertise"]) ? json_decode($_POST["regions_expertise"], true) : null; //regions
        $languages = isset($_POST["languages"]) ? json_decode($_POST["languages"]) : null; //languages
        $countries_experience = isset($_POST["countries_experience"]) ? json_decode($_POST["countries_experience"]) : null; //country experiences

        //make sure required fields are present 
        if((empty($issues_expertise)) && (empty($issues_expertise_other))) {$returnVal["errors"]["issuesExpertise"] = $requiredError;} //require >= 1 issue of expertise
        if((empty($countries_expertise)) && (empty($countries_expertise_other))) {$returnVal["errors"]["countriesExpertise"] = $requiredError;} //require >= 1 country of expertise
        if((empty($regions_expertise)) && (empty($regions_expertise_other))) {$returnVal["errors"]["regionsExpertise"] = $requiredError;} //require >= 1 region of expertise
        if(empty($firstname)) {$returnVal["errors"]["firstname"] = $requiredError;} //require first name
        if(empty($lastname)) {$returnVal["errors"]["lastname"] = $requiredError;} //require last name
        if(empty($affiliations)) {$returnVal["errors"]["affiliations"] = $requiredError;} //require affiliations
        //require language proficiency level
        for ($i = 0; $i < count($languages); $i++) {
            if(!property_exists($languages[$i], 'proficiency_level')){
                $returnVal["errors"]["language ".$languages[$i]->id] = $requiredError;
            }
        }
        //require country experiences
        foreach($countries_experience as $experience) {
            $tempOtherExperience = '';
            if(property_exists($experience, 'other_experience')){$tempOtherExperience = trim($experience->other_experience);}
            if((!property_exists($experience, 'experiences') || empty($experience->experiences)) && empty($tempOtherExperience)){
                $returnVal["errors"]["country ".$experience->id] = $requiredError;
            }
        }

        if (!empty($phone) && !ctype_digit($phone)) {$returnVal["errors"]["phone"] = $phoneDigitsError;} //make sure phone number only includes digits

        if(empty($login_email)){ //make sure login email is specified
            $returnVal["errors"]["loginEmail"] = $requiredError;
        }
        else if(!filter_var($login_email, FILTER_VALIDATE_EMAIL)){ //make sure the email is correctly formatted
            $returnVal["errors"]["loginEmail"] = $emailFormatError;
        }
        else{
            list($em, $domain) = explode('@', $login_email);
            if (strtolower($domain) != "wmich.edu"){ //make sure login email is actually a WMICH email address
                $returnVal["errors"]["loginEmail"] = $wmichEmailOnlyError;
            }
            else if(!$editing){ //if creating, make sure the address is unique
                if($database->doesLoginEmailExist($login_email)){
                    $returnVal["errors"]["loginEmail"] = $nonUniqueEmailError;
                }
            }
        }

        if(!empty($alternate_email)){ //only worry about the alternate email if it's specified
            if(!filter_var($alternate_email, FILTER_VALIDATE_EMAIL)){ //make sure the email is correctly formatted
                $returnVal["errors"]["alternateEmail"] = $emailFormatError;
            }
            else{ 
                list($em, $domain) = explode('@', $alternate_email);
                if (strtolower($domain) == "wmich.edu"){ //make sure alternate email is not a WMICH email address
                    $returnVal["errors"]["alternateEmail"] = $nonWmichEmailOnlyError;
                }
                else if(!$editing){ //if creating, make sure the address is unique
                    if($database->doesAlternateEmailExist($alternate_email)){
                        $returnVal["errors"]["alternateEmail"] = $nonUniqueEmailError;
                    }
                }
                else{ //if editing, make sure the address is unique, ignoring the current profile
                    if($database->doesAlternateEmailExistIgnoreProfile($alternate_email, $login_email)){
                        $returnVal["errors"]["alternateEmail"] = $nonUniqueEmailError;
                    }
                }
            }
        }
    }

    //If there are no errors after all validation, then attempt to insert the data
    if(empty($returnVal["errors"])){
        
        $insertRes = $database->insertProfile($login_email, $firstname, $lastname, $alternate_email, $affiliations, $phone, $issues_expertise_other, $regions_expertise_other, $countries_expertise_other, $social_link, $issues_expertise, $countries_expertise, $regions_expertise, $languages, $countries_experience);
    
        if(isset($insertRes["error"])){ //if there was an error inserting or editing
            if(!$editing){ //creating
                $returnVal["errors"]["other"] = "There was an error inserting your profile into the database: ".$insertRes["error"];
            }
            else{ //editing
                $returnVal["errors"]["other"] = "There was an error updating your profile in the database: ".$insertRes["error"];
            }
        }
        else{ //no errors, so delete pending code
            $database->removeCode($primaryEmail);
        }
    }

    if(empty($returnVal["errors"])){$returnVal["success"] = true;} //if no errors, define success as true
}



//For sending a confirmation code
else if (array_key_exists('send_code', $_GET)) {
    $returnVal["success"] = false; //set to true if there are no errors after validation & running
    $returnVal["error"] = []; //push errors to this array if any arise

    if(isset($_POST["email"]) && isset($_POST["creating"])){ //if email address was sent
        $email = trim(json_decode($_POST["email"]));
        $creating = json_decode($_POST["creating"]);

        if($email !== ''){ //not empty
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) { //valid format
                if($creating){ //if creating new profile, make sure email is a wmu address and isn't associated with any existing profiles
                    list($em, $domain) = explode('@', $email);
                    if (strtolower($domain) != "wmich.edu"){ //make sure login email is actually a WMICH email address
                        $returnVal["error"] = "You must use a wmich.edu address to create a profile!";;
                    }
                    else if($database->doesLoginEmailExist($email)){ //make sure email address is unique
                        $returnVal["error"] = "This address is already in use with another profile!";
                    }
                }
    
                //As long as there were no errors from a new user trying to create a profile above, continue and check to see if the code is pending
                if(empty($returnVal["error"])){
                    if($database->isCodePending($email)){ //code already pending
                        $returnVal["error"] = "Code already pending!";
                    }
                }

                //If no errors yet, check to see if there is already a pending profile for the given email
                if(empty($returnVal["error"])){
                    if($database->isProfilePending($email)){ //update already pending
                        $returnVal["error"] = "An update for this profile is currently pending, so a new code cannot be created!";
                    }
                }
    
                //If there were no errors yet, go ahead and send the confirmation code
                if(empty($returnVal["error"])){
                    //generate a new code, save it to the database, and send it in an email.
                    $totalBytes = 16; //byte length of the string
                    $bytes = openssl_random_pseudo_bytes($totalBytes, $cstrong);
                    $hex   = bin2hex($bytes); //final code in hex
                    $current_timestamp = time(); //current datetime
                    $expiration_timestamp = strtotime('+1 day', $current_timestamp); //add 1 day to the deadline
                    $result = $database->saveCode($email, $hex, $expiration_timestamp); //save to database
                    if(!$result){ //database error
                        $returnVal["error"] = "Error inserting new code into database.";
                    }
                    else{ //no errors so far, continue and send email
                        $emailResult = $emailHelper->codeConfirmationSendEmail($email, $hex, null);
                        //set error codes if any
                        if(!$emailResult["saveSuccess"]){
                            $returnVal["error"] = $emailResult["saveError"];
                        }
                        else if(!$emailResult["sendSuccess"]){
                            $returnVal["error"] = $emailResult["sendError"];
                        }
                    }
                }
            }
            else{
                $returnVal["error"] = "Invalid email address given!";
            }
        }else{ //gvien email was empty
            $returnVal["error"] = "Empty email address given!";
        }
    }else{ //email address not sent
        $returnVal["error"] = "No email address and/or creating variable specified!";
    }

    if(empty($returnVal["error"])){$returnVal["success"] = true;} //if no errors, define success as true
}



//For verifying a confirmation code
else if (array_key_exists('confirm_code', $_GET)) {
    $returnVal["success"] = false; //set to true if there are no errors after validation & running
    $returnVal["error"] = []; //push errors to this array if any arise

    if(isset($_POST["userID"]) && isset($_POST["email"]) && isset($_POST["code"])){ //if userID, email, and code were sent
        $userID = json_decode($_POST["userID"]);
        $email = trim(json_decode($_POST["email"]));
        $code = trim(json_decode($_POST["code"]));

        if(!boolval($database->isCodePending($email))){ //code isn't pending
            $returnVal["error"] = "There is currently no pending code for this profile.";
        }else{
            $res = $database->confirmCode($email, $code); //confirm the code, ge the timestamp if it exists
            if(!$res){ //code/email combo is incorrect
                $returnVal["error"] = "Incorrect code for this profile!";
            }else{ //everything is correct, append additional data for use on the edit profile page
                $bothEmails = $database->getBothEmails($userID);
                $returnVal["login_email"] = $bothEmails["login_email"]; //user's wmu email
                $returnVal["alternate_email"] = $bothEmails["alternate_email"]; //user's optional non-wmu email
                $returnVal["expiration_time"] = $res; //the expiration timestamp of the given code
            }
        } 
    }else{ //email or code not sent
        $returnVal["error"] = "Code, email address, or user ID was not given.";
    }

    if(empty($returnVal["error"])){$returnVal["success"] = true;} //if no errors, define success as true
}



//for adding administrators
else if(array_key_exists('add_admin', $_GET)){
    if(isset($_POST["broncoNetID"]) && isset($_POST["name"])){
        $broncoNetID = json_decode($_POST["broncoNetID"]);
        $name = json_decode($_POST["name"]);

        //must have permission to do this
        if($database->isAdministrator($CASbroncoNetID)){
            try{
                $returnVal = $database->addAdmin($broncoNetID, $name);
            }
            catch(Exception $e){
                $errorMessage = $logger->logError("Unable to insert administrator due to an internal exception: ".$e->getMessage(), $CASbroncoNetID, dirname(__FILE__), true);
			    $returnVal["error"] = "Error: Unable to insert administrator due to an internal exception. ".$errorMessage;
            }
        }
        else{
            $returnVal["error"] = "Permission denied, you are not permitted to add administrators.";
        }
    }
    else{
        $returnVal["error"] = "broncoNetID and/or name is not set";
    }
}



//for getting admins
else if(array_key_exists('get_admins', $_GET)){
    if($database->isAdministrator($CASbroncoNetID)){
        try{
            $returnVal = $database->getAdministrators();
        }
        catch(Exception $e){
            $errorMessage = $logger->logError("Unable to retrieve administrator due to an internal exception: ".$e->getMessage(), $CASbroncoNetID, dirname(__FILE__), true);
			$returnVal["error"] = "Error: Unable to retrieve administrator due to an internal exception. ".$errorMessage;
        }
    }
    else{
        $returnVal["error"] = "Permission denied, you are not permitted to retrieve the administrators list.";
    }
}



//for removing an admin
else if(array_key_exists('remove_admin', $_GET)){
    if(isset($_POST["broncoNetID"])){
        $broncoNetID = json_decode($_POST["broncoNetID"]);
    
        //must have permission to do this
        if($database->isAdministrator($CASbroncoNetID)){
            if(strcasecmp($CASbroncoNetID, $broncoNetID) != 0){ //not trying to remove self
                try{
                    $returnVal = $database->removeAdmin($broncoNetID);
                }
                catch(Exception $e){
                    $errorMessage = $logger->logError("Unable to remove administrator due to an internal exception: ".$e->getMessage(), $CASbroncoNetID, dirname(__FILE__), true);
			        $returnVal["error"] = "Error: Unable to remove administrator due to an internal exception. ".$errorMessage;
                }
            }
            else{
                $returnVal["error"] = "Admins cannot remove themselves";
            }
        }
        else{
            $returnVal["error"] = "Permission denied, you are not permitted to remove administrators.";
        }
    }
    else{
        $returnVal["error"] = "broncoNetID is not set";
    }
}



//for saving a site warning
else if(array_key_exists('save_site_warning', $_GET)){
    if(isset($_POST["siteWarning"])){
        $siteWarning = json_decode($_POST["siteWarning"]);

        if(isset($siteWarning) && trim($siteWarning) !== ''){ //must not be an empty string
            //must have permission to do this
            if($database->isAdministrator($CASbroncoNetID)){
                try{
                    $returnVal = $database->saveSiteWarning(trim($siteWarning));
                }
                catch(Exception $e){
                    $errorMessage = $logger->logError("Unable to save site warning due to an internal exception: ".$e->getMessage(), $CASbroncoNetID, dirname(__FILE__), true);
                    $returnVal["error"] = "Error: Unable to save site warning due to an internal exception. ".$errorMessage;
                }
            }
            else{
                $returnVal["error"] = "Permission denied, you are not permitted to save site warnings.";
            }
        }
        else{
            $returnVal["error"] = "Warning message is empty";
        }
    }
    else{
        $returnVal["error"] = "Warning message is not set";
    }
}
//for clearing a site warning
else if(array_key_exists('clear_site_warning', $_GET)){
    //must have permission to do this
    if($database->isAdministrator($CASbroncoNetID)){
        try{
            $returnVal = $database->saveSiteWarning("");
        }
        catch(Exception $e){
            $errorMessage = $logger->logError("Unable to clear site warning due to an internal exception: ".$e->getMessage(), $CASbroncoNetID, dirname(__FILE__), true);
            $returnVal["error"] = "Error: Unable to clear site warning due to an internal exception. ".$errorMessage;
        }
    }
    else{
        $returnVal["error"] = "Permission denied, you are not permitted to clear site warnings.";
    }
}



//To approve a profile, which also involves deleting an old profile if there is one, and sending an email to the updated profile's primary contact address
else if(array_key_exists('approve_profile', $_GET)){
    //must have permission to do this
    if(isset($CASbroncoNetID) && $database->isAdministrator($CASbroncoNetID)){
        if(isset($_POST["userID"]) && isset($_POST["emailAddress"]) && isset($_POST["name"]) && isset($_POST["update"])){
            $userID = json_decode($_POST["userID"]);
            $emailAddress = json_decode($_POST["emailAddress"]);
            $name = json_decode($_POST["name"]);
            $update = json_decode($_POST["update"]);

            try{
                $returnVal["approve"] = $database->approveProfile($userID, $CASbroncoNetID);
                if(!$returnVal["approve"]["success"]){
                    $errorMessage = $logger->logError("Unable to approve profile.", $CASbroncoNetID, dirname(__FILE__), true);
                    $returnVal["error"] = "Error: Unable to approve profile. ".$errorMessage;
                }
                else{ //successfully approved profile, so now send the email
                    $returnVal["email"] = $emailHelper->profileApprovedEmail($emailAddress, $name, $update, $CASbroncoNetID); //get results of trying to save/send email message
                }
            }catch(Exception $e){
                $errorMessage = $logger->logError("Unable to approve application due to an internal exception: ".$e->getMessage(), $CASbroncoNetID, dirname(__FILE__), true);
			    $returnVal["error"] = "Error: Unable to approve application due to an internal exception. ".$errorMessage;
            }
        }
        else{
            $returnVal["error"] = "UserID, name, email address, and/or update boolean is not set";
        }
    }
    else{
        $returnVal["error"] = "Permission denied, you are not permitted to approve profiles.";
    }
}



//To deny a profile, which involves deleting the pending profile, and sending an email to the deleted profile's primary contact address
else if(array_key_exists('deny_profile', $_GET)){
    //must have permission to do this
    if(isset($CASbroncoNetID) && $database->isAdministrator($CASbroncoNetID)){
        if(isset($_POST["userID"]) && isset($_POST["emailAddress"]) && isset($_POST["name"]) && isset($_POST["update"])){
            $userID = json_decode($_POST["userID"]);
            $emailAddress = json_decode($_POST["emailAddress"]);
            $name = json_decode($_POST["name"]);
            $update = json_decode($_POST["update"]);

            try{
                $returnVal["approve"] = $database->deleteProfile($userID, $CASbroncoNetID);
                if(!$returnVal["approve"]["success"]){
                    $errorMessage = $logger->logError("Unable to deny profile.", $CASbroncoNetID, dirname(__FILE__), true);
                    $returnVal["error"] = "Error: Unable to deny profile. ".$errorMessage;
                }
                else{ //successfully denied profile, so now send the email
                    $returnVal["email"] = $emailHelper->profileDeniedEmail($emailAddress, $name, $update, $CASbroncoNetID); //get results of trying to save/send email message
                }
            }catch(Exception $e){
                $errorMessage = $logger->logError("Unable to deny application due to an internal exception: ".$e->getMessage(), $CASbroncoNetID, dirname(__FILE__), true);
                $returnVal["error"] = "Error: Unable to deny application due to an internal exception. ".$errorMessage;
            }
        }
        else{
            $returnVal["error"] = "UserID, name, email address, and/or update boolean is not set";
        }
    }
    else{
        $returnVal["error"] = "Permission denied, you are not permitted to deny profiles.";
    }
}



//To completely delete a profile
else if(array_key_exists('delete_profile', $_GET)){
    //must have permission to do this
    if(isset($CASbroncoNetID) && $database->isAdministrator($CASbroncoNetID)){
        if(isset($_POST["userID"])){
            $userID = json_decode($_POST["userID"]);

            try{
                $returnVal = $database->deleteProfile($userID, $CASbroncoNetID);
            }catch(Exception $e){
                $errorMessage = $logger->logError("Unable to delete application due to an internal exception: ".$e->getMessage(), $CASbroncoNetID, dirname(__FILE__), true);
                $returnVal["error"] = "Error: Unable to delete application due to an internal exception. ".$errorMessage;
            }
        }
        else{
            $returnVal["error"] = "UserID is not set";
        }
    }
    else{
        $returnVal["error"] = "Permission denied, you are not permitted to delete this profile.";
    }
}



//no appropriate function called
else{
    echo json_encode("No function called");
}

echo json_encode($returnVal); //return results

$database->close(); //close database connections
?>