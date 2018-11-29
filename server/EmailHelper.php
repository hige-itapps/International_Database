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
	private $logger; //for logging to files
	private $mailHost; //mail server information from config.ini
	private $mailUsername;
	private $mailNoReply;
	private $mailPassword;
	private $mailPort;
	private $defaultSubject; //the default subject line for when it isn't specified
	private $customFooter; //custom email footer to be attached to the bottom of every sent message

	/* Constructior retrieves configurations and initializes private vars */
	public function __construct($logger){
		$this->logger = $logger;
		$config_url = dirname(__FILE__).'/../config.ini'; //set config file url
		$settings = parse_ini_file($config_url); //get all settings		
		$this->mailHost = $settings["mail_host"]; //load mail host
		$this->mailUsername = $settings["mail_username"]; //load mail username
		$this->mailNoReply = $settings["mail_noreply"]; //load mail no-reply address
		$this->mailPassword = $settings["mail_password"]; //load mail password
		$this->mailPort = $settings["mail_port"]; //load mail port number

		$this->defaultSubject = "International Scholars Database Update";
		$this->customFooter = "
		
		<strong>Please do not reply to this email, this account is not being monitored.
		If you need more information, please contact the International Scholars Database administrator.</strong>";
	}

	//Send an email to a specific address, with a custom message and subject. If the subject is left blank, a default one is prepared instead.
	//NOTE- must save to the database first! Use the appID to save it correctly.
	public function customEmail($toAddress, $customMessage, $customSubject, $CASbroncoNetID) {
		$this->logger->logInfo("Sending Email", $CASbroncoNetID, dirname(__FILE__));
		
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
				$mail->setFrom($this->mailUsername, 'Mailer');
				$mail->addReplyTo($this->mailNoReply, 'No-Reply');
					
				//Content
				$mail->isHTML(true);                                  // Set email format to HTML
				$mail->addAddress($toAddress);
				$mail->Subject = $customSubject;
				$mail->Body    = $fullMessage;

				$data["sendSuccess"] = $mail->send(); //notify of successful sending of message (or unsuccessful if it fails)
				if(!$data["sendSuccess"]){ //error
					$errorMessage = $this->logger->logError("Email message could not be sent: ".$mail->ErrorInfo, $toAddress, dirname(__FILE__), true);
					$data["sendError"] = "Error: Email message could not be sent. ".$errorMessage;
				}
			}
			catch (phpmailerException $e) { //catch phpMailer specific exceptions
				$errorMessage = $this->logger->logError("Email message could not be sent: ".$e->errorMessage(), $toAddress, dirname(__FILE__), true);
				$data["sendError"] = "Error: Email message could not be sent. ".$errorMessage;
			}
			catch (Exception $e) {
				$errorMessage = $this->logger->logError("Email message could not be sent: ".$e->getMessage(), $toAddress, dirname(__FILE__), true);
				$data["sendError"] = "Error: Email message could not be sent. ".$errorMessage;
			}
		}
		else{
			$errorMessage = $this->logger->logError("Email could not be saved to the database.", $toAddress, dirname(__FILE__), true);
			$data["saveError"] = "Error: Email could not be saved to the database. ".$errorMessage;
		}

		$database->close(); //close database connections

		return $data; //pass back the data array
	}

	//The email to send to the profile owner to let them know of their new confirmation code
	public function codeConfirmationSendEmail($toAddress, $code, $CASbroncoNetID){
		$subject = "International Scholars Database - Confirmation Code";

		$body = "Greetings,
			Here is your confirmation code to create/update your profile in the WMU International Scholars Database:

			#code

			Please paste this code into the box provided on the Profile Confirmation page. You can find this page at globalexpertise.wmich.edu.
			This code will expire in 24 hours, or once your profile has been created/updated.
			If you did not choose to create/update a profile on our site, please ignore this message.";
		$body = str_replace("#code", nl2br($code), $body); //insert the code into the message

		return $this->customEmail($toAddress, $body, $subject, $CASbroncoNetID);
	}

	//The email to send to the profile owner to let them know that their pending profile was approved.
	public function profileApprovedEmail($toAddress, $message, $CASbroncoNetID){
		$subject = "International Scholars Database - Pending Profile Approved";
		return $this->customEmail($toAddress, $message, $subject, $CASbroncoNetID);
	}

	//The email to send to the profile owner to let them know that their pending profile was denied.
	public function profileDeniedEmail($toAddress, $message, $CASbroncoNetID){
		$subject = "International Scholars Database - Pending Profile Denied";
		return $this->customEmail($toAddress, $message, $subject, $CASbroncoNetID);
	}

	//The email to send to the profile owner to let them know that their profile was deleted.
	public function profileDeleteEmail($toAddress, $message, $CASbroncoNetID){
		$subject = "International Scholars Database - Profile Deleted";
		return $this->customEmail($toAddress, $message, $subject, $CASbroncoNetID);
	}

	//The email to send to all profile owners every X amount of time to remind them to update their profiles if possible
	public function siteReminderEmail($toAddress, $name, $CASbroncoNetID){
		$subject = "International Scholars Database - Automatic Website Reminder";

		$body = "Dear #name,
			This is an automated message to remind you to check your profile on our site at globalexpertise.wmich.edu to make sure your information is up-to-date.".PHP_EOL.
			"If you wish, you may update your information or remove your profile from the database by visiting your profile on our site and clicking the 'EDIT PROFILE' button.";

		$body = str_replace("#name", nl2br($name), $body); //insert the code into the message

		return $this->customEmail($toAddress, $body, $subject, $CASbroncoNetID);
	}
}
	
?>