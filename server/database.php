<?php

/*Database helper object for transactions*/
if(!class_exists('DatabaseHelper')){
    class DatabaseHelper
    {
        private $conn; //pdo database connection object
        private $sql; //pdo prepared statement
        private $config_url; //url of config file
        private $settings; //configuration settings

        /* Constructior retrieves configurations and sets up a connection */
        public function __construct()
        {
            $this->config_url = dirname(__FILE__).'/../config.ini'; //set config file url
            $this->settings = parse_ini_file($this->config_url); //get all settings
            $this->connect();
        }

        public function getConnection()
        {
            return $this->conn;
        }

        public function close()
        {
            $this->sql = null;
            $this->conn = null;
        }


        /* Specific transactions */

        /* For Users */

        public function getAllUsersSummaries()
        {
			$this->sql = $this->conn->prepare("Select * FROM users");
			$this->sql->execute();
            return $this->sql->fetchAll(PDO::FETCH_ASSOC); //return names as keys
        }

        public function getUserSummary($userID)
        {
            $this->sql = $this->conn->prepare("Select * FROM users WHERE id = :id LIMIT 1");
            $this->sql->bindParam(':id', $userID);
			$this->sql->execute();
            return $this->sql->fetch(PDO::FETCH_ASSOC); //return names as keys
        }

        public function getUserProfile($userID)
        {
            //initialize user object
            $user = null;

            //start with summary
            $this->sql = $this->conn->prepare("Select * FROM users WHERE id = :id LIMIT 1");
            $this->sql->bindParam(':id', $userID);
			$this->sql->execute();
            $user = $this->sql->fetch(PDO::FETCH_ASSOC); //set user to summary values to start with

            //get user's issues of expertise
            $this->sql = $this->conn->prepare("SELECT i.issue FROM users u
                INNER JOIN users_issues ui ON u.id = ui.user_id
                INNER JOIN issues i ON ui.issue_id = i.id
            WHERE u.id = :id");
            $this->sql->bindParam(':id', $userID);
            $this->sql->execute();
            $user["issues_expertise"] = $this->sql->fetchAll(PDO::FETCH_COLUMN); //append issues as an array

            //get user's regions of expertise
            $this->sql = $this->conn->prepare("SELECT r.region FROM users u
                INNER JOIN users_regions ur ON u.id = ur.user_id
                INNER JOIN regions r ON ur.region_id = r.id
            WHERE u.id = :id");
            $this->sql->bindParam(':id', $userID);
            $this->sql->execute();
            $user["regions_expertise"] = $this->sql->fetchAll(PDO::FETCH_COLUMN);

            //get user's countries of expertise
            $this->sql = $this->conn->prepare("SELECT c.country_name FROM users u
                INNER JOIN users_country_expertise uce ON u.id = uce.user_id
                INNER JOIN countries c ON uce.country_id = c.id
            WHERE u.id = :id");
            $this->sql->bindParam(':id', $userID);
            $this->sql->execute();
            $user["countries_expertise"] = $this->sql->fetchAll(PDO::FETCH_COLUMN);

            //get user's language proficiencies
            $this->sql = $this->conn->prepare("SELECT l.name AS language, lp.proficiency_level AS proficiency FROM users u
                INNER JOIN users_languages ul ON u.id = ul.user_id
                INNER JOIN languages l ON ul.language_id = l.id
                INNER JOIN language_proficiencies lp ON ul.proficiency_id = lp.id
            WHERE u.id = :id");
            $this->sql->bindParam(':id', $userID);
            $this->sql->execute();
            $user["languages"] = $this->sql->fetchAll(PDO::FETCH_ASSOC);

            //get user's country experience
            $this->sql = $this->conn->prepare("SELECT c.country_name AS country, ce.experience AS experience, oce.experience AS other_experience FROM users_country_experience uce 
                INNER JOIN countries c ON uce.country_id = c.id 
                LEFT JOIN country_experience ce ON uce.experience_id = ce.id
                LEFT JOIN other_country_experience oce ON uce.other_experience_id = oce.id
            WHERE uce.user_id = :id");
            $this->sql->bindParam(':id', $userID);
            $this->sql->execute();
            $user_country_experience /*$user["country_experience"]*/ = $this->sql->fetchAll(PDO::FETCH_ASSOC); //save as a temporary array
            $user["countries_experience"] = []; //initialize country experience array
            foreach($user_country_experience as $experience) {
                if (!array_key_exists($experience["country"], $user["countries_experience"])) { //initialize country experience array for a specific country if not yet made
                    $user["countries_experience"][$experience["country"]]["experience"] = []; //create sub array for experiences
                    $user["countries_experience"][$experience["country"]]["other_experience"] = ""; //create empty string for possible other experience
                }
                if (!empty($experience["experience"])) {$user["countries_experience"][$experience["country"]]["experience"][] = $experience["experience"];} //add experience to country's array
                if (!empty($experience["other_experience"])) {$user["countries_experience"][$experience["country"]]["other_experience"] = $experience["other_experience"];} //add other experience to country's array
            }

            return $user;
        }

        /* For Static Data */

        public function getIssues()
        {
            $this->sql = $this->conn->prepare("Select issue FROM issues");
			$this->sql->execute();
            return $this->sql->fetchAll(PDO::FETCH_COLUMN); //return names only
        }

        public function getCountries()
        {
            $this->sql = $this->conn->prepare("Select country_name FROM countries");
			$this->sql->execute();
            return $this->sql->fetchAll(PDO::FETCH_COLUMN); //return names only
        }

        public function getRegions()
        {
            $this->sql = $this->conn->prepare("Select region FROM regions");
			$this->sql->execute();
            return $this->sql->fetchAll(PDO::FETCH_COLUMN); //return names only
        }




        /* Establishes an sql connection to the database, and returns the object; MAKE SURE TO SET OBJECT TO NULL WHEN FINISHED */
        private function connect()
        {
            try 
            {
                $this->conn = new AtomicPDO("mysql:host=" . $this->settings["hostname"] . ";dbname=" . $this->settings["database_name"] . ";charset=utf8", $this->settings["database_username"], 
                    $this->settings["database_password"], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // set the PDO error mode to exception
                $this->conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
            }
            catch(PDOException $e)
            {
                echo "Connection failed: " . $e->getMessage();
            }
        }
            
    }
}


/*allow for atomic transactions (to ensure one insert only occurs when another has succeeded, so that either both or neither of them succeed)
found at http://php.net/manual/en/pdo.begintransaction.php*/
if(!class_exists('AtomicPDO')){
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
}

?>