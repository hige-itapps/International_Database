<?php
/*
    Database helper object for transactions
    This class is built to support all necessary queries to the database. It is not built to verify that incoming data is valid however, so this should be done elsewhere.
*/

class DatabaseHelper
{
    private $conn; //pdo database connection object
    private $sql; //pdo prepared statement
    private $config_url; //url of config file
    private $settings; //configuration settings

    /* Constructior retrieves configurations and sets up a connection */
    public function __construct(){
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
    /* Just quick summaries; only data from the users table- only return the primary email adresses. Also only return approved profiles unless specified otherwise with $approvedOnly = false */
    public function getAllUsersSummaries($approvedOnly = true){
        $query = "Select u.id, u.firstname, u.lastname, u.affiliations, COALESCE(u.alternate_email, u.login_email) as email, u.phone, u.social_link, u.issues_expertise_other, u.regions_expertise_other, u.countries_expertise_other FROM users u";
        if($approvedOnly){$query.=" WHERE approved = 1";} //only approved if necessary
        $this->sql = $this->conn->prepare($query);
        $this->sql->execute();
        return $this->sql->fetchAll(PDO::FETCH_ASSOC); //return names as keys
    }

    public function getUserSummary($userID, $approvedOnly = true){
        $query = "Select u.id, u.firstname, u.lastname, u.affiliations, COALESCE(u.alternate_email, u.login_email) as email, u.phone, u.social_link, u.issues_expertise_other, u.regions_expertise_other, u.countries_expertise_other FROM users u WHERE u.id = :id";
        if($approvedOnly){$query.=" AND approved = 1";} //only approved if necessary
        $query.= " LIMIT 1"; //only return 1 result
        $this->sql = $this->conn->prepare($query);
        $this->sql->bindParam(':id', $userID);
        $this->sql->execute();
        return $this->sql->fetch(PDO::FETCH_ASSOC); //return names as keys
    }

    /* Full user profiles- still only return the primary email addresses. Also only return approved profiles unless specified otherwise with $approvedOnly = false */
    public function getUserProfile($userID, $approvedOnly = true){
        //initialize user object
        $user = null;

        //start with summary
        $query = "Select u.id, u.firstname, u.lastname, u.affiliations, COALESCE(u.alternate_email, u.login_email) as email, u.phone, u.social_link, u.issues_expertise_other, u.regions_expertise_other, u.countries_expertise_other FROM users u WHERE u.id = :id";
        if($approvedOnly){$query.=" AND approved = 1";} //only approved if necessary
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
            $this->sql = $this->conn->prepare("SELECT c.id AS country_id, c.country_name AS country_name, ce.id AS experience_id, ce.experience AS experience, oce.experience AS other_experience FROM users_country_experience uce 
                INNER JOIN countries c ON uce.country_id = c.id 
                LEFT JOIN country_experience ce ON uce.experience_id = ce.id
                LEFT JOIN other_country_experience oce ON uce.other_experience_id = oce.id
            WHERE uce.user_id = :id");
            $this->sql->bindParam(':id', $userID);
            $this->sql->execute();
            $user_country_experience = $this->sql->fetchAll(PDO::FETCH_ASSOC); //save as a temporary array
            $user["countries_experience"] = []; //initialize country experience array
            foreach($user_country_experience as $experience) {
                if (!array_key_exists($experience["country_id"], $user["countries_experience"])) { //initialize country experience array for a specific country if not yet made
                    $user["countries_experience"][$experience["country_id"]]["id"] = $experience["country_id"]; //save the country's id
                    $user["countries_experience"][$experience["country_id"]]["country_name"] = $experience["country_name"]; //save the country's name
                    $user["countries_experience"][$experience["country_id"]]["experiences"] = []; //create sub array for experiences
                    $user["countries_experience"][$experience["country_id"]]["other_experience"] = ""; //create empty string for possible other experience
                }
                if (!empty($experience["experience"])) {$user["countries_experience"][$experience["country_id"]]["experiences"][] = ["id"=>$experience["experience_id"], "experience"=>$experience["experience"]];} //add experience to country's array
                if (!empty($experience["other_experience"])) {$user["countries_experience"][$experience["country_id"]]["other_experience"] = $experience["other_experience"];} //add other experience to country's array
            }
        }

        return $user;
    }

    //Insert a profile into the database. This will only work if there is a current pending code for the login email. 
    //This operation will automatically set the 'approved' field to false (0).
    //Error handling should be done beforehand, including verifying that a code is currently pending for the login email.
    public function insertProfile($login_email, $firstname, $lastname, $alternate_email, $affiliations, $phone, $issues_expertise_other, $regions_expertise_other, $countries_expertise_other, $social_link, $issues_expertise, $countries_expertise, $regions_expertise, $languages, $countries_experience){
        //return false;
        $retval = [];
        $newProfileID = null; // the ID of the profile just inserted

        $this->conn->beginTransaction(); //begin atomic transaction

        //insert base profile first
        $this->sql = $this->conn->prepare("INSERT INTO users(login_email, firstname, lastname, alternate_email, affiliations, phone, issues_expertise_other, regions_expertise_other, countries_expertise_other, social_link, approved)
            VALUES(:login_email, :firstname, :lastname, :alternate_email, :affiliations, :phone, :issues_expertise_other, :regions_expertise_other, :countries_expertise_other, :social_link, 0)");
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
                        $otherExperienceID = null; // the ID of the added other experience

                        $this->sql = $this->conn->prepare("INSERT INTO other_country_experience(experience) VALUES(:experience)");
                        $this->sql->bindParam(':experience', $i->other_experience);

                        if ($this->sql->execute() === TRUE){//successfully added other experience
                            $otherExperienceID = $this->conn->lastInsertId();
                        }
                        else{ //query failed
                            $this->conn->rollBack();
                            $retval["error"] = "Failed to insert other country experience.";
                            break;
                        }
                        
                        //if inserting other experience was successful
                        if(empty($retval["error"])){
                            $this->sql = $this->conn->prepare("INSERT INTO users_country_experience(user_id, country_id, other_experience_id) VALUES(:user_id, :country_id, :other_experience_id)");
                            $this->sql->bindParam(':user_id', $newProfileID);
                            $this->sql->bindParam(':country_id', $i->id);
                            $this->sql->bindParam(':other_experience_id', $otherExperienceID);

                            if ($this->sql->execute() !== TRUE){//query failed
                                $this->conn->rollBack();
                                $retval["error"] = "Failed to insert other user country experience.";
                                break;
                            }
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


    /* For Static Data */

    public function getIssues(){
        $this->sql = $this->conn->prepare("Select * FROM issues");
        $this->sql->execute();
        return $this->sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCountries(){
        $this->sql = $this->conn->prepare("Select * FROM countries");
        $this->sql->execute();
        return $this->sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRegions(){
        $this->sql = $this->conn->prepare("Select * FROM regions");
        $this->sql->execute();
        return $this->sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLanguages(){
        $this->sql = $this->conn->prepare("Select * FROM languages");
        $this->sql->execute();
        return $this->sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLanguageProficiencies(){
        $this->sql = $this->conn->prepare("Select * FROM language_proficiencies");
        $this->sql->execute();
        return $this->sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCountryExperiences(){
        $this->sql = $this->conn->prepare("Select * FROM country_experience");
        $this->sql->execute();
        return $this->sql->fetchAll(PDO::FETCH_ASSOC);
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
        $this->sql = $this->conn->prepare("Select CHARACTER_MAXIMUM_LENGTH FROM information_schema.columns WHERE table_schema = '" . $this->settings["database_name"] . "' AND table_name = 'other_country_experience' AND COLUMN_NAME = 'experience'");
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

    /* Check if a profile is currently pending (approved = 0) for a given email (returns 1 or 0). If this is the login email, check the profile associated with it, otherwise find the profile associated with the given alternate email */
    public function isProfilePending($email){
        $this->sql = $this->conn->prepare("SELECT EXISTS(SELECT 1 FROM users WHERE (login_email = :email OR login_email = (SELECT u.login_email FROM users u WHERE u.alternate_email = :email)) AND approved = 0 )");
        $this->sql->bindParam(':email', $email);
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


    /*Search functions*/

    /*Return number of users using this login email (should be 0 or 1)*/
    public function doesLoginEmailExist($testEmail){
        $this->sql = $this->conn->prepare("SELECT COUNT(*) FROM users u WHERE u.login_email = :email");
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

    /*Search the database using a wildcard term; returns all matching user summaries. Also only return approved profiles unless specified otherwise with $approvedOnly = false*/
    public function searchByWildcard($wildcard, $approvedOnly = true){
        //If no wildcard given, don't waste time doing complicated queries, just return all profiles
        if($wildcard === ""){
            return  $this->getAllUsersSummaries($approvedOnly);
        }
        
        $wildcard = '%'.$wildcard.'%'; //add percent signs to search all strings
        //Combine results from all relevant tables
        /*
        SELECT * FROM
            (SELECT COALESCE(u.alternate_email, u.login_email) AS `email` FROM users u) as e
        WHERE e.email LIKE '%sam%'
        */
        //This extra long query combines search results from all relevant tables; return only the primary email addresses
        $query = "SELECT res.id, res.firstname, res.lastname, res.affiliations, res.foundIn, COALESCE(res.alternate_email, res.login_email) as email, res.phone, res.social_link, res.issues_expertise_other, res.regions_expertise_other, res.countries_expertise_other FROM 
        (SELECT u.*, 'profile' as foundIn FROM users u
        WHERE CONCAT(u.firstname,' ', u.lastname) LIKE :wildcard
        OR COALESCE(u.alternate_email, u.login_email) LIKE :wildcard
        OR u.affiliations LIKE :wildcard
        OR u.phone LIKE :wildcard
        OR u.issues_expertise_other LIKE :wildcard
        OR u.regions_expertise_other LIKE :wildcard
        OR u.countries_expertise_other LIKE :wildcard
        OR u.social_link LIKE :wildcard
        UNION DISTINCT
        SELECT DISTINCT u.*, 'country experience' as foundIn FROM users u
        INNER JOIN users_country_experience uce ON u.id = uce.user_id
        INNER JOIN countries c ON uce.country_id = c.id
        WHERE c.country_name LIKE :wildcard
        UNION DISTINCT
        SELECT DISTINCT u.*, 'countries of expertise' as foundIn FROM users u
        INNER JOIN users_country_expertise uce ON u.id = uce.user_id
        INNER JOIN countries c ON uce.country_id = c.id
        WHERE c.country_name LIKE :wildcard
        UNION DISTINCT
        SELECT DISTINCT u.*, 'issues of expertise' as foundIn FROM users u
        INNER JOIN users_issues ui ON u.id = ui.user_id
        INNER JOIN issues i ON ui.issue_id = i.id
        WHERE i.issue LIKE :wildcard
        UNION DISTINCT
        SELECT DISTINCT u.*, 'regions of expertise' as foundIn FROM users u
        INNER JOIN users_regions ur ON u.id = ur.user_id
        INNER JOIN regions r ON ur.region_id = r.id
        WHERE r.region LIKE :wildcard
        UNION DISTINCT
        SELECT DISTINCT u.*, 'languages' as foundIn FROM users u
        INNER JOIN users_languages ul ON u.id = ul.user_id
        INNER JOIN languages l ON ul.language_id = l.id
        WHERE l.name LIKE :wildcard) res";

        if($approvedOnly) {$query.=" WHERE res.approved = 1";} //only approved if necessary

        $this->sql = $this->conn->prepare($query);
        $this->sql->bindParam(':wildcard', $wildcard);
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