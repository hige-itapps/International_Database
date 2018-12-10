<?php
/*This class is used to save and send emails.*/

/*Get DB connection*/
include_once(dirname(__FILE__) . "/DatabaseHelper.php");

/*Use PHP mailer functions*/
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require dirname(__FILE__) . '/../PHPMAILER/src/PHPMailer.php';
require dirname(__FILE__) . '/../PHPMAILER/src/SMTP.php';

if (!class_exists('PHPMailer'))
	require_once dirname(__FILE__) . '/../PHPMAILER/src/Exception.php';
if (!class_exists('PHPMailer'))
	require_once dirname(__FILE__) . '/../PHPMAILER/src/PHPMailer.php';
if (!class_exists('PHPMailer'))
	require_once dirname(__FILE__) . '/../PHPMAILER/src/SMTP.php';

/*Logger*/
include_once(dirname(__FILE__) . "/Logger.php");

class EmailHelper
{
	private $thisLocation; //get current location of file for logging purposes;
	private $logger; //for logging to files
	private $siteURL; //public URL of site
	private $mailHost; //mail server information from config.ini
	private $mailAddress; //public address
	private $mailUsername; //private login username
	private $mailPassword;
	private $mailPort;
	private $defaultSubject; //the default subject line for when it isn't specified
	private $customFooter; //custom email footer to be attached to the bottom of every sent message

	/* Constructior retrieves configurations and initializes private vars */
	public function __construct($logger){
		$this->thisLocation = dirname(__FILE__).DIRECTORY_SEPARATOR.basename(__FILE__);
		$this->logger = $logger;
		$config_url = dirname(__FILE__).'/../config.ini'; //set config file url
		$settings = parse_ini_file($config_url); //get all settings		
		$this->siteURL =  $settings["site_url"]; //load public url

		$this->mailHost = $settings["mail_host"]; //load mail host
		$this->mailAddress = $settings["mail_address"]; //load mail address
		$this->mailUsername = $settings["mail_username"]; //load mail username
		$this->mailPassword = $settings["mail_password"]; //load mail password
		$this->mailPort = $settings["mail_port"]; //load mail port number

		$this->defaultSubject = "Global Expertise Database Update";
		//skip a line, give a bold contact address, then skip a line and add a thank you message
		$this->customFooter = PHP_EOL.PHP_EOL."<strong>If you need help or more information, please reply to this message, or send a new message to ".$this->mailAddress.".</strong>".PHP_EOL.PHP_EOL.
		"Thank you,".PHP_EOL."The Haenicke Institute".PHP_EOL."Western Michigan University";
	}

