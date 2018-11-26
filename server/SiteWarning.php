<?php
/* This class deals with site warnings. It simply is used to display one in html if one is specified in the database.*/

/*For DB connection*/
include_once(dirname(__FILE__) . "/DatabaseHelper.php");

class SiteWarning
{
	private $siteWarning; //the site warning message

	/* Pass in an initialized DatabaseHelper object to retrieve the correct info */
	public function __construct($database){
		$this->siteWarning = $database->getSiteWarning();
	}
	
	/*Render the site warning banner if the warning exists*/
	public function showIfExists()
	{
		if(isset($this->siteWarning) && trim($this->siteWarning) !== ''){
			echo '<div id="site-warning-banner">';
			echo '<p>'.$this->siteWarning.'</p>';
			echo '</div>';
		}
	}
}
?>
