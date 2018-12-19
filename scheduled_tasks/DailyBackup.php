<?php

/*Get DB connection*/
include_once(dirname(__FILE__) . "/../server/DatabaseHelper.php");

/*Logger*/
include_once(dirname(__FILE__) . "/../server/Logger.php");

$logger = new Logger(); //for logging to files
$database = new DatabaseHelper($logger); //database helper object used for some verification and insertion

$thisLocation = dirname(__FILE__).DIRECTORY_SEPARATOR.basename(__FILE__); //get current location of file for logging purposes;

/*
\wamp64\bin\mariadb\mariadb10.2.8\bin\mysqldump.exe --user="exampleuser" --password="exampleuser" --host="141.218.158.65" --no-create-info --no-create-db --skip-triggers --replace international users users_codes users_country_experience users_country_expertise users_issues users_languages users_regions variables administrators emails > C:\wamp64\www\International_Database\backups\file.sql
*/
//create and execute backup command (command line)

$config_url = dirname(__FILE__).'/../config.ini'; //set config file url
$settings = parse_ini_file($config_url); //get all settings
$mysqldump_path = $settings["mysqldump_path"]; //path to appropriate mysqldump command
$backup_directory = $settings["backup_directory"].DIRECTORY_SEPARATOR; //location to save backups to (full path)
$number_of_backups_saved = $settings["number_of_backups_saved"]; //how many backup files should exist in total

$backup_filename = "backup_".time().".sql"; //create a filename with the current timestamp in it

//check if a file should be replaced (if number of files starting with 'backup_' and ending with '.sql' >= number_of_backups_saved), and if so, then delete the oldest one
$filenames = array();
foreach (glob($backup_directory."backup_*.sql") as $file) {
  $filenames[] = basename($file);
}
if(sizeof($filenames) >= $number_of_backups_saved){
    //find the file with the oldest timestamp, start with the first one in the array
    $oldest = $filenames[0];
    for($i = 1; $i < sizeof($filenames); $i++){
        $oldestTimestamp = (int)preg_replace("/backup_|.sql/", "", $oldest);
        $fileTimestamp = (int)preg_replace("/backup_|.sql/", "", $filenames[$i]);
        if($fileTimestamp < $oldestTimestamp){$oldest = $filenames[$i];} //set this file as the oldest one if the timestamp is smaller
    }
    //now finally, remove the oldest file
    unlink($backup_directory.$oldest);
}


//database credentials
$database_name = $settings["database_name"];
$database_username = $settings["database_username"];
$database_password = $settings["database_password"];
$hostname = $settings["hostname"];

$mysqldumpCommand = $mysqldump_path." --user=".$database_username." --password=".$database_password." --host=".$hostname." --single-transaction --no-create-info --no-create-db --skip-triggers --replace ".$database_name." users users_codes users_country_experience users_country_expertise users_issues users_languages users_regions variables administrators emails > ".$backup_directory.$backup_filename;

//echo $mysqldumpCommand;
$backupResults = exec($mysqldumpCommand); //run the backup command, save any results
$logger->logInfo('backing up database to file "'.$backup_filename.'", results: '.$backupResults, 'automatic daily backup', $thisLocation);


$database->saveDatabaseLastBackedUpTime(); //save the timestamp to the database

?>