	//Send an email to a specific address, with a custom message and subject. If the subject is left blank, a default one is prepared instead.
	//NOTE- must save to the database first! Use the appID to save it correctly.
	public function customEmail($toAddress, $customMessage, $customSubject, $CASbroncoNetID) {
		$this->logger->logInfo("Sending Email", $CASbroncoNetID, $this->thisLocation);
		
		$data = array(); // array to pass back data

		$customSubject = trim($customSubject); //remove surrounding spaces
		if($customSubject == null || $customSubject === ''){//it's blank, so just use the default subject
			$customSubject = $this->defaultSubject;
		}

		$fullMessage = $customMessage . $this->customFooter; //combine everything

		$database = new DatabaseHelper($this->logger); //database helper object used for some verification and insertion

		$saveResult = $database->saveEmail($toAddress, $customSubject, $fullMessage); //try to save the email message
		$data["saveSuccess"] = $saveResult; //save it to return it later
		$data["sendSuccess"] = false; //initialize to false, set to true if it sends correctly

		if($saveResult === true){//if it saved, then try to send it
			//insert <br>s where newlines are so the message renders correctly in email clients
			$fullMessage = nl2br($fullMessage);

			$mail = new PHPMailer(true); //set exceptions to true
			try{
				//Server settings
				//$mail->SMTPDebug = 2;                                 // Enable verbose debug output
				$mail->isSMTP();                                      // Set mailer to use SMTP
				$mail->Host = $this->mailHost;							  // Specify main and backup SMTP servers
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				$mail->Username = $this->mailUsername;				      // SMTP username
				$mail->Password = $this->mailPassword;	                  // SMTP password
				$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
				$mail->Port = $this->mailPort;                              // TCP port to connect to

				//Recipients
				//$mail->AddReplyTo($this->mailAddress, 'Reply to name');
				$mail->setFrom($this->mailAddress, 'Mailer');
					
				//Content
				$mail->isHTML(true);                                  // Set email format to HTML
				$mail->addAddress($toAddress);
				$mail->Subject = $customSubject;
				$mail->Body    = $fullMessage;

				$data["sendSuccess"] = $mail->send(); //notify of successful sending of message (or unsuccessful if it fails)
				if(!$data["sendSuccess"]){ //error
					$errorMessage = $this->logger->logError("Email message could not be sent: ".$mail->ErrorInfo, $toAddress, $this->thisLocation, true);
					$data["sendError"] = "Error: Email message could not be sent. ".$errorMessage;
				}
			}
			catch (phpmailerException $e) { //catch phpMailer specific exceptions
				$errorMessage = $this->logger->logError("Email message could not be sent: ".$e->errorMessage(), $toAddress, $this->thisLocation, true);
				$data["sendError"] = "Error: Email message could not be sent. ".$errorMessage;
			}
			catch (Exception $e) {
				$errorMessage = $this->logger->logError("Email message could not be sent: ".$e->getMessage(), $toAddress, $this->thisLocation, true);
				$data["sendError"] = "Error: Email message could not be sent. ".$errorMessage;
			}
		}
		else{
			$errorMessage = $this->logger->logError("Email could not be saved to the database.", $toAddress, $this->thisLocation, true);
			$data["saveError"] = "Error: Email could not be saved to the database. ".$errorMessage;
		}

		$database->close(); //close database connections

		return $data; //pass back the data array
	}

	//The email to send to the profile owner to let them know of their new confirmation code
	public function codeConfirmationSendEmail($toAddress, $code, $CASbroncoNetID){
		$subject = "Global Expertise Database - Confirmation Code";

		$body = "Greetings,
			Here is your confirmation code to create/update your profile in the WMU Global Expertise Database:

			#code

			Please paste this code into the box provided on the Profile Confirmation page. You can find this page at ".$this->siteURL.".
			This code will expire in 24 hours, or once your profile has been created/updated.
			If you did not choose to create/update a profile on our site, please ignore this message.";
		$body = str_replace("#code", nl2br($code), $body); //insert the code into the message

		return $this->customEmail($toAddress, $body, $subject, $CASbroncoNetID);
	}

	//The email to send to the profile owner to let them know that their pending profile was approved.
	public function profileApprovedEmail($toAddress, $message, $CASbroncoNetID){
		$subject = "Global Expertise Database - Pending Profile Approved";
		return $this->customEmail($toAddress, $message, $subject, $CASbroncoNetID);
	}

	//The email to send to the profile owner to let them know that their pending profile was denied.
	public function profileDeniedEmail($toAddress, $message, $CASbroncoNetID){
		$subject = "Global Expertise Database - Pending Profile Denied";
		return $this->customEmail($toAddress, $message, $subject, $CASbroncoNetID);
	}

	//The email to send to the profile owner to let them know that their profile was deleted.
	public function profileDeleteEmail($toAddress, $message, $CASbroncoNetID){
		$subject = "Global Expertise Database - Profile Deleted";
		return $this->customEmail($toAddress, $message, $subject, $CASbroncoNetID);
	}

	//The email to send to all profile owners every X amount of time to remind them to update their profiles if possible
	public function siteReminderEmail($toAddress, $name, $CASbroncoNetID){
		$subject = "Global Expertise Database - Automatic Website Reminder";

		$body = "Dear #name,
			This is a bi-annual automated message to remind you to keep your profile up-to-date on our site at ".$this->siteURL.".".PHP_EOL.
			"If you wish, you may update your information or remove your profile from the database by visiting your profile on our site and clicking the 'EDIT PROFILE' button.";

		$body = str_replace("#name", nl2br($name), $body); //insert the code into the message

		return $this->customEmail($toAddress, $body, $subject, $CASbroncoNetID);
	}
}
	
?>