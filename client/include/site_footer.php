<?php 
	$config_url = dirname(__FILE__).'/../../config.ini'; //set config file url
	$settings = parse_ini_file($config_url); //get all settings		
	$mailAddress = $settings["mail_address"]; //public contact email address
?>
<div role="contentinfo" class="page-footer container-fluid">
	<div class="row">
		<div class="col-md-4">
			<h2>Questions? Contact <a href="mailto:<?php echo $mailAddress;?>?Subject=Global%20Expertise%20Database%20Question" target="_top"><?php echo $mailAddress;?></a> for help.</h2>
			<form id="loginForm" method="post" action="?login">
				<input type="hidden" name="loginUser" value="login" /> 
				<input type="submit" class="btn btn-link" id="loginSub" name="loginSub" value="Admin Login" />
			</form>
		</div>
		<div class="col-md-4"> 
			<h1>© <?php echo date("Y"); //always set copyright notice to current year ?> All rights reserved.</h1>
		</div>
		<div class="col-md-4 address-info">
			<h1>Haenicke Institute for Global Education</h1>
			<h2>Western Michigan University</h2>
			<h2>1903 W Michigan Ave</h2>
			<h2>Kalamazoo MI 49008-5245 USA</h2>
		</div>
	</div>
</div>