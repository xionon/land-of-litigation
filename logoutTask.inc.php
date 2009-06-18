<?php
	//This snippet of code checks to be sure the user has logged in; if not, they are redirected to the login page.
	if (!isset($_SESSION["user"]))
	{
		header("Refresh: 0; URL=login.php");
	}
	
	//This part unserializes the user
	$localuser = unserialize($_SESSION["user"]);

	//If there is a task set, perform it (i.e., logout)
	if (isset($_GET["task"]))
	{
		if ($_GET["task"] == 'logout')
		{
			
			$localuser->log_out();
			unset($localuser);
			unset($_SESSION["user"]);
			header("Refresh:0; URL=index.php");
		}
	}
?>