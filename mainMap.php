<?php
	session_start();
	include_once('user.inc.php');
	include_once('sidebar.inc.php');
	include_once('logoutTask.inc.php');
	$thisSite = "mainMap";
	
	$_SESSION['user'] = serialize($localuser);
	session_write_close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Places to go!</title>
</head>

<body>
<?php outputSidebar($thisSite); ?>

The Deep Cave<br/>
The Dark Forrest<br/>
<a href="TheBlueRiver.php">The Blue River</a><br/>
<a href="Town.php">The Town</a><br/>
</body>
</html>
