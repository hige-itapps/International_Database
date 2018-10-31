<?php
/*This file serves as the project's RESTful API. Simply send a get request to this file with a specified function name, with additional POST data when necessary.*/

/*Get DB connection*/
include_once(dirname(__FILE__) . "/../server/DatabaseHelper.php");

/*for sending emails*/
include_once(dirname(__FILE__) . "/../server/EmailHelper.php");

$database = new DatabaseHelper(); //database helper object used for some verification and insertion
$emailHelper = new EmailHelper(); //email helper for sending custom emails

//For creating a new profile
if (array_key_exists('create_profile', $_GET)) {
    $returnVal = []; //associative array to return afterwards
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

    //set vars if possible (also trim excess whitespace off strings where possible)
    if(isset($_POST["issues_expertise"])){$issues_expertise = json_decode($_POST["issues_expertise"], true);} //issues
    if(isset($_POST["issues_expertise_other"])){$issues_expertise_other = trim(json_decode($_POST["issues_expertise_other"], true));} //other issues
    if(isset($_POST["countries_expertise"])){$countries_expertise = json_decode($_POST["countries_expertise"], true);} //countries of expertise
    if(isset($_POST["countries_expertise_other"])){$countries_expertise_other = trim(json_decode($_POST["countries_expertise_other"], true));} //other countries of expertise
    if(isset($_POST["regions_expertise"])){$regions_expertise = json_decode($_POST["regions_expertise"], true);} //regions
    if(isset($_POST["regions_expertise_other"])){$regions_expertise_other = trim(json_decode($_POST["regions_expertise_other"], true));} //other regions
    if(isset($_POST["languages"])){$languages = json_decode($_POST["languages"]);} //languages
    if(isset($_POST["countries_experience"])){$countries_experience = json_decode($_POST["countries_experience"]);} //country experiences
    if(isset($_POST["firstname"])){$firstname = trim(json_decode($_POST["firstname"], true));} //first name
    if(isset($_POST["lastname"])){$lastname = trim(json_decode($_POST["lastname"], true));} //last name
    if(isset($_POST["login_email"])){$login_email = trim(json_decode($_POST["login_email"], true));} //login email
    if(isset($_POST["alternate_email"])){$alternate_email = trim(json_decode($_POST["alternate_email"], true));} //alternate email
    if(isset($_POST["phone"])){$phone = trim(json_decode($_POST["phone"], true));} //phone number
    if(isset($_POST["affiliations"])){$affiliations = trim(json_decode($_POST["affiliations"], true));} //affiliations
    if(isset($_POST["social_link"])){$social_link = trim(json_decode($_POST["social_link"], true));} //social link

    //make sure required fields are present 
    if((!isset($issues_expertise) || empty($issues_expertise)) && (!isset($issues_expertise_other) || empty($issues_expertise_other))) {$returnVal["errors"]["issuesExpertise"] = $requiredError;} //require >= 1 issue of expertise
    if((!isset($countries_expertise) || empty($countries_expertise)) && (!isset($countries_expertise_other) || empty($countries_expertise_other))) {$returnVal["errors"]["countriesExpertise"] = $requiredError;} //require >= 1 country of expertise
    if((!isset($regions_expertise) || empty($regions_expertise)) && (!isset($regions_expertise_other) || empty($regions_expertise_other))) {$returnVal["errors"]["regionsExpertise"] = $requiredError;} //require >= 1 region of expertise
    if(!isset($firstname) || empty($firstname)) {$returnVal["errors"]["firstname"] = $requiredError;} //require first name
    if(!isset($lastname) || empty($lastname)) {$returnVal["errors"]["lastname"] = $requiredError;} //require last name
    if(!isset($affiliations) || empty($affiliations)) {$returnVal["errors"]["affiliations"] = $requiredError;} //require affiliations
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

    if (isset($phone) && !ctype_digit($phone)) {$returnVal["errors"]["phone"] = $phoneDigitsError;} //make sure phone number only includes digits

    if(!isset($login_email) || empty($login_email)){ //make sure login email is specified
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
        else if($database->doesLoginEmailExist($login_email)){ //make sure the address is unique
            $returnVal["errors"]["loginEmail"] = $nonUniqueEmailError;
        }
    }

    if(isset($alternate_email)){ //only worry about the alternate email if it's specified
        if(!filter_var($alternate_email, FILTER_VALIDATE_EMAIL)){ //make sure the email is correctly formatted
            $returnVal["errors"]["alternateEmail"] = $emailFormatError;
        }
        else{ 
            list($em, $domain) = explode('@', $alternate_email);
            if (strtolower($domain) == "wmich.edu"){ //make sure alternate email is not a WMICH email address
                $returnVal["errors"]["alternateEmail"] = $nonWmichEmailOnlyError;
            }
            else if($database->doesAlternateEmailExist($alternate_email)){ //make sure the address is unique
                $returnVal["errors"]["alternateEmail"] = $nonUniqueEmailError;
            }
        }
    }

    //If there are no errors after all validation, then attempt to insert the data
    if(empty($returnVal["errors"])){
        $insertRes = $database->insertProfile();
        if(isset($insertRes["error"])){ //if there was an error inserting
            $returnVal["errors"]["other"] = "There was an error inserting your profile into the database: ".$insertRes["error"];
        }
    }

    if(empty($returnVal["errors"])){$returnVal["success"] = true;} //if no errors, define success as true
    echo json_encode($returnVal); //return results
}



//For sending a confirmation code
else if (array_key_exists('send_code', $_GET)) {
    $returnVal = []; //associative array to return afterwards
    $returnVal["success"] = false; //set to true if there are no errors after validation & running
    $returnVal["error"] = []; //push errors to this array if any arise

    if(isset($_POST["email"]) && isset($_POST["creating"])){ //if email address was sent
        $email = trim($_POST["email"]);
        $creating = $_POST["creating"];

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
                        $emailResult = $emailHelper->codeConfirmationSendEmail($email, $hex);
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
    echo json_encode($returnVal); //return results
}



//For verifying a confirmation code
else if (array_key_exists('confirm_code', $_GET)) {
    $returnVal = []; //associative array to return afterwards
    $returnVal["success"] = false; //set to true if there are no errors after validation & running
    $returnVal["error"] = []; //push errors to this array if any arise

    if(isset($_POST["userID"]) && isset($_POST["email"]) && isset($_POST["code"])){ //if userID, email, and code were sent
        $userID = $_POST["userID"];
        $email = trim($_POST["email"]);
        $code = trim($_POST["code"]);

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
    echo json_encode($returnVal); //return results
}



//no appropriate function called
else{
    echo json_encode("No function called");
}



$database->close(); //close database connections
?>