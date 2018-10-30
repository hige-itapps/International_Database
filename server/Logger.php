<?php
/* This class is used to log information to a log file specified in config.ini. */
class Logger
{
    private $log_directory; //the root directory for log files to be written to
    private $timezone; //the server's timezone

    /* Constructior retrieves configurations */
    public function __construct(){
        $config_url = dirname(__FILE__).'/../config.ini'; //set config file url
        $settings = parse_ini_file($config_url); //get all settings
        $this->log_directory = dirname(__FILE__) ."/..".$settings["log_directory"]; //get the absolute path to the log directory
        $this->timezone = $settings["timezone"];
        date_default_timezone_set($this->timezone); //set the local time zone
    }

    /* A wrapper for the logging method for outputting errors */
    public function logError($message, $user, $callLocation, $shouldReturnMessage){
        return $this->writeLog("Error", $message, $user, $callLocation, $shouldReturnMessage);
    }

    /* A wrapper for the logging method for outputting standard information */
    public function logInfo($message, $user, $callLocation){
        return $this->writeLog("Info", $message, $user, $callLocation, false);
    }

    /* A wrapper for the logging method for outputting debug information */
    public function logDebug($message, $user, $callLocation){
        return $this->writeLog("Debug", $message, $user, $callLocation, false);
    }

    /* The actual method that writes information to the log file
    Takes the log type, message, user id (BroncoNetID), and calling file's location as arguments to print out, and a 'return generic error message' boolean
    Generates a random id to correspond with the logged information
    If the error message boolean is false, only return the generated ID. Otherwise return a generic error message which includes the generated ID and datetime.*/
    private function writeLog($logType, $message, $user, $callLocation, $shouldReturnMessage){
        //$logID = substr(md5(microtime()),rand(0,26),6); //generate a random 6 character string (found at https://stackoverflow.com/questions/5438760/generate-random-5-characters-string)
        $logID = substr(str_shuffle(MD5(microtime())), 0, 6); //generate a random 6 character string (found at https://stackoverflow.com/questions/4356289/php-random-string-generator)
        $weekName = date("d");
        $monthName = date("M");
        $yearName = date("Y");
        $fullLogMessage = "[".$logType."]".PHP_EOL
            ."id: ".$logID.PHP_EOL
            ."time: ".date("Y/m/d")." ".date("h:i:s").PHP_EOL
            ."user: ".$user.PHP_EOL
            ."location: ".$callLocation.PHP_EOL
            ."message: ".$message.PHP_EOL.PHP_EOL;
        
        if (!is_dir($this->log_directory)) { //create uploads directory if not yet created
            mkdir($this->log_directory);
        }
        if (!is_dir($this->log_directory."/".$yearName)) { //create year subdirectory if not yet created
            mkdir($this->log_directory."/".$yearName);
        }
        if (!is_dir($this->log_directory."/".$yearName."/".$monthName)) { //create month subdirectory if not yet created
            mkdir($this->log_directory."/".$yearName."/".$monthName);
        }
        $logTo = $this->log_directory."/".$yearName."/".$monthName."/".$weekName.".log"; //full location to print log file to

        if(file_put_contents($logTo, $fullLogMessage, FILE_APPEND)) {
            if(!$shouldReturnMessage){
                return $logID;
            }else{
                return "This error been logged with id '".$logID."' at '".date("Y/m/d")." ".date("h:i:s")."'. If this problem persists, please notify the system admin of this error.".PHP_EOL;
            }
        }
        else {return -1;}
    }
}
?>
