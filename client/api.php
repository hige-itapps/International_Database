<?php
/*Get DB connection*/
include_once(dirname(__FILE__) . "/../server/database.php");
/*This file serves as the project's RESTful API. */

//For creating a new profile
if (array_key_exists('create_profile', $_GET)) {
    $returnVal = []; //associative array to return afterwards
    $returnVal["success"] = false; //set to true if there are no errors after validation & running
    $returnVal["errors"] = []; //push errors to this array if any arise
    $database = new DatabaseHelper(); //database helper object used for some verification and insertion

    //define error messages
    $requiredError = "This field is required";
    $tooLongError = "Field exceeds maximum character limit";
    $phoneDigitsError = "Only digits (0-9) are allowed, please do not include any other formatting";
    $wmichEmailOnlyError = "This address must be a wmich.edu address";
    $nonWmichEmailOnlyError = "This address must not be a wmich.edu address";
    $emailFormatError = "This address is not a valid email address";
    $nonUniqueEmailError = "This address is already in use";

    //set vars if possible (also trim excess whitespace off strings where possible)
    if(isset($_POST["userIssuesExpertise"])){$userIssuesExpertise = json_decode($_POST["userIssuesExpertise"], true);} //issues
    if(isset($_POST["issuesExpertiseOther"])){$issuesExpertiseOther = trim(json_decode($_POST["issuesExpertiseOther"], true));} //other issues
    if(isset($_POST["userCountriesExpertise"])){$userCountriesExpertise = json_decode($_POST["userCountriesExpertise"], true);} //countries of expertise
    if(isset($_POST["countriesExpertiseOther"])){$countriesExpertiseOther = trim(json_decode($_POST["countriesExpertiseOther"], true));} //other countries of expertise
    if(isset($_POST["userRegionsExpertise"])){$userRegionsExpertise = json_decode($_POST["userRegionsExpertise"], true);} //regions
    if(isset($_POST["regionsExpertiseOther"])){$regionsExpertiseOther = trim(json_decode($_POST["regionsExpertiseOther"], true));} //other regions
    if(isset($_POST["userLanguages"])){$userLanguages = json_decode($_POST["userLanguages"]);} //languages
    if(isset($_POST["userCountriesExperience"])){$userCountriesExperience = json_decode($_POST["userCountriesExperience"]);} //country experiences
    if(isset($_POST["firstName"])){$firstName = trim(json_decode($_POST["firstName"], true));} //first name
    if(isset($_POST["lastName"])){$lastName = trim(json_decode($_POST["lastName"], true));} //last name
    if(isset($_POST["login_email"])){$login_email = trim(json_decode($_POST["login_email"], true));} //login email
    if(isset($_POST["alternate_email"])){$alternate_email = trim(json_decode($_POST["alternate_email"], true));} //alternate email
    if(isset($_POST["phone"])){$phone = trim(json_decode($_POST["phone"], true));} //phone number
    if(isset($_POST["affiliations"])){$affiliations = trim(json_decode($_POST["affiliations"], true));} //affiliations
    if(isset($_POST["socialLink"])){$socialLink = trim(json_decode($_POST["socialLink"], true));} //social link

    //make sure required fields are present 
    if((!isset($userIssuesExpertise) || empty($userIssuesExpertise)) && (!isset($issuesExpertiseOther) || empty($issuesExpertiseOther))) {$returnVal["errors"]["issuesExpertise"] = $requiredError;} //require >= 1 issue of expertise
    if((!isset($userCountriesExpertise) || empty($userCountriesExpertise)) && (!isset($countriesExpertiseOther) || empty($countriesExpertiseOther))) {$returnVal["errors"]["countriesExpertise"] = $requiredError;} //require >= 1 country of expertise
    if((!isset($userRegionsExpertise) || empty($userRegionsExpertise)) && (!isset($regionsExpertiseOther) || empty($regionsExpertiseOther))) {$returnVal["errors"]["regionsExpertise"] = $requiredError;} //require >= 1 region of expertise
    if(!isset($firstName) || empty($firstName)) {$returnVal["errors"]["firstName"] = $requiredError;} //require first name
    if(!isset($lastName) || empty($lastName)) {$returnVal["errors"]["lastName"] = $requiredError;} //require last name
    if(!isset($affiliations) || empty($affiliations)) {$returnVal["errors"]["affiliations"] = $requiredError;} //require affiliations
    //require language proficiency level
    for ($i = 0; $i < count($userLanguages); $i++) {
        if(!property_exists($userLanguages[$i], 'proficiency_level')){
            $returnVal["errors"]["language ".$userLanguages[$i]->id] = $requiredError;
        }
    }
    //require country experiences
    for ($i = 0; $i < count($userCountriesExperience); $i++) {
        $tempOtherExperience = '';
        if(property_exists($userCountriesExperience[$i], 'otherExperience')){$tempOtherExperience = trim($userCountriesExperience[$i]->otherExperience);}
        if((!property_exists($userCountriesExperience[$i], 'experiences') || empty($userCountriesExperience[$i]->experiences)) && empty($tempOtherExperience)){
            $returnVal["errors"]["country ".$userCountriesExperience[$i]->id] = $requiredError;
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

    $returnVal["errors"]["other"] = "Other Error";


    $database->close();
    if(empty($returnVal["errors"])){$returnVal["success"] = true;} //if no errors, define success as true
    echo json_encode($returnVal); //return results
}



else{
    echo json_encode("No function called");
}
?>