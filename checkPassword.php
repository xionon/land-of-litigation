<?php
	session_start();
?>
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<?php
	include_once("user.inc.php");
	
	if ( !isset($_POST['username']) || !isset($_POST['password']))
	{
		header("Refresh:0; URL=login.php");
	}

	$localuser = new user($_POST['username'],$_POST['password']);
	if ($localuser->isLoggedIn())
	{
		$_SESSION['user'] = serialize($localuser);
		session_write_close();
		header("Refresh:0;URL=userProfile.php");
	}
	else
	{
		session_write_close();
		header("Refresh:3;URL=login.php");
		echo "There is a problem with your Username or Password. You are being redirected
		 to the login page. <br />
		 If your browser does not support this or you are not redirected in 3 seconds,
		 <a href = \"login.php\">click here</a>";
	}
?>


</body>
</html>