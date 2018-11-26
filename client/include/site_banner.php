<div role="banner" class="page-header container-fluid">

	<a id="mainContentLink" href="#MainContent">Jump To Main Content</a>
	
	<div class="row">
		<div class="col-md-4">
			<img src="../images/WMU.png" alt="WMU Logo - The Letter W" class="logo" />
			<h1 class="HomeText">WMU Global Expertise Database</h1>
			<h2 class="HomeText">Haenicke Institute for Global Education</h2>
		</div>
		<div class="col-md-4"> 
		</div>
		<div class="col-md-4">
			<a href="/" class="btn btn-home">Home</a>
			<?php
				if(isset($CASbroncoNetID)){
					?>
					<form id="logoutForm" method="post" action="?logout=">
						<input type="hidden" name="logoutUser" value="logout" /> 
						<input type="submit" class="btn btn-home" id="logoutSub" name="logoutSub" value="Logout" />
					</form>
					<?php
				}
			?>
		</div>
	</div>
	
</div>
