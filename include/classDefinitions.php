<?php


/* Holds information for a final report; these fields should be mostly self-explanatory */
	if(!class_exists('FinalReport')){
		class FinalReport{
			public $appID;
			public $date;				// (DATE)
			public $travelFrom;			// (DATE)
			public $travelTo;			// (DATE)
			public $activityFrom;		// (DATE)
			public $activityTo;			// (DATE)
			public $amountAwardedSpent; // (DECIMAL)
			public $projectSummary;		// (STRING)
			public $status;				// approved, denied, on hold, pending, etc. (STRING)
			
			/*Constructor; just pass in the report array received from the database call*/
			public function __construct($reportInfo) {
				$this->appID = $reportInfo[0];
				$this->date = $reportInfo[1];
				$this->travelFrom = $reportInfo[2];
				$this->travelTo = $reportInfo[3];
				$this->activityFrom = $reportInfo[4];
				$this->activityTo = $reportInfo[5];
				$this->amountAwardedSpent = $reportInfo[6];
				$this->projectSummary = $reportInfo[7];
				$this->status = $reportInfo[8];
			}
		}
	}

	/* Holds information for an application; these fields should be mostly self-explanatory */
	if(!class_exists('Application')){
		class Application{
			public $id; 				// id of application (INT)
			public $broncoNetID;		// applicant's broncoNetID (STRING)
			public $dateSubmitted;		// date submitted (DATE)
			public $nextCycle; 			// true=submitted for next cycle, false=current (BOOLEAN)
			public $name;				// (STRING)
			public $email;				// (STRING)
			public $department;			// (STRING)
			public $deptChairEmail;		// (STRING)
			public $travelFrom;			// (DATE)
			public $travelTo;			// (DATE)
			public $activityFrom;		// (DATE)
			public $activityTo;			// (DATE)
			public $title;				// title of activity (STRING)
			public $destination;		// (STRING)
			public $amountRequested;	// (DECIMAL)	
			public $purpose1;			// is research (BOOLEAN)
			public $purpose2;			// is conference (BOOLEAN)
			public $purpose3;			// is creative activity (BOOLEAN)
			public $purpose4;			// is other event text (STRING)
			public $otherFunding;		// (STRING)
			public $proposalSummary;	// (STRING)
			public $goal1;				// (BOOLEAN)
			public $goal2;				// (BOOLEAN)
			public $goal3;				// (BOOLEAN)
			public $goal4;				// (BOOLEAN)
			public $deptChairApproval;	// (STRING)
			public $amountAwarded; 		// (DECIMAL)
			public $status;				// approved, denied, on hold, pending, etc. (STRING)

			public $budget; 			// (ARRAY of budget items), must be received separately
			
			/*Constructor(for everything except budget); just pass in the application array received from the database call*/
			public function __construct($appInfo) {
				$this->id = $appInfo[0]; 
				$this->broncoNetID = $appInfo[1];
				$this->dateSubmitted = $appInfo[2];
				$this->nextCycle = $appInfo[3];
				$this->name = $appInfo[4];
				$this->email = $appInfo[5];
				$this->department = $appInfo[6];
				$this->deptChairEmail = $appInfo[7];
				$this->travelFrom = $appInfo[8];
				$this->travelTo = $appInfo[9];
				$this->activityFrom = $appInfo[10];
				$this->activityTo = $appInfo[11];
				$this->title = $appInfo[12];
				$this->destination = $appInfo[13];
				$this->amountRequested = $appInfo[14];
				$this->purpose1 = $appInfo[15];
				$this->purpose2 = $appInfo[16];
				$this->purpose3 = $appInfo[17];
				$this->purpose4 = $appInfo[18];
				$this->otherFunding = $appInfo[19];
				$this->proposalSummary = $appInfo[20];
				$this->goal1 = $appInfo[21];
				$this->goal2 = $appInfo[22];
				$this->goal3 = $appInfo[23];
				$this->goal4 = $appInfo[24];
				$this->deptChairApproval = $appInfo[25];
				$this->amountAwarded = $appInfo[26];
				$this->status = $appInfo[27];
			}
		}
	}

?>