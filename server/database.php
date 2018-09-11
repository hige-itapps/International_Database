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

        public function getAllUsersProfiles()
        {
            $this->sql = $this->conn->prepare("Select * FROM users");
            $this->sql->execute();
            $res = $this->sql->fetchAll(PDO::FETCH_ASSOC); //save basic user data
        }

        public function getUserSummary()
        {

        }

        public function getUserProfile()
        {

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