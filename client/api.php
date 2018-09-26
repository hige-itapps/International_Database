<?php
/*Get DB connection*/
include_once(dirname(__FILE__) . "/../server/database.php");
/*This file serves as the project's RESTful API. */

//For creating a new profile
if (array_key_exists('create_profile', $_GET)) {
    $returnVal = []; //associative array to return afterwards
    $returnVal["success"] = false; //set to true if there are no errors after validation & running
    $returnVal["errors"] = []; //push errors to this array if any arise

    //define error messages
    $requiredError = "This field is required";
    $tooLongError = "Field exceeds maximum character limit";
    $phoneDigitsError = "Only digits (0-9) are allowed, please do not include any other formatting";


    /*foreach($_POST as $key=>$value)
    {
        echo $key." ==> ".$value.";";
    }*/

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
    if(isset($_POST["email"])){$email = trim(json_decode($_POST["email"], true));} //alternate primary email
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
        //$returnVal["errors"]["language ".$i] = $userLanguages[$i]->name;
        if(!property_exists($userLanguages[$i], 'proficiency_level')){
            $returnVal["errors"]["language ".$userLanguages[$i]->id] = $requiredError;
        }
    }

    //make sure phone number only includes digits
    if (isset($phone) && !ctype_digit($phone)) {$returnVal["errors"]["phone"] = $phoneDigitsError;}

    if(empty($returnVal["errors"])){$returnVal["success"] = true;} //if no errors, define success as true
    echo json_encode($returnVal); //return results
}



else{
    echo json_encode("No function called");
}
?>