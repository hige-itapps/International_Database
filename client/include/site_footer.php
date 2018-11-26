<div role="contentinfo" class="page-footer container-fluid">
	<div class="row">
		<div class="col-md-4">
		</div>
		<div class="col-md-4"> 
			<h1 class="FooterText">© <?php echo date("Y"); //always set copyright notice to current year ?> All rights reserved.</h1>
		</div>
		<div class="col-md-4">
			<form id="loginForm" method="post" action="?login">
				<input type="hidden" name="loginUser" value="login" /> 
				<input type="submit" class="btn btn-link" id="loginSub" name="loginSub" value="Admin Login" />
			</form>
		</div>
	</div>
</div>