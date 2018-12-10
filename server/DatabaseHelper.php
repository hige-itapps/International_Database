<?php
/*
    Database helper object for transactions
    This class is built to support all necessary queries to the database. It is not built to verify that incoming data is valid however, so this should be done elsewhere.
*/

/*Logger*/
include_once(dirname(__FILE__) . "/Logger.php");

class DatabaseHelper
{
    private $thisLocation; //get current location of file for logging purposes;
    private $logger; //for logging to files
    private $conn; //pdo database connection object
    private $sql; //pdo prepared statement
    private $config_url; //url of config file
    private $settings; //configuration settings

    /* Constructior retrieves configurations and sets up a connection */
    public function __construct($logger){
        $this->thisLocation = dirname(__FILE__).DIRECTORY_SEPARATOR.basename(__FILE__);

        $this->logger = $logger;
        $this->config_url = dirname(__FILE__).'/../config.ini'; //set config file url
        $this->settings = parse_ini_file($this->config_url); //get all settings
        $this->connect();
    }

    public function getConnection(){
        return $this->conn;
    }

    public function close(){
        $this->sql = null;
        $this->conn = null;
    }


    /* Specific transactions */

    /* For Users */
    /* Just quick summaries; only data from the users table- only return the primary email adresses. 
    Also only return approved profiles unless specified otherwise with $approvedOnly = false (denied) or null (pending)*/
    public function getAllUsersSummaries($approvedOnly = true){
        $query = "Select u.id, u.firstname, u.lastname, u.affiliations, COALESCE(u.alternate_email, u.login_email) as email, u.phone, u.social_link, u.issues_expertise_other, u.regions_expertise_other, u.countries_expertise_other FROM users u";
        if(!isset($approvedOnly)){$query.=" WHERE approved IS NULL";} //approvedOnly is NULL, so return pending only
        else if($approvedOnly){$query.=" WHERE approved = 1";} //only approved if necessary
        else{$query.=" WHERE approved = 0";} //otherwise, denied only
        $this->sql = $this->conn->prepare($query);
        $this->sql->execute();
        return $this->sql->fetchAll(PDO::FETCH_ASSOC); //return names as keys
    }
    public function getUserSummary($userID, $approvedOnly = true){
        $query = "Select u.id, u.firstname, u.lastname, u.affiliations, COALESCE(u.alternate_email, u.login_email) as email, u.phone, u.social_link, u.issues_expertise_other, u.regions_expertise_other, u.countries_expertise_other FROM users u WHERE u.id = :id";
        if(!isset($approvedOnly)){$query.=" AND approved IS NULL";} //approvedOnly is NULL, so return pending only
        else if($approvedOnly){$query.=" AND approved = 1";} //only approved if necessary
        else{$query.=" AND approved = 0";} //otherwise, denied only
        $query.= " LIMIT 1"; //only return 1 result
        $this->sql = $this->conn->prepare($query);
        $this->sql->bindParam(':id', $userID);
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_ASSOC); //return names as keys
    }


    /* Get the primary email addresses of all users.
    Also only return approved profiles' addresses unless specified otherwise with $approvedOnly = false (denied) or null (pending) */
    public function getAllPrimaryEmailAddresses($approvedOnly = true){
        $query = "SELECT COALESCE(u.alternate_email, u.login_email) as email FROM users u";
        if(!isset($approvedOnly)){$query.=" WHERE approved IS NULL";} //approvedOnly is NULL, so return pending only
        else if($approvedOnly){$query.=" WHERE approved = 1";} //only approved if necessary
        else{$query.=" WHERE approved = 0";} //otherwise, denied only
        $this->sql = $this->conn->prepare($query);
        $this->sql->execute();
        return $this->sql->fetchAll(PDO::FETCH_COLUMN); //return addresses only
    }



    /* Return a denied profile given its (preferably login) email address */
    public function getDeniedProfile($email){
        $id = $this->doesProfileExist($email, false); //first, get the ID of the denied profile if it exists

        return $this->getUserProfile($id, false, false); //return only a denied profile for the specified id
    }



    /* Full user profiles- still only return the primary email addresses. 
    Only return 1 general coalesced 'email' field unless specified otherwise with $coalesceEmails = false
    Also only return approved profiles unless specified otherwise with $approvedOnly = false (denied) or null (pending) */
    public function getUserProfile($userID, $coalesceEmails = true, $approvedOnly = true){
        //initialize user object
        $user = null;

        //start with summary
        $query = "Select u.id, u.firstname, u.lastname, u.affiliations, ";

        if($coalesceEmails){$query.="COALESCE(u.alternate_email, u.login_email) as email, ";} //coalesce email addresses so that the primary contact email is the only one to show
        else{$query.="u.login_email, u.alternate_email, ";} //return both addresses
        
        $query.="u.phone, u.social_link, u.issues_expertise_other, u.regions_expertise_other, u.countries_expertise_other FROM users u WHERE u.id = :id";

        if(!isset($approvedOnly)){$query.=" AND approved IS NULL";} //approvedOnly is NULL, so return pending only
        else if($approvedOnly){$query.=" AND approved = 1";} //only approved if necessary
        else{$query.=" AND approved = 0";} //otherwise, denied only

        $query.= " LIMIT 1"; //only return 1 result
        $this->sql = $this->conn->prepare($query);
        $this->sql->bindParam(':id', $userID);
        $this->sql->execute();

        if($this->sql->rowCount() > 0){ //user found, so continue

            $user = $this->sql->fetch(PDO::FETCH_ASSOC); //set user to summary values to start with

            //get user's issues of expertise
            $this->sql = $this->conn->prepare("SELECT i.id, i.issue FROM users u
                INNER JOIN users_issues ui ON u.id = ui.user_id
                INNER JOIN issues i ON ui.issue_id = i.id
            WHERE u.id = :id");
            $this->sql->bindParam(':id', $userID);
            $this->sql->execute();
            $user["issues_expertise"] = $this->sql->fetchAll(PDO::FETCH_ASSOC); //append issues as an array

            //get user's regions of expertise
            $this->sql = $this->conn->prepare("SELECT r.id, r.region FROM users u
                INNER JOIN users_regions ur ON u.id = ur.user_id
                INNER JOIN regions r ON ur.region_id = r.id
            WHERE u.id = :id");
            $this->sql->bindParam(':id', $userID);
            $this->sql->execute();
            $user["regions_expertise"] = $this->sql->fetchAll(PDO::FETCH_ASSOC);

            //get user's countries of expertise
            $this->sql = $this->conn->prepare("SELECT c.id, c.country_name FROM users u
                INNER JOIN users_country_expertise uce ON u.id = uce.user_id
                INNER JOIN countries c ON uce.country_id = c.id
            WHERE u.id = :id");
            $this->sql->bindParam(':id', $userID);
            $this->sql->execute();
            $user["countries_expertise"] = $this->sql->fetchAll(PDO::FETCH_ASSOC);

            //get user's language proficiencies
            $this->sql = $this->conn->prepare("SELECT l.id, l.name, lp.id AS proficiency_id, lp.proficiency_level FROM users u
                INNER JOIN users_languages ul ON u.id = ul.user_id
                INNER JOIN languages l ON ul.language_id = l.id
                INNER JOIN language_proficiencies lp ON ul.proficiency_id = lp.id
            WHERE u.id = :id");
            $this->sql->bindParam(':id', $userID);
            $this->sql->execute();
            $user_languages = $this->sql->fetchAll(PDO::FETCH_ASSOC); //save as a temporary array
            $user["languages"] = []; //initialize languages array
            foreach($user_languages as $language) {
                $user["languages"][] = ["id"=>$language["id"], "name"=>$language["name"], "proficiency_level"=>["id"=>$language["proficiency_id"], "proficiency_level"=>$language["proficiency_level"]]]; //format object as needed
            }

            //get user's country experience
            $this->sql = $this->conn->prepare("SELECT c.id AS country_id, c.country_name AS country_name, ce.id AS experience_id, ce.experience AS experience, uce.other_experience AS other_experience FROM users_country_experience uce 
                INNER JOIN countries c ON uce.country_id = c.id 
                LEFT JOIN country_experience ce ON uce.experience_id = ce.id
            WHERE uce.user_id = :id");
            $this->sql->bindParam(':id', $userID);
            $this->sql->execute();
            $user_country_experience = $this->sql->fetchAll(PDO::FETCH_ASSOC); //save as a temporary array
            $user["countries_experience"] = new stdClass(); //initialize country experience object
            foreach($user_country_experience as $experience) {
                if (!property_exists($user["countries_experience"], $experience["country_id"])) {//initialize country experience array for a specific country if not yet made
                    $user["countries_experience"]->$experience["country_id"] = new stdClass();
                    $user["countries_experience"]->$experience["country_id"]->id = $experience["country_id"]; //save the country's id
                    $user["countries_experience"]->$experience["country_id"]->country_name = $experience["country_name"]; //save the country's name
                    $user["countries_experience"]->$experience["country_id"]->experiences = []; //create sub array for experiences
                    $user["countries_experience"]->$experience["country_id"]->other_experience = ""; //create empty string for possible other experience
                }
                if (!empty($experience["experience"])) {//add experience to country's array
                    $newExperience = new stdClass();
                    $newExperience->id = $experience["experience_id"];
                    $newExperience->experience = $experience["experience"];
                    $user["countries_experience"]->$experience["country_id"]->experiences[] = $newExperience;
                } 
                if (!empty($experience["other_experience"])) {$user["countries_experience"]->$experience["country_id"]->other_experience = $experience["other_experience"];} //add other experience to country's array
            }
        }

        return $user;
    }



    public function searchByWildcards($wildcards, $approvedOnly = true){
        //If no wildcard given, don't waste time doing complicated queries, just return all profiles
        if(empty($wildcards)){
            return $this->getAllUsersSummaries($approvedOnly);
        }

        $queries = []; //save sub queries to be later built into 1 very large final query
        //start with base query
        $finalQuery = "SELECT res.id, res.firstname, res.lastname, res.affiliations, res.foundIn, COALESCE(res.alternate_email, res.login_email) as email, res.phone, res.social_link, res.issues_expertise_other, res.regions_expertise_other, res.countries_expertise_other FROM (";

        for($i = 0; $i < sizeof($wildcards); $i++){ //for each wildcard term
            $wildcards[$i] = '%'.$wildcards[$i].'%'; //add percent signs to search all strings

            //This extra long query combines search results from all relevant tables for a given wildcard; consider only the primary email addresses
            $queries[] = "(SELECT u.*, 'profile summary' as foundIn FROM users u
                WHERE CONCAT(u.firstname,' ', u.lastname) LIKE :wildcard".$i."
                OR COALESCE(u.alternate_email, u.login_email) LIKE :wildcard".$i."
                OR u.affiliations LIKE :wildcard".$i."
                OR u.phone LIKE :wildcard".$i."
                OR u.issues_expertise_other LIKE :wildcard".$i."
                OR u.regions_expertise_other LIKE :wildcard".$i."
                OR u.countries_expertise_other LIKE :wildcard".$i."
                OR u.social_link LIKE :wildcard".$i.")
                UNION DISTINCT
                (SELECT DISTINCT u.*, 'country experience' as foundIn FROM users u
                INNER JOIN users_country_experience uce ON u.id = uce.user_id
                INNER JOIN countries c ON uce.country_id = c.id
                WHERE c.country_name LIKE :wildcard".$i.")
                UNION DISTINCT
                (SELECT DISTINCT u.*, 'countries of expertise' as foundIn FROM users u
                INNER JOIN users_country_expertise uce ON u.id = uce.user_id
                INNER JOIN countries c ON uce.country_id = c.id
                WHERE c.country_name LIKE :wildcard".$i.")
                UNION DISTINCT
                (SELECT DISTINCT u.*, 'issues of expertise' as foundIn FROM users u
                INNER JOIN users_issues ui ON u.id = ui.user_id
                INNER JOIN issues i ON ui.issue_id = i.id
                WHERE i.issue LIKE :wildcard".$i.")
                UNION DISTINCT
                (SELECT DISTINCT u.*, 'regions of expertise' as foundIn FROM users u
                INNER JOIN users_regions ur ON u.id = ur.user_id
                INNER JOIN regions r ON ur.region_id = r.id
                WHERE r.region LIKE :wildcard".$i.")
                UNION DISTINCT
                (SELECT DISTINCT u.*, 'languages' as foundIn FROM users u
                INNER JOIN users_languages ul ON u.id = ul.user_id
                INNER JOIN languages l ON ul.language_id = l.id
                WHERE l.name LIKE :wildcard".$i.")";
        }

        if(sizeof($queries) == 1){ //only 1 query to consider
            $finalQuery.=$queries[0];
        }
        else{ //multiple sub-queries, build them into 1 long query string with inner-joins
            for($i = 0; $i < sizeof($queries); $i++){
                if($i > 0){ //union all following queries
                    $finalQuery.=" UNION DISTINCT ";
                }

                $finalQuery.=$queries[$i]; //append subquery to final query
            }
        }
        
        //finish query
        $finalQuery.=") res";
        if(!isset($approvedOnly)){$finalQuery.=" WHERE res.approved IS NULL";} //approvedOnly is NULL, so return pending only
        else if($approvedOnly){$finalQuery.=" WHERE res.approved = 1";} //only approved if necessary
        else{$finalQuery.=" WHERE res.approved = 0";} //otherwise, denied only

        $this->sql = $this->conn->prepare($finalQuery);

        for($i = 0; $i < sizeof($wildcards); $i++){ //bind each wildcard term
            $this->sql->bindParam(':wildcard'.$i, $wildcards[$i]);
        }

        $this->sql->execute();
        $results = $this->sql->fetchAll(PDO::FETCH_ASSOC); //save results
        $editedResults = []; //sort by index to avoid search time
        //remove duplicate ids while saving the foundIn categories, so we can remember which category the string was found in
        foreach($results as $result) {
            if (!array_key_exists($result["id"], $editedResults)) { //push profile summary if new
                $editedResults[$result["id"]] = $result;
                $editedResults[$result["id"]]["foundIn"] = [];
            }
            $editedResults[$result["id"]]["foundIn"][] = $result["foundIn"]; //push the foundIn category
        }
        return $editedResults;
    }



    /*Search the database with individual fields; returns all matching user summaries. 
    Also only return approved profiles unless specified otherwise with $approvedOnly = false (denied) or null (pending)*/
    public function advancedSearch($name, $affiliations, $email, $phone, $social_link, $issues_expertise, $issues_expertise_other, 
    $countries_expertise, $countries_expertise_other, $regions_expertise, $regions_expertise_other, $languages, $countries_experience, $approvedOnly = true){
        //return func_get_args();
        //If no parameters given, don't waste time doing complicated queries, just return all profiles
        if($name == null && $affiliations == null && $email == null && $phone == null && $social_link == null && $issues_expertise == null && $issues_expertise_other == null
            && $countries_expertise == null && $countries_expertise_other == null && $regions_expertise == null && $regions_expertise_other == null && $languages == null && $countries_experience == null){
            return $this->getAllUsersSummaries($approvedOnly);
        }

        //Start with the base query
        $query = "SELECT res.id, res.firstname, res.lastname, res.affiliations, COALESCE(res.alternate_email, res.login_email) as email, res.phone, res.social_link, res.issues_expertise_other, res.regions_expertise_other, res.countries_expertise_other FROM( ";

        //At least 1 field isn't null, so build up the query by first collecting any necessary search term strings
        $textQueryTerms = [];
        $textQuery = "";

        //add text search terms
        if($name !== NULL) {                        $textQueryTerms[] = "CONCAT(u.firstname,' ', u.lastname) LIKE :name";}
        if($affiliations !== NULL) {                $textQueryTerms[] = "u.affiliations LIKE :affiliations";}
        if($email !== NULL) {                       $textQueryTerms[] = "COALESCE(u.alternate_email, u.login_email) LIKE :email";}
        if($phone !== NULL) {                       $textQueryTerms[] = "u.phone LIKE :phone";}
        if($social_link !== NULL) {                 $textQueryTerms[] = "u.social_link LIKE :social_link";}
        if($issues_expertise_other !== NULL) {      $textQueryTerms[] = "u.issues_expertise_other LIKE :issues_expertise_other";}
        if($countries_expertise_other !== NULL) {   $textQueryTerms[] = "u.countries_expertise_other LIKE :countries_expertise_other";}
        if($regions_expertise_other !== NULL) {     $textQueryTerms[] = "u.regions_expertise_other LIKE :regions_expertise_other";}

        if(sizeof($textQueryTerms) > 0){$textQuery.="SELECT u.* FROM users u WHERE ".$textQueryTerms[0]." ";} //add first search term, starting with 'Where'
        for($i = 1; $i < sizeof($textQueryTerms); $i++){
            $textQuery.="AND ".$textQueryTerms[$i]." "; //loop through remaining search terms with AND
        }

        //build queries for other search terms

        //build issues of expertise query
        $issuesExpertiseQuery = "";
        if($issues_expertise !== NULL){ //at least 1 issue of expertise
            for($i = 0; $i < sizeof($issues_expertise); $i++){ //add queries for each specified issue of expertise
                if($i == 0){ //setup first-issue-specific syntax
                    $issuesExpertiseQuery.="SELECT DISTINCT issue_expertise0.* FROM";
                }else{ //secondary issue
                    $issuesExpertiseQuery.="INNER JOIN";
                }

                $issuesExpertiseQuery.= "(SELECT DISTINCT u.* FROM users u
                INNER JOIN users_issues ui ON u.id = ui.user_id
                WHERE ui.issue_id = :issue_expertise".$i;

                $issuesExpertiseQuery.=") issue_expertise".$i." ";

                if($i > 0){ //setup secondary-issue-specific syntax
                    $issuesExpertiseQuery.="on issue_expertise0.id = issue_expertise".$i.".id ";
                }
            }
        }

        //build countries of expertise query
        $countriesExpertiseQuery = "";
        if($countries_expertise !== NULL){ //at least 1 country of expertise
            for($i = 0; $i < sizeof($countries_expertise); $i++){ //add queries for each specified country of expertise
                if($i == 0){ //setup first-country-specific syntax
                    $countriesExpertiseQuery.="SELECT DISTINCT country_expertise0.* FROM";
                }else{ //secondary country
                    $countriesExpertiseQuery.="INNER JOIN";
                }

                $countriesExpertiseQuery.= "(SELECT DISTINCT u.* FROM users u
                INNER JOIN users_country_expertise uce ON u.id = uce.user_id
                WHERE uce.country_id = :country_expertise".$i;

                $countriesExpertiseQuery.=") country_expertise".$i." ";

                if($i > 0){ //setup secondary-country-specific syntax
                    $countriesExpertiseQuery.="on country_expertise0.id = country_expertise".$i.".id ";
                }
            }
        }

        //build regions of expertise query
        $regionsExpertiseQuery = "";
        if($regions_expertise !== NULL){ //at least 1 region of expertise
            for($i = 0; $i < sizeof($regions_expertise); $i++){ //add queries for each specified region of expertise
                if($i == 0){ //setup first-region-specific syntax
                    $regionsExpertiseQuery.="SELECT DISTINCT region_expertise0.* FROM";
                }else{ //secondary region
                    $regionsExpertiseQuery.="INNER JOIN";
                }

                $regionsExpertiseQuery.= "(SELECT DISTINCT u.* FROM users u
                INNER JOIN users_regions ur ON u.id = ur.user_id
                WHERE ur.region_id = :region_expertise".$i;

                $regionsExpertiseQuery.=") region_expertise".$i." ";

                if($i > 0){ //setup secondary-region-specific syntax
                    $regionsExpertiseQuery.="on region_expertise0.id = region_expertise".$i.".id ";
                }
            }
        }

        //build language query
        $languageQuery = "";
        if($languages !== NULL){ //at least 1 specified language
            for($i = 0; $i < sizeof($languages); $i++){ //add queries for each specified language
                if($i == 0){ //setup first-language-specific syntax
                    $languageQuery.="SELECT DISTINCT language0.* FROM";
                }else{ //secondary language
                    $languageQuery.="INNER JOIN";
                }

                $languageQuery.= "(SELECT DISTINCT u.* FROM users u
                INNER JOIN users_languages ul ON u.id = ul.user_id
                WHERE ul.language_id = :language".$i;

                if(is_array($languages[$i])){ //language has a specified proficiency, so add that to the query
                    $languageQuery.=" AND ul.proficiency_id = :proficiency".$i;
                }

                $languageQuery.=") language".$i." ";

                if($i > 0){ //setup secondary-language-specific syntax
                    $languageQuery.="on language0.id = language".$i.".id ";
                }
            }
        }

        //build countries experience query
        $countriesExperienceQuery = "";
        if($countries_experience !== NULL){ //at least 1 country experience
            for($i = 0; $i < sizeof($countries_experience); $i++){ //add queries for each specified country experience
                if($i == 0){ //setup first-country-specific syntax
                    $countriesExperienceQuery.="SELECT DISTINCT country_experience0.* FROM";
                }else{ //secondary country
                    $countriesExperienceQuery.="INNER JOIN";
                }

                if(is_object($countries_experience[$i])){ //country has a specified experience and/or other experience

                    if(property_exists($countries_experience[$i], 'experiences')){ //go through regular experiences
                        for($j = 0; $j < sizeof($countries_experience[$i]->experiences); $j++){
                            if($j == 0){ //setup first-country_experience-specific syntax
                                $countriesExperienceQuery.="( SELECT DISTINCT country_experience".$i."_experience0.* FROM";
                            }else{ //secondary country_experience
                                $countriesExperienceQuery.="INNER JOIN";
                            }

                            $countriesExperienceQuery.= "(SELECT DISTINCT u.* FROM users u
                            INNER JOIN users_country_experience uce ON u.id = uce.user_id
                            WHERE uce.country_id = :country_experience".$i." AND uce.experience_id = :country_experience".$i."_experience".$j.") country_experience".$i."_experience".$j." ";

                            if($j > 0){ //setup secondary-country_experience-specific syntax
                                $countriesExperienceQuery.="on country_experience".$i."_experience0.id = country_experience".$i."_experience".$j.".id ";
                            }
                        } 
                    }
                    
                    if(property_exists($countries_experience[$i], 'other_experience')){ //add other experience
                        if(property_exists($countries_experience[$i], 'experiences')){ //if regular experiences were also specified, then add an inner join prefix
                            $countriesExperienceQuery.="INNER JOIN";
                        }
                        else{ //only other experience
                            $countriesExperienceQuery.="( SELECT DISTINCT country_experience".$i."_other_experience.* FROM";
                        }

                        $countriesExperienceQuery.= "(SELECT DISTINCT u.* FROM users u
                            INNER JOIN users_country_experience uce ON u.id = uce.user_id
                            WHERE uce.country_id = :country_experience".$i." AND uce.other_experience LIKE :country_experience".$i."_other_experience) country_experience".$i."_other_experience ";
                        
                        if(property_exists($countries_experience[$i], 'experiences')){ //if regular experiences were also specified, then add an inner join on suffix
                            $countriesExperienceQuery.="on country_experience".$i."_experience0.id = country_experience".$i."_other_experience.id ";
                        }
                    }
                }
                else{ //just a country id
                    $countriesExperienceQuery.= "(SELECT DISTINCT u.* FROM users u
                    INNER JOIN users_country_experience uce ON u.id = uce.user_id
                    WHERE uce.country_id = :country_experience".$i;
                }

                $countriesExperienceQuery.=") country_experience".$i." ";

                if($i > 0){ //setup secondary-country-specific syntax
                    $countriesExperienceQuery.="on country_experience0.id = country_experience".$i.".id ";
                }
            }
        }


        //add search term queries to base query
        $allQueries = [];
        if($textQuery != ""){$allQueries[] = $textQuery;}
        if($issuesExpertiseQuery != ""){$allQueries[] = $issuesExpertiseQuery;}
        if($countriesExpertiseQuery != ""){$allQueries[] = $countriesExpertiseQuery;}
        if($regionsExpertiseQuery != ""){$allQueries[] = $regionsExpertiseQuery;}
        if($languageQuery != ""){$allQueries[] = $languageQuery;}
        if($countriesExperienceQuery != ""){$allQueries[] = $countriesExperienceQuery;}


        if(sizeof($allQueries) == 1){ //only 1 query to consider
            $query.=$allQueries[0];
        }
        else{ //multiple sub-queries, build them into 1 long query string with inner-joins
            for($i = 0; $i < sizeof($allQueries); $i++){
                if($i == 0){ //setup first-query-specific syntax
                    $query.="SELECT DISTINCT results0.* FROM(";
                }else{ //secondary-query-specific syntax, need to inner join results
                    $query.="INNER JOIN(";
                }

                $query.=$allQueries[$i];

                $query.=") results".$i." ";

                if($i > 0){ //inner join on results id
                    $query.="on results0.id = results".$i.".id ";
                }
            }
        }

        //finish query
        $query.=") res";
        if(!isset($approvedOnly)){$query.=" WHERE res.approved IS NULL";} //approvedOnly is NULL, so return pending only
        else if($approvedOnly){$query.=" WHERE res.approved = 1";} //only approved if necessary
        else{$query.=" WHERE res.approved = 0";} //otherwise, denied only

        //return $query;

        $this->sql = $this->conn->prepare($query);

        //bind parameters, wrap them with %s so that any part of the text can be matched
        if($name !== NULL){                         $name = '%'.$name.'%';                                              $this->sql->bindParam(':name', $name); }
        if($affiliations !== NULL){                 $affiliations = '%'.$affiliations.'%';                              $this->sql->bindParam(':affiliations', $affiliations); }
        if($email !== NULL){                        $email = '%'.$email.'%';                                            $this->sql->bindParam(':email', $email); }
        if($phone !== NULL){                        $phone = '%'.$phone.'%';                                            $this->sql->bindParam(':phone', $phone); }
        if($social_link !== NULL){                  $social_link = '%'.$social_link.'%';                                $this->sql->bindParam(':social_link', $social_link); }
        if($issues_expertise_other !== NULL){       $issues_expertise_other = '%'.$issues_expertise_other.'%';          $this->sql->bindParam(':issues_expertise_other', $issues_expertise_other); }
        if($countries_expertise_other !== NULL){    $countries_expertise_other = '%'.$countries_expertise_other.'%';    $this->sql->bindParam(':countries_expertise_other', $countries_expertise_other); }
        if($regions_expertise_other !== NULL){      $regions_expertise_other = '%'.$regions_expertise_other.'%';        $this->sql->bindParam(':regions_expertise_other', $regions_expertise_other); }

        if($issues_expertise !== NULL){ //at least 1 specified issue of expertise
            for($i = 0; $i < sizeof($issues_expertise); $i++){
                $this->sql->bindParam(':issue_expertise'.$i, $issues_expertise[$i]);
            }
        }

        if($countries_expertise !== NULL){ //at least 1 specified country of expertise
            for($i = 0; $i < sizeof($countries_expertise); $i++){
                $this->sql->bindParam(':country_expertise'.$i, $countries_expertise[$i]);
            }
        }

        if($regions_expertise !== NULL){ //at least 1 specified region of expertise
            for($i = 0; $i < sizeof($regions_expertise); $i++){
                $this->sql->bindParam(':region_expertise'.$i, $regions_expertise[$i]);
            }
        }

        if($languages !== NULL){ //at least 1 specified language
            for($i = 0; $i < sizeof($languages); $i++){
                if(is_array($languages[$i])){ //language has a specified proficiency
                    $this->sql->bindParam(':language'.$i, $languages[$i][0]);
                    $this->sql->bindParam(':proficiency'.$i, $languages[$i][1]);
                }
                else{ //no specified proficiency
                    $this->sql->bindParam(':language'.$i, $languages[$i]);
                }
            }
        }

        if($countries_experience !== NULL){ //at least 1 specified country experience
            for($i = 0; $i < sizeof($countries_experience); $i++){
                if(is_object($countries_experience[$i])){ //country has a specified experience and/or other experience
                    $this->sql->bindParam(':country_experience'.$i, $countries_experience[$i]->id);
                    if(property_exists($countries_experience[$i], 'experiences')){ //country has specified experiences
                        for($j = 0; $j < sizeof($countries_experience[$i]->experiences); $j++){
                            $this->sql->bindParam(':country_experience'.$i.'_experience'.$j, $countries_experience[$i]->experiences[$j]); //bind each individual experience id
                        }
                    }
                    if(property_exists($countries_experience[$i], 'other_experience')){
                        $countries_experience[$i]->other_experience = '%'.$countries_experience[$i]->other_experience.'%'; //wrap text field so that any part of it can be matched
                        $this->sql->bindParam(':country_experience'.$i.'_other_experience', $countries_experience[$i]->other_experience); //bind the other experience string
                    }
                }
                else{ //only id
                    $this->sql->bindParam(':country_experience'.$i, $countries_experience[$i]);
                }
            }
        }

        $this->sql->execute();
        return $this->sql->fetchAll(PDO::FETCH_ASSOC);
    }



    //Insert a profile into the database. This will only work if there is a current pending code for the login email. 
    //This operation will automatically set the 'approved' field to false (0).
    //Error handling should be done beforehand, including verifying that a code is currently pending for the login email.
    public function insertProfile($login_email, $firstname, $lastname, $alternate_email, $affiliations, $phone, $issues_expertise_other, $regions_expertise_other, $countries_expertise_other, $social_link, $issues_expertise, $countries_expertise, $regions_expertise, $languages, $countries_experience){
        $retval = [];
        $newProfileID = null; // the ID of the profile just inserted

        $this->conn->beginTransaction(); //begin atomic transaction

        //If there was a previously denied profile for this user, delete it first before inserting a new pending one
        $deniedProfileID = $this->doesProfileExist($login_email, false);
        if($deniedProfileID > 0){ //denied profile exists
            $deleteError = $this->atomicDeleteProfile($deniedProfileID, null); //delete profile
            if(!empty($deleteError)){$retval["error"] = $deleteError;} //set $retval["error"] if there was an error
        }


        //insert base profile
        $this->sql = $this->conn->prepare("INSERT INTO users(login_email, firstname, lastname, alternate_email, affiliations, phone, issues_expertise_other, regions_expertise_other, countries_expertise_other, social_link)
            VALUES(:login_email, :firstname, :lastname, :alternate_email, :affiliations, :phone, :issues_expertise_other, :regions_expertise_other, :countries_expertise_other, :social_link)");
        $this->sql->bindParam(':login_email', $login_email);
        $this->sql->bindParam(':firstname', $firstname);
        $this->sql->bindParam(':lastname', $lastname);
        $this->sql->bindParam(':alternate_email', $alternate_email);
        $this->sql->bindParam(':affiliations', $affiliations);
        $this->sql->bindParam(':phone', $phone);
        $this->sql->bindParam(':issues_expertise_other', $issues_expertise_other);
        $this->sql->bindParam(':regions_expertise_other', $regions_expertise_other);
        $this->sql->bindParam(':countries_expertise_other', $countries_expertise_other);
        $this->sql->bindParam(':social_link', $social_link);
        
        if ($this->sql->execute() !== TRUE){//query failed
            $this->conn->rollBack();
            $retval["error"] = "Failed to insert base profile.";
        }

        if(empty($retval["error"])){ //no errors yet, so continue
            //get the profile ID of the just-added profile
            $this->sql = $this->conn->prepare("SELECT max(id) FROM users WHERE login_email = :login_email LIMIT 1");
            $this->sql->bindParam(':login_email', $login_email);
            $this->sql->execute();
            $newProfileID = $this->sql->fetch(PDO::FETCH_COLUMN);//now we have the current ID!

            //insert user's issues of expertise
            foreach($issues_expertise as $i){//go through issues
                $this->sql = $this->conn->prepare("INSERT INTO users_issues(user_id, issue_id) VALUES(:user_id, :issue_id)");
                $this->sql->bindParam(':user_id', $newProfileID);
                $this->sql->bindParam(':issue_id', $i["id"]);

                if ($this->sql->execute() !== TRUE){//query failed
                    $this->conn->rollBack();
                    $retval["error"] = "Failed to insert an issue of expertise.";
                    break;
                }
            }
        }

        if(empty($retval["error"])){ //no errors yet, so continue
            //insert user's countries of expertise
            foreach($countries_expertise as $i){//go through countries
                $this->sql = $this->conn->prepare("INSERT INTO users_country_expertise(user_id, country_id) VALUES(:user_id, :country_id)");
                $this->sql->bindParam(':user_id', $newProfileID);
                $this->sql->bindParam(':country_id', $i["id"]);

                if ($this->sql->execute() !== TRUE){//query failed
                    $this->conn->rollBack();
                    $retval["error"] = "Failed to insert a country of expertise.";
                    break;
                }
            }
        }

        if(empty($retval["error"])){ //no errors yet, so continue
            //insert user's regions of expertise
            foreach($regions_expertise as $i){//go through regions
                $this->sql = $this->conn->prepare("INSERT INTO users_regions(user_id, region_id) VALUES(:user_id, :region_id)");
                $this->sql->bindParam(':user_id', $newProfileID);
                $this->sql->bindParam(':region_id', $i["id"]);

                if ($this->sql->execute() !== TRUE){//query failed
                    $this->conn->rollBack();
                    $retval["error"] = "Failed to insert a region of expertise.";
                    break;
                }
            }
        }

        if(empty($retval["error"])){ //no errors yet, so continue
            //insert user's languages
            foreach($languages as $i){//go through languages
                $this->sql = $this->conn->prepare("INSERT INTO users_languages(user_id, language_id, proficiency_id) VALUES(:user_id, :language_id, :proficiency_id)");
                $this->sql->bindParam(':user_id', $newProfileID);
                $this->sql->bindParam(':language_id', $i->id);
                $this->sql->bindParam(':proficiency_id', $i->proficiency_level->id);

                if ($this->sql->execute() !== TRUE){//query failed
                    $this->conn->rollBack();
                    $retval["error"] = "Failed to insert a language.";
                    break;
                }
            }
        }

        if(empty($retval["error"])){ //no errors yet, so continue
            //insert user's country experience
            foreach($countries_experience as $i){//go through countries

                foreach($i->experiences as $experience){//go through regular experiences
                    $this->sql = $this->conn->prepare("INSERT INTO users_country_experience(user_id, country_id, experience_id) VALUES(:user_id, :country_id, :experience_id)");
                    $this->sql->bindParam(':user_id', $newProfileID);
                    $this->sql->bindParam(':country_id', $i->id);
                    $this->sql->bindParam(':experience_id', $experience->id);

                    if ($this->sql->execute() !== TRUE){//query failed
                        $this->conn->rollBack();
                        $retval["error"] = "Failed to insert a country experience.";
                        break;
                    }
                }
                
                //check for an "other" experience, if no errors yet
                if(empty($retval["error"])){
                    if($i->other_experience !== ""){ //an other experience exists
                        //$otherExperienceID = null; // the ID of the added other experience
                        $this->sql = $this->conn->prepare("INSERT INTO users_country_experience(user_id, country_id, other_experience) VALUES(:user_id, :country_id, :other_experience)");
                        $this->sql->bindParam(':user_id', $newProfileID);
                        $this->sql->bindParam(':country_id', $i->id);
                        $this->sql->bindParam(':other_experience', $i->other_experience);

                        if ($this->sql->execute() !== TRUE){//query failed
                            $this->conn->rollBack();
                            $retval["error"] = "Failed to insert other country experience.";
                            break;
                        }
                    }
                }
                else{ //if there already is an error, then break
                    break;
                }
            }
        }

        if(empty($retval["error"])){ //no errors, so queries can be committed
            $this->conn->commit();
        }

        //$retval["error"] = "Not implemented yet!";
        return $retval;
    }



    /* Approve a profile; this requires deleting an old application as well if the new one is an update 
    Takes $userID, the ID of the profile to be deleted. Also takes an optional broncoNetID to be logged.
    Returns $retval, an associative array. If there are no errors, $retval["success"] will be true. If there is an error, $retval["error"] will contain an error string.*/
    public function approveProfile($userID, $CASbroncoNetID){
        $retval = [];
        $retval["success"] = false;
        $oldID = -1; //set to the old profile ID if one existed

        $this->conn->beginTransaction(); //begin atomic transaction

        $currentEmail = $this->getLoginEmailFromID($userID);
        if(isset($currentEmail)){ $oldID = $this->doesProfileExist($currentEmail);} //retrieve old ID

        if($oldID > 0){ //this was an updated profile, so delete the old one
            $deleteError = $this->atomicDeleteProfile($oldID, $CASbroncoNetID); //delete profile
            if(!empty($deleteError)){$retval["error"] = $deleteError;} //set $retval["error"] if there was an error
        }

        //now set the pending profile to approved, if no errors yet
        if(empty($retval["error"])){
            $this->sql = $this->conn->prepare("UPDATE users u SET u.approved = 1 WHERE id = :userID");
            $this->sql->bindParam(':userID', $userID);
            
            if ($this->sql->execute() !== TRUE){//query failed
                $this->conn->rollBack();
                $retval["error"] = "Failed approve pending profile.";
            }
        }

        if(empty($retval["error"])){ //no errors, so queries can be committed
            $this->conn->commit();
            $retval["success"] = true;
        }

        return $retval;
    }



    /* Deny a profile; this simply sets the approved boolean to false. When this happens, the profile will no longer be considered pending, 
    but will privately remain in the database until the profile owner submits a new profile, at which point it will be deleted.*/
    public function denyProfile($userID, $CASbroncoNetID){
        $this->sql = $this->conn->prepare("UPDATE users u SET u.approved = 0 WHERE u.id = :userID");
        $this->sql->bindParam(':userID', $userID);
        return $this->sql->execute();
    }



    /* Completely delete a profile from the database. This is used by admins when deleting a profile.
    Takes $userID, the ID of the profile to be deleted. Also takes an optional broncoNetID to be logged.
    Returns $retval, an associative array. If there are no errors, $retval["success"] will be true. If there is an error, $retval["error"] will contain an error string.
    This is an atomic transaction, so every related bit of information for this profile will be removed, or none of it. */
    public function deleteProfile($userID, $CASbroncoNetID){
        $retval = [];
        $retval["success"] = false;

        $this->conn->beginTransaction(); //begin atomic transaction

        $deleteError = $this->atomicDeleteProfile($userID, $CASbroncoNetID); //delete profile
        if(!empty($deleteError)){$retval["error"] = $deleteError;} //set $retval["error"] if there was an error

        if(empty($retval["error"])){ //no errors, so queries can be committed
            $this->conn->commit();
            $retval["success"] = true;
        }

        return $retval;
    }



    /*Reusable function to delete profiles, specifically set up to be usable as an atomic function along with other sql queries.
    Takes $userID, the ID of the profile to be deleted. Also takes an optional broncoNetID to be logged.
    Will return $error, an error string. If $error isn't empty, then an error occured, otherwise everything should have worked correctly.
    To use this properly, make sure to setup a beginTransaction command before calling this method, and a commit command afterwards (that only triggers if $retval["error"] is empty)*/
    private function atomicDeleteProfile($userID, $CASbroncoNetID){

        $error = null; //error string to be set if an error occurs

        //remove any relevant country experiences
        $this->sql = $this->conn->prepare("DELETE FROM users_country_experience WHERE user_id = :userID");
        $this->sql->bindParam(':userID', $userID);
        
        if ($this->sql->execute() !== TRUE){//query failed
            $this->conn->rollBack();
            $error = "Failed to delete relevant user country experiences.";
        }

        if(empty($error)){ //no errors yet, so continue
                //remove any relevant languages
            $this->sql = $this->conn->prepare("DELETE FROM users_languages WHERE user_id = :userID");
            $this->sql->bindParam(':userID', $userID);
            
            if ($this->sql->execute() !== TRUE){//query failed
                $this->conn->rollBack();
                $error = "Failed to delete relevant user languages.";
            }
        }

        if(empty($error)){ //no errors yet, so continue
            //remove any relevant regions of expertise
            $this->sql = $this->conn->prepare("DELETE FROM users_regions WHERE user_id = :userID");
            $this->sql->bindParam(':userID', $userID);
            
            if ($this->sql->execute() !== TRUE){//query failed
                $this->conn->rollBack();
                $error = "Failed to delete relevant user regions of expertise.";
            }
        }

        if(empty($error)){ //no errors yet, so continue
            //remove any relevant countries of expertise
            $this->sql = $this->conn->prepare("DELETE FROM users_country_expertise WHERE user_id = :userID");
            $this->sql->bindParam(':userID', $userID);
            
            if ($this->sql->execute() !== TRUE){//query failed
                $this->conn->rollBack();
                $error = "Failed to delete relevant user countries of expertise.";
            }
        }

        if(empty($error)){ //no errors yet, so continue
            //remove any relevant issues of expertise
            $this->sql = $this->conn->prepare("DELETE FROM users_issues WHERE user_id = :userID");
            $this->sql->bindParam(':userID', $userID);
            
            if ($this->sql->execute() !== TRUE){//query failed
                $this->conn->rollBack();
                $error = "Failed to delete relevant user issues of expertise.";
            }
        }

        if(empty($error)){ //no errors yet, so continue
            //remove base profile
            $this->sql = $this->conn->prepare("DELETE FROM users WHERE id = :userID");
            $this->sql->bindParam(':userID', $userID);
            
            if ($this->sql->execute() !== TRUE){//query failed
                $this->conn->rollBack();
                $error = "Failed to delete relevant user base profile.";
            }
        }

        return $error;
    }



    /*Checks if a user is an administrator (true or false)*/
	public function isAdministrator($broncoNetID){
		$this->sql = $this->conn->prepare("Select * FROM administrators WHERE broncoNetID = :id");
		$this->sql->bindParam(':id', $broncoNetID);
		$this->sql->execute();
		return boolval($this->sql->fetch(PDO::FETCH_COLUMN));
    }
    
    /* Returns array of all administrators */
	public function getAdministrators(){
		$this->sql = $this->conn->prepare("Select broncoNetID, name FROM administrators");
		$this->sql->execute();
		return $this->sql->fetchAll(PDO::FETCH_NUM); //return indexes as keys
    }
    
    /* Add an admin to the administrators table */
	public function addAdmin($broncoNetID, $name){
		if ($broncoNetID != "" && $name != ""){//valid params
			$this->logger->logInfo("Inserting administrator (".$broncoNetID.", ".$name.")", $broncoNetID, $this->thisLocation);
			$this->sql = $this->conn->prepare("INSERT INTO administrators(BroncoNetID, Name) VALUES(:id, :name)");
			$this->sql->bindParam(':id', $broncoNetID);
			$this->sql->bindParam(':name', $name);
			return $this->sql->execute();
		}
    }
    
    /* Remove an admin to the administrators table */
	public function removeAdmin($broncoNetID){
		if ($broncoNetID != ""){//valid params
			$this->sql = $this->conn->prepare("DELETE FROM administrators WHERE BroncoNetID = :id");
			$this->sql->bindParam(':id', $broncoNetID);
			return $this->sql->execute();
		}
	}

    //get all issues, with ids as keys
    public function getIssues(){
        $this->sql = $this->conn->prepare("Select issues.id, issues.* FROM issues");
        $this->sql->execute();
        return $this->sql->fetchAll(\PDO::FETCH_UNIQUE|\PDO::FETCH_ASSOC); //order with id as key
    }

    //get all countries, with ids as keys
    public function getCountries(){
        $this->sql = $this->conn->prepare("Select countries.id, countries.* FROM countries");
        $this->sql->execute();
        return $this->sql->fetchAll(\PDO::FETCH_UNIQUE|\PDO::FETCH_ASSOC); //order with id as key
    }

    //get all regions, with ids as keys
    public function getRegions(){
        $this->sql = $this->conn->prepare("Select regions.id, regions.* FROM regions");
        $this->sql->execute();
        return $this->sql->fetchAll(\PDO::FETCH_UNIQUE|\PDO::FETCH_ASSOC); //order with id as key
    }

    //get all languages, with ids as keys
    public function getLanguages(){
        $this->sql = $this->conn->prepare("Select languages.id, languages.* FROM languages");
        $this->sql->execute();
        return $this->sql->fetchAll(\PDO::FETCH_UNIQUE|\PDO::FETCH_ASSOC); //order with id as key
    }

    //get all language proficiencies, with ids as keys
    public function getLanguageProficiencies(){
        $this->sql = $this->conn->prepare("Select language_proficiencies.id, language_proficiencies.* FROM language_proficiencies");
        $this->sql->execute();
        return $this->sql->fetchAll(\PDO::FETCH_UNIQUE|\PDO::FETCH_ASSOC); //order with id as key
    }

    //get all country experiences, with ids as keys
    public function getCountryExperiences(){
        $this->sql = $this->conn->prepare("Select country_experience.id, country_experience.* FROM country_experience");
        $this->sql->execute();
        return $this->sql->fetchAll(\PDO::FETCH_UNIQUE|\PDO::FETCH_ASSOC); //order with id as key
    }

    /*return an array of the maximum lengths of every column in the users table*/
    public function getUsersMaxLengths(){
        $this->sql = $this->conn->prepare("Select COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH FROM information_schema.columns WHERE table_schema = '" . $this->settings["database_name"] . "' AND table_name = 'users'");
        $this->sql->execute();
        $tempArray = $this->sql->fetchAll(PDO::FETCH_ASSOC); //save to a temporary array.
        $retVals = []; //init new array which will hold only column names as keys and their lengths as values
        foreach($tempArray as $maxLength){
            $retVals[$maxLength["COLUMN_NAME"]] = $maxLength["CHARACTER_MAXIMUM_LENGTH"];
        }
        return $retVals;
    }
    
    /*Get the maximum length of other country experiences (just an integer, not an array)*/
    public function getOtherCountryExperiencesMaxLength(){
        $this->sql = $this->conn->prepare("Select CHARACTER_MAXIMUM_LENGTH FROM information_schema.columns WHERE table_schema = '" . $this->settings["database_name"] . "' AND table_name = 'users_country_experience' AND COLUMN_NAME = 'other_experience'");
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_COLUMN);
    }

    /*Check if a confirmation code was already sent for a user (returns 1 or 0)*/
    public function isCodePending($email){
        $currentTime = time(); //get current timestamp
        $this->sql = $this->conn->prepare("SELECT EXISTS(SELECT 1 FROM users_codes WHERE email = :email AND expiration_timestamp >= :currentTime)");
        $this->sql->bindParam(':email', $email);
        $this->sql->bindParam(':currentTime', $currentTime);
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_COLUMN);
    }

    /* Check if a profile is currently pending (approved IS NULL) for a given email (returns 1 or 0). If this is the login email, check the profile associated with it, otherwise find the profile associated with the given alternate email */
    public function isProfilePending($email){
        $this->sql = $this->conn->prepare("SELECT EXISTS(SELECT 1 FROM users WHERE (login_email = :email OR login_email = (SELECT u.login_email FROM users u WHERE u.alternate_email = :email)) AND approved IS NULL )");
        $this->sql->bindParam(':email', $email);
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_COLUMN);
    }

    /* Get number of pending profiles */
    public function getNumberOfPendingProfiles(){
        $this->sql = $this->conn->prepare("SELECT COUNT(*) FROM international.users WHERE approved IS NULL");
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_COLUMN);
    }

    /*Check if a confirmation code is correct for a given email (returns an expiration timestamp if so, or nothing otherwise)*/
    public function confirmCode($email, $code){
        $this->sql = $this->conn->prepare("SELECT expiration_timestamp FROM users_codes WHERE email = :email AND code = :code LIMIT 1");
        $this->sql->bindParam(':email', $email);
        $this->sql->bindParam(':code', $code);
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_COLUMN);
    }

    /* Save a profile's code to the database -- need to specify an expiration date */
    public function saveCode($email, $code, $expiration_timestamp){
        //first, remove an existing code if one exists and its timestamp is outdated
        $this->sql = $this->conn->prepare("DELETE FROM users_codes WHERE email = :email AND expiration_timestamp < :expiration_timestamp");
        $this->sql->bindParam(':email', $email);
        $this->sql->bindParam(':expiration_timestamp', $expiration_timestamp);
        $this->sql->execute();

        //now insert the new code
        $this->sql = $this->conn->prepare("INSERT INTO users_codes(email, code, expiration_timestamp) VALUES(:email, :code, :expiration_timestamp)");
        $this->sql->bindParam(':email', $email);
        $this->sql->bindParam(':code', $code);
        $this->sql->bindParam(':expiration_timestamp', $expiration_timestamp);
        return $this->sql->execute();
    }

    /* Remove a code if pending */
    public function removeCode($email){
        $this->sql = $this->conn->prepare("DELETE FROM users_codes WHERE email = :email");
        $this->sql->bindParam(':email', $email);
        return $this->sql->execute();
    }

    /*Get both possible emails from a user*/
    public function getBothEmails($userID){
        $this->sql = $this->conn->prepare("SELECT login_email, alternate_email FROM users WHERE id = :id");
        $this->sql->bindParam(':id', $userID);
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_ASSOC);
    }

    /*Save a new email message to the database, return true if successful or false otherwise*/
    public function saveEmail($address, $subject, $message){
        $curTime = date('Y-m-d H:i:s');//get current timestamp
                    
        $this->sql = $this->conn->prepare("INSERT INTO emails(address, subject, message, time) VALUES(:address, :subject, :message, :time)");
        $this->sql->bindParam(':address', $address);
        $this->sql->bindParam(':subject', $subject);
        $this->sql->bindParam(':message', $message);
        $this->sql->bindParam(':time', $curTime);
        return $this->sql->execute();
    }



    /*Return number of users using this login email (should be 0 or 1). 
    By default, only approved and pending profiles are considered, but if $deniedOnly is set to true then only denied profiles are considered.*/
    public function doesLoginEmailExist($testEmail, $deniedOnly = false){
        $query = "SELECT COUNT(*) FROM users u WHERE u.login_email = :email ";

        if($deniedOnly){$query.=" AND approved = 0";} //only approved if necessary
        else{$query.=" AND (approved = 1 OR approved IS NULL)";} //otherwise, pending only

        $this->sql = $this->conn->prepare($query);
        $this->sql->bindParam(':email', $testEmail);
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_COLUMN);
    }
    /*Return number of users using this alternate email (should be 0 or 1)*/
    public function doesAlternateEmailExist($testEmail){
        $this->sql = $this->conn->prepare("SELECT COUNT(*) FROM users u WHERE u.alternate_email = :email");
        $this->sql->bindParam(':email', $testEmail);
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_COLUMN);
    }
    /*Return number of users using this alternate email EXCEPT a user with the given login email (should be 0 or 1)*/
    public function doesAlternateEmailExistIgnoreProfile($testEmail, $profileEmail){
        $this->sql = $this->conn->prepare("SELECT COUNT(*) FROM users u WHERE u.alternate_email = :testemail AND u.login_email != :profileemail");
        $this->sql->bindParam(':testemail', $testEmail);
        $this->sql->bindParam(':profileemail', $profileEmail);
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_COLUMN);
    }
    /* Check if a profile already exists for a given email. By default, $approvedOnly = true will only return approved profiles, but it can be set to null (pending) or false (denied)
    If this is the login email, check the profile associated with it, otherwise find the profile associated with the given alternate email 
    Returns the id of the existing profile, or 0 if nonexistant*/
    public function doesProfileExist($email, $approvedOnly = true){
        $query = "SELECT IFNULL( (SELECT id FROM users WHERE (login_email = :email OR login_email IN (SELECT u.login_email FROM users u WHERE u.alternate_email = :email))"; //start main query

        if(!isset($approvedOnly)){$query.=" AND approved IS NULL";} //approvedOnly is NULL, so return pending only
        else if($approvedOnly){$query.=" AND approved = 1";} //only approved if necessary
        else{$query.=" AND approved = 0";} //otherwise, denied only

        $query.=" LIMIT 1), 0)";//finish query


        $this->sql = $this->conn->prepare($query);
        $this->sql->bindParam(':email', $email);
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_COLUMN);
    }

    /* Get the user's login email given their ID */
    public function getLoginEmailFromID($userID){
        $this->sql = $this->conn->prepare("SELECT u.login_email FROM users u WHERE u.id = :userID");
        $this->sql->bindParam(':userID', $userID);
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_COLUMN);
    }



    /*Retrieve the site warning, if any*/
	public function getSiteWarning(){
		$this->sql = $this->conn->prepare("SELECT v.Value FROM variables v WHERE v.name = 'SiteWarning'");
		$this->sql->execute();
		return $this->sql->fetch(PDO::FETCH_COLUMN); //return value only
	}
	/*Save a new site warning*/
	public function saveSiteWarning($warning){
		$this->sql = $this->conn->prepare("UPDATE variables v SET v.Value = :warning WHERE v.name = 'SiteWarning'");
		$this->sql->bindParam(':warning', $warning);
		return $this->sql->execute();
    }
    


    /* Save the current timestamp in the database 'variables' table, to the variable 'ReminderEmailsLastSent' */
    public function saveReminderEmailsLastSentTime(){
        $currentTime = time();
        $this->sql = $this->conn->prepare("UPDATE variables v SET v.Value = :currentTime WHERE v.name = 'ReminderEmailsLastSent'");
		$this->sql->bindParam(':currentTime', $currentTime);
		return $this->sql->execute();
    }
    /* Get the timestamp from the latest time the reminder emails were sent */
    public function getReminderEmailsLastSentTime(){
		$this->sql = $this->conn->prepare("SELECT v.Value FROM variables v WHERE v.name = 'ReminderEmailsLastSent'");
		$this->sql->execute();
		return $this->sql->fetch(PDO::FETCH_COLUMN); //return value only
    }
    


    /* Save the current timestamp in the database 'variables' table, to the variable 'DatabaseLastBackedUp' */
    public function saveDatabaseLastBackedUpTime(){
        $currentTime = time();
        $this->sql = $this->conn->prepare("UPDATE variables v SET v.Value = :currentTime WHERE v.name = 'DatabaseLastBackedUp'");
		$this->sql->bindParam(':currentTime', $currentTime);
		return $this->sql->execute();
    }
    /* Get the timestamp from the latest time the database was backed up */
    public function getDatabaseLastBackedUpTime(){
		$this->sql = $this->conn->prepare("SELECT v.Value FROM variables v WHERE v.name = 'DatabaseLastBackedUp'");
		$this->sql->execute();
		return $this->sql->fetch(PDO::FETCH_COLUMN); //return value only
	}



    /* Establishes an sql connection to the database, and returns the object; MAKE SURE TO SET OBJECT TO NULL WHEN FINISHED */
    private function connect(){
        try{
            $this->conn = new AtomicPDO("mysql:host=" . $this->settings["hostname"] . ";dbname=" . $this->settings["database_name"] . ";charset=utf8", $this->settings["database_username"], 
                $this->settings["database_password"], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // set the PDO error mode to exception
            $this->conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, true ); //emulate prepared statements, allows for more flexibility
        }
        catch(PDOException $e){
            echo "Connection failed: " . $e->getMessage();
        }
    }
        
}


/*allow for atomic transactions (to ensure one insert only occurs when another has succeeded, so that either both or neither of them succeed)
found at http://php.net/manual/en/pdo.begintransaction.php*/
class AtomicPDO extends PDO
{
    protected $transactionCounter = 0;

    public function beginTransaction()
    {
        if (!$this->transactionCounter++) {
            return parent::beginTransaction();
        }
        $this->exec('SAVEPOINT trans'.$this->transactionCounter);
        return $this->transactionCounter >= 0;
    }

    public function commit()
    {
        if (!--$this->transactionCounter) {
            return parent::commit();
        }
        return $this->transactionCounter >= 0;
    }

    public function rollback()
    {
        if (--$this->transactionCounter) {
            $this->exec('ROLLBACK TO trans'.($this->transactionCounter + 1));
            return true;
        }
        return parent::rollback();
    }
    
}
?